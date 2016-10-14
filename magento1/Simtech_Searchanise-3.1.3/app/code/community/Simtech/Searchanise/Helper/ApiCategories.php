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

class Simtech_Searchanise_Helper_ApiCategories extends Mage_Core_Helper_Data
{
    private static $_excludedCategories = array(// use id to hide categories
    );

    public static function generateCategoryFeed($category, $store = null, $checkData = true)
    {
        $item = array();

        if ($checkData) {
            if (!$category ||
                !$category->getId() ||
                !$category->getName() ||
                !$category->getIsActive() ||
                in_array($category->getId(), self::$_excludedCategories)
                ) {
                return $item;
            }
        }
        // Need for generate correct url.
        if ($store) {
            $category->getUrlInstance()->setStore($store->getId());
        }

        $item['id'] = $category->getId();

        $item['parent_id'] = $category->getId();
        $parentCategory = $category->getParentCategory();
        $item['parent_id'] = $parentCategory ? $parentCategory->getId() : 0;
        $item['title'] = $category->getName();
        $item['link'] = $category->getUrl();

        // Fixme in the future
        // if need to add ico for image.
        // Show images without white field
        // Example: image 360 x 535 => 47 х 70
        // $flagKeepFrame = false;
        // $image =  Mage::helper('searchanise/ApiProducts')->getProductImageLink($category, $flagKeepFrame);
        // if ($image) {
        //     $imageLink = '' . $image;

        //     if ($imageLink != '') {
        //         $item['image_link'] = '' . $imageLink;
        //     }
        // }
        // end fixme
        $item['image_link'] = $category->getImageUrl();
        $item['summary'] = $category->getDescription();

        return $item;
    }

    public static function getCategories($categoryIds = Simtech_Searchanise_Model_Queue::NOT_DATA, $store = null)
    {
        if ($store) {
            Mage::app()->setCurrentStore($store->getId());
        } else {
            Mage::app()->setCurrentStore(0);
        }

        static $arrCategories = array();
        
        $keyCategories = '';
        if ($categoryIds) {
            if (is_array($categoryIds)) {
                $keyCategories .= implode('_', $categoryIds);
            } else {
                $keyCategories .= $categoryIds;
            }
        }
        $storeId = $store ? $store->getId() : 0;
        $keyCategories .= ':' .  $storeId;
        $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();

        if (isset($arrCategories[$keyCategories])) {
            // Nothing
        } else {
            $collection = Mage::getModel('catalog/category')->getCollection();

            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
            $collection
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('path', array('like' => "1/{$rootCategoryId}/%"));
            
            if ($categoryIds !== Simtech_Searchanise_Model_Queue::NOT_DATA) {
                // Already exist automatic definition 'one value' or 'array'.
                $collection->addIdFilter($categoryIds);
            }

            $collection->load();

            $arrCategories[$keyCategories] = $collection;
        }

        return $arrCategories[$keyCategories];
    }

    public static function generateCategoriesFeed($categoryIds = Simtech_Searchanise_Model_Queue::NOT_DATA, $store = null, $checkData = true)
    {
        $items = array();

        $categories = self::getCategories($categoryIds, $store);

        if ($categories) {
            foreach ($categories as $category) {
                if ($item = self::generateCategoryFeed($category, $store, $checkData)) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    public static function getMinMaxCategoryId($store = null)
    {
        $startId = 0;
        $endId = 0;

        $categoryStartCollection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSort('entity_id', Varien_Data_Collection::SORT_ORDER_ASC)
            ->setPageSize(1);
        if ($store) {
            $categoryStartCollection = $categoryStartCollection->setStoreId($store->getId());
        }
        $categoryStartCollection = $categoryStartCollection->load();

        $categoryEndCollection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSort('entity_id', Varien_Data_Collection::SORT_ORDER_DESC)
            ->setPageSize(1);
        if ($store) {
            $categoryEndCollection = $categoryEndCollection->setStoreId($store->getId());
        }

        $categoryEndCollection = $categoryEndCollection->load();

        if ($categoryStartCollection) {
            $categoryArr = $categoryStartCollection->toArray(array('entity_id'));
            if (!empty($categoryArr)) {
                $firstItem = reset($categoryArr);
                $startId = $firstItem['entity_id'];
            }
        }

        if ($categoryEndCollection) {
            $categoryArr = $categoryEndCollection->toArray(array('entity_id'));
            if (!empty($categoryArr)) {
                $firstItem = reset($categoryArr);
                $endId = $firstItem['entity_id'];
            }
        }

        return array($startId, $endId);
    }

    public static function getCategoryIdsFormRange($start, $end, $step, $store = null)
    {
        $arrCategories = array();

        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('entity_id', array("from" => $start, "to" => $end))
            ->setPageSize($step);
        
        if ($store) {
            $categories = $categories->setStoreId($store->getId());
        }
        
        $categories = $categories->load();
        if ($categories) {
            // Not used because 'arrCategories' comprising 'stock_item' field and is 'array(array())'
            // $arrCategories = $categories->toArray(array('entity_id'));
            foreach ($categories as $category) {
                $arrCategories[] = $category->getId();
            }
        }
        // It is necessary for save memory.
        unset($categories);

        return $arrCategories;
    }

    public static function getAllChildrenCategories($catId)
    {
        $categoryIds = array();
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addFieldToFilter('entity_id', $catId)
            ->load()
            ;

        if (!empty($categories)) {
            foreach ($categories as $cat) {
                if (!empty($cat)) {
                    $categoryIds = $cat->getAllChildren(true);
                }
            }
        }

        return $categoryIds;
    }
}