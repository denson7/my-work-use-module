<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Shopbybrand Edit Tabs Block
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Block_Adminhtml_Brand_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('shopbybrand_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('shopbybrand')->__('Brand Information'));
    }

    /**
     * prepare before render block to html
     *
     * @return Magestore_Shopbybrand_Block_Adminhtml_Shopbybrand_Edit_Tabs
     */
    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('shopbybrand')->__('General Information'),
            'title' => Mage::helper('shopbybrand')->__('General Information'),
            'content' => $this->getLayout()
                    ->createBlock('shopbybrand/adminhtml_brand_edit_tab_form')
                    ->toHtml(),
        ));

        $this->addTab('product', array(
            'label' => Mage::helper('shopbybrand')->__('Products'),
            'url' => $this->getUrl('*/*/product', array('_current' => true)),
            'class' => 'ajax',
        ));
        if($this->getRequest()->getParam('id')){
        
        $this->addTab('order_items', array(
            'label' => Mage::helper('shopbybrand')->__('Sold Items'),
            'title' => Mage::helper('shopbybrand')->__('Sold Items'),
            'content' => $this->getLayout()
                    ->createBlock('shopbybrand/adminhtml_brand_edit_tab_orderitems')
                    ->toHtml(),
        ));
        $checkActive='';
        if ($this->getRequest()->getParam('reportSales'))
            $checkActive = TRUE;
        $this->addTab('sales_chart', array(
            'label' => Mage::helper('shopbybrand')->__('Sales Chart'),
            'title' => Mage::helper('shopbybrand')->__('Sales Chart'),
            'content' => $this->getLayout()
                    ->createBlock('shopbybrand/adminhtml_brand_edit_tab_report_dashboard')
                    ->toHtml(),
            'active' => $checkActive,
        ));
        }
        return parent::_beforeToHtml();
    }

}
