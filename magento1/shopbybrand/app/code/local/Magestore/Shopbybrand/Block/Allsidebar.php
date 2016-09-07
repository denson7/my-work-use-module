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
 * Shopbybrand Block
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Block_Allsidebar extends Mage_Core_Block_Template
{
    /**
     * prepare block's layout
     *
     * @return Magestore_Shopbybrand_Block_Shopbybrand
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getStoreId(){
        $storeId = Mage::app()->getStore()->getId();
        return $storeId;
    }
    
    public function displayBrandSearchBox(){
        $store = Mage::app()->getStore()->getId();
        $display = true;
        $position = 1;
        $display = Mage::getStoreConfig('shopbybrand/brand_detail/display_brand_instant_search',$store);
        $position = Mage::getStoreConfig('shopbybrand/brand_detail/brand_instant_search_position',$store);
        if ($display)
            return  array('pos' => $position, 'name' => 'shopbybrand-searchbox');
        else
            return false;
    }
    
    public function displayShopByOptionBox(){
        $store = Mage::app()->getStore()->getId();
        $display = true;
        $position = 2;
        $display = Mage::getStoreConfig('shopbybrand/brand_detail/display_brand_shop_by_option',$store);
        $position = Mage::getStoreConfig('shopbybrand/brand_detail/brand_shop_by_option_position',$store);
        if ($display)
            return  array('pos' => $position, 'name' => 'shopbybrand-leftnav');
        else
            return false;
    }
    
    public function displayBestSellerBox(){
        $store = Mage::app()->getStore()->getId();
        $display = true;
        $position = 3;
        $display = Mage::getStoreConfig('shopbybrand/brand_detail/display_brand_bestseller_products',$store);
        $position = Mage::getStoreConfig('shopbybrand/brand_detail/brand_bestseller_products_position',$store);
        if ($display)
            return  array('pos' => $position, 'name' => 'shopbybrand-bestseller');
        else
            return false;
    }
    
    public function displaySubscribeBox(){
        $store = Mage::app()->getStore()->getId();
        $display = true;
        $position = 4;
        $display = Mage::getStoreConfig('shopbybrand/brand_detail/display_brand_subcriber_box',$store);
        $position = Mage::getStoreConfig('shopbybrand/brand_detail/brand_subcriber_box_position',$store);
        if ($display)
            return  array('pos' => $position, 'name' => 'shopbybrand-subscriber');
        else
            return false;
    }
    
    public function getAllSidebar() {
        $allsidebar = array();
        
        if ($displayBrandSearchBox = $this->displayBrandSearchBox())
            $allsidebar[$displayBrandSearchBox['name']] = $displayBrandSearchBox['pos'];
        
        if ($displayShopByOptionBox = $this->displayShopByOptionBox())
            $allsidebar[$displayShopByOptionBox['name']] = $displayShopByOptionBox['pos'];
        
        if ($displayBestSellerBox = $this->displayBestSellerBox())
            $allsidebar[$displayBestSellerBox['name']] = $displayBestSellerBox['pos'];
        
        if ($displaySubscribeBox = $this->displaySubscribeBox())
            $allsidebar[$displaySubscribeBox['name']] = $displaySubscribeBox['pos'];
        
        asort($allsidebar);
        return $allsidebar;
    }
   
}