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
 * Shopbybrand Resource Model
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Model_Mysql4_Brand extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('shopbybrand/brand', 'brand_id');
    }
    
    public function getCatalogBrand($allStore = false)
	{
		$prefix = Mage::helper('shopbybrand')->getTablePrefix();			
		$attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
		$select = $this->_getReadAdapter()->select()
					->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id','eaov.value','eaov.store_id'))
					->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())
					->join(array('eaov'=> $prefix .'eav_attribute_option_value'),'eao.option_id=eaov.option_id',array())
					->where('ea.attribute_code=?',$attributeCode);
        if($allStore)
            $select->where('eaov.store_id=?',0);
        else {
            $select->where('eaov.store_id !=?',0);
        }
		$option = $this->_getReadAdapter()->fetchAll($select);
		return $option;	
	}
    
    public function addOption($brand){
//        $op = Mage::getModel('eav/entity_attribute_option')->load($brand->getOptionId());
        $prefix = Mage::helper('shopbybrand')->getTablePrefix();			
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $brandStoreId = 0;
        if($brand->getOptionId()){
            if($brand->getStoreId())
                $brandStoreId = $brand->getStoreId();
            $select = $this->_getReadAdapter()->select()
                ->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id','eaov.value','eaov.store_id'))
                ->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())
                ->join(array('eaov'=> $prefix .'eav_attribute_option_value'),'eao.option_id=eaov.option_id',array())
                ->where('ea.attribute_code=?',$attributeCode)
                ->where('eao.option_id=?', $brand->getOptionId())
                ->where('eaov.store_id=?', $brandStoreId)
            ;
            $storeValue = $this->_getReadAdapter()->fetchAll($select);
            if(count($storeValue)){
                foreach ($storeValue as $value){
                    if(isset($value['value'])&& $value['value']){
                        if($value['value'] == $brand->getName())
                            return ;
                        else{
                            $data = array(
                                'value' => $brand->getName()
                            );
                            $where= array(
                                'option_id=?' => $brand->getOptionId(),
                                'store_id=?' => $brandStoreId
                            );
                            $update = $this->_getWriteAdapter()->update($prefix.'eav_attribute_option_value', $data, $where);
                        }
                    }
                }
            }else{
                $eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
				$attId = $eavAttribute->getIdByCode('catalog_product', $attributeCode);
				$data = array(

                    'value' => $brand->getName(),

                    'option_id' => $brand->getOptionId(),

                    'store_id' => $brandStoreId

                );
				$select = $this->_getReadAdapter()->select()

					->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id'))

					->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())

					->where('ea.attribute_code=?',$attributeCode)

					->where('eao.option_id=?', $brand->getOptionId())

				;
				$storeValue = $this->_getReadAdapter()->fetchAll($select);
				if(count($storeValue) == 0){
					$optionData = array(
						'option_id' => $brand->getOptionId(),
						'attribute_id' => $attId,
						'sort_order' => 0

					);
					$option = $this->_getWriteAdapter()->insert($prefix.'eav_attribute_option', $optionData);
				}
				try{
					$update = $this->_getWriteAdapter()->insert($prefix.'eav_attribute_option_value', $data);
				}catch(Exception $e){
				}
            }
        }else{
            $attributeId = Mage::getSingleton('eav/config')
                ->getAttribute('catalog_product', $attributeCode)->getId();
            $setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
            $option['attribute_id'] = $attributeId;
            if($brand->getStoreId())
                $option['value']['option'][$brand->getStoreId()] = $brand->getName();
            else {
                $option['value']['option'][0] = $brand->getName();
            }
            $setup->addAttributeOption($option);
            //get option id
            $select = $this->_getReadAdapter()->select()
                ->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id','eaov.value','eaov.store_id'))
                ->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())
                ->join(array('eaov'=> $prefix .'eav_attribute_option_value'),'eao.option_id=eaov.option_id',array())
                ->where('ea.attribute_code=?',$attributeCode)
                ->where('eaov.value=?', $brand->getName())
                ->where('eaov.store_id=?', $brandStoreId)
            ;
            $option = $this->_getReadAdapter()->fetchAll($select);
            if(count($option)){
                $optionId = $option[0]['option_id'];
                return $optionId;
            }
        }
        return null;
    }
    
    public function addMultiOption($arrayName) {
        $prefix = Mage::helper('shopbybrand')->getTablePrefix();			
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $attributeId = Mage::getSingleton('eav/config')
            ->getAttribute('catalog_product', $attributeCode)->getId();
        $setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
        $option['attribute_id'] = $attributeId;
        $option['value']['option'][0] = $brand->getName();
        $setup->addAttributeOption($option);
        $select = $this->_getReadAdapter()->select()
            ->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id','eaov.value','eaov.store_id'))
            ->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())
            ->join(array('eaov'=> $prefix .'eav_attribute_option_value'),'eao.option_id=eaov.option_id',array())
            ->where('ea.attribute_code=?',$attributeCode)
            ->where('eaov.value=?', $brand->getName())
            ->where('eaov.store_id=?',  0);
        $option = $this->_getReadAdapter()->fetchAll($select);
        if(count($option)){
            $optionId = $option[0]['option_id'];
            $setup->endSetup();
            return $optionId;
        }
        $setup->endSetup();
        return FALSE;
    }
    
    public function removeOption($brand){
        $op = Mage::getModel('eav/entity_attribute_option')->load($brand->getOptionId());
        $prefix = Mage::helper('shopbybrand')->getTablePrefix();			
		$attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $brandStoreId = 0;
        if($brand->getOptionId()){
            if($brand->getStoreId())
                $brandStoreId = $brand->getStoreId();
            $option = Mage::getModel('eav/entity_attribute_option')->load($brand->getOptionId());
            try{
                $option -> delete();
            }  catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }
    
    public function getBrandByOption($option)
	{
        $brand = Mage::getModel('shopbybrand/brand')
                    ->setStoreId($option['store_id']);
		if(isset($option['option_id']) && $option['option_id']){
            $brand->load($option['option_id'], 'option_id');
        }
		return $brand;
	}
    
    public function convertData() {
        $data = $this->getOldDataBrand();
        if (!count($data))
            return;
        foreach ($data as $value) {
           
            if ($value['store_id'] == 0) {
                $urlRewrite = Mage::getModel('shopbybrand/brand')->loadByRequestPath($value['url_key']);
                if($urlRewrite->getId())
                    $urlRewrite->delete();
                $modelBrand = $this->getBrandByName($value['name']);
                $dataBrand = array(
                    'name' => $value['name'],
                    'url_key' => $value['url_key'],
                    'page_title' => $value['page_title'],
                    'image' => $value['image'],
                    'thumbnail_image' => $value['image_thumbnail'],
                    'is_featured' => $value['featured'],
                    'meta_keywords' => $value['meta_keywords'],
                    'meta_description' => $value['meta_description'],
                    'short_description' => $value['description_short'],
                    'description' => $value['description'],
                    'status' => $value['status'],
                    'brand_id' => $modelBrand->getBrandId(),
                    'created_time' => $value['created_time'],
                    'updated_time' => $value['update_time'],
                    'order' => $value['ordering'],
                    'option_id'=>$value['option_id'],
                );
                $modelBrand->setData($dataBrand)->setStoreId($value['store_id']);
                try {
                    
                    $productIds = Mage::helper('shopbybrand/brand')->getProductIdsByBrand($modelBrand);
                    if(is_string($productIds))
                    $modelBrand->setProductIds($productIds)->save();
                    
                    $categoryIds=Mage::helper('shopbybrand/brand')->getCategoryIdsByBrand($modelBrand);
                    if(is_string($categoryIds))
                    $modelBrand->setCategoryIds($categoryIds);
                    
                    
                    $modelBrand->save();
                    $modelBrand->updateUrlKey();
                    if($value['image'])
                    $this->copyImagOldData($modelBrand,'image',$value['image']);
                    if($value['image_thumbnail'])
                    $this->copyImagOldData($modelBrand,'thumbnail_image',$value['image_thumbnail']);
                } catch (Exception $exc) {
//                echo $exc->getTraceAsString();
                }
            } else {
                $modelBrand = $this->getBrandByName($value['name']);
                if (!$value['default_name_store']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'name', $value['name_store']);
                }
                if ($value['url_key'] && $value['url_key'] != $modelBrand->getUrlKey()) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'url_key', $value['url_key']);
                }
                if (!$value['default_page_title']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'page_title', $value['page_title']);
                }
                if (!$value['default_image']  && $value['image']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'image', $value['image']);
                }
                if ($value['image_thumbnail'] && $value['image_thumbnail'] != $modelBrand->getThumbnailImage()) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'thumbnail_image', $value['image_thumbnail']);
                }
                if (!$value['default_featured']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'is_featured', $value['featured']);
                }
                if (!$value['default_meta_keywords']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'meta_keywords', $value['meta_keywords']);
                }
                if (!$value['default_meta_description']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'meta_description', $value['meta_description']);
                }
                if (!$value['default_description_short']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'short_description', $value['description_short']);
                }
                if (!$value['default_description']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'description', $value['description']);
                }
                if (!$value['default_status']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'status', $value['status']);
                }
            }
        }
    }

    public function getBrandByName($name) {
        $collection = Mage::getModel('shopbybrand/brand')->getCollection()
                ->addFieldToFilter('name', $name);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }
        return Mage::getModel('shopbybrand/brand');
    }

    public function saveStoreValue($brand, $storeId, $attribute, $value) {
        $attributeValue = Mage::getModel('shopbybrand/brandvalue')
                ->loadAttributeValue($brand->getId(), $storeId, $attribute);
        try {
            $this->copyImagOldData($brand,$attribute,$value);
            $attributeValue->setValue($value)
                    ->save();
        } catch (Exception $e) {
            
        }
    }

    public function getOldDataBrand() {
        $prefix = Mage::helper('shopbybrand')->getTablePrefix();
        try {
            $select = $this->_getReadAdapter()->select()
                    ->from(array('manu' => $prefix . 'manufacturer'));
            $data = $this->_getReadAdapter()->fetchAll($select);
        } catch (Exception $exc) {
//            echo $exc->getTraceAsString();
        }
        return $data;
    }
    public function copyImagOldData($brand,$type,$image){
		if($type=='image'){
			Mage::helper('shopbybrand')->createImageFolder($brand->getId());
			$copyfrom = Mage::getBaseDir('media') . DS .'manufacturers' .DS. strtolower(substr($brand->getName(),0,1)).substr(md5($brand->getName()),0,10). Mage::helper('shopbybrand')->refineUrlKey($brand->getName()).DS.$image;
			// $copyto = Mage::getBaseDir('media') . DS .'brands' .DS. strtolower(substr($brand->getName(),0,1)).substr(md5($brand->getName()),0,10). Mage::helper('shopbybrand')->refineUrlKey($brand->getName()).DS.$image;
			$copyto = Mage::getBaseDir('media') . DS .'brands' .DS. $brand->getId().DS.$image;
			copy($copyfrom, $copyto);
			$copyfrom = Mage::getBaseDir('media') . DS .'manufacturers\cache' .DS. strtolower(substr($brand->getName(),0,1)).substr(md5($brand->getName()),0,10). Mage::helper('shopbybrand')->refineUrlKey($brand->getName()).DS.$image;
			// $copyto = Mage::getBaseDir('media') . DS .'brands\cache' .DS. strtolower(substr($brand->getName(),0,1)).substr(md5($brand->getName()),0,10). Mage::helper('shopbybrand')->refineUrlKey($brand->getName()).DS.$image;
            $copyto = Mage::getBaseDir('media') . DS .'brands\cache' .DS.$brand->getId().DS.$image;
			copy($copyfrom, $copyto);
		}
		if($type=='thumbnail_image'){
			Mage::helper('shopbybrand')->createThumbnailImageFolder($brand->getId());
			$copyfrom = Mage::getBaseDir('media') . DS .'manufacturers\thumbnail' .DS. strtolower(substr($brand->getName(),0,1)).substr(md5($brand->getName()),0,10). Mage::helper('shopbybrand')->refineUrlKey($brand->getName()).DS.$image;
			$copyto = Mage::getBaseDir('media') . DS .'brands\thumbnail' .DS. $brand->getId() .DS.$image;
			copy($copyfrom, $copyto);
		}
	}
    
    public function getAttributeOptions($value){
        $prefix = Mage::helper('shopbybrand')->getTablePrefix();			
		$attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
		$select = $this->_getReadAdapter()->select()
					->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id','eaov.value','eaov.store_id'))
					->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())
					->join(array('eaov'=> $prefix .'eav_attribute_option_value'),'eao.option_id=eaov.option_id',array())
					->where('ea.attribute_code=?',$attributeCode);
        //$select->where('eaov.store_id=?',0);
		$select->where('eaov.value=?',$value);
		$option = $this->_getReadAdapter()->fetchAll($select);
		return $option;	
    }
    
    
    public function import($is_update) {
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
            $fileName   = $_FILES['csv_brand']['tmp_name'];
            $csvObject  = new Varien_File_Csv();
            $csvData = $csvObject->getData($fileName);
            $number = array('insert'=>0 , 'update' => 0);
            $brandUpdate = array();
            /** checks columns */
            $csvFields  = array(
                0    => 'Name',
                1    => 'Sort Order',
                2    => 'URL Key',
                3    => 'Page Title',
                4    => 'Is Featured',
                5    => 'Status',
                6    => 'Short Description',
                7    => 'Description',
                8    => 'Meta Keywords',
                9    => 'Meta Description',
            );

            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');				
            $brandTable = $resource->getTableName('shopbybrand/brand');
            $allStores = Mage::app()->getStores();
            $lastbrandId = Mage::getModel('shopbybrand/brand')->getCollection()
                    ->addFieldtoSelect('brand_id')
                    ->getLastItem()
                    ->getId();
            if ($csvData[0] == $csvFields) {
                $arrayUpdate = $this->csvGetArrName($csvData);
                
                
                $prefix = Mage::helper('shopbybrand')->getTablePrefix();			
                $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
                $attributeId = Mage::getSingleton('eav/config')
                    ->getAttribute('catalog_product', $attributeCode)->getId();
                $setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
                $option['attribute_id'] = $attributeId;
                $optionId = 0;
                
                                
                try {
                    foreach ($csvData as $k => $v) {
                        if ($k == 0) {
                            continue;
                        }
                        //end of file has more then one empty lines
                        if (count($v) <= 1 && !strlen($v[0])) {
                            continue;
                        }                		
                        if (!empty($v[0])) {
                            $data  = array(
                                'name'              => trim(preg_replace('/[^\w\s-]/','',$v[0])),
                                'position_brand'    => (is_numeric($v[1]))?$v[1]:0,
                                'url_key'           => Mage::helper('shopbybrand')->refineUrlKey($v[2]),
                                'page_title'        => trim(preg_replace('/[^\w\s-]/','',$v[3])),
                                'is_featured'       => (is_numeric($v[4]))?$v[4]:0,
                                'status'            => (is_numeric($v[5]))?$v[5]:0,
                                'short_description' => trim($v[6]),
                                'description'       => trim($v[7]),
                                'meta_keywords'     => trim($v[8]),
                                'meta_description'  => trim($v[9])
                            );
                            if($data['url_key']=='')
                                $data['url_key'] = Mage::helper('shopbybrand')->refineUrlKey($data['name']);
                            if(in_array($v[0], $arrayUpdate)){
                                if($is_update){
                                    $number['update']++;
                                    $write->update($brandTable, $data, 'name = "'.$data['name'].'"');
                                }
                                continue;
                            }
                            
                            $option['value']['option'][0] = $data['name'];
                            $setup->addAttributeOption($option);
                            if($optionId==0){
                                $select = $this->_getReadAdapter()->select()
                                    ->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id','eaov.value','eaov.store_id'))
                                    ->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())
                                    ->join(array('eaov'=> $prefix .'eav_attribute_option_value'),'eao.option_id=eaov.option_id',array())
                                    ->where('ea.attribute_code=?',$attributeCode)
                                    ->where('eaov.value=?', $data['name'])
                                    ->where('eaov.store_id=?',  0);
                                $newOption = $this->_getReadAdapter()->fetchAll($select);
                                if(count($newOption)){
                                    $optionId = $newOption[0]['option_id'];
                                }
                            }  else {
                                $optionId++;
                            }
                            $data['option_id'] = $optionId;
                            $dataBrand[] = $data;
                            $number['insert']++;
                            if (count($dataBrand) >= 200) {
                               $write->insertMultiple($brandTable, $dataBrand);
                               $dataBrand = array();
                            }
                        }
                    }
                    if (!empty($dataBrand)) {
                        $write->insertMultiple($brandTable, $dataBrand);
                    }
                    $write->commit();
                }catch (Exception $e) {
                    $write->rollback();
                    throw $e;
                }
                $setup->endSetup();
            }
            else {
                Mage::throwException(Mage::helper('shopbybrand')->__('Please choose the csv file as sample to import brands.'));
            }
        return $number;
    }
    public function csvGetArrName($csvData) {
        $array = array();
        foreach ($csvData as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $array[] = $v[0];
        }
        $shopbybrands = Mage::getModel('shopbybrand/brand')
            ->getCollection()
            ->addFieldToFilter('name', array('in' => $array))
            ->getAllField('name');
        return $shopbybrands;
    }
    
    public function csvGetArrId($csvData) {
        $array = array();
        foreach ($csvData as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $array[] = $v[0];
        }
        $shopbybrands = Mage::getModel('shopbybrand/brand')
            ->getCollection()
            ->addFieldToFilter('name', array('in' => $array))
            ->getAllField('brand_id');
        return $shopbybrands;
    }
    
    public function csvGetArrUrl($csvData) {
        $array = array();
        foreach ($csvData as $k => $v) {
            if ($k == 2) {
                continue;
            }
            $array[] = $v[2];
        }
        $rewrite = Mage::getModel('core/url_rewrite')->getCollection()
            ->addFieldToFilter('request_path',array('nin'=>$array))
            ->addFieldToFilter('store_id',1)
            ->getData();
//            ->getAllField('name');
        return $rewrite;
    }
    
    public function createUrlRewrite($brands){
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $urlRewriteTable = $resource->getTableName('core/url_rewrite');
        $allStores = Mage::app()->getStores();
        try {
            foreach ($brands as $brand) {
                foreach ($allStores as $_eachStoreId => $val) {
                    $urlKey = $brand->getUrlKey();
                    $select = $read->select()
                        ->from($urlRewriteTable)
                        ->where('store_id = "'.$_eachStoreId.'" and request_path ="'.$urlKey.'"')									
                        ->limit(1);
                    if($read->fetchOne($select))
                        $urlKey.='_1';
                    $data = array(
                        'store_id'      => $_eachStoreId,
                        'id_path'       => 'brand/'.$brand->getId(),
                        'request_path'  => $urlKey,
                        'target_path'   => 'brand/index/view/id/'.$brand->getId(),
                        'is_system'     => 1
                    );
                    
                    $dataUrl[] = $data;
                    if (count($dataUrl) >= 200) {
                        $write->insertMultiple($urlRewriteTable, $dataUrl);
                        $dataUrl = array();
                    }
                }
            }
            if (!empty($dataUrl)) {
                $write->insertMultiple($urlRewriteTable, $dataUrl);
            }
            $write->commit();
        }catch (Exception $e) {
            $write->rollback();
            throw $e;
        }
    }
    
    public function deleteOldUrl($brandIds) {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $urlRewriteTable = $resource->getTableName('core/url_rewrite');
        try {
        foreach ($brandIds as $key => $brandId){
            $brandIds[$key] = "'brand/$brandId'";
        }
        $where = '(id_path IN('.implode(', ', $brandIds).'))';
            $write->delete($urlRewriteTable, $where);
            $write->commit();
        }catch (Exception $e) {
            $write->rollback();
            throw $e;
        }
    }
}