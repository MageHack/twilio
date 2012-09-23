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
 * Abstract Message Grid
 */
abstract class Magehack_Sms_Block_AbstractMessageGrid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected $_resourceClass = null;

    public function setCollectionResourceModel($model)
    {
        $this->_collectionResourceModel = $model;
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function getCollectionResourceModel()
    {
        return $this->_collectionResourceModel;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->getCollectionResourceModel());
        $this->_filterCollection($collection);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _filterCollection(Varien_Data_Collection_Db $collection)
    {
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                 'header' => $this->__('Message #'),
                 'width'  => '80px',
                 'type'   => 'number',
                 'index'  => 'id',
            )
        );

        $this->addColumn(
            'store_id',
            array(
                 'header'          => $this->__('Store'),
                 'index'           => 'store_id',
                 'type'            => 'store',
                 'store_view'      => true,
                 'display_deleted' => true,
            )
        );

        $this->addColumn(
            'created_at',
            array(
                 'header' => $this->__('Created'),
                 'index'  => 'created_at',
                 'type'   => 'datetime',
            )
        );

        $this->addColumn(
            'updated_at',
            array(
                 'header' => $this->__('Updated'),
                 'index'  => 'updated_at',
                 'type'   => 'datetime',
            )
        );

        $this->addColumn(
            'to',
            array(
                 'header' => $this->__('To'),
                 'index'  => 'to',
            )
        );

        $this->addColumn(
            'from',
            array(
                 'header' => $this->__('From'),
                 'index'  => 'from',
            )
        );

        $this->addColumn(
            'content',
            array(
                 'header' => $this->__('Content'),
                 'index'  => 'content',
                 'filter' => false,
            )
        );

        return parent::_prepareColumns();
    }
}