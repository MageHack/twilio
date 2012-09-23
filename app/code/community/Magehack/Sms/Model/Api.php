<?php
/**
 * Mediaburst SMS Magento Integration
 *
 * Copyright © 2011 by Mediaburst Limited
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND ISC DISCLAIMS ALL WARRANTIES WITH REGARD
 * TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND
 * FITNESS. IN NO EVENT SHALL ISC BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT,
 * OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF
 * USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE
 * OF THIS SOFTWARE.
 *
 * @category  Mage
 * @package   Magehack_Sms
 * @license   http://opensource.org/licenses/isc-license.txt
 * @copyright Copyright © 2011 by Mediaburst Limited
 * @author    Lee Saferite <lee.saferite@lokeycoding.com>
 */

/**
 * API implementation class
 */
class Magehack_Sms_Model_Api extends Zend_Service_Abstract
{
    const SMS_PER_REQUEST_LIMIT = 500;

    /**
     * Reference to a config object that can provide the details needed for the API
     *
     * @var Magehack_Sms_Model_ApiConfig
     */
    protected $_config;

    /**
     * Basic constructor
     *
     * @param Magehack_Sms_Model_ApiConfig $config
     */
    public function __construct(Magehack_Sms_Helper_Data $config)
    {
        $this->_config = $config;
    }

    /**
     * Send a single SMS message
     *
     * @param string  $from     The name or number of the sender
     * @param string  $to       The mobile number of the receipient
     * @param string  $content  Message to send. Limited to 160 GMS characters
     * @param array   $extra    Addition parameters
     */
    public function sendMessage(Magehack_Sms_Model_Message $message)
    {
        return $this->sendMessages(array($message));
    }

    /**
     * Send multiple messages at once
     *
     * @param array  $messages  Array containing multiple messages
     */
    public function sendMessages(array $messages)
    {

        $this->_config->log('Messages ' . count($messages), Zend_Log::WARN);

        if (count($messages) === 0) {
            return;
        }

        if (count($messages) > self::SMS_PER_REQUEST_LIMIT) {
            throw new Magehack_Sms_Exception('Too many messages. Limit is ' . self::SMS_PER_REQUEST_LIMIT . ' per request');
        }

        $result = array(
            'pending' => array(),
            'sent'    => array(),
            'failed'  => array(),
            'errors'  => array()
        );

        $indexedMessages = array();

        $xml  = new DOMDocument('1.0', 'UTF-8');
        $root = $xml->appendChild($xml->createElement('Message'));
        $root->appendChild($xml->createElement('Key', $this->_config->getKey()));
        foreach ($messages as $message) {
            if (!$message instanceof Magehack_Sms_Model_Message) {
                $this->_config->log('Message object not expected type', Zend_Log::WARN);
                continue;
            }

            $result['pending'][$message->getId()] = $message;

            $sms = $root->appendChild($xml->createElement('SMS'));

            $from = trim($message->getFrom());
            if (!empty($from)) {
                $sms->appendChild($xml->createElement('From', $message->getFrom()));
            }

            $sms->appendChild($xml->createElement('To', $this->_formatMobileNumber($message->getTo())));
            $sms->appendChild($xml->createElement('ClientID', $message->getId()));
            $sms->appendChild($xml->createElement('Content', $message->getContent()));
            // TODO: Map additional parameters
        }

        $requestBody = $xml->saveXML();
        $this->_config->log($requestBody, Zend_Log::DEBUG);

        $client = $this->getHttpClient();
        $client->resetParameters(true);
        $client->setUri($this->_config->getSendUrl());
        $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'text/xml');
        $client->setRawData($requestBody);

        unset($requestBody);

        $response = $client->request(Zend_Http_Client::POST);

        if (!$response->isSuccessful()) {
            throw new Magehack_Sms_Exception("Problem communicating with host", $response->getStatus());
        }

        $responseBody = $response->getBody();
        $this->_config->log($responseBody, Zend_Log::DEBUG);

        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->loadXML($responseBody);

        unset($responseBody);

        $xpath = new DOMXPath($xml);

        $responseNodes = $xpath->query('//SMS_Resp');
        foreach ($responseNodes as $responseNode) {
            $clientId = $xpath->evaluate('string(./ClientID)', $responseNode);
            if (!isset($result['pending'][$clientId])) {
                $result['errors'][] = "Unexpected ClientID:" . $clientId;
                continue;
            }

            $message = $result['pending'][$clientId];
            unset($result['pending'][$clientId]);

            $to               = $xpath->evaluate('string(./To)', $responseNode);
            $messageId        = $xpath->evaluate('string(./MessageID)', $responseNode);
            $errorNumber      = $xpath->evaluate('string(./ErrNo)', $responseNode);
            $errorDescription = $xpath->evaluate('string(./ErrDesc)', $responseNode);

            if (!empty($errorNumber)) {
                try {
                    $message->getMessageId(null);
                    $message->setErrorNumber($errorNumber);
                    $message->setErrorDescription($errorDescription);
                    $message->setStatus(Magehack_Sms_Model_Message::STATUS_FAILED);
                    $message->save();
                    $result['failed'][$clientId] = $message;
                }
                catch (Exception $e) {
                    $result['errors'][] = array($e->getMessage(), $to, $errorNumber, $errorDescription);
                }
            } elseif (!empty($messageId)) {
                try {
                    $message->setMessageId($messageId);
                    $message->setErrorNumber(null);
                    $message->setErrorDescription(null);
                    $message->setStatus(Magehack_Sms_Model_Message::STATUS_SENT);
                    $message->save();
                    $result['sent'][$clientId] = $message;
                }
                catch (Exception $e) {
                    $result['errors'][] = array($e->getMessage(), $to, $messageId);
                }
            } else {
                $result['errors'][] = array($e->getMessage(), $to);
            }
        }

        $messageNodes = $xpath->query('//Message_Resp[ErrNo]');
        foreach ($messageNodes as $messageNode) {
            $errorNumber        = $xpath->evaluate('string(./ErrNo)', $messageNode);
            $errorDescription   = $xpath->evaluate('string(./ErrDesc)', $messageNode);
            $result['errors'][] = array($errorNumber, $errorDescription);
        }

        return $result;
    }

    /**
     * Return the current number of remaining credits
     *
     * @return int
     */
    public function checkCredits()
    {
        $credits = false;

        $client = $this->getHttpClient();
        $client->resetParameters(true);
        $client->setUri( str_replace( 'credit', 'balance', $this->_config->getCheckUrl() ) );
        $client->setParameterPost('key', $this->_config->getKey());

        $response = $client->request(Zend_Http_Client::POST);

        if ($response->isSuccessful()) {
            $body = $response->getBody();
            $this->_config->log($body, Zend_Log::DEBUG);
            preg_match( '/Balance: ([0-9]+\.[0-9]{2}) \(([A-Z]+)\)/', $body, $matches );
            
            switch( $matches[2] ) {
              case 'GBP':
                $credits = '£' . $matches[1];
                break;
              case 'USD':
                $credits = '$' . $matches[1];
                break;
              case 'EUR':
                $credits = '€' . $matches[1];
                break;
              default:
                $credits = '£' . $matches[1];
                break;                
            }
        }

        return $credits;
    }

    protected function _formatMobileNumber($number)
    {
        return preg_replace('#[^\d]#', '', trim($number));
    }
}
