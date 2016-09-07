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
class Magestore_Shopbybrand_Block_Brandcategories extends Mage_Core_Block_Template 
{
    public function getAllCategories() {
        return Mage::helper('shopbybrand/brand')->getParentCategories();
        $catids = Mage::getModel('shopbybrand/brand')
                ->getCollection()->setStoreId($this->getStoreId())
                ->getAllCategories();
        $catids = implode(",", $catids);
        $catids = explode(",", $catids);
        $catids = array_unique($catids);
        $categories = Mage::getModel('catalog/category')->getCollection()
                ->setStoreId($this->getStoreId())
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToFilter('level', array('gteq'=>2))
                ->addFieldToFilter('entity_id', array('in' => $catids));
        return $categories;
    }
    
    public function getStoreId() {
        $storeId = Mage::app()->getStore()->getId();
        return $storeId;
    }
}

