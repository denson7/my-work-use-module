<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

class Simtech_Searchanise_Model_Observer
{
    protected static $productIdsInCategory = array();
    protected static $isExistsCategory = false;
    protected static $isExistsPage = false;
    
    public function __construct() 
    {
        // nothing for now
    }

    /**
     * Function for cron
     *
     */
    public function autoSync()
    {
        // only run if set to
        $cronAsyncEnabled = Mage::helper('searchanise/ApiSe')->checkCronAsync();
        if ($cronAsyncEnabled) {
            $result = Mage::helper('searchanise/ApiSe')->async();
        }

        return $this;
    }

    /**
     * Function for cron
     *
     */
    public function reimport()
    {
        if (Mage::helper('searchanise/ApiSe')->isPeriodicSyncMode()) {
            Mage::helper('searchanise/ApiSe')->queueImport();
        }

        return $this;
    }

    // FOR SYSTEM //
    /**
     * After image cache was cleaned
     *
     */
    public function cleanCatalogImagesCacheAfter()
    {
        Mage::helper('searchanise/ApiSe')->queueImport();

        return $this;
    }
    // END FOR SYSTEM //
    
    // FOR PRODUCTS //
    /**
     * Before save product
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogProductSaveBefore(Varien_Event_Observer $observer)
    {
        Mage::getModel('searchanise/queue')->addActionDeleteProductFromOldStore($observer->getEvent()->getProduct());
        
        return $this;
    }
    
    /**
     * After save product
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        // fixme in the future
        // Add a check-up for changes of the parameters which are related to other languages and storefronts.
        //~ Mage::getModel('searchanise/queue')->addActionUpdateProduct($observer->getEvent()->getProduct(), $observer->getEvent()->getProduct()->getStoreId());
        Mage::getModel('searchanise/queue')->addActionUpdateProduct($observer->getEvent()->getProduct());
        
        return $this;
    }
    
    /**
     * Before delete product
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogProductDeleteBefore(Varien_Event_Observer $observer)
    {
        Mage::getModel('searchanise/queue')->addActionDeleteProduct($observer->getEvent()->getProduct());
        
        return $this;
    }
    
    /**
     * Product attribute update
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogProductAttributeUpdateBefore(Varien_Event_Observer $observer)
    {
        $productIds = $observer->getEvent()->getData('product_ids');
        
        if (!empty($productIds)) {
            foreach ($productIds as $k => $productId) {
                $product = Mage::getModel('catalog/product')
                    ->load($productId);
                
                if (!empty($product)) {
                    $storeIds = $product->getStoreIds();
                    
                    if (!empty($storeIds)) {
                        foreach ($storeIds as $k => $storeId) {
                            Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS, $product->getId(), null, $storeId);
                        }
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Product website update
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogProductWebsiteUpdateBefore(Varien_Event_Observer $observer)
    {
        $productIds = $observer->getEvent()->getData('product_ids');
        $websiteIds = $observer->getEvent()->getData('website_ids');
        $action = $observer->getEvent()->getData('action');
        $storeIds = Mage::helper('searchanise/ApiSe')->getStoreByWebsiteIds($websiteIds);
        
        if ((!empty($storeIds)) && (!empty($productIds))) {
            foreach ($productIds as $k => $productId) {
                if ($action == 'add') {
                    foreach ($storeIds as $k => $storeId) {
                        Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS, $productId, null, $storeId);
                    }
                    
                } elseif ($action == 'remove') {
                    $productOld = Mage::getModel('catalog/product')
                        ->load($productId);
                    
                    if (!empty($productOld)) {
                        $storeIdsOld = $productOld->getStoreIds();
                        
                        if (!empty($storeIdsOld)) {
                            foreach ($storeIds as $k => $storeId) {
                                if (in_array($storeId, $storeIdsOld)) {
                                    Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_DELETE_PRODUCTS, $productId, null, $storeId);
                                }
                            }
                        }
                    }
                }
            } 
        }
        
        return $this;
    }
    
    // FOR CATEGORIES //
    /**
     * Delete category before
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogCategoryDeleteBefore(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        
        if ($category && $category->getId()) {
            // For category
            Mage::getModel('searchanise/queue')->addActionCategory($category, Simtech_Searchanise_Model_Queue::ACT_DELETE_CATEGORIES);
            
            // For products from category
            $products = $category->getProductCollection();
        }
        
        return $this;
    }

    /**
     * Save category before
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogCategorySaveBefore(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        if ($category && $category->getId()) {
            self::$isExistsCategory = true; // New category doesn't run the catalogCategorySaveBefore function.
            // For category
            Mage::getModel('searchanise/queue')->addActionCategory($category);

            // For products from category
            // It save before because products could remove from $category.
            $products = $category->getProductCollection();
            Mage::getModel('searchanise/queue')->addActionProducts($products);

            // save current products ids
            // need for find new products in catalogCategorySaveAfter
            if ($products) {
                self::$productIdsInCategory = array();
                
                foreach ($products as $product) {
                    if ($product->getId()) {
                        self::$productIdsInCategory[] = $product->getId();
                    }
                }
            }
        }
        
        return $this;
    }

    /**
     * Save category after
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogCategorySaveAfter(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        if ($category && $category->getId()) {
            // For category
            if (!self::$isExistsCategory) { // if category was created now
                Mage::getModel('searchanise/queue')->addActionCategory($category);
            }

            // For products from category
            $products = $category->getProductCollection();

            if (!empty($products)) {
                if (empty(self::$productIdsInCategory)) {
                    Mage::getModel('searchanise/queue')->addActionProducts($products);
                } else {
                    $productIds = array();
                    foreach ($products as $product) {
                        $id = $product->getId();
                        if ((!empty($id)) && (!in_array($id, self::$productIdsInCategory))) {
                            $productIds[] = $id;
                        }
                    }

                    Mage::getModel('searchanise/queue')->addActionProductIds($productIds);
                }
            }
        }
        self::$isExistsCategory = false;
        self::$productIdsInCategory = array();
        
        return $this;
    }

    /**
     * Move category after
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogCategoryTreeMoveAfter(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        
        if ($category && $category->getId()) {
            $products = $category->getProductCollection();
            if ($products) {
                Mage::getModel('searchanise/queue')->addActionProducts($products);
            }
        }
        
        return $this;
    }

    // FOR PAGES //
    /**
     * Delete page before
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CmsIndex_Model_Observer
     */
    public function cmsPageDeleteBefore(Varien_Event_Observer $observer)
    {
        $page = $observer->getEvent()->getObject();
                
        if ($page && $page->getId()) {
            Mage::getModel('searchanise/queue')->addActionPage($page, Simtech_Searchanise_Model_Queue::ACT_DELETE_PAGES);
        }
        
        return $this;
    }

    /**
     * Save page before
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CmsIndex_Model_Observer
     */
    public function cmsPageSaveBefore(Varien_Event_Observer $observer)
    {
        $page = $observer->getEvent()->getObject();

        if ($page && $page->getId()) {
            self::$isExistsPage = true; // New page doesn't run the cmsPageSaveBefore function.
            Mage::getModel('searchanise/queue')->addActionPage($page);
        }

        return $this;
    }

    /**
     * Save page after
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CmsIndex_Model_Observer
     */
    public function cmsPageSaveAfter(Varien_Event_Observer $observer)
    {
        $page = $observer->getEvent()->getObject();

        if ($page && $page->getId()) {
            if (!self::$isExistsPage) { // if page was created now
                Mage::getModel('searchanise/queue')->addActionPage($page);
            }
        }
        self::$isExistsPage = false;
                
        return $this;
    }

    // FOR SALES //
    /**
     * 
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function salesOrderSaveAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        
        if ($order && $order->getId()) {
            Mage::getModel('searchanise/queue')->addActionOrderItems($order->getItemsCollection());
        }
        
        return $this;
    }
    
    // FOR IMPORTEXPORT //
    /**
     * 
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseImportSaveProductEntityAfter(Varien_Event_Observer $observer)
    {
        $_newSku = $observer->getData('_newSku');
        
        if (!empty($_newSku)) {
            $productIds = array();
            
            foreach ($_newSku as $entity) {
                if ($entity['entity_id']) {
                    $productIds[] = $entity['entity_id'];
                }
            }
            
            if (!empty($productIds)) {
                Mage::getModel('searchanise/queue')->addActionProductIds($productIds , Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS);
            }
        }
        
        return $this;
    }
    
    /**
     * 
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseImportDeleteProductEntityAfter(Varien_Event_Observer $observer)
    {
        $idToDelete = $observer->getData('idToDelete');
        
        if (!empty($idToDelete)) {
            Mage::getModel('searchanise/queue')->addActionProductIds($idToDelete, Simtech_Searchanise_Model_Queue::ACT_DELETE_PRODUCTS);
        }
        
        return $this;
    }
    
    // FOR CORE //
    /**
     * Before save store
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseCoreSaveStoreBefore(Varien_Event_Observer $observer)
    {
        $store = $observer->getData('store');
        
        if ($store && $store->getId()) {
            $isActive = $store->getIsActive();
            $isActiveOld = null;
            $storeOld = Mage::app()->getStore($store->getId());
            
            if ($storeOld) {
                $isActiveOld = $storeOld->getIsActive();
            }
                        
            if ($isActiveOld != $isActive) {
                if (Mage::helper('searchanise/ApiSe')->signup($store, false, false) == true) {
                    if ($isActive) {
                        Mage::helper('searchanise/ApiSe')->sendAddonStatusRequest('enabled', $store);
                        Mage::helper('searchanise/ApiSe')->queueImport($store, false);
                        Mage::helper('searchanise/ApiSe')->setNotification(
                            'N',
                            Mage::helper('searchanise')->__('Notice'),
                            str_replace('[language]', $store->getName(), Mage::helper('searchanise')->__('Searchanise: New search engine for [language] created. Catalog import started'))
                        );
                    } else {
                        Mage::helper('searchanise/ApiSe')->sendAddonStatusRequest('disabled', $store);
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Save store
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseCoreSaveStoreAfter(Varien_Event_Observer $observer)
    {
        $store = $observer->getData('store');
        
        if ($store && $store->getId()) {
            $checkPrivateKey = Mage::helper('searchanise/ApiSe')->checkPrivateKey($store);
            
            if (Mage::helper('searchanise/ApiSe')->signup($store, false, false) == true) {
                if (!$checkPrivateKey) {
                    if ($store->getIsActive()) {
                        Mage::helper('searchanise/ApiSe')->queueImport($store, false);
                        Mage::helper('searchanise/ApiSe')->setNotification(
                            'N',
                            Mage::helper('searchanise')->__('notice'),
                            str_replace('[language]', $store->getName(), Mage::helper('searchanise')->__('Searchanise: New search engine for [language] created. Catalog import started'))
                        );
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Delete store
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseCoreDeleteStoreAfter(Varien_Event_Observer $observer)
    {
        $store = $observer->getData('store');
        
        if ($store && $store->getId()) {
            Mage::helper('searchanise/ApiSe')->deleteKeys($store);
        }
        
        return $this;
    }
    
    // FOR ADMINHTML //
    /**
     * Before save adminhtml config data
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseAdminhtmlConfigDataSaveBefore(Varien_Event_Observer $observer)
    {
        $model = $observer->getData('object');
        $groups  = $model->getGroups();
        $section = $model->getSection();
        $storesIds = $model->getStore();
        $website = $model->getWebsite();
        
        if (empty($storesIds)) {
            if (!empty($website)) {
                $storesIds = Mage::helper('searchanise/ApiSe')->getStoreByWebsiteCodes($website);
            }
        }
        
        $stores = Mage::helper('searchanise/ApiSe')->getStores(null, $storesIds);
        
        if (!empty($stores)) {
            if ($section == 'catalog') {
                
            // Change status module
            } elseif ($section == 'advanced') {
                foreach ($groups as $group => $groupData) {
                    if (isset($groupData['fields']['Simtech_Searchanise']['value'])) {
                        $status = ($groupData['fields']['Simtech_Searchanise']['value']) ? 'D' : 'Y';
                        
                        foreach ($stores as $k => $store) {
                            if ($store->getIsActive()) {
                                $statusOld = Mage::helper('searchanise/ApiSe')->getStatusModule($store);
                                
                                if ($statusOld != $status) {
                                    if (Mage::helper('searchanise/ApiSe')->signup($store, false, false) == true) {
                                        if ($status == 'Y') {
                                            Mage::helper('searchanise/ApiSe')->sendAddonStatusRequest('enabled', $store);
                                            Mage::helper('searchanise/ApiSe')->queueImport($store, false);
                                            Mage::helper('searchanise/ApiSe')->setNotification(
                                                'N',
                                                Mage::helper('searchanise')->__('Notice'),
                                                str_replace('[language]', $store->getName(), Mage::helper('searchanise')->__('Searchanise: New search engine for [language] created. Catalog import started'))
                                            );
                                        } else {
                                            Mage::helper('searchanise/ApiSe')->sendAddonStatusRequest('disabled', $store);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $this;
    }
    /**
     * After save adminhtml config data
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseAdminhtmlConfigDataSaveAfter(Varien_Event_Observer $observer)
    {
        $model = $observer->getData('object');
        $section = $model->getSection();
        $storesIds = $model->getStore();
        $website = $model->getWebsite();
        
        if (empty($storesIds)) {
            if (!empty($website)) {
                $storesIds = Mage::helper('searchanise/ApiSe')->getStoreByWebsiteCodes($website);
            }
        }
        
        $stores = Mage::helper('searchanise/ApiSe')->getStores(null, $storesIds);
        
        if (!empty($stores)) {
            if ($section == 'catalog') {
                foreach ($stores as $k => $store) {                   
                    if ($attributes = Mage::helper('searchanise/ApiProducts')->getProductAttributes(null, $store, true)) {
                        foreach ($attributes as $attribute) {
                            Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE_ATTRIBUTES, $attribute->getId(), $store);
                        }
                    }

                    // change facet-prices
                    {
                        $queueData = array(
                            'data'     => serialize(Simtech_Searchanise_Model_Queue::DATA_FACET_PRICES),
                            'action'   => Simtech_Searchanise_Model_Queue::ACT_UPDATE_ATTRIBUTES,
                            'store_id' => $store->getId(),
                        );
                        
                        Mage::getModel('searchanise/queue')->setData($queueData)->save();
                    }
                }
                
            // Change status module
            } elseif ($section == 'advanced') {
                
            }
        }
        
        return $this;
    }
    
    // FOR EAV //
    /**
     * Before save attribute
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogEntityAttributeSaveBefore(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        
        if ($attribute && $attribute->getId()) {
            $isFacet = Mage::helper('searchanise/ApiProducts')->isFacet($attribute);
            
            $isFacetPrev = null;
            
            $prevAttribute = Mage::getModel('catalog/entity_attribute')
                ->load($attribute->getId());
            
            if ($prevAttribute) {
                $isFacetPrev = Mage::helper('searchanise/ApiProducts')->isFacet($prevAttribute);
            }
            
            if ($isFacet != $isFacetPrev) {
                if (!$isFacet) {
                    Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_DELETE_FACETS, $attribute->getId());
                }
            }
        }

        return $this;
    }

    /**
     * Save attribute
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogEntityAttributeSaveAfter(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        
        if ($attribute && $attribute->getId()) {
            Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE_ATTRIBUTES, $attribute->getId());
        }

        return $this;
    }
    
    /**
     * Delete attribute
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogEntityAttributeDeleteAfter(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        
        if ($attribute && $attribute->getId()) {
            if (Mage::helper('searchanise/ApiProducts')->isFacet($attribute)) {
                Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_DELETE_FACETS, $attribute->getId());
            }
        }
        
        return $this;
    }
    
    // FOR TAG //
    /**
     * After save tag
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function tagSaveAfter(Varien_Event_Observer $observer)
    {
        $tag = $observer->getEvent()->getData('object');
        
        if (!empty($tag)) {
            $productIds = $tag->getRelatedProductIds();
            
            Mage::getModel('searchanise/queue')->addActionProductIds($productIds, Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS);
        }
        
        return $this;
    }
    
    /**
     * Before delete tag
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function tagDeleteBefore(Varien_Event_Observer $observer)
    {
        $tag = $observer->getEvent()->getData('object');
        
        if (!empty($tag)) {
            $productIds = $tag->getRelatedProductIds();
            
            Mage::getModel('searchanise/queue')->addActionProductIds($productIds, Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS);
        }
        
        return $this;
    }
    
    /**
     * Add tag to product
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseTagRelationSaveAfter(Varien_Event_Observer $observer)
    {
        // fixme in the future
        // need add check approved tag
        $tagRelation = $observer->getEvent()->getData('object');
        
        if (!empty($tagRelation)) {
            $tag = Mage::getModel('tag/tag')
                ->setData('tag_id', $tagRelation->getTagId())
                ->load();
            
            if (!empty($tag)) {
                $productIds = $tag->getRelatedProductIds();
                
                Mage::getModel('searchanise/queue')->addActionProductIds($productIds, Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS);
            }
        }
        
        return $this;
    }

    /**
     * Before catalogSearchResultIndex dispatch
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function controllerActionPredispatchCatalogSearchResultIndex(Varien_Event_Observer $observer)
    {
        $data = $observer->getData();
        $controller = $data['controller_action'];

        $defaultToolbarBlock = 'catalog/product_list_toolbar';

        if (Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {
            
            $query = Mage::helper('catalogsearch')->getQuery();

            $query->setStoreId(Mage::app()->getStore()->getId());

            if ($query->getQueryText() != '') {
                if (Mage::helper('searchanise')->checkEnabled()) {
                    $blockToolbar = $controller->getLayout()->getBlock('product_list_toolbar');
                    if (!$blockToolbar) {
                        $blockToolbar = $controller->getLayout()->createBlock($defaultToolbarBlock, microtime());
                    }
                                    
                    Mage::helper('searchanise')->execute(Simtech_Searchanise_Helper_Data::TEXT_FIND, $controller, $blockToolbar, $query);
                }
            }
        }
        
        return $this;
    }

    /**
     * Before catalogSerachAdvancedResult dispatch
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function controllerActionPredispatchCatalogSearchAdvancedResult(Varien_Event_Observer $observer)
    {
        $data = $observer->getData();
        $controller = $data['controller_action'];

        $default_toolbar_block = 'catalog/product_list_toolbar';

        if (Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {

            try {
                $query = $controller->getRequest()->getQuery();
            } catch (Mage_Core_Exception $e) {
                return $this;
            }

            if ($query) {
                if (Mage::helper('searchanise')->checkEnabled()) {
                    $block_toolbar = $controller->getLayout()->createBlock($default_toolbar_block, microtime());
                    
                    Mage::helper('searchanise')->execute(Simtech_Searchanise_Helper_Data::TEXT_ADVANCED_FIND, $controller, $block_toolbar, $query);
                }
            }
        }
        
        return $this;
    }

    public function coreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        $data = $observer->getData();
        $block = $data['block'];

        if ($block instanceof Mage_CatalogSearch_Block_Layer) {
            $filters = $block->getFilters();
            foreach ($filters as $filter) {
                if ($filter->getType() == 'catalog/layer_filter_price') {
                    if (!Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {
                        break;
                    }

                    $collection = $block->getLayer()->getProductCollection();

                    if ((!method_exists($collection, 'checkSearchaniseResult')) || (!$collection->checkSearchaniseResult())) {
                        break;
                    }             

                    $newRange = $collection
                           ->getSearchaniseRequest()
                           ->getPriceRangeFromAttribute($filter->getAttributeModel());
                    if (!$newRange) {
                        break;
                    }
                    
                    $rate = Mage::app()->getStore()->getCurrentCurrencyRate();

                    if ((!$rate) || ($rate == 1)) {
                        // nothing
                    } else {
                        $newRange *= $rate;
                    }

                    $currentCategory = Mage::registry('current_category_filter');
                    if ($currentCategory) {
                        $currentCategory->setFilterPriceRange($newRange);
                    } else {
                        $filter->getLayer()->getCurrentCategory()->setFilterPriceRange($newRange);
                    }
                }
            }
        }
    }

    public function controllerActionPredispatch(Varien_Event_Observer $observer)
    {
        if (Mage::helper('searchanise/ApiSe')->getSetting('redirect_to_admin_after_install')) {
            Mage::helper('searchanise/ApiSe')->setSetting('redirect_to_admin_after_install', false);
            if (!($observer->getData('controller_action') instanceof Simtech_Searchanise_IndexController)) {
                $redirect_url = Mage::helper('adminhtml')->getUrl(Mage::helper('searchanise/ApiSe')->getSearchaniseLink());
                Mage::app()->getResponse()->setRedirect($redirect_url)->sendResponse();
                exit;
            }
        }

        return $this;
    }
}
