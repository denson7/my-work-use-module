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
class Magestore_Shopbybrand_Block_Searchbox extends Mage_Core_Block_Template 
{
    public function getAllBrands() {
        $storeId = Mage::app()->getStore()->getId();
        $shopbybrands = Mage::getModel('shopbybrand/brand')
                                ->getCollection()
                                ->setStoreId($storeId)
                                ->setOrder('name', 'ASC')
                                ;
        return $shopbybrands;
    }
    public function getSearchData() {
        $store = Mage::app()->getStore()->getId();
        $brandData = unserialize(Mage::app()->getCacheInstance()->load('brand_search_data_'.$store));
        if($brandData)
            return $brandData;
        $shopbybrands = Mage::getSingleton('shopbybrand/brand')->getBrandCollection();
        $array = array();
        foreach ($shopbybrands as $brand) {
            $array[] = array('n' => $brand->getName(),
                            'k' => $brand->getUrlKey());
        }
        Mage::app()->getCacheInstance()->save(serialize($array), 'brand_search_data_'.$store); 
        return $array;
    }
}

