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
 * @package   Mediaburst_Sms
 * @license   http://opensource.org/licenses/isc-license.txt
 * @copyright Copyright © 2011 by Mediaburst Limited
 * @author    Lee Saferite <lee.saferite@lokeycoding.com>
 */

require_once Mage::getBaseDir('lib') . '/Twilio/Services/Twilio.php';

/**
 * API implementation class
 */
class Mediaburst_Sms_Model_ApiTwilio extends Mediaburst_Sms_Model_Api
{
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

        $sid = $this->_config->get('twilio/account');
        $token = $this->_config->get('twilio/token');
        $client = new Services_Twilio($sid, $token);

        foreach ($messages as $message) {
            $sms = $client->account->sms_messages->create(
                $this->_formatMobileNumber($this->_config->get('twilio/twilio_number')),
                $this->_formatMobileNumber($message->getTo()),
                $message->getContent());
            if ($sms->sid) {
                $this->_config->log('Queued ' . $sms->sid);
                $message->setStatus(Mediaburst_Sms_Model_Message::STATUS_SENT);
            } else {
                $message->setStatus(Mediaburst_Sms_Model_Message::STATUS_FAILED);
            }
            $message->save();
//            $this->_config->log('Messages ' . count($messages), Zend_Log::WARN);
        }
    }

    protected function _formatMobileNumber($number)
    {
        return preg_replace('/[^\d+]/', '', trim($number));
    }
}
