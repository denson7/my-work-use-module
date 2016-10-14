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

class Simtech_Searchanise_Model_Queue extends Mage_Core_Model_Abstract
{
    const NOT_DATA              = 'N';
    const DATA_FACET_TAGS       = 'facet_tags';
    const DATA_FACET_PRICES     = 'facet_prices';
    
    const DATA_CATEGORIES = 'categories';
    
    public static $dataTypes = array(
        self::DATA_FACET_TAGS,
        self::DATA_FACET_PRICES,
        self::DATA_CATEGORIES,
    );
    
    const ACT_PHRASE                = 'phrase';

    const ACT_UPDATE_PAGES          = 'update_pages';
    const ACT_UPDATE_PRODUCTS       = 'update_products';
    const ACT_UPDATE_ATTRIBUTES     = 'update_attributes';
    const ACT_UPDATE_CATEGORIES     = 'update_categories';

    const ACT_DELETE_PAGES          = 'delete_pages';
    const ACT_DELETE_PAGES_ALL      = 'delete_pages_all';
    const ACT_DELETE_PRODUCTS       = 'delete_products';
    const ACT_DELETE_PRODUCTS_ALL   = 'delete_products_all';
    const ACT_DELETE_FACETS         = 'delete_facets';
    const ACT_DELETE_FACETS_ALL     = 'delete_facets_all';
    const ACT_DELETE_ATTRIBUTES     = 'delete_attributes';     // not used
    const ACT_DELETE_ATTRIBUTES_ALL = 'delete_attributes_all'; // not used
    const ACT_DELETE_CATEGORIES     = 'delete_categories';
    const ACT_DELETE_CATEGORIES_ALL = 'delete_categories_all';

    const ACT_PREPARE_FULL_IMPORT   = 'prepare_full_import';
    const ACT_START_FULL_IMPORT     = 'start_full_import';
    const ACT_GET_INFO              = 'update_info';
    const ACT_END_FULL_IMPORT       = 'end_full_import';
    
    public static $mainActionTypes = array(
        self::ACT_PREPARE_FULL_IMPORT,
        self::ACT_START_FULL_IMPORT,
        self::ACT_END_FULL_IMPORT,
    );
    
    public static $actionTypes = array(
        self::ACT_PHRASE,

        self::ACT_UPDATE_PAGES,
        self::ACT_UPDATE_PRODUCTS,
        self::ACT_UPDATE_CATEGORIES,
        self::ACT_UPDATE_ATTRIBUTES,

        self::ACT_DELETE_PAGES,
        self::ACT_DELETE_PAGES_ALL,
        self::ACT_DELETE_PRODUCTS,
        self::ACT_DELETE_PRODUCTS_ALL,      
        self::ACT_DELETE_FACETS,
        self::ACT_DELETE_FACETS_ALL,
        self::ACT_DELETE_ATTRIBUTES,
        self::ACT_DELETE_ATTRIBUTES_ALL,
        self::ACT_DELETE_CATEGORIES,
        self::ACT_DELETE_CATEGORIES_ALL,

        self::ACT_PREPARE_FULL_IMPORT,
        self::ACT_START_FULL_IMPORT,
        self::ACT_END_FULL_IMPORT,
    );
    
    const STATUS_PENDING    = 'pending';
    const STATUS_DISABLED   = 'disabled'; 
    const STATUS_PROCESSING = 'processing';
    
    public static $statusTypes = array(
        self::STATUS_PENDING,
        self::STATUS_DISABLED,
        self::STATUS_PROCESSING,
    );
    
    protected function _construct()
    {
        $this->_init('searchanise/queue');
    }

    public static function isUpdateAction($action)
    {
        $isUpdate = false;

        if ($action == Simtech_Searchanise_Model_Queue::ACT_UPDATE_PAGES || 
            $action == Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS ||
            $action == Simtech_Searchanise_Model_Queue::ACT_UPDATE_ATTRIBUTES ||
            $action == Simtech_Searchanise_Model_Queue::ACT_UPDATE_CATEGORIES) {
            $isUpdate = true;
        }

        return $isUpdate;
    }

    public static function isDeleteAction($action)
    {
        $isDelete = false;

        if ($action == Simtech_Searchanise_Model_Queue::ACT_DELETE_PAGES || 
            $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_PRODUCTS ||
            $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_ATTRIBUTES ||
            $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_FACETS ||
            $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_CATEGORIES) {
            $isDelete = true;
        }

        return $isDelete;
    }
    public static function isDeleteAllAction($action)
    {
        $isDeleteAll = false;

        if ($action == Simtech_Searchanise_Model_Queue::ACT_DELETE_PAGES_ALL || 
            $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_PRODUCTS_ALL ||
            $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_ATTRIBUTES_ALL ||
            $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_FACETS_ALL ||
            $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_CATEGORIES_ALL) {
            $isDeleteAll = true;
        }

        return $isDeleteAll;
    }

    public static function getAPITypeByAction($action)
    {
        $type = '';

        if ($action == Simtech_Searchanise_Model_Queue::ACT_DELETE_PRODUCTS ||
            $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_PRODUCTS_ALL) {
            $type = 'items';
        } elseif ($action == Simtech_Searchanise_Model_Queue::ACT_DELETE_CATEGORIES ||
                  $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_CATEGORIES_ALL) {
            $type = 'categories';
        } elseif ($action == Simtech_Searchanise_Model_Queue::ACT_DELETE_PAGES || 
                  $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_PAGES_ALL) {
            $type = 'pages';
        } elseif ($action == Simtech_Searchanise_Model_Queue::ACT_DELETE_FACETS ||
                  $action == Simtech_Searchanise_Model_Queue::ACT_DELETE_FACETS_ALL) {
            $type = 'facets';
        }

        return $type;
    }
    
    public function deleteKeys($curStore = null)
    {
        $stores = Mage::helper('searchanise/ApiSe')->getStores($curStore);
        
        foreach ($stores as $keyStore => $store) {
            $queue = Mage::getModel('searchanise/queue')->getCollection()->addFilter('store_id', $store->getId())->toArray();
            
            if (!empty($queue['items'])) {
                foreach ($queue['items'] as $item) {
                    try {
                        Mage::getModel('searchanise/queue')->load($item['queue_id'])->delete();
                    } catch (Mage_Core_Exception $e) {
                        Mage::helper('searchanise/ApiSe')->log($e->getMessage(), 'Delete error');
                    }
                }
            }
        }
        
        return true;
    }

    public function getTotalItems()
    {
        $total = 0;

        $collection = $this->getCollection()
            ->setPageSize(0)
            ->load();

        if ($collection) {
            $total = count($collection);
        }

        return $total;
    }

    public function getNextQueueArray($queueId = null, $flagIgnoreError = false)
    {
        $collection = $this->getCollection()
            ->addOrder('queue_id', 'ASC')
            ->setPageSize(1);
        
        if (!empty($queueId)) {
            $collection = $collection->addFieldToFilter('queue_id', array('gt' => $queueId));
        }

        // Not use in current version.
        if ($flagIgnoreError) {
            $collection = $collection->addFieldToFilter('error_count', array('lt' => Mage::helper('searchanise/ApiSe')->getMaxErrorCount()));
        }

        return $collection->load()->toArray();
    }
    
    public function getNextQueue($queueId = null)
    {
        $q = array();
        $queueArr = self::getNextQueueArray($queueId);
        
        if (!empty($queueArr['items'])) {
            $q = reset($queueArr['items']);
        }
        
        return $q;
    }

    public function clearActions($store = null)
    {
        $collection = Mage::getModel('searchanise/queue')->getCollection();

        if ($store) {
            $collection = $collection->addFilter('store_id', $store->getId());
        }

        return $collection->load()->delete();
    }
    
    public function addAction($action, $data = null, $curStore = null, $curStoreId = null)
    {
        if (in_array($action, self::$actionTypes)) {
            if (
                !Mage::helper('searchanise/ApiSe')->checkParentPrivateKey()
                || (
                    !Mage::helper('searchanise/ApiSe')->isRealtimeSyncMode()
                    && !in_array($action, self::$mainActionTypes)
                )
            ) {
                return false;
            }
            
            $data = serialize((array)$data);
            $data = array($data);

            $stores = Mage::helper('searchanise/ApiSe')->getStores($curStore, $curStoreId);

            if ($action == self::ACT_PREPARE_FULL_IMPORT && !empty($curStore)) {
                // Truncate queue for all
                Mage::getModel('searchanise/queue')->clearActions($curStore);
            }
            
            foreach ($data as $d) {
                foreach ($stores as $keyStore => $store) {
                    if (Mage::helper('searchanise/ApiSe')->getStatusModule($store) != 'Y') {
                        if (!in_array($action, self::$mainActionTypes)) {
                            continue;
                        }
                    }
                    
                    if ($action != self::ACT_PHRASE) {
                        // Remove duplicate actions
                        $exist_actions = Mage::getModel('searchanise/queue')
                            ->getCollection()
                            ->addFilter('status',   self::STATUS_PENDING)
                            ->addFilter('action',   $action)
                            ->addFilter('data',     $data)
                            ->addFilter('store_id', $store->getId())
                            ->load()
                            ->delete();
                    }
                    
                    $queueData = array(
                        'action'    => $action,
                        'data'      => $d,
                        'store_id'  => $store->getId(),
                    );

                    $this->setData($queueData)->save();
                }
            }
            
            return true;
        }
        
        return false;
    }

    public function addActionCategory($category, $action = Simtech_Searchanise_Model_Queue::ACT_UPDATE_CATEGORIES)
    {
        if ($category) {
            // Fixme in the future
            // need get $currentIsActive for all stores because each store can have his value of IsActive for category.
            $currentIsActive = $category->getIsActive();
            $storeId = $category->getStoreId();
            $prevCategory = Mage::getModel('catalog/category')
                ->setStoreId($category->getStoreId())
                ->load($category->getId());

            if ($action == Simtech_Searchanise_Model_Queue::ACT_DELETE_CATEGORIES) {
                if ($prevCategory && $prevCategory->getIsActive()) {
                    // Delete in all stores
                    Mage::getModel('searchanise/queue')->addAction($action, $category->getId());
                }
            } elseif ($action == Simtech_Searchanise_Model_Queue::ACT_UPDATE_CATEGORIES) {
                if ($currentIsActive) {
                    Mage::getModel('searchanise/queue')->addAction($action, $category->getId(), null, $storeId);
                } else {
                    $prevIsActive = $prevCategory->getIsActive();
                    if ($prevIsActive != $currentIsActive) {
                        // Delete need for all stores
                        Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_DELETE_CATEGORIES, $category->getId());
                    }
                }
            }
            // end fixme
        }

        return true;
    }

    public function addActionPage($page, $action = Simtech_Searchanise_Model_Queue::ACT_UPDATE_PAGES)
    {
        if ($page) {
            // Fixme in the future
            // need get $currentIsActive for all stores because each store can have his value of IsActive for page.
            $currentIsActive = $page->getIsActive();
            $storeId = $page->getStoreId();
            $prevPage = Mage::getModel('cms/page')
                // Fixme in the future
                // need check for correct
                ->setStoreId($page->getStoreId())
                // ->addStoreFilter($page->getStoreId())
                // end fixme
                ->load($page->getId());

            if ($action == Simtech_Searchanise_Model_Queue::ACT_DELETE_PAGES) {
                if ($prevPage && $prevPage->getIsActive()) {
                    // Delete in all stores
                    Mage::getModel('searchanise/queue')->addAction($action, $page->getId());
                }
            } elseif ($action == Simtech_Searchanise_Model_Queue::ACT_UPDATE_PAGES) {
                if ($currentIsActive) {
                    Mage::getModel('searchanise/queue')->addAction($action, $page->getId(), null, $storeId);
                } else {
                    $prevIsActive = $prevPage->getIsActive();
                    if ($prevIsActive != $currentIsActive) {
                        // Delete need for all stores
                        Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_DELETE_PAGES, $page->getId());
                    }
                }
            }
            // end fixme
        }

        return true;
    }
    
    public function addActionProducts($products)
    {
        if (!empty($products)) {
            $productIds = array();
            
            foreach ($products as $product) {
                if ($product->getId()) {
                    $productIds[] = $product->getId();
                }
                if (count($productIds) >= Mage::helper('searchanise/ApiSe')->getProductsPerPass()) {
                    Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS, $productIds);
                    $productIds = array();
                }
            }
            
            if ((!empty($productIds)) && (count($productIds) > 0)) {
                Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS, $productIds);
            }
        }
        
        return $this;
    }
    
    public function addActionProductIdsForAllStore($productIds, $action = self::ACT_UPDATE_PRODUCTS)
    {
        if (!empty($productIds)) {
            if (count($productIds) <= Mage::helper('searchanise/ApiSe')->getProductsPerPass()) {
                Mage::getModel('searchanise/queue')->addAction($action, $productIds);
            } else {
                $actProductIds = array();
                
                foreach ($productIds as $productId) {
                    if ($productId) {
                        $actProductIds[] = $productId;
                    }
                    if (count($actProductIds) >= Mage::helper('searchanise/ApiSe')->getProductsPerPass()) {
                        Mage::getModel('searchanise/queue')->addAction($action, $actProductIds);
                        $actProductIds = array();
                    }
                }
                
                if (!empty($actProductIds)) {
                    Mage::getModel('searchanise/queue')->addAction($action, $productIds);
                }
            }
        }
        
        return $this;
    }
    
    public function addActionProductIds($productIds, $action = self::ACT_UPDATE_PRODUCTS)
    {
        if (!empty($productIds)) {
            if (!is_array($productIds)) {
                $productIds = array(0 => $productIds);
            }
            
            foreach ($productIds as $k => $productId) {
                $storeIds = null;
                $product = Mage::getModel('catalog/product')
                    ->load($productId);
                
                if ($product) {
                    $storeIds = $product->getStoreIds();                
                    Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS, $productId, null, $storeIds);
                }
            }
        }
        
        return $this;
    }
    
    public function addActionOrderItems($items)
    {
        if (!empty($items)) {
            $productIds = array();
            
            foreach ($items as $item) {
                if ($item->getProductId()) {
                    $productIds[] = $item->getProductId();
                }
                if (count($productIds) >= Mage::helper('searchanise/ApiSe')->getProductsPerPass()) {
                    Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS, $productIds, null, $item->getStoreId());
                    $productIds = array();
                }
            }
            
            if (!empty($productIds)) {
                Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS, $productIds, null, $item->getStoreId());
            }
        }
        
        return $this;
    }
    
    public function addActionDeleteProductFromOldStore($product = null)
    {
        if ($product && $product->getId()) {
            $storeIds = $product->getStoreIds();
            
            $product_old = Mage::getModel('catalog/product')
                ->load($product->getId());
            
            if (!empty($product_old)) {
                $storeIdsOld = $product_old->getStoreIds();
                
                if (!empty($storeIdsOld)) {
                    foreach ($storeIdsOld as $k => $storeIdOld) {
                        if ((empty($storeIds)) || (!in_array($storeIdOld, $storeIds))) {
                            $this->addAction(Simtech_Searchanise_Model_Queue::ACT_DELETE_PRODUCTS, $product->getId(), null, $storeIdOld);
                        }
                    }
                }
            }
        }
        
        return $this;
    }
    
    public function addActionDeleteProduct($product = null)
    {
        if ($product && $product->getId()) {
            $storeIds = $product->getStoreIds();
            
            if (!empty($storeIds)) {
                foreach ($storeIds as $k => $storeId) {
                    $this->addAction(Simtech_Searchanise_Model_Queue::ACT_DELETE_PRODUCTS, $product->getId(), null, $storeId);
                }
            }
        }
        
        return $this;
    }
    
    public function addActionUpdateProduct($product = null, $storeIds = null)
    {
        if ($product && $product->getId()) {
            if (!empty($storeIds)) {
                if (!is_array($storeIds)) {
                    $storeIds = array(0 => $storeIds);
                }
            }
            
            if (empty($storeIds)) {
                $storeIds = $product->getStoreIds();
            }
            
            if (!empty($storeIds)) {
                foreach ($storeIds as $k => $storeId) {
                    $this->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE_PRODUCTS, $product->getId(), null, $storeId);
                }
            }
        }
        
        return $this;
    }
}
