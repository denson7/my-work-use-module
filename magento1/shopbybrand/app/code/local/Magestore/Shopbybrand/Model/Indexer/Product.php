<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogIndex
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog indexer eav processor
 *
 * @method Mage_CatalogIndex_Model_Resource_Indexer_Eav _getResource()
 * @method Mage_CatalogIndex_Model_Resource_Indexer_Eav getResource()
 * @method Mage_CatalogIndex_Model_Indexer_Eav setEntityId(int $value)
 * @method int getAttributeId()
 * @method Mage_CatalogIndex_Model_Indexer_Eav setAttributeId(int $value)
 * @method int getStoreId()
 * @method Mage_CatalogIndex_Model_Indexer_Eav setStoreId(int $value)
 * @method int getValue()
 * @method Mage_CatalogIndex_Model_Indexer_Eav setValue(int $value)
 *
 * @category    Mage
 * @package     Mage_CatalogIndex
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magestore_Shopbybrand_Model_Indexer_Product extends Mage_Index_Model_Indexer_Abstract
{
    
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'brand_product_match_result';
    
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
             Mage_Index_Model_Event::TYPE_DELETE
        ),
        Mage_Catalog_Model_Category::ENTITY => array(
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
             Mage_Index_Model_Event::TYPE_DELETE
        ),
        /*Magestore_Shopbybrand_Model_Brandproduct::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        )*/
    );
    public function getName() {
        return Mage::helper('shopbybrand')->__('Brand Category Index');
    }

    public function getDescription() {
        return Mage::helper('shopbybrand')->__('Brand Category Index');
    }

    protected function _getIndexer() {
        return Mage::getResourceSingleton('shopbybrand/brandproduct');
    }
    
    public function matchEvent(Mage_Index_Model_Event $event) {
        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }
        $entity = $event->getEntity();
        if ($entity == Mage_Core_Model_Store::ENTITY) {
            $store = $event->getDataObject();
            if ($store && ($store->isObjectNew() || $store->dataHasChangedFor('group_id'))) {
                $result = true;
            } else {
                $result = false;
            }
        } else if ($entity == Mage_Core_Model_Store_Group::ENTITY) {
            $storeGroup = $event->getDataObject();
            $hasDataChanges = $storeGroup && ($storeGroup->dataHasChangedFor('root_category_id')
                || $storeGroup->dataHasChangedFor('website_id'));
            if ($storeGroup && !$storeGroup->isObjectNew() && $hasDataChanges) {
                $result = true;
            } else {
                $result = false;
            }
        }elseif ($entity == Mage_Catalog_Model_Product::ENTITY){
            return true;
        }elseif ($entity == Mage_Catalog_Model_Category::ENTITY){
            return true;
        }else{
            $result = parent::matchEvent($event);
        }
        return $result;
    }

    protected function _registerEvent(Mage_Index_Model_Event $event) {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $dataObject = $event->getDataObject()->getData();
        switch ($event->getEntity()) {
            case Mage_Catalog_Model_Category::ENTITY:
                $this->_registerBrandCategoriesEvent($event);
                break;
            case Mage_Catalog_Model_Product::ENTITY:
                $this->_registerBrandProductsEvent($event);
                break;
        }
        return $this;
    }
    
    protected function _registerBrandCategoriesEvent(Mage_Index_Model_Event $event){
        $category = $event->getDataObject();
        if ($category->getIsChangedProductList()) {
            $process = $event->getProcess();
            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
        return $this;
    }
    
    protected function _registerBrandProductsEvent(Mage_Index_Model_Event $event){
        $data = $event->getDataObject()->getData();
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        if(isset($data['attributes_data'][$attributeCode]) && $data['attributes_data'][$attributeCode]){
            if(is_array($data['product_ids']) && count($data['product_ids'])){
                
                $process = $event->getProcess();
                $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            }
        }
        return $this;
    }

    protected function _processEvent(Mage_Index_Model_Event $event) {
        
    }

    public function reindexAll() {
        Mage::helper('shopbybrand/brand')->reindexBrandCategories();
        $stores = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('store_id', array('neq' => 0));
        foreach ($stores as $store) {
            Mage::app()->getCacheInstance()->save(serialize(''), 'brand_cate_data_'.$store->getId());         }
    }
}
