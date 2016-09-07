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
class Magestore_Shopbybrand_Block_Shopbybrand extends Mage_Core_Block_Template {
    protected $_brandCollection = null;
    
    /**
     * prepare block's layout
     *
     * @return Magestore_Shopbybrand_Block_Shopbybrand
     */
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    /* ThinhND
     * display All Brand
     */

    public function generateListCharacter() {
        $begin = $this->getRequest()->getParam("begin");
        return $begin;
    }

    public function getListCharacterBegin() {
        $lists = array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 
                        'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 
                        'S', 'T', 'W', 'U', 'V', 'X', 'Y', 'Z'
                      );
        return $lists;
    }

    public function getCharSearchUrl($begin) {
        $setlink = Mage::getStoreConfig('shopbybrand/general/router');
        $lists = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'W', 'U', 'V', 'X', 'Y', 'Z');
        if (!in_array($begin, $lists)) {
            return $url = $this->getUrl($setlink, array());
        }
        return $this->getUrl($setlink . "/index/index/begin/" . $begin).'#shopbybrand_char_filter';
    }
    /* add by cuong */
    public function getAllBrands(){
        if(is_null($this->_brandCollection)){
            return Mage::getModel('shopbybrand/brand')->getCollection();
        }
        return $this->_brandCollection;
    }
    /* end ađ by cuong */   
    

    public function getBrandsByBegin($begin=null) {
        if(is_null($this->_brandCollection)){
            $store = Mage::app()->getStore()->getId();
            $onlyBrandHaveProduct = Mage::getStoreConfig('shopbybrand/optional/display_brand_have_product', $store);
            if ($input = $this->getInputSearch()) {
                $extended_search = Mage::getStoreConfig('shopbybrand/general/brand_extended_search', $store);
                $shopbybrands = Mage::getModel('shopbybrand/brand')->getCollection();
                $shopbybrands->setStoreId($this->getStoreId());
                if($input!="Enter Keyword"){
                    $shopbybrands->addFieldToFilter('name', array('like' => '%' . $input . '%'));
                }

                if ($extended_search == 'all') {
                    $allIds1 = $shopbybrands->getAllIDs();
                    $allIds2 = Mage::getModel('shopbybrand/brand')
                            ->getCollection()
                            ->setStoreId($this->getStoreId())
                            ->addFieldToFilter('description', array('like' => '%' . $input . '%'))
                            ->getAllIDs();
                    $allIds = array_merge($allIds1, $allIds2);
                    $allIds = array_unique($allIds);
                    if (count($allIds)) {
                        $shopbybrands = Mage::getModel('shopbybrand/brand')
                                ->getCollection()
                                ->setStoreId($this->getStoreId())
                                ->addFieldToFilter('main_table.brand_id', array('in' => $allIds));
                    } else {
                        return Mage::getModel('shopbybrand/brand')->getCollection()->addFieldToFilter('main_table.brand_id', -1);
                    }
                } elseif ($extended_search == 'description') {
                    $shopbybrands = Mage::getModel('shopbybrand/brand')
                            ->getCollection()
                            ->setStoreId($this->getStoreId())
                            ->addFieldToFilter('description', array('like' => '%' . $input . '%'));
                }
            } else {
                if(!$begin)
                $begin = $this->getRequest()->getParam("begin");
                $shopbybrands = Mage::helper("shopbybrand")->getBrandsByBegin($begin);
            }

            $shopbybrands->addFieldToFilter('status', array('eq' => 1));
            $productIDs = Mage::getModel('shopbybrand/layer')
                    ->getProductCollection()
                    ->getSelect()
                    ->assemble();
            $shopbybrands->getSelect()
                ->joinleft(array('product'=> new Zend_Db_Expr("($productIDs)")),'FIND_IN_SET(product.entity_id,main_table.product_ids)',array('entity_id'=>'product.entity_id'))
                ->group('main_table.brand_id')
                ->columns(array(
                        'number_product' => 'SUM(IF( product.entity_id > 0, 1, 0 ))'
            ));
            if ($onlyBrandHaveProduct)
                $shopbybrands->addFieldToFilter('SUM(IF( product.entity_id > 0, 1, 0 ))', array('neq' => 0));
            $this->_brandCollection = $shopbybrands;
        }
        return $this->_brandCollection;
    }

    public function getBrandUrl($brand) {
        $url = $this->getUrl($brand->getUrlKey(), array());
        return $url;
    }

    public function getFeaturedBrands() {
        $featuredBrands = Mage::helper("shopbybrand")->getFeaturedBrands();
        return $featuredBrands;
    }

    public function getBrandImage($brand, $width) {
        if ($brand->getImage()) {
            $file = Mage::getBaseUrl('media') . 'brands/' . strtolower(substr($brand->getName(), 0, 1)) . substr(md5($brand->getName()), 0, 10) . Mage::helper('shopbybrand')->refineUrlKey($brand->getName()) . '/' . $brand->getImage();
        } else {
            $file = Mage::getBaseUrl('media') . 'brands/no-image.png';
        }
        if ($file) {
            $img = "<img  src='" . $file . "' title='" . $brand->getName() . "' width=" . $width . " border='0'/>";
            return $img;
        } else {
            return NULL;
        }
    }

    public function getStoreId() {
        $storeId = Mage::app()->getStore()->getId();
        return $storeId;
    }

    public function getInputSearch() {
        return $this->getRequest()->getParam('input');
    }

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
                ->addFieldToFilter('entity_id', array('in' => $catids));
        return $categories;
    }

    public function displayFeaturedBrand() {
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/optional/display_featured_brand', $store);
        return $display;
    }

    public function displayBrandImage() {
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/optional/display_brand_image', $store);
        return $display;
    }

    public function displayBrandsearch() {
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/optional/display_brand_list_search', $store);
        return $display;
    }

    public function displayBrandByCategory() {
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/optional/display_brand_by_category', $store);
        return $display;
    }

//    public function getFeatureModeCode() {
//        $store = Mage::app()->getStore()->getId();
//        $display = Mage::getStoreConfig('shopbybrand/general/feature_display_mode', $store);
//        return $display;
//    }
    
    /* add by Peter */
    public function getProductBestseller($attributeCodeId){
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $numberConfig = 5;
        $visibility = array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
        );
        $storeId = Mage::app()->getStore()->getId();
        $products = Mage::getResourceModel('reports/product_collection')
            ->addAttributeToSelect('*')
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->addAttributeToFilter('visibility', $visibility)
            ->setPageSize($numberConfig);
        $products->addOrderedQty();
        $products->addOrderedQty();
        $products->setOrder('ordered_qty', 'desc');
        $products->addAttributeToFilter($attributeCode,$attributeCodeId);
        return $products;
    }
    /* end add by Peter */
    
    public function getNumberProductsOfBrand($brand) {
        $productIDs = $brand->getData('product_ids');
        
        if (!$productIDs)
            return 0;
        
        $productIDs = array_unique(explode(",", $productIDs));   

        $collection = Mage::getModel('catalog/product')->getCollection()
                                ->addAttributeToSelect('*')
                                ->addFieldToFilter('entity_id',array('in'=>$productIDs))
                                ->addAttributeToFilter('status', 1)
                                ->addAttributeToFilter('visibility', array("neq" => 1));  
        $collection->getSelect()->joinLeft(
                                    array('stock' => Mage::getModel('core/resource')->getTableName('cataloginventory_stock_item')),
                                    "e.entity_id = stock.product_id",
                                    array('stock.is_in_stock')
                                )->where('stock.is_in_stock = 1');
        $allProductIds = $collection->getAllIds();
        return count($allProductIds);
    }
     public function getBrandCollection(){
        if(is_null($this->_brandCollection)){
            $store = Mage::app()->getStore()->getId();
            $showNumberOfProducts = Mage::getStoreConfig('shopbybrand/brand_list/display_product_number', $store);
            $onlyBrandHaveProduct = Mage::getStoreConfig('shopbybrand/brand_list/display_brand_have_product', $store);
            
            $collection = Mage::getModel('shopbybrand/brand')->getCollection()
                ->setStoreId($store, array(
                    'name',
                    'is_featured'
                ))
                ->setOrder('position_brand','DESC')
                ->setOrder('name','ASC')
                ->addFieldToFilter('status',array('eq'=>1))
                ->load();
 
            if($showNumberOfProducts||$onlyBrandHaveProduct){
                $productIDs = Mage::getModel('catalog/category')
                    ->load(Mage::app()->getStore()->getRootCategoryId())
                    ->setIsAnchor(1)
                    ->getProductCollection()
                    ->getSelect()
                    ->assemble();
            }
//            $collection->getSelect()
//                ->reset(Zend_Db_Select::COLUMNS);
//            $collection->getSelect()->columns(array(
//                    'name',
//                    'url_key',
//                    'thumbnail_image',
//                    'category_ids',
//                    'is_featured'));
            
            if($showNumberOfProducts){
                $collection->getSelect()
                    ->joinLeft(array('product'=> new Zend_Db_Expr("($productIDs)")),'FIND_IN_SET(product.entity_id,main_table.product_ids)',array())
                    ->group('main_table.brand_id')
                    ->columns(array(
                        'number_product' => 'SUM(IF( product.entity_id > 0, 1, 0 ))'
                    ));
//               $collection->setIsGroupCountSql(true);
            }
            
            if ($onlyBrandHaveProduct)
                $collection->addFieldToFilter('SUM(IF( product.entity_id > 0, 1, 0 ))', array('neq' => 0));
            
            $this->_brandCollection = $collection;
        }
        return $this->_brandCollection;
    }
    
    public function getCateClass($str) {
        $array = explode(',', $str);
        foreach ($array as $key){
            if($key!='')
                echo ' c'.$key;
        }
    }
}