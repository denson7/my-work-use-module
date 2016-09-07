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
 * Shopbybrand Model
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Model_Brand extends Mage_Core_Model_Abstract {

    protected $_storeId = null;
    protected $_productIds = array();
    protected $_brandCollection = null;
    
    public function getStoreId() {
        return $this->_storeId;
    }

    public function setStoreId($storeId) {
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreAttributes() {
        return array(
            'name',
            'is_featured',
            'page_title',
            'meta_keywords',
            'meta_description',
            'short_description',
            'description',
            'status'
        );
    }

    public function load($id, $field = null) {
        parent::load($id, $field);
        if ($this->getStoreId()) {
            $this->loadStoreValue();
        }
        return $this;
    }

    public function loadStoreValue($storeId = null) {
        if (!$storeId)
            $storeId = $this->getStoreId();
        if (!$storeId)
            return $this;
        $storeValues = Mage::getModel('shopbybrand/brandvalue')->getCollection()
                ->addFieldToFilter('brand_id', $this->getId())
                ->addFieldToFilter('store_id', $storeId);
        foreach ($storeValues as $value) {
            $this->setData($value->getAttributeCode() . '_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }

        return $this;
    }

    protected function _beforeSave() {
        if ($storeId = $this->getStoreId()) {
            $defaultBrand = Mage::getModel('shopbybrand/brand')->load($this->getId());
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_default')) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                $this->setData($attribute, $defaultBrand->getData($attribute));
            }
        }
        return parent::_beforeSave();
    }

    protected function _afterSave() {
        if ($storeId = $this->getStoreId()) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                $attributeValue = Mage::getModel('shopbybrand/brandvalue')
                        ->loadAttributeValue($this->getId(), $storeId, $attribute);
                if ($this->getData($attribute . '_in_store')) {
                    try {
                        $attributeValue->setValue($this->getData($attribute . '_value'))
                                ->save();
                    } catch (Exception $e) {
                        
                    }
                } elseif ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
        $stores = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('store_id', array('neq' => 0));
        foreach ($stores as $store) {
            Mage::app()->getCacheInstance()->save(serialize(''), 'brand_data_'.$store->getId());
            Mage::app()->getCacheInstance()->save(serialize(''), 'brand_cate_data_'.$store->getId()); 
        }  
        return parent::_afterSave();
    }

    public function _construct() {
        parent::_construct();
        if ($storeId = Mage::app()->getStore()->getId()) {
            $this->setStoreId($storeId);
        }
        $this->_init('shopbybrand/brand');
    }

    public function updateUrlKey() {
        $id = $this->getId();
        $url_key = $this->getData('url_key');
        try {
            if ($this->getStoreId()) {
                                if((version_compare(Mage::getVersion(), '1.13', '>='))&&(version_compare(Mage::getVersion(), '1.14', '<'))){
					$urlrewrite = $this->loadByIdpath("brand/" . $id, $this->getStoreId());
					$urlrewrite->setData("identifier", "brand/" . $id);
					$urlrewrite->setData("entity_type", 1);
					$urlrewrite->setData("is_system", 1);
					$urlrewrite->setData("request_path", $this->getData('url_key'));
					$urlrewrite->setData("target_path", 'brand/index/view/id/' . $id);
				}else if((version_compare(Mage::getVersion(), '1.13', '>='))){
					$urlrewrite = $this->loadByIdpath("brand/" . $id, $this->getStoreId());
					$urlrewrite->setData("identifier", "brand/" . $id);
					$urlrewrite->setData("entity_type", 1);
					$urlrewrite->setData("is_system", 1);
					$urlrewrite->setData("request_path", $this->getData('url_key'));
					$urlrewrite->setData("target_path", 'brand/index/view/id/' . $id);
					$urlrewrite->setData("store_id", $this->getStoreId());
				}else{
					$urlrewrite = $this->loadByIdpath("brand/" . $id, $this->getStoreId());
					$urlrewrite->setData("id_path", "brand/" . $id);
					$urlrewrite->setData("request_path", $this->getData('url_key'));
					$urlrewrite->setData("target_path", 'brand/index/view/id/' . $id);
					$urlrewrite->setData("store_id", $this->getStoreId());
				}
                try {
                    $urlrewrite->save();
                } catch (Exception $e) {
                    
                }
            }else{
                $stores = Mage::getModel('core/store')->getCollection()
                        ->addFieldToFilter('is_active', 1)
                        ->addFieldToFilter('store_id', array('neq' => 0))
                ;
                foreach ($stores as $store) {
					if((version_compare(Mage::getVersion(), '1.13', '>='))){
						$rewrite = $this->loadByIdpath("brand/" . $id, $store->getId());
						$rewrite->setData("identifier", "brand/" . $id);
						$rewrite->setData("entity_type", 1);
						$rewrite->setData("is_system", 1);
						$rewrite->setData("request_path", $this->getData('url_key'));
						$rewrite->setData("target_path", 'brand/index/view/id/' . $id);
					}else{
						$rewrite = $this->loadByIdpath("brand/" . $id, $store->getId());
						$rewrite->setData("id_path", "brand/" . $id);
						$rewrite->setData("request_path", $this->getData('url_key'));
						$rewrite->setData("target_path", 'brand/index/view/id/' . $id);
					}
				   try {
                        $rewrite->setData('store_id', $store->getId())->save()
                        ;
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function getProductIds() {
        if (count($this->_productIds) == 0) {
            if ($this->getId()) {
                $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
                $optionId = $this->getOptionId();
                $collection = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addAttributeToSelect($attributeCode)
                        ->addAttributeToFilter($attributeCode, $optionId);
                $this->_productIds = $collection->getAllIds();
            }
        }
        return $this->_productIds;
    }

    public function getSubscriberIds() {
        $subIds = array(0);
        if ($this->getId()) {
            $brandSubscribers = Mage::getModel('shopbybrand/brandsubscriber')
                    ->getCollection()
                    ->addFieldToFilter('brand_id', $this->getId())
                    ->getAllSubscriberIds()
            ;
            $subIds = array_unique($brandSubscribers);
        }
        return $subIds;
    }

    public function deleteUrlRewrite() {
        if ($this->getId()) {
            $stores = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('is_active', 1)
            ;
            foreach ($stores as $store) {
                $url = $this->loadByIdPath('brand/' . $this->getId(), $store->getId());
                if ($url->getId()) {
                    $url->delete();
                }
            }
        }
    }
    /*add by cuong*/
    public function getFeaturedProductIds(){
        $defaultProduct = $this->getProductIds();
        $brandProductCollection = Mage::getModel('shopbybrand/brandproducts')->getCollection()
                ->addFieldToFilter('product_id', array('in' => $defaultProduct))
                ->addFieldToFilter('is_featured', 1);
        $array = array();
        foreach ($brandProductCollection as $collection){
            $array[$collection->getProductId()] = 0;
        }
        $productIds = array_unique(array_keys($array));
        $_products = Mage::getModel('catalog/product')->getCollection()
                        ->addAttributeToSelect(array('name', 'product_url', 'small_image'))
                        ->addAttributeToFilter('entity_id', array('in'=>$productIds));
        return $_products;
    }
    /*end add by cuong*/
   public function getBrandCollection(){
        if(is_null($this->_brandCollection)){
            $store = Mage::app()->getStore()->getId();
            $showNumberOfProducts = Mage::getStoreConfig('shopbybrand/brand_list/display_product_number', $store);
            $onlyBrandHaveProduct = Mage::getStoreConfig('shopbybrand/brand_list/display_brand_have_product', $store);
            $curentRouter = Mage::app()->getRequest()->getRouteName();
            $array = array(
                'name',
//                'position_brand'
            );
            $collection = $this->getCollection()
                ->setStoreId($store, $array)
                ->setOrder('position_brand','DESC')
                ->setOrder('name','ASC')
                ->addFieldToFilter('status',array('eq'=>1));
                if($showNumberOfProducts||$onlyBrandHaveProduct){
                    $productIDs = Mage::getModel('catalog/category')
                        ->load(Mage::app()->getStore()->getRootCategoryId())
                        ->setIsAnchor(1)
                        ->getProductCollection()
                        ->addAttributeToFilter('status', array('in'=>Mage::getSingleton('catalog/product_status')->getVisibleStatusIds()))
                        ->addAttributeToFilter('visibility', array('in'=>Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds()))
                        ->getSelect()
                        ->assemble();
                    $collection->getSelect()
                        ->joinLeft(array('product'=> new Zend_Db_Expr("($productIDs)")),'FIND_IN_SET(product.entity_id,main_table.product_ids)',array())
                        ->group('main_table.brand_id')
                        ->columns(array(
                            'number_product' => 'SUM(IF( product.entity_id > 0, 1, 0 ))'
                        ));
                }
                if ($onlyBrandHaveProduct)
                    $collection->addFieldToFilter('SUM(IF( product.entity_id > 0, 1, 0 ))', array('neq' => 0));
            $this->_brandCollection = $collection;
        }
        return $this->_brandCollection;
    }
    public function getBrand(){
        if(!$this->hasData('current_brand')){ 
            $this->setStoreId(Mage::app()->getStore()->getId())
                    ->load(Mage::app()->getRequest()->getParam("id"));
            $this->setData('current_brand', $this);
        }
        return $this->getData('current_brand');
    }
    
    public function getBrandsData() {
        if($this->getBrandCollectionData())
            return $this->getBrandCollectionData();
        $store = Mage::app()->getStore()->getId();
        $brandData = unserialize(Mage::app()->getCacheInstance()->load('brand_data_'.$store));
        if($brandData)
            return $brandData;  
        $brandData = $this->getBrandCollection();
        $array = array();
        foreach ($brandData as $brand) {
            $data['brand_id'] = $brand->getData('brand_id');
            $data['name'] = $brand->getData('name');
            $data['url_key'] = $brand->getData('url_key');
            $data['thumbnail_image'] = $brand->getData('thumbnail_image');
            $data['category_ids'] = $brand->getData('category_ids');
            $data['number_product'] = $brand->getData('number_product');
            $array[] = $data;
        }
        Mage::app()->getCacheInstance()->save(serialize($array), 'brand_data_'.$store); 
        $this->setBrandCollectionData($array);
        return $array;
    }
    
    public function loadByIdpath($idPath, $storeId){
		if((version_compare(Mage::getVersion(), '1.13', '>='))&&(version_compare(Mage::getVersion(), '1.14', '<'))){
			$model = Mage::getModel('enterprise_urlrewrite/url_rewrite')->getCollection()
				->addFieldToFilter('identifier', $identifier)
				->getFirstItem();
		}else if((version_compare(Mage::getVersion(), '1.13', '>='))){
			$model = Mage::getModel('enterprise_urlrewrite/url_rewrite')->getCollection()
				->addFieldToFilter('identifier', $identifier)
				->addFieldToFilter('store_id', $storeId)
				->getFirstItem();
		}else{
			$model = Mage::getModel('core/url_rewrite')->getCollection()
				->addFieldToFilter('id_path', $idPath)
				->addFieldToFilter('store_id', $storeId)
				->getFirstItem();
		}
		return $model;
    }
    public function loadByRequestPath($requestPath, $storeId){
        if((version_compare(Mage::getVersion(), '1.13', '>='))){
            $model = Mage::getModel('enterprise_urlrewrite/url_rewrite');
        }else{
            $model = Mage::getModel('core/url_rewrite');
        }
        $collection = $model->getCollection();
        $collection->addFieldToFilter('request_path', $requestPath);
        if($storeId&&!(version_compare(Mage::getVersion(), '1.13', '>='))&&(version_compare(Mage::getVersion(), '1.14', '<')))
            $collection->addFieldToFilter('store_id', $storeId);
    	if($collection->getSize()){
            $model = $collection->getFirstItem();
        }
        return $model;
    }

}