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

class Simtech_Searchanise_Helper_ApiPages extends Mage_Core_Helper_Data
{
    private static $_excludedPages = array(
        'no-route', // 404 page
        'enable-cookies', // Enable Cookies
        'privacy-policy-cookie-restriction-mode', // Privacy Policy
        'service-unavailable', // 503 Service Unavailable
        'private-sales', // Welcome to our Exclusive Online Store
        'home', // Home
    );

    public static function generatePageFeed($page, $store = null, $checkData = true)
    {
        $item = array();

        if ($checkData) {
            if (!$page ||
                !$page->getId() ||
                !$page->getTitle() ||
                !$page->getIsActive() ||
                in_array($page->getIdentifier(), self::$_excludedPages)
                ) {
                return $item;
            }
        }
        // Need for generate correct url.
        if ($store) {
            Mage::app()->setCurrentStore($store->getId());
        } else {
            Mage::app()->setCurrentStore(0);
        }

        $item['id'] = $page->getId();
        $item['title'] = $page->getTitle();
        $item['link'] = Mage::helper('cms/page')->getPageUrl($page->getId());
        $item['summary'] = $page->getContent();

        return $item;
    }

    public static function getPages($pageIds = Simtech_Searchanise_Model_Queue::NOT_DATA, $store = null)
    {
        static $arrPages = array();
        
        $keyPages = '';
        if ($pageIds) {
            if (is_array($pageIds)) {
                $keyPages .= implode('_', $pageIds);
            } else {
                $keyPages .= $pageIds;
            }
        }
        $storeId = $store ? $store->getId() : 0;
        $keyPages .= ':' .  $storeId;

        if (isset($arrPages[$keyPages])) {
            // Nothing
        } else {
            $collection = Mage::getModel('cms/page')->getCollection();

            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Page_Collection */
            $collection->addStoreFilter($storeId);
            
            if ($pageIds !== Simtech_Searchanise_Model_Queue::NOT_DATA) {
                // Already exist automatic definition 'one value' or 'array'.
                self::_addIdFilter($collection, $pageIds);
            }

            $collection->load();

            $arrPages[$keyPages] = $collection;
        }

        return $arrPages[$keyPages];
    }

    public static function generatePagesFeed($pageIds = Simtech_Searchanise_Model_Queue::NOT_DATA, $store = null, $checkData = true)
    {
        $items = array();

        $pages = self::getPages($pageIds, $store);

        if ($pages) {
            foreach ($pages as $page) {
                if ($item = self::generatePageFeed($page, $store, $checkData)) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    public static function getMinMaxPageId($store = null)
    {
        $startId = 0;
        $endId = 0;

        $pageStartCollection = Mage::getModel('cms/page')
            ->getCollection()
            ->setPageSize(1);
        self::_addAttributeToSort($pageStartCollection, 'page_id', Varien_Data_Collection::SORT_ORDER_ASC);
        if ($store) {
            $pageStartCollection = $pageStartCollection->addStoreFilter($store->getId());
        }
        $pageStartCollection = $pageStartCollection->load();

        $pageEndCollection = Mage::getModel('cms/page')
            ->getCollection()
            ->setPageSize(1);
        self::_addAttributeToSort($pageEndCollection, 'page_id', Varien_Data_Collection::SORT_ORDER_DESC);
        if ($store) {
            $pageEndCollection = $pageEndCollection->addStoreFilter($store->getId());
        }

        $pageEndCollection = $pageEndCollection->load();

        if ($pageStartCollection) {
            $pageArr = $pageStartCollection->toArray(array('page_id'));
            if (!empty($pageArr)) {
                $firstItem = reset($pageArr);
                $startId = $firstItem['page_id'];
            }
        }

        if ($pageEndCollection) {
            $pageArr = $pageEndCollection->toArray(array('page_id'));
            if (!empty($pageArr)) {
                $firstItem = reset($pageArr);
                $endId = $firstItem['page_id'];
            }
        }

        return array($startId, $endId);
    }

    public static function getPageIdsFormRange($start, $end, $step, $store = null)
    {
        $arrPages = array();

        $pages = Mage::getModel('cms/page')
            ->getCollection()
            ->addFieldToFilter('page_id', array("from" => $start, "to" => $end))
            ->setPageSize($step);
        
        if ($store) {
            $pages = $pages->addStoreFilter($store->getId());
        }
        
        $pages = $pages->load();
        if ($pages) {
            // Not used because 'arrPages' comprising 'stock_item' field and is 'array(array())'
            // $arrPages = $pages->toArray(array('page_id'));
            foreach ($pages as $page) {
                $arrPages[] = $page->getId();
            }
        }
        // It is necessary for save memory.
        unset($pages);

        return $arrPages;
    }

    /**
     * Add Id filter
     *
     * @param array $pageIds
     * @return Mage_Catalog_Model_Resource_Page_Collection
     */
    private static function _addIdFilter(&$collection, $pageIds)
    {
        if (is_array($pageIds)) {
            if (empty($pageIds)) {
                $condition = '';
            } else {
                $condition = array('in' => $pageIds);
            }
        } elseif (is_numeric($pageIds)) {
            $condition = $pageIds;
        } elseif (is_string($pageIds)) {
            $ids = explode(',', $pageIds);
            if (empty($ids)) {
                $condition = $pageIds;
            } else {
                $condition = array('in' => $ids);
            }
        }
        
        return $collection->addFieldToFilter('page_id', $condition);
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Catalog_Model_Resource_Pages_Flat_Collection
     */
    private static function _addAttributeToSort(&$collection, $attribute, $dir = Varien_Data_Collection::SORT_ORDER_ASC)
    {
        if (!is_string($attribute)) {
            return $collection;
        }
        
        return $collection->setOrder($attribute, $dir);;
    }
}