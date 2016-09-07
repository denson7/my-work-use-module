<?php

class Magestore_Shopbybrand_Block_Toplink extends Mage_Core_Block_Template {

    public function __construct() {
        parent::__construct();
    }

    public function _prepareLayout() {
        $store = Mage::app()->getStore()->getId();
        parent::_prepareLayout();
        if (!Mage::getStoreConfig('shopbybrand/general/enable', $store)) {
            return $this;
        }
        $this->addBrandToplink();
    }

    public function addBrandToplink() {
        $block = $this->getLayout()->getBlock('top.links');
        $store = Mage::app()->getStore()->getId();
        $toplink = Mage::getStoreConfig('shopbybrand/general/toplink',$store);
		if($block && $toplink )
			$block->addLink(Mage::helper('shopbybrand')->__('Brands'), Mage::helper('shopbybrand')->getShopbybrandUrl(), Mage::helper('shopbybrand')->__('Brand Listing'), '', '', 10);
    }
}