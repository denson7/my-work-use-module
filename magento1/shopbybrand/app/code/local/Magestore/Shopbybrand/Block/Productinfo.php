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
 * Product Info Block
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Block_Productinfo extends Mage_Core_Block_Template
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
    
    public function getProduct(){
        $product = Mage::registry('current_product');
        return $product;
    }
    public function getStoreId(){
        return Mage::app()->getStore()->getId();
    }
    public function getBrand(){
        $brand = Mage::getModel('shopbybrand/brand');
        $product = $this->getProduct();
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        if($product->getId()){
            $optionId = $product->getData($attributeCode);
            if($optionId){
                $brand->load($optionId, 'option_id');
                $brand->setStoreId($this->getStoreId())->load($brand->getId());
            }
        }
        if($brand->getStatus()==1)
        return $brand;
    }
}