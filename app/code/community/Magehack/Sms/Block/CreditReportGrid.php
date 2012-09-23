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
 * Credit Report Grid
 */
class Magehack_Sms_Block_CreditReportGrid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->unsetChild('reset_filter_button');
        $this->unsetChild('search_button');

        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();

        $helper = Mage::helper('Magehack_Sms/Data');

        $runs = array();

        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            if ($helper->isActive($store)) {
                $key      = $helper->getKey($store);
                $url      = $helper->getCheckUrl($store);
                $hash     = md5($key . ':' . $url);

                if (!isset($runs[$hash])) {
                    $runs[$hash] = array(
                        'key'      => $key,
                        'url'      => $url,
                        'stores'   => array()
                    );
                }

                $runs[$hash]['stores'][] = $store->getId();
            }
        }

        $api = Mage::getModel('Magehack_Sms/Api', $helper);
        /* @var $api Magehack_Sms_Model_Api */

        $results = array();

        foreach ($runs as $hash => $run) {
            $helper->setDefaultStore(reset($run['stores']));
            $credits = $api->checkCredits();

            $item = new Varien_Object();
            $item->setKey($run['key']);
            $item->setUrl($run['url']);
            $item->setCredits($credits);

            $collection->addItem($item);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'key',
            array(
                 'header' => $this->__('Key'),
                 'index'  => 'key',
                 'filter' => false,
            )
        );

        $this->addColumn(
            'url',
            array(
                 'header' => $this->__('Service URL'),
                 'index'  => 'url',
                 'filter' => false,
            )
        );

        $this->addColumn(
            'credits',
            array(
                 'header' => $this->__('Balance'),
                 'index'  => 'credits',
                 'filter' => false,
            )
        );

        return parent::_prepareColumns();
    }

    public function registerBuyButton()
    {
        $container = $this->getParentBlock();
        if ($container instanceof Mage_Adminhtml_Block_Widget_Grid_Container) {
            $helper = Mage::helper('Magehack_Sms/Data');
            $container->addButton(
                'buy',
                array(
                     'label'   => $this->__('Buy Messages'),
                     'onclick' => 'setLocation(\'http://www.clockworksms.com/platforms/magento/?utm_source=magentoadmin&utm_medium=plugin&utm_campaign=magento\')',
                     'class'   => 'add',
                )
            );
        }
    }
}
