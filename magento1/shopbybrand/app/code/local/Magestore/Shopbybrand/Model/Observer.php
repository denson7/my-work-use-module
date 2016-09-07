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
 * Shopbybrand Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Model_Observer
{ 
	public function getlink(){
        $link=Mage::app()->getRequest()->getRouteName().
		Mage::app()->getRequest()->getControllerName().
        Mage::app()->getRequest()->getActionName().
        Mage::app()->getRequest()->getModuleName();
        return $link;
    }
	public function catalog_product_collection_load_before($observer) {
        if($this->getlink()!='shopbybrandindexviewbrand')
            return $this;
        $productCollection = $observer['collection'];
        $productCollection->addFieldToFilter('entity_id', array('in'=>Mage::getSingleton('shopbybrand/brand')->getBrand()->getProductIds()));
        $_filters = Mage::getSingleton('catalog/layer')->getState()->getFilters();
        $attributeCode = ucfirst(Mage::helper('shopbybrand/brand')->getAttributeCode());
        foreach ($_filters as $key => $_filter) {
            if($attributeCode==$_filter->getName()){
                unset($_filters[$key]);
                break;
            }
        }
        Mage::getSingleton('catalog/layer')->getState()->setFilters($_filters);    
        return $this;
	}
    public function catalog_category_load_after($observer)
    {
        $brandName = Mage::getSingleton('shopbybrand/brand')
            ->getBrand()->getName();
	$category = $observer['category'];
        if($category->getId()==Mage::app()->getStore()->getRootCategoryId())
            $category->setIsAnchor(1)->setName($brandName)->setDisplayMode('PRODUCTS');
        return $this;
    }
    public function setLayerBrand($observer)
    {
		$controller = $observer['controller_action'];
        if (!Mage::registry('current_category')) {
            Mage::register('current_category',Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId()));
		}
        $request = $controller->getRequest();
        $request->setParam(Mage::helper('shopbybrand/brand')->getAttributeCode(), Mage::getSingleton('shopbybrand/brand')
            ->getBrand()    
            ->getOptionId());
            return $this; 
    }
    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_Shopbybrand_Model_Observer
     */
    public function controller_front_init_routers($observer)
    {
    }
    
    public function brandAttributeUpate($observer){
        $resource = Mage::getResourceModel('shopbybrand/brand');
        $attribute = $observer->getAttribute();
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $stores = Mage::getModel('core/store')->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('store_id', array('neq' => 0))
        ;
        if($attribute->getAttributeCode() == $attributeCode){
            $optionValue = $attribute->getOption();
            $options = $optionValue['value'];
            $deletes = $optionValue['delete'];
            /*add by cuong*/
            $OldAttributeValue = $this->getOldAttributeValue($attributeCode);
            /*end add by cuong*/
            foreach($options as $id=>$option){
                    if(intval($id) == 0){
                        $optionDatabase = $resource->getAttributeOptions($option[0]);
                        $optionDatabase = $optionDatabase[0];
                        if($optionDatabase['option_id'])
                            $id = $optionDatabase['option_id'];
                    }
                    $brand = Mage::getModel('shopbybrand/brand')->load($id,'option_id');
                    
                    if(isset($deletes[$id]) && $deletes[$id]){

                        if($brand->getId()){
                            foreach ($stores as $store){
                                $urlRewrite = Mage::getModel('shopbybrand/brand')->loadByIdPath('brand/'.$brand->getId(), $store->getId());
                                if($urlRewrite->getId())
                                    $urlRewrite->delete();
                            }
                            $brand->delete();
                            continue;
                        }
                    }else{
                    //if(!$brand->getId()){
                        $op['store_id'] = 0;
                        $op['option_id'] = $id;
                        $op['value'] = $option[0];
                        /*add by cuong*/
                        if($OldAttributeValue[$id]!=$option[0]&&isset($OldAttributeValue[$id]))
                        /*end add by cuong*/
                        Mage::helper('shopbybrand/brand')->insertBrandFromOption($op);
                        foreach($stores as $store){
                            if(isset($option[$store->getId()]) && $option[$store->getId()]){
                                $opStore['store_id'] = $store->getId();
                                $opStore['option_id'] = $id;
                                $opStore['value'] = $option[$store->getId()];

                                if(!$brand->getId())
                                    $brand = Mage::getModel('shopbybrand/brand')->load($id, 'option_id');

                                if($brand->getId()){
                                    $brandValue = Mage::getModel('shopbybrand/brandvalue')->loadAttributeValue($brand->getId(), $store->getId(), 'name');
                                    if ($brandValue->getValue() != $option[$store->getId()]) {
                                        $brandValue->setData('value', $option[$store->getId()])
                                                ->setStoreId($store->getId())
                                                ->save();
                                    }
                                }
                            }
                        }
                }
                //}
                //}
                
            }
        }
        foreach ($stores as $store) {
            Mage::app()->getCacheInstance()->save(serialize(''), 'brand_data_'.$store->getId());
        }  
    }
    /*add by cuong*/
    public function brandUpdateOptionId($observer) {
        $resource = Mage::getResourceModel('shopbybrand/brand');
        $attribute = $observer->getAttribute();
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        if($attribute->getAttributeCode() == $attributeCode){
            $optionValue = $attribute->getOption();
            $options = $optionValue['value'];
            $OldAttributeValue = $this->getOldAttributeValue($attributeCode);
            $getAllBrandOptionId = $this->getAllBrandOptionId();
            foreach ($getAllBrandOptionId as $key => $value){
                unset($OldAttributeValue[$key]);
            }
            unset($OldAttributeValue['']);
            foreach($OldAttributeValue as $id=>$option){
                    if(intval($id) == 0){
                        $optionDatabase = $resource->getAttributeOptions($option);
                        $optionDatabase = $optionDatabase[0];
                        if($optionDatabase['option_id'])
                            $id = $optionDatabase['option_id'];
                    }
                    $brand = Mage::getModel('shopbybrand/brand')->load($id,'option_id');
                        $op['store_id'] = 0;
                        $op['option_id'] = $id;
                        $op['value'] = $option;
                        Mage::helper('shopbybrand/brand')->insertBrandFromOption($op);                
            }
        }
    }
    /* end add by cuong*/
    public function updateBrand($observer){
        $controllerAction = $observer->getControllerAction();
        $request = $controllerAction->getRequest();
        $attributeId = $request->getParam('attribute_id');
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        
        $attribute = Mage::getModel('eav/entity_attribute')->load($attributeId);
        
        if($attribute->getAttributeCode() == $attributeCode){
            Mage::helper('shopbybrand/brand')->updateBrandsFormCatalog();
        }
        return;
    }
    
    /* add and edit by Peter */
    public function saveAdminProduct($observer) {
        $product = $observer->getProduct();
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        /* edit by cuong */
        $attributeCode = strtolower($attributeCode);
        /* end edit by cuong */
        $oldOptionId = Mage::getModel('catalog/product')->load($product->getId())->getData($attributeCode);
        $countBrand = Mage::getModel('shopbybrand/brand')->getCollection()->addFieldToFilter('option_id', $oldOptionId)->getSize();
        if ($oldOptionId != null&&$countBrand) {
            $optionId = $product->getData($attributeCode);
            $oldBrand = Mage::getModel('shopbybrand/brand')->load($oldOptionId, 'option_id');
            $productIds = $oldBrand->getData('product_ids');
            $pos = strpos($productIds, $product->getId());
            if ($pos == 0) {
                if ($product->getId() == $productIds) {
                    $productIds = '';
                } else {
                    $productIds = str_replace($product->getId() . ',', '', $productIds);
                }
            } elseif ($pos > 0) {
                $productIds = str_replace(',' . $product->getId(), '', $productIds);
            }
            $oldBrand->setProductIds($productIds)->save();
            if($optionId){
            $newBrand = Mage::getModel('shopbybrand/brand')->load($optionId, 'option_id');
            if ($newBrand->getData('product_ids')) {
                $newProductIds = $newBrand->getData('product_ids') . ',' . $product->getId();
            } else {
                $newProductIds = $product->getId();
            }
            $newBrand->setProductIds($newProductIds)->save();
            }
        }
    }

    public function saveAdminProductAfter($observer) {
        $product = $observer->getProduct();
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        /* edit by cuong */
        $attributeCode = strtolower($attributeCode);
        /* end edit by cuong */
        $oldOptionId = Mage::getModel('catalog/product')->load($product->getId())->getData($attributeCode);
        $optionId = $product->getData($attributeCode);
        if ($optionId != 0) {
            $oldBrand = Mage::getModel('shopbybrand/brand')->load($oldOptionId, 'option_id');
            $productIds = $oldBrand->getData('product_ids');
            $pos = strpos($productIds, $product->getId());
            if ($pos == 0) {
                if ($product->getId() == $productIds) {
                    $productIds = '';
                } else {
                    $productIds = str_replace($product->getId() . ',', '', $productIds);
                }
            } elseif ($pos > 0) {
                $productIds = str_replace(',' . $product->getId(), '', $productIds);
            }
            $oldBrand->setProductIds($productIds)->save();
            $newBrand = Mage::getModel('shopbybrand/brand')->load($optionId, 'option_id');
            if ($newBrand->getData('product_ids')) {
                $newProductIds = $newBrand->getData('product_ids') . ',' . $product->getId();
            } else {
                $newProductIds = $product->getId();
            }
            $newBrand->setProductIds($newProductIds)->save();
        }
    }
    /* end add and edit by Peter */  
    
    public function productUpdateAttribute($observer){
        $attributesData = $observer->getAttributesData();
        $productIds = $observer->getProductIds();
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $storeId = $observer->getStoreId();
        if(count($productIds)){
            if(isset($attributesData[$attributeCode]) && $attributesData[$attributeCode]){
                $brand = Mage::getModel('shopbybrand/brand')->load($attributesData[$attributeCode], 'option_id');
                Mage::helper('shopbybrand/brand')->updateProductsForBrands($productIds, $brand);
            }
        }
        $stores = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('store_id', array('neq' => 0));
        foreach ($stores as $store) {
            Mage::app()->getCacheInstance()->save(serialize(''), 'brand_data_'.$store->getId());
        }  
    }
    
    public function categoryAdminSave($observer){
        $category = $observer->getCategory();
        $oldIds = $category->getProductCollection()->getAllIds();
        $newIds = $category->getData('posted_products');
    }
    
    public function gotoConfig($observer){
        $controllerAction = $observer->getControllerAction();
        $request = $controllerAction->getRequest();
        $section = $request->getParam('section');
        if($section == 'shopbybrand'){
            $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
            Mage::getSingleton('adminhtml/session')->setAttributeCode($attributeCode);
        }
    }
    
    public function configChange($observer){
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $oldCode = Mage::getSingleton('adminhtml/session')->getAttributeCode();
        if($attributeCode != $oldCode){
            $brands = Mage::getModel('shopbybrand/brand')->getCollection();
            foreach($brands as $brand){
                $brand->deleteUrlRewrite();
                $brand->delete();
            }
            Mage::helper('shopbybrand/brand')->updateBrandsFormCatalog();
        }
        $stores = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('store_id', array('neq' => 0));
        foreach ($stores as $store) {
            Mage::app()->getCacheInstance()->save(serialize(''), 'brand_data_'.$store->getId());
            Mage::app()->getCacheInstance()->save(serialize(''), 'brand_cate_data_'.$store->getId()); 
        }  
    }
    /*add   /edit by Cuong*/
        /**
     * @param $observer
     * bat event
     */
    public function sortbybrandposition($observer){
        if (!Mage::registry('is_join_position')) {
            $route = Mage::app()->getRequest()->getRouteName();
            $params = Mage::app()->getRequest()->getParams();
            if(isset($params['id'])){
                $brandId = $params['id'];
                if(!isset($params['order']))
                    $params['order'] = '';
                if(($route=='shopbybrand')&&($params['order']==null||$params['order']=='position')){
                    if(!isset($params['dir']))
                    $params['dir'] = '';
                        $dir = ($params['dir']!='desc')?'asc':'desc';
                        $observer->getCollection()
                            ->getSelect()
                            ->joinLeft(
                                array('brand_products'=>Mage::getModel('core/resource')->getTableName('brand_products')),
                                "e.entity_id = brand_products.product_id",
                                array(
                                    'position' => 'brand_products.position',
                                )
                                )
                             ->order('brand_products.position '.$dir);
                }
            }
            Mage::register('is_join_position','1');
        }
    }
    
    /**
     * 
     * @param type $observer catalog_product_import_finish_before
     */
    public function reconvertData($observer){
        $modelBrand = Mage::getModel('shopbybrand/brand')->getCollection();
        foreach ($modelBrand->getItems() as $value) {
            $productIds = Mage::helper('shopbybrand/brand')->getProductIdsByBrand($value);
            if(is_string($productIds))
            $value->setProductIds($productIds)->save();

            $categoryIds=Mage::helper('shopbybrand/brand')->getCategoryIdsByBrand($value);
            if(is_string($categoryIds))
            $value->setCategoryIds($categoryIds)->save();
        }
        $stores = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('store_id', array('neq' => 0));
        foreach ($stores as $store) {
            Mage::app()->getCacheInstance()->save(serialize(''), 'brand_data_'.$store->getId());
        }  
    }
    public function addToTopmenu(Varien_Event_Observer $observer)
    {
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/general/display_brandnavigation', $store);
        $numberShow = Mage::getStoreConfig('shopbybrand/general/maximum_item_brandnavigation', $store);
        $router = Mage::getStoreConfig('shopbybrand/general/router', $store);
        if($display){
            try{
                $menu = $observer->getMenu();
                $tree = $menu->getTree();
                $node = new Varien_Data_Tree_Node(array(
                        'name'   => __('Brands'),
                        'id'     => 'brands',
                        'url'    => Mage::app()->getStore()->getUrl($router), 
                ), 'id', $tree, $menu);
                $menu->addChild($node);
                $collection = Mage::getSingleton('shopbybrand/brand')->getBrandsData();
                $i = 0;
                $tree = $node->getTree();
                foreach ($collection as $brand) {
                    $i++;
                    if($i<$numberShow||$numberShow==0||!is_numeric($numberShow)){
                        $data = array(
                            'name'   => $brand['name'],
                            'id'     => 'brand-node-'.$brand['brand_id'],
                            'url'    => Mage::app()->getStore()->getUrl($brand['url_key']),
                        );
                        $subNode = new Varien_Data_Tree_Node($data, 'id', $tree, $node);
                        $node->addChild($subNode);
                    }  else {
                        break;
                    }
                }
                /*$data = array(
                    'name'   => __('All Brands'),
                    'id'     => 'brand-node-all-brand',
                    'url'    => Mage::getUrl($router),
                );*/
                $subNode = new Varien_Data_Tree_Node($data, 'id', $tree, $node);
                $node->addChild($subNode);
            } catch (Exception $e) {
            
            }
        }
    }
    public function getOldAttributeValue($attributename) {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attributename);
        foreach ( $attribute->getSource()->getAllOptions(true, true) as $option){
             $attributeArray[$option['value']] = $option['label'];
             }
             return $attributeArray;
    }
    public function getAllBrandOptionId(){
        $brands = Mage::getModel('shopbybrand/brand')->getCollection();
        $array = array();
        foreach ($brands as $item){
            $array[$item->getOptionId()] = $item->getName();
        }
        return $array;
    }
    /*add   /edit by Cuong*/
}