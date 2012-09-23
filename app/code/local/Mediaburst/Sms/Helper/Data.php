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

/**
 * Helper
 */
class Mediaburst_Sms_Helper_Data extends Mage_Core_Helper_Abstract implements Mediaburst_Sms_Model_ApiConfig
{
    const XML_CONFIG_BASE_PATH = 'mediaburst_sms/';

    protected $_defaultStore = null;

    public function setDefaultStore($store)
    {
        $this->_defaultStore = $store;
    }

    public function isActive($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'general/active', $store);
    }

    public function getCheckUrl($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'general/check_url', $store);
    }

    public function getBuyUrl($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'general/buy_url', $store);
    }

    public function getSendUrl($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'general/send_url', $store);
    }

    public function getKey($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'general/key', $store);
    }
    
    public function get($path, $store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . $path, $store);
    }

    public function getApi()
    {
        $provider = $this->get('general/provider');
        $api = null;
        switch ($provider) {
            case 'mediaburst':
                $api = Mage::getModel('Mediaburst_Sms/Api', $this);
                break;
            case 'twilio':
                $api = Mage::getModel('Mediaburst_Sms/ApiTwilio', $this);
                break;
        }
        return $api;
    }

    public function isDebug($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'general/debug', $store);
    }

    public function log($message, $level = Zend_Log::DEBUG, $store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        if ($message instanceof Exception) {
            $message = "\n" . $message->__toString();
            $level   = Zend_Log::ERR;
            $file    = Mage::getStoreConfig('dev/log/exception_file', $store);
        } else {
            if (is_array($message) || is_object($message)) {
                $message = print_r($message, true);
            }
            $file = Mage::getStoreConfig('dev/log/file', $store);
        }

        if ($level < Zend_Log::DEBUG || $this->isDebug($store)) {
            $force = ($level <= Zend_Log::ERR);
            Mage::log($message, $level, $file, $force);
        }
    }

    public function isOrderCreatedActive($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return $this->isActive($store) && Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'order_created/active', $store);
    }

    public function getOrderCreatedTo($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'order_created/to', $store);
    }

    public function getOrderCreatedFrom($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'order_created/from', $store);
    }

    public function getOrderCreatedContent($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'order_created/content', $store);
    }

    public function isOrderHeldActive($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return $this->isActive($store) && Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'order_held/active', $store);
    }

    public function getOrderHeldFrom($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'order_held/from', $store);
    }

    public function getOrderHeldContent($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'order_held/content', $store);
    }

    public function isOrderUnheldActive($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return $this->isActive($store) && Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'order_unheld/active', $store);
    }

    public function getOrderUnheldFrom($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'order_unheld/from', $store);
    }

    public function getOrderUnheldContent($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'order_unheld/content', $store);
    }

    public function isOrderShippedActive($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return $this->isActive($store) && Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'order_shipped/active', $store);
    }

    public function getOrderShippedFrom($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'order_shipped/from', $store);
    }

    public function getOrderShippedContent($store = null)
    {
        if ($store === null) {
            $store = $this->_defaultStore;
        }

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'order_shipped/content', $store);
    }

    public function generateOrderCreatedContent(Mage_Sales_Model_Order $order)
    {
        $filter = Mage::getModel('core/email_template_filter');
        $filter->setPlainTemplateMode(true);
        $filter->setStoreId($order->getStoreId());
        $filter->setVariables(array('order' => $order));
        return $filter->filter($this->getOrderCreatedContent($order->getStoreId()));
    }

    public function generateOrderShippedContent(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $filter = Mage::getModel('core/email_template_filter');
        $filter->setPlainTemplateMode(true);
        $filter->setStoreId($order->getStoreId());
        $filter->setVariables(
            array(
                 'order'    => $order,
                 'shipment' => $shipment
            )
        );
        return $filter->filter($this->getOrderShippedContent($order->getStoreId()));
    }

    public function generateOrderHeldContent(Mage_Sales_Model_Order $order)
    {
        $filter = Mage::getModel('core/email_template_filter');
        $filter->setPlainTemplateMode(true);
        $filter->setStoreId($order->getStoreId());
        $filter->setVariables(array('order' => $order));
        return $filter->filter($this->getOrderHeldContent($order->getStoreId()));
    }

    public function generateOrderUnheldContent(Mage_Sales_Model_Order $order)
    {
        $filter = Mage::getModel('core/email_template_filter');
        $filter->setPlainTemplateMode(true);
        $filter->setStoreId($order->getStoreId());
        $filter->setVariables(array('order' => $order));
        return $filter->filter($this->getOrderUnheldContent($order->getStoreId()));
    }

    /**
     * Convert a result array into a series of session messages
     *
     * @param Mage_Core_Model_Session_Abstract $session
     *
     * @return Mediaburst_Sms_Helper_Data
     */
    public function reportResults(Mage_Core_Model_Session_Abstract $session, array $result)
    {
        foreach ($result['sent'] as $message) {
            $session->addSuccess($this->__('Sent message %s to %s', $message->getId(), $message->getTo()));
        }
        foreach ($result['failed'] as $message) {
            $session->addError($this->__('Failed sending message %s to %s (%s: %s)', $message->getId(), $message->getTo(), $message->getErrorNumber(), $message->getErrorDescription()));
        }
        foreach ($result['errors'] as $error) {
            $session->addError(implode(' / ', $error));
        }

        return $this;
    }

    public function getTelephone(Mage_Sales_Model_Order $order)
    {
        $billingAddress = $order->getBillingAddress();

        $number = $billingAddress->getTelephone();
        $number = preg_replace('#[^\+\d]#', '', trim($number));

        if (substr($number, 0, 1) === '+') {
            $number = substr($number, 1);
        } elseif (substr($number, 0, 2) === '00') {
            $number = substr($number, 2);
        } else {
            // Handle special case where mobile numbers are prefixed with a 0
            if (substr($number, 0, 1) === '0') {
                $number = substr($number, 1);
            }

            // Find the telephone dialing code for the billing country
            $expectedPrefix = Zend_Locale_Data::getContent(Mage::app()->getLocale()->getLocale(), 'phonetoterritory', $billingAddress->getCountry());

            // If we couldn't find the dialing code by billing country, chose the store level default
            if (empty($expectedPrefix)) {
                $expectedPrefix = Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'general/failsafe_prefix', $store);
            }

            // Try to prepend the dialing prefix if it's not part of the number already (Not bullet-proof)
            if (!empty($expectedPrefix)) {
                $prefix = substr($number, 0, strlen($expectedPrefix));
                if ($prefix !== $expectedPrefix) {
                    $number = $expectedPrefix . $number;
                }
            }
        }

        // Final trim of number, Just-In-Case™
        $number = preg_replace('#[^\d]#', '', trim($number));

        return $number;
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @param string                 $comment
     *
     * @return Mediaburst_Sms_Helper_Data
     */
    public function addOrderComment(Mage_Sales_Model_Order $order, $comment)
    {
        Mage::getModel('sales/order_status_history')
            ->setOrder($order)
            ->setStatus($order->getStatus())
            ->setComment($comment)
            ->setIsCustomerNotified(true)
            ->save();

        return $this;
    }
}
