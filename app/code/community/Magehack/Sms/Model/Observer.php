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
 * Event Observer
 */
class Magehack_Sms_Model_Observer
{

    public function createOrderCreatedMessage(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        if ($order instanceof Mage_Sales_Model_Order) {
            /* @var $order Mage_Sales_Model_Order */
            if (!$this->getHelper()->isOrderCreatedActive($order->getStoreId())) {
                return;
            }
            try {
                $message = Mage::getModel('Magehack_Sms/Message');
                $message->setStoreId($order->getStoreId());
                $message->setTo($this->getHelper()->getOrderCreatedTo());
                $message->setFrom($this->getHelper()->getOrderCreatedFrom());
                $message->setContent($this->getHelper()->generateOrderCreatedContent($order));
                $message->save();
            }
            catch (Exception $e) {
                $this->getHelper()->log('Error creating Order Created SMS Message Record for Order ' . $order->getIncrementId(), Zend_Log::ERR);
            }
        }
    }

    public function createOrderShippedMessage(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getShipment();
        if ($shipment instanceof Mage_Sales_Model_Order_Shipment) {
            /* @var $shipment Mage_Sales_Model_Order_Shipment */
            $order = $shipment->getOrder();
            if (!$this->getHelper()->isOrderShippedActive($order->getStoreId())) {
                return;
            }
            try {
                $message = Mage::getModel('Magehack_Sms/Message');
                $message->setStoreId($order->getStoreId());
                $message->setTo($this->getHelper()->getTelephone($order));
                $message->setFrom($this->getHelper()->getOrderShippedFrom());
                $message->setContent($this->getHelper()->generateOrderShippedContent($order, $shipment));
                $message->save();
                $this->getHelper()->addOrderComment($order, 'SMS message generated (' . $message->getId() . ')');
            }
            catch (Exception $e) {
                $this->getHelper()->log('Error creating Order Shipped SMS Message Record for Order ' . $order->getIncrementId(), Zend_Log::ERR);
            }
        }
    }

    public function createOrderHeldMessage(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        if ($order instanceof Mage_Sales_Model_Order) {
            /* @var $order Mage_Sales_Model_Order */
            if (!$this->getHelper()->isOrderHeldActive($order->getStoreId())) {
                return;
            }
            if ($order->getState() !== $order->getOrigData('state') && $order->getState() === Mage_Sales_Model_Order::STATE_HOLDED) {
                try {
                    $message = Mage::getModel('Magehack_Sms/Message');
                    $message->setStoreId($order->getStoreId());
                    $message->setTo($this->getHelper()->getTelephone($order));
                    $message->setFrom($this->getHelper()->getOrderHeldFrom());
                    $message->setContent($this->getHelper()->generateOrderHeldContent($order));
                    $message->save();
                    $this->getHelper()->addOrderComment($order, 'SMS message generated (' . $message->getId() . ')');
                }
                catch (Exception $e) {
                    $this->getHelper()->log('Error creating Order Held SMS Message Record for Order ' . $order->getIncrementId(), Zend_Log::ERR);
                }
            }
        }
    }

    public function createOrderUnheldMessage(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        if ($order instanceof Mage_Sales_Model_Order) {
            /* @var $order Mage_Sales_Model_Order */
            if (!$this->getHelper()->isOrderUnheldActive($order->getStoreId())) {
                return;
            }
            if ($order->getState() !== $order->getOrigData('state') && $order->getOrigData('state') === Mage_Sales_Model_Order::STATE_HOLDED) {
                try {
                    $message = Mage::getModel('Magehack_Sms/Message');
                    $message->setStoreId($order->getStoreId());
                    $message->setTo($this->getHelper()->getTelephone($order));
                    $message->setFrom($this->getHelper()->getOrderUnheldFrom());
                    $message->setContent($this->getHelper()->generateOrderUnheldContent($order));
                    $message->save();
                    $this->getHelper()->addOrderComment($order, 'SMS message generated (' . $message->getId() . ')');
                }
                catch (Exception $e) {
                    $this->getHelper()->log('Error creating Order Held SMS Message Record for Order ' . $order->getIncrementId(), Zend_Log::ERR);
                }
            }
        }
    }

    /**
     * Cron Job
     */
    public function sendPendingMessages($session = null)
    {
        $runs = array();

        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            if ($this->getHelper()->isActive($store)) {
                $key = $this->getHelper()->getKey($store);
                $url      = $this->getHelper()->getSendUrl($store);
                $hash     = md5($key . ':' . $url);

                if (!isset($runs[$hash])) {
                    $runs[$hash] = array(
                        'key' => $key,
                        'url'      => $url,
                        'stores'   => array()
                    );
                }

                $runs[$hash]['stores'][] = $store->getId();
            }
        }

        $api = $this->getHelper()->getApi();

        foreach ($runs as $run) {
            $collection = Mage::getModel('Magehack_Sms/Message')->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('status', Magehack_Sms_Model_Message::STATUS_PENDING)
                ->addFieldToFilter('store_id', $run['stores'])
                ->setPageSize(Magehack_Sms_Model_Api::SMS_PER_REQUEST_LIMIT);

            Mage::dispatchEvent('mediaburst_sms_send_pending_before', array('collection' => $collection));

            $this->getHelper()->setDefaultStore(reset($run['stores']));

            $results = $api->sendMessages($collection->getItems());

            if ($session instanceof Mage_Core_Model_Session_Abstract) {
                $this->getHelper()->reportResults($session, $results);
            }
        }

        $this->getHelper()->setDefaultStore(null);
    }

    /**
     *
     * @return Magehack_Sms_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('Magehack_Sms/Data');
    }
}
