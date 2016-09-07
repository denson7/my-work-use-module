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
 * Brand Helper
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Helper_Brand extends Mage_Core_Helper_Abstract {

    protected $_storeId = null;
    protected $_brand_product=null;

    public function getStoreId() {
        if (is_null($this->_storeId))
            $this->_storeId = Mage::app()->getStore()->getId();
        return $this->_storeId;
    }

    public function getAttributeCode() {
        $storeId = $this->getStoreId();
        if(!$storeId)
            $storeId = 0;
        $attributeCode = Mage::getStoreConfig('shopbybrand/general/attribute_code', $storeId);
        return $attributeCode ? $attributeCode : 'manufacturer';
    }

    public function getOptionStore() {
        $arrStore = array();
        $arrOptionStore = array();
        $arrOptionStore[] = array('value' => 0, 'label' => 'admin');
        $collection_store = Mage::getModel('core/store')->getCollection();
        foreach ($collection_store as $store) {
            $arrOptionStore[] = array('value' => $store->getId(), 'label' => $store->getName(),);
        }
        return $arrOptionStore;
    }

    public function getOptionData($option) {
        $storeName = Mage::getModel('core/store')->load($option['store_id'])->getData('name');
        $urlKey = $option['value'];
        $data['name'] = $option['value'];
        $data['page_title'] = $option['value'];
        $data['meta_keywords'] = $option['value'];
        $data['meta_description'] = $option['value'];
        $data['option_id'] = $option['option_id'];
        $data['status'] = 1;
        $data['created_time'] = now();
        $data['update_time'] = now();
        $data['url_key'] = Mage::helper('shopbybrand')->refineUrlKey($urlKey);
        return $data;
    }

    public function insertBrandFromOption($option) {
        if (isset($option['store_id'])) {
            $data = $this->getOptionData($option);
            $model = Mage::getModel('shopbybrand/brand')->load($option['option_id'], 'option_id');
            $model->addData($data);
            $productIds = $this->getProductIdsByBrand($model);
            if (is_string($productIds))
                $model->setProductIds($productIds);
            $urlKey = $model->getUrlKey();
            $urlRewrite = Mage::getModel('shopbybrand/brand')->loadByRequestPath($urlKey, $option['store_id']);
            if(!$model->getId()){
                if($urlRewrite->getId()){
                    $urlKey = $urlKey.'-2';
                    $model->setData('url_key', $urlKey);
                }
            }
            $model->setStoreId($option['store_id'])
                    ->save();
            $categoryIds = $this->getCategoryIdsByBrand($model);
            if (is_string($categoryIds) && $categoryIds) {
                $model->setCategoryIds($categoryIds)
                        ->save();
            }
            //update url_key
           if ($option['store_id'] == 0)
                $model->updateUrlKey();
        }
    }

    public function updateBrandsFormCatalog() {
        $defaultOptionBrands = Mage::getResourceModel('shopbybrand/brand')->getCatalogBrand(true);
        $storeOptionBrands = Mage::getResourceModel('shopbybrand/brand')->getCatalogBrand(false);
        foreach($defaultOptionBrands as $option){
            $this->insertBrandFromOption($option);
        }
        foreach($storeOptionBrands as $option){
            $defaultBrand = Mage::getModel('shopbybrand/brand')->load($option['option_id'], 'option_id');
            $brandValue = Mage::getModel('shopbybrand/brandvalue')->loadAttributeValue($defaultBrand->getId(), $option['store_id'], 'name');
            if ($brandValue->getValue() != $option['value']) {
                $brandValue->setData('value', $option['value'])
                        ->save();
            }
        }
    }

    public function getCategoryIdsByBrand($brand) {
        $catIds = array();
        $collection = Mage::getModel('shopbybrand/brand')
                ->getCollection()
                ->addFieldToFilter('brand_id', $brand->getId());
        $resource = Mage::getSingleton('core/resource');
        $collection->getSelect()
                ->join(array('category_product' => $resource->getTableName('catalog_category_product')), 'FIND_IN_SET(category_product.product_id, main_table.product_ids)', 'category_id')
                ->group('category_id')
        ;
        if ($collection->getSize()) {
            $catIds = $collection->getCategoryIdsFromProducts('category_product');
        }
        $catIds = implode(',', $catIds);
        return $catIds;
    }

    public function getProductIdsByBrand($brand) {
        $attributeCode = $this->getAttributeCode();
        $optionId = $brand->getOptionId();
        $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter($attributeCode, $optionId);
        $roductIds = implode(",", $collection->getAllIDs());
        return $roductIds;
    }

    /**
     * get brands order most subscribers
     * @return brand collection
     */
    public function getMostSubscriber() {
        $storeId = Mage::app()->getStore()->getId();
        $resource = Mage::getSingleton('core/resource');
        $collection=$this->getBrandProduct();
        $collection->getSelect()
                ->joinLeft(array('brand_subscriber' => $resource->getTableName('brand_subscriber')), 'main_table.brand_id = brand_subscriber.brand_id',array(''))
                ->group('main_table.brand_id')
                ->columns(array(
                    'subscribers'=>'SUM(IF(brand_subscriber.brand_id IS NULL, 0, 1))',
                ))
                ->order('subscribers DESC');
        ;
        $maxBrand = Mage::getStoreConfig('shopbybrand/general/number_brand_top', $storeId);
        if($maxBrand)
            $collection->getSelect()->limit($maxBrand);
        return $collection;
    }

    public function getBrandTopSeller() {
        $storeId = Mage::app()->getStore()->getId();
        $collection = Mage::getModel('shopbybrand/brand')->getCollection()->setStoreId($storeId);
        $productIDs=Mage::getModel('shopbybrand/layer')
                ->getProductCollection()
                ->getSelect()
                ->assemble();
        $brandproduct=Mage::getModel('shopbybrand/brand')
                ->getCollection()
                ->addFieldToFilter('status', array('eq' => 1))
                ->getSelect()
                ->joinleft(array('product'=> new Zend_Db_Expr("($productIDs)")),'FIND_IN_SET(product.entity_id,main_table.product_ids)')
                ->group('main_table.brand_id')
                ->columns(array(
                    'number_product' => 'SUM(IF( product.entity_id > 0, 1, 0 ))'
                ))
                ->assemble();
        $collection->getSelect()
                ->joinleft(array('sfoi' => Mage::getModel('core/resource')->getTableName('sales_flat_order_item')), 'FIND_IN_SET(sfoi.product_id, main_table.product_ids)', array('qty_ordered', 'base_row_total',));
        $collection->addFieldToFilter('status', array('eq' => 1))
                ->getSelect()
                ->joinleft(array('brand_product'=> new Zend_Db_Expr("($brandproduct)")),'brand_product.brand_id=main_table.brand_id')
                ->group('main_table.brand_id')
                ->columns(array(
                    'number_product' => 'brand_product.number_product',
                    'brand_qty_ordered' => 'SUM(IF( qty_ordered > 0, qty_ordered, 0 ))',
                    'brand_base_row_total' => 'SUM(IF( base_row_total > 0, base_row_total, 0 ))'
                ))
                ->order('brand_qty_ordered DESC');
        $onlyBrandHaveProduct = Mage::getStoreConfig('shopbybrand/optional/display_brand_have_product', $storeId);
        if ($onlyBrandHaveProduct)
            $collection->addFieldToFilter('brand_product.number_product', array('neq' => 0));
        $maxBrand = Mage::getStoreConfig('shopbybrand/general/number_brand_top', $storeId);
        if($maxBrand)
            $collection->getSelect()->limit($maxBrand);
        return $collection;
    }

    public function getBrandTopNew() {
        $storeId = Mage::app()->getStore()->getId();
        $collection=$this->getBrandProduct();
        $collection->getSelect()
                ->order('updated_time DESC');
        $maxBrand = Mage::getStoreConfig('shopbybrand/general/number_brand_top', $storeId);
        if($maxBrand)
            $collection->getSelect()->limit($maxBrand);
        return $collection;
    }
    public function getBrandTopProduct() {
        $storeId = Mage::app()->getStore()->getId();
        $collection=$this->getBrandProduct();
        $collection->getSelect()
                ->order('number_product DESC');
        $maxBrand = Mage::getStoreConfig('shopbybrand/general/number_brand_top', $storeId);
        if($maxBrand)
            $collection->getSelect()->limit($maxBrand);
        return $collection;
    }
    public function getBrandProduct() {
        if(is_null($this->_brand_product)){
        $storeId = Mage::app()->getStore()->getId();
        $collection = Mage::getModel('shopbybrand/brand')->getCollection()->setStoreId($storeId);
        $productIDs=Mage::getModel('shopbybrand/layer')
                ->getProductCollection()
                ->getSelect()
                ->assemble();
        $collection->addFieldToFilter('status', array('eq' => 1))
                ->getSelect()
                ->joinleft(array('product'=> new Zend_Db_Expr("($productIDs)")),'FIND_IN_SET(product.entity_id,main_table.product_ids)')
                ->group('main_table.brand_id')
                ->columns(array(
                    'number_product' => 'SUM(IF( product.entity_id > 0, 1, 0 ))'
                ));
        $onlyBrandHaveProduct = Mage::getStoreConfig('shopbybrand/optional/display_brand_have_product', $storeId);
        if ($onlyBrandHaveProduct)
            $collection->addFieldToFilter('SUM(IF( product.entity_id > 0, 1, 0 ))', array('neq' => 0));
        
        $this->_brand_product=$collection;
        }
        return $this->_brand_product;
    }

    public function reindexBrandCategories() {
        $brandCollection = Mage::getModel('shopbybrand/brand')
                ->getCollection();
        foreach ($brandCollection as $brand) {
            $categoryIds = $this->getCategoryIdsByBrand($brand);
            if ($categoryIds != $brand->getCategoryIds()) {
                $brand->setCategoryIds($categoryIds)
                        ->save();
            }
        }
    }

    /**
     * update product ids for brand
     * @param type $productIds
     */
    /* add and edit by Peter */
    public function updateProductsForBrands($productIds, $brand) {
        $brands = Mage::getModel('shopbybrand/brand')
            ->getCollection()
            ->setOrder('brand_id','DESC')
            ->getFirstItem();
        if($brands->getOptionId() == null){
            $brandCollection = Mage::getModel('shopbybrand/brand')
                    ->getCollection()
                    ->addFieldToFilter('brand_id', array('neq' => $brand->getId()));
            foreach ($brandCollection as $br) {
                $brandProductIds = explode(',', $br->getData('product_ids'));
                $oldSize = count($brandProductIds);
                $brandProductIds = array_diff($brandProductIds, $productIds);
                $newSize = count($brandProductIds);
                if ($oldSize > $newSize) {
                    $br->setProductIds(implode(',', $brandProductIds))->save();
                }
            }
            if($brands->getData('product_ids')){
                $brands->setData('product_ids', $brands->getData('product_ids').','.implode(',', $productIds))->save();
            }else{
                $brands->setProductIds(implode(',', $productIds))->save();
            }
        }
        else{
            $brandCollection = Mage::getModel('shopbybrand/brand')
                ->getCollection()
                ->addFieldToFilter('brand_id', array('neq' => $brand->getId()));
            foreach ($brandCollection as $br) {
                $brandProductIds = explode(',', $br->getData('product_ids'));
                $oldSize = count($brandProductIds);
                $brandProductIds = array_diff($brandProductIds, $productIds);
                $newSize = count($brandProductIds);
                if ($oldSize > $newSize) {
                    $br->setProductIds(implode(',', $brandProductIds))->save();
                }
            }
            if($brand->getData('product_ids')){
                $brand->setData('product_ids', $brand->getData('product_ids').','.implode(',', $productIds))->save();
            }else{
                $brand->setProductIds(implode(',', $productIds))->save();
            }
        }
    }
    /* end add and edit by Peter */
    
    public function reindexBrandUrls() {
        $brandCollection = Mage::getModel('shopbybrand/brand')
                ->getCollection();
        foreach ($brandCollection as $brand) {
            $brand->updateUrlKey();
        }
    }
    
    public function getParentCategories($catids=null){
        $brandId = Mage::app()->getRequest()->getParam("id");
        if(!$brandId){
                $brandData = unserialize(Mage::app()->getCacheInstance()->load('brand_cate_data_'.$this->getStoreId()));
                if($brandData)
                        return $brandData;
        }else{
                $brandData = unserialize(Mage::app()->getCacheInstance()->load('brand_cate_data_'.$this->getStoreId().'_'.$brandId));
                if($brandData)
                        return $brandData;
        }
        $cats = array();
        $parentIds = array();
        $children = array();
        if(is_null($catids)){
            $catids = Mage::getModel('shopbybrand/brand')
                    ->getCollection()->setStoreId($this->getStoreId())
                    ->getAllCategories();
            $catids = implode(",", $catids);
            $catids = explode(",", $catids);
            $catids = array_unique($catids);
        }
        $catRootId = Mage::app()->getStore()->getRootCategoryId();
        unset($catids[array_search($catRootId, $catids)]);
        $categories = Mage::getModel('catalog/category')->getCollection()
                ->setStoreId($this->getStoreId())
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToFilter('level', array('gteq'=>2))
                ->addFieldToFilter('entity_id', array('in' => $catids));
        
        foreach($categories as $category){
            $parents = $category->getParentIds();
            if(count(array_intersect($parents, $catids))== 0){
                $parentIds[$category->getId()] = $category;
            }else{
                foreach(array_intersect($parents, $catids) as $parentId){
                    $children[$parentId][] = $category;
                }
            }
        }
        $cats['parent'] = $parentIds;
        $cats['children'] = $children;
        if(!$brandId){
                Mage::app()->getCacheInstance()->save(serialize($cats), 'brand_cate_data_'.$this->getStoreId()); 
        }else{
                Mage::app()->getCacheInstance()->save(serialize($cats), 'brand_cate_data_'.$this->getStoreId().'_'.$brandId); 
        }
        return $cats;
    }
}