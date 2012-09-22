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
 *
 */
class Mediaburst_Sms_Mediaburst_SmsController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_redirect('*/*/pending');
    }

    public function checkAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/mediaburst_sms/check');
        $this->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));
        $this->renderLayout();
    }

    public function pendingAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/mediaburst_sms/pending');
        $this->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));
        $this->renderLayout();
    }

    public function sentAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/mediaburst_sms/sent');
        $this->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));
        $this->renderLayout();
    }

    public function failedAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/mediaburst_sms/failed');
        $this->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));
        $this->renderLayout();
    }

    public function sendAction()
    {
        $id      = (int)$this->getRequest()->getParam('id');
        $message = Mage::getModel('Mediaburst_Sms/Message')->load($id);
        if ($message->getId() > 0 && $message->getId() == $id) {
            if ($message->getStatus() == Mediaburst_Sms_Model_Message::STATUS_PENDING) {
                $helper = Mage::helper('Mediaburst_Sms/Data');
                $helper->setDefaultStore($message->getStoreId());
                $api    = Mage::getModel('Mediaburst_Sms/Api', $helper);
                $result = $api->sendMessage($message);
                $helper->setDefaultStore(null);
                $helper->reportResults($this->_getSession(), $result);
            } else {
                $this->_getSession()->addError($this->__('Invalid Message Status'));
            }
        } else {
            $this->_getSession()->addError($this->__('Invalid Message ID'));
        }

        $this->_redirect('*/*/pending');
    }

    public function requeueAction()
    {
        $id      = (int)$this->getRequest()->getParam('id');
        $message = Mage::getModel('Mediaburst_Sms/Message')->load($id);
        if ($message->getId() > 0 && $message->getId() == $id) {
            if ($message->getStatus() == Mediaburst_Sms_Model_Message::STATUS_SENT) {
                try {
                    $message->setStatus(Mediaburst_Sms_Model_Message::STATUS_PENDING);
                    $message->getMessageId(null);
                    $message->setErrorNumber(null);
                    $message->setErrorDescription(null);
                    $message->save();
                    $this->_getSession()->addSuccess($this->__('Requeued message %s to %s', $message->getId(), $message->getTo()));
                }
                catch (Exception $e) {
                    $this->_getSession()->addException($e, $e->getMessage());
                }
            } else {
                $this->_getSession()->addError($this->__('Invalid Message Status'));
            }
        } else {
            $this->_getSession()->addError($this->__('Invalid Message ID'));
        }

        $this->_redirect('*/*/sent');
    }

    public function retryAction()
    {
        $id      = (int)$this->getRequest()->getParam('id');
        $message = Mage::getModel('Mediaburst_Sms/Message')->load($id);
        if ($message->getId() > 0 && $message->getId() == $id) {
            if ($message->getStatus() == Mediaburst_Sms_Model_Message::STATUS_FAILED) {
                try {
                    $message->setStatus(Mediaburst_Sms_Model_Message::STATUS_PENDING);
                    $message->getMessageId(null);
                    $message->setErrorNumber(null);
                    $message->setErrorDescription(null);
                    $message->save();
                    $this->_getSession()->addSuccess($this->__('Retrying message %s to %s', $message->getId(), $message->getTo()));
                }
                catch (Exception $e) {
                    $this->_getSession()->addException($e, $e->getMessage());
                }
            } else {
                $this->_getSession()->addError($this->__('Invalid Message Status'));
            }
        } else {
            $this->_getSession()->addError($this->__('Invalid Message ID'));
        }

        $this->_redirect('*/*/failed');
    }

    public function forceCronAction()
    {
        Mage::getSingleton('Mediaburst_Sms/Observer')->sendPendingMessages($this->_getSession());
        $this->_redirect('*/*');
    }

    protected function _isAllowed()
    {
        $allowed = false;

        $action = $this->getRequest()->getActionName();
        switch ($action) {
            case 'pending':
            case 'send':
            case 'sent':
            case 'requeue':
            case 'failed':
            case 'retry':
            case 'check':
                $allowed = $this->_permissionCheck($action);
                break;
            case 'index':
            case 'forceCron':
                $allowed = true;
                break;
        }

        return $allowed;
    }

    protected function _permissionCheck($permission)
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/mediaburst_sms/' . $permission);
    }
}