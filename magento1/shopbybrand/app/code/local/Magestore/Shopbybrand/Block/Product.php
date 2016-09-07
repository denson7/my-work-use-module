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
class Magestore_Shopbybrand_Block_Product extends Mage_Catalog_Block_Product_List
{
    public function getColumnCount(){
        /*edit by cuong*/
        $config = Mage::getStoreConfig('shopbybrand/brand_detail/brand_products_per_row', $this->getStore());
        $config = $config ? $config:4;
        /*end edit by cuong*/
        return $config;
    }
    public function getStore(){
        $store = Mage::app()->getStore()->getId();
        return $store;
    }
    /* add/edit by Cuong */
         /**
     * chuyen position mac dinh cua product_list_toolbar thanh brand_position
     */
    public function setAvailableOrders() {
        
        $this->getChild('product_list_toolbar')
            ->setAvailableOrders(array(
                'brand_position'  => $this->__('Recommended'),
                'name'      => $this->__('Name'),
                'price'     => $this->__('Price'),
            ));
    }
    /* add/edit by Cuong */
}