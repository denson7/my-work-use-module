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
class Magestore_Shopbybrand_Block_Bestseller extends Mage_Core_Block_Template 
{
    public function getProductBestseller(){
        $storeId = Mage::app()->getStore()->getId();
        $attributeCodeId = Mage::getSingleton('shopbybrand/brand')->getBrand()->getOptionId();
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $numberConfig =  Mage::getStoreConfig('shopbybrand/brand_detail/bestseller_products_number_show', $storeId);
        $visibility = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();
        $attributesToSelect = array('name', 'small_image');
        $productFlatTable = Mage::getResourceSingleton('catalog/product_flat')->getFlatTableName($storeId);
        try{
            $resourceCollection = Mage::getResourceSingleton('reports/product_collection')
            ->addOrderedQty();
            if(Mage::helper('catalog/product_flat')->isEnabled()){
                $resourceCollection->joinTable(array('flat_table'=>$productFlatTable),'entity_id=entity_id', $attributesToSelect);
            }else{
                $resourceCollection->addAttributeToSelect($attributesToSelect);
            }
            $resourceCollection
            ->setVisibility($visibility)
            ->addAttributeToFilter(ucfirst($attributeCode),$attributeCodeId)
            ->addStoreFilter($storeId)
            ->setPageSize($numberConfig)
            ->setOrder('ordered_qty','desc');
            return $resourceCollection;
        }catch (Exception $e){
            Mage::logException($e->getMessage());
        }
    }
    
    public function getBrand(){
        if(!$this->hasData('current_brand')){
            $brandId = $this->getRequest()->getParam('id');
            
            $storeId = $this->getStoreId();
            $brand = Mage::getModel('shopbybrand/brand')->setStoreId($storeId)
                    ->load($brandId);
            $this->setData('current_brand', $brand);
        }
        return $this->getData('current_brand');
    }
}

