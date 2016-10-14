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

class Simtech_Searchanise_Helper_ApiProducts extends Mage_Core_Helper_Data
{
    const WEIGHT_SHORT_TITLE         = 100;
    const WEIGHT_SHORT_DESCRIPTION   = 40;
    const WEIGHT_DESCRIPTION         = 40;
    const WEIGHT_DESCRIPTION_GROUPED = 30;

    const WEIGHT_TAGS              = 60;
    const WEIGHT_CATEGORIES        = 60;

    // <if_isSearchable>
    const WEIGHT_META_TITLE        =  80;
    const WEIGHT_META_KEYWORDS     = 100;
    const WEIGHT_META_DESCRIPTION  =  40;

    const WEIGHT_SELECT_ATTRIBUTES    = 60;
    const WEIGHT_TEXT_ATTRIBUTES      = 60;
    const WEIGHT_TEXT_AREA_ATTRIBUTES = 40;
    // </if_isSearchable>

    // Tweaks
    const ADDITIONAL_CHECK_FOR_INCORRECT_PRODUCTS = false;

    protected static $flWithoutTags = false;

    public static $isGetProductsByItems = false;

    public static function setIsGetProductsByItems($value = false)
    {
        self::$isGetProductsByItems = $value;
    }
    
    public static function getStockItem($product, $store = null)
    {
        $stockItem = null;
        
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($product);
        }
        
        return $stockItem;
    }
    
    public static function getTagCollection($product, $store = null)
    {
        $tagCollection = null;

        if (self::$flWithoutTags) {
            return $tagCollection;
        }
        
        $tagModel = Mage::getModel('tag/tag');
        
        if ($tagModel) {
            $tagCollection = $tagModel->getResourceCollection();
        }
        // Check if tags don't work correctly.
        if (!$tagCollection) {
            self::$flWithoutTags = true;
        
        } else {
            $tagCollection = $tagCollection
                ->setFlag('relation', true)
                ->setActiveFilter();
            
            if (!empty($store)) {
                $tagCollection->addStoreFilter($store->getId(), true);
            }

            $tagCollection = $tagCollection
                ->addPopularity()
                ->addStatusFilter(Mage::getModel('tag/tag')->getApprovedStatus())
                ->addStoresVisibility()
                ->addProductFilter($product->getId())
                ->load();
        }
        
        
        return $tagCollection;
    }

    /**
     * generateImage
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $flagKeepFrame
     * @param int $width
     * @param int $height
     * @return Mage_Catalog_Model_Product_Image $image
     */
    private static function generateImage($object, $imageType = 'small_image', $flagKeepFrame = true, $width = 70, $height = 70)
    {
        $image = null;
        $objectImage = $object->getData($imageType);

        if (!empty($objectImage) && $objectImage != 'no_selection') {
            try {
                $image = Mage::helper('catalog/image')
                    ->init($object, $imageType)
                    ->constrainOnly(true)        // Guarantee, that image picture will not be bigger, than it was.
                    ->keepAspectRatio(true)      // Guarantee, that image picture width/height will not be distorted.
                    ->keepFrame($flagKeepFrame); // Guarantee, that image will have dimensions, set in $width/$height

                if ($width || $height) {
                    $image->resize($width, $height);
                }
            } catch (Exception $e) {
                // image not exists
                $image = null;
            }
        }

        return $image;
    }

    /**
     * getProductImageLink
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $flagKeepFrame
     * @param int $width
     * @param int $height
     * @return Mage_Catalog_Model_Product_Image $image
     */
    public static function getProductImageLink($product, $flagKeepFrame = true, $width = 70, $height = 70)
    {
        $image = null;

        if ($product) {
            $image = self::generateImage($product, 'small_image', $flagKeepFrame, $width, $height);
            
            if (empty($image)) {
                $image = self::generateImage($product, 'image', $flagKeepFrame, $width, $height);
            }
            if (empty($image)) {
                $image = self::generateImage($product, 'thumbnail', $flagKeepFrame, $width, $height);
            }
        }

        return $image;
    }

    /**
     * getProductQty
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     * @param array Mage_Catalog_Model_Product $unitedProducts - Current product + childrens products (if exists)
     * @return float
     */
    private static function getProductQty($product, $store, $unitedProducts = array())
    {
        $quantity = 1;

        $stockItem = self::getStockItem($product);
        if ($stockItem) {
            $manageStock = null;
            if ($stockItem->getData('use_config_manage_stock')) {
                $manageStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
            } else {
                $manageStock = $stockItem->getData('manage_stock');
            }

            if (!$manageStock) {
                $quantity = 1;
            } else {
                $isInStock = $stockItem->getIsInStock();

                if (empty($isInStock)) {
                    $quantity = 0;
                } else {
                    $quantity = $stockItem->getQty();

                    if ($quantity <= 0) {

                        $backorders = 0;
                        if ($stockItem->getData('use_config_backorders') == 1) {
                            $backorders = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS);
                        } else {
                            $backorders = $stockItem->getData('backorders');
                        }

                        if ($backorders == 1 || $backorders == 2) {
                            $quantity = 1;
                        }
                    }

                    if ($unitedProducts) {
                        $quantity = 0;
                        foreach ($unitedProducts as $itemProductKey => $itemProduct) {
                            $quantity += self::getProductQty($itemProduct, $store);
                        }
                    }
                }
            }
        }

        return $quantity;
    }

    /**
     * Get product price with tax if it is need
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float $price
     * @return float
     */
    private static function getProductShowPrice($product, $price)
    {
        static $taxHelper;
        static $showPricesTax;

        if (!isset($taxHelper)) {
            $taxHelper = Mage::helper('tax');
            $showPricesTax = ($taxHelper->displayPriceIncludingTax() || $taxHelper->displayBothPrices());
        }

        $finalPrice = $taxHelper->getPrice($product, $price, $showPricesTax);

        return $finalPrice;
    }

    /**
     * Get product minimal price without "Tier Price" (quantity discount) and with tax (if it is need)
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     * @param Mage_Catalog_Model_Resource_Product_Collection $childrenProducts
     * @param int $customerGroupId
     * @param float $groupPrice
     * @return float
     */
    private static function _getProductMinimalPrice($product, $store, $childrenProducts = null, $customerGroupId = null, $groupPrice = null)
    {
        $minimalPrice = false;

        if ($customerGroupId != null) {
            $product->setCustomerGroupId($customerGroupId);
        }

        $_priceModel = $product->getPriceModel();
        
        if ($_priceModel && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            // [1.5]
            if (version_compare(Mage::getVersion(), '1.6', '<')) {
                $minimalPrice = $_priceModel->getPrices($product, 'min');
            // [/1.5]
            // [v1.6] [v1.7] [v1.8] [v1.9]
            } else {
                $minimalPrice = $_priceModel->getTotalPrices($product, 'min', null, false);
            }
            // [/v1.6] [/v1.7] [/v1.8] [/v1.9]
            $minimalPrice = self::getProductShowPrice($product, $minimalPrice);

        } elseif ($product->isGrouped() && $childrenProducts) {
            // fixme in the future
            // maybe exist better solution get `minimalPrice` for `Grouped` product
            $minimalPrice = false;

            foreach ($childrenProducts as $childrenProductsKey => $childrenProduct) {
                if ($childrenProduct) {
                    $minimalPriceChildren = self::_getProductMinimalPrice(clone $childrenProduct, $store, null, $customerGroupId);

                    if (($minimalPriceChildren < $minimalPrice) || ($minimalPrice === false)) {
                        $minimalPrice = $minimalPriceChildren;
                    }
                }
            }
            // end fixme
        } else {
            if ($groupPrice != null) {
                $minimalPrice = $groupPrice;
            } else {
                $isCorrectProduct = true;

                // Additional check for incorrect configurable products.
                if (self::ADDITIONAL_CHECK_FOR_INCORRECT_PRODUCTS) {
                    static $arrIncorrectProductIds = array();

                    if (in_array($product->getId(), $arrIncorrectProductIds)) {
                        $isCorrectProduct = false;
                    }

                    if ($isCorrectProduct && $product->isConfigurable()) {
                        try {
                            $attributes = $product->getTypeInstance(true)
                                ->getConfigurableAttributes($product);
                            foreach ($attributes as $attribute) {
                                if (!$attribute->getProductAttribute()) {
                                    $isCorrectProduct = false;
                                    $arrIncorrectProductIds[] = $product->getId();
                                    Mage::helper('searchanise/ApiSe')->log('Incorrect configurable product ID = ' . $product->getId(), 'Warning');
                                    break;
                                }
                            }
                        } catch (Exception $e) {
                            Mage::helper('searchanise/ApiSe')->log($e->getMessage(), "Error: Script couldn't check for incorrect configurable product ID = " . $product->getId());
                        }
                    }
                }
                // end check

                if ($isCorrectProduct) {
                    try {
                        $minimalPrice = $product->getFinalPrice();
                    } catch (Exception $e) {
                        $minimalPrice = false;
                        Mage::helper('searchanise/ApiSe')->log($e->getMessage(), "Error: Script couldn't get final price for product ID = " . $product->getId());
                    }
                }
            }

            if ($minimalPrice === false) {
                $minimalPrice = $product->getPrice();
            }

            $minimalPrice = self::getProductShowPrice($product, $minimalPrice);
        }

        return $minimalPrice;
    }
    private static function _getCustomerGroups()
    {
        static $customerGroups;

        if (!isset($customerGroups)) {
            $customerGroups = Mage::getModel('customer/group')->getCollection()->load();
        }

        return $customerGroups;
    }
        

    private static function _generateProductPrices(&$item, $product, $childrenProducts = null, $store = null)
    {
        $product->getGroupPrice();//preload group_price attribute

        if ($customerGroups = self::_getCustomerGroups()) {
            foreach ($customerGroups as $customerGroup) {
                // It is needed because the 'setCustomerGroupId' function works only once.
                $productCurrentGroup = clone $product;
                $customerGroupId = $customerGroup->getId();

                if ($customerGroupId == Mage_Customer_Model_Group::NOT_LOGGED_IN_ID || !isset($equalPriceForAllGroups)) {
                    $price = self::_getProductMinimalPrice($productCurrentGroup, $store, $childrenProducts, $customerGroupId);

                    if ($price !== false) {
                        $price = round($price, Mage::helper('searchanise/ApiSe')->getFloatPrecision());
                    }

                    if ($customerGroupId == Mage_Customer_Model_Group::NOT_LOGGED_IN_ID) {
                        $item['price'] = $price;

                        $specialPrice = $product->getSpecialPrice();
                        if (!is_null($specialPrice) && $specialPrice != false) {
                            $item['list_price'] = round($product->getPrice(), Mage::helper('searchanise/ApiSe')->getFloatPrecision());
                        }

                        $groupPrices = $product->getData('group_price');
                        if (empty($groupPrices)) {
                            $equalPriceForAllGroups = $price;
                        }
                    }

                } else {
                    $price = $equalPriceForAllGroups;
                }

                $label_ = Mage::helper('searchanise/ApiSe')->getLabelForPricesUsergroup() . $customerGroup->getId();
                $item[$label_] = $price;
                unset($productCurrentGroup);
            }
        }

        return true;
    }

    /**
     * Get childs products
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array Mage_Catalog_Model_Resource_Product
     */
    private static function getChildrenProducts($product, $store = null)
    {
        $childrenProducts = array();

        // if CONFIGURABLE OR GROUPED OR BUNDLE
        if (($product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) ||
            ($product->isSuper())) {

            if ($typeInstance = $product->getTypeInstance()) {
                $requiredChildrenIds = $typeInstance->getChildrenIds($product->getId(), true);
                if ($requiredChildrenIds) {
                    $childrenIds = array();

                    foreach ($requiredChildrenIds as $groupedChildrenIds) {
                        $childrenIds = array_merge($childrenIds, $groupedChildrenIds);
                    }

                    if ($childrenIds) {
                        $childrenProducts = self::getProducts($childrenIds, $store, null);
                    }
                }
            }
        }

        return $childrenProducts;
    }

    private static function _getIdAttributeValues($value)
    {
        $values = '';

        $arrValues = explode(',', $value);
        if (!empty($arrValues)) {
            foreach ($arrValues as $v) {
                if ($v != '') {
                    // Example values: '0', '1', 'AF'.
                    $values[] = $v;
                }
            }
        }

        return $values;
    }

    private static function _getTextAttributeValues($product, $attributeCode, $inputType, $store = null)
    {
        //static $arrTextValues = array();
        $key = $attributeCode;
        if ($store) {
            $key .= '__' . $store->getId();
        }

        if (!isset($arrTextValues[$key]) && !is_null($product->getData($attributeCode))) {
            $values = array();
            // Dependency of store already exists
            $textValues = $product->getResource()->getAttribute($attributeCode)->setStoreId($store->getId())->getFrontend()->getValue($product);

            if ($textValues != '') {
                if ($inputType == 'multiselect') {
                    $values = explode(',', $textValues);
                } else {
                    $values[] = $textValues;
                }
            }

            $arrTextValues[$key] = $values;
        } else {
            $arrTextValues[$key] = array();
        }

        return $arrTextValues[$key];
    }

    private static function _getProductAttributeTextValues($products, $attributeCode, $inputType, $store = null)
    {
        $arrTextValues = array();

        foreach ($products as $p) {
            if ($values = self::_getTextAttributeValues($p, $attributeCode, $inputType, $store)) {
                foreach ($values as $key => $value) {
                    $trimValue = trim($value);
                    if ($trimValue != '' && !in_array($trimValue, $arrTextValues)) {
                        $arrTextValues[] = $value;
                    }
                }
            }
        }

        return $arrTextValues;
    }

    private static function _getIdAttributesValues($products, $attributeCode)
    {
        $values = array();
        foreach ($products as $productKey => $product) {
            $value = $product->getData($attributeCode);
            if ($value == '') {
                // Nothing.
            } elseif (is_array($value) && empty($value)) {
                // Nothing.

            } else {
                if (!in_array($value, $values)) {
                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    public static function getProductAttributes($attributeIds = Simtech_Searchanise_Model_Queue::NOT_DATA, $store = null, $isPrice = false)
    {
        if ($attributeIds === Simtech_Searchanise_Model_Queue::NOT_DATA) {
            static $allAttributes;

            if (!isset($allAttributes)) {
                $allAttributes = Mage::getResourceModel('catalog/product_attribute_collection');
                $allAttributes
                    ->setItemObjectClass('catalog/resource_eav_attribute')
                    // ->setOrder('position', 'ASC') // not need, because It will slow with "order"
                    ->load();
            }

            return $allAttributes;
        }

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute');

        // fixme in the future
        // need delete
        // if ($store) {
            // $filters->addStoreLabel($store->getId());
        // }
        // end fixme

        if (is_array($attributeIds)) {
            $attributes->addFieldToFilter('main_table.attribute_id', array('in' => $attributeIds));
        } else {
            $attributes->addFieldToFilter('main_table.attribute_id', array('eq' => $attributeIds));
        }
        if ($isPrice) {
            $attributes->addFieldToFilter('main_table.frontend_input', array('eq' => 'price'));
        }
        
        $attributes->load();

        return $attributes;        
    }

    private static function _generateProductAttributes(&$item, $product, $childrenProducts = null, $unitedProducts = null, $store = null)
    {
        $attributes = self::getProductAttributes(Simtech_Searchanise_Model_Queue::NOT_DATA, $store);

        if ($attributes) {
            $requiredAttributes = self::_getRequiredAttributes();
            $useFullFeed = Mage::helper('searchanise/ApiSe')->getUseFullFeed();

            foreach ($attributes as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                $value = $product->getData($attributeCode);

                // unitedValues - main value + childrens values
                $unitedValues = self::_getIdAttributesValues($unitedProducts, $attributeCode);
               
                $inputType = $attribute->getData('frontend_input');
                $isSearchable = $attribute->getIsSearchable();
                $isVisibleInAdvancedSearch = $attribute->getIsVisibleInAdvancedSearch();
                $usedForSortBy = $attribute->getUsedForSortBy();
                $isFilterableInSearch = $attribute->getIsFilterableInSearch();

                $attributeName = 'attribute_' . $attribute->getId();

                $isNecessaryAttribute = $useFullFeed || $isSearchable || $isVisibleInAdvancedSearch || $usedForSortBy || $isFilterableInSearch || in_array($attributeCode, $requiredAttributes);
                
                if (!$isNecessaryAttribute) {
                    continue;
                }

                if (empty($unitedValues)) {
                    // nothing

                // <system_attributes>
                } elseif ($attributeCode == 'price') {
                    // already defined in the '<cs:price>' field

                } elseif ($attributeCode == 'status' || $attributeCode == 'visibility') {
                    $item[$attributeCode] = $value;

                } elseif ($attributeCode == 'has_options') {
                } elseif ($attributeCode == 'required_options') {
                } elseif ($attributeCode == 'custom_layout_update') {
                } elseif ($attributeCode == 'tier_price') { // quantity discount
                } elseif ($attributeCode == 'image_label') {
                } elseif ($attributeCode == 'small_image_label') {
                } elseif ($attributeCode == 'thumbnail_label') {
                } elseif ($attributeCode == 'tax_class_id') {
                } elseif ($attributeCode == 'url_key') { // seo name
                // <system_attributes>

                } elseif ($attributeCode == 'group_price') {
                    // nothing
                    // fixme in the future if need

                } elseif ($attributeCode == 'short_description' || $attributeCode == 'name' || $attributeCode == 'sku') {
                    if (count($unitedValues) > 1) {
                        $item['se_grouped_' . $attributeCode] = array_slice($unitedValues, 1);
                    }

                } elseif ($attributeCode == 'description') {
                    $item['full_description'] = $value;
                    if (count($unitedValues) > 1) {
                        $item['se_grouped_full_' . $attributeCode] = array_slice($unitedValues, 1);
                    }

                } elseif (
                    $attributeCode == 'meta_title' || 
                    $attributeCode == 'meta_description' || 
                    $attributeCode == 'meta_keyword') {

                    $item[$attributeCode] = $unitedValues;

                } elseif ($inputType == 'price') {
                    // Other attributes with type 'price'.
                    $item[$attributeCode] = $unitedValues;

                } elseif ($inputType == 'select' || $inputType == 'multiselect') {
                    // <text_values>
                    $unitedTextValues = self::_getProductAttributeTextValues($unitedProducts, $attributeCode, $inputType, $store);
                    $item[$attributeCode] = $unitedTextValues;

                } elseif ($inputType == 'text' || $inputType == 'textarea') {
                    $item[$attributeCode] = $unitedValues;
                    
                } elseif ($inputType == 'date') {
                    $dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($value));
                    $item[$attributeCode] = $dateTimestamp;
                } elseif ($inputType == 'media_image') {
                    $image = self::generateImage($product, $attributeCode, true, 0, 0);
                    if (!empty($image)) {
                        $imageLink = '' . $image;
                        $item[$attributeCode] = $imageLink;
                    }
                } elseif ($inputType == 'gallery') {
                    // Nothing.
                } else {
                    // Attribute not will use.
                }
            }
        }

        return $item;
    }

    public static function generateProductFeed($product, $store = null, $checkData = true)
    {
        $item = array();
        if ($checkData) {
            if (!$product ||
                !$product->getId() ||
                !$product->getName()
                ) {
                return $item;
            }
        }

        $unitedProducts = array($product); // current product + childrens products (if exists)
        $childrenProducts = self::getChildrenProducts($product, $store);
        if ($childrenProducts) {
            foreach ($childrenProducts as $childrenProductsKey => $childrenProduct) {
                $unitedProducts[] = $childrenProduct;
            }
        }

        $item['id'] = $product->getId();      
        $item['title'] = $product->getName();
        $item['summary'] = $product->getData('short_description');
        $item['link'] = $product->getProductUrl(false);
        $item['product_code'] = $product->getSku();

        self::_generateProductPrices($item, $product, $childrenProducts, $store);

        $quantity = self::getProductQty($product, $store, $unitedProducts);
        $item['quantity'] = ceil($quantity);
        $item['is_in_stock'] = $quantity > 0;

        // Show images without white field
        // Example: image 360 x 535 => 47 х 70
        if (Mage::helper('searchanise/ApiSe')->getResultsWidgetEnabled($store)) {
            $image = self::getProductImageLink($product, false, 160, 160);
        } else {
            $image = self::getProductImageLink($product, false, 70, 70);
        }

        if ($image) {
            $imageLink = '' . $image;

            if ($imageLink != '') {
                $item['image_link'] = '' . $imageLink;
            }
        }

        self::_generateProductAttributes($item, $product, $childrenProducts, $unitedProducts, $store);

        $categoryCollection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToFilter('path', array('like' => "1/{$store->getRootCategoryId()}/%"));

        $categoryCollection->getSelect()
            ->join(array('cp' => $product->getResource()->getTable('catalog/category_product')), 'cp.category_id=entity_id')
            ->where('cp.product_id = ' . $product->getId());

        $categoryIds = $categoryCollection->getAllIds();

        if (!empty($categoryIds)) {
            $categoryNames = array();
            foreach ($categoryIds as $catKey => $categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                if ($category) {
                    $categoryNames[] = $category->getName();
                }
            }

            $item['category_ids'] = $categoryIds;
            $item['categories'] = $categoryNames;
        }

        $tagNames = array();
        $tags = self::getTagCollection($product, $store);
        if ($tags && count($tags) > 0) {
            foreach ($tags as $tag) {
                if ($tag) {
                    $tagNames[] = $tag->getName();
                }
            }
        }

        if (!empty($tagNames)) {
            $item['tags'] = $tagNames;
        }

        return $item;
    }
    
    public static function getOptionCollection($filter, $store = null)
    {
        // not used in current module
        $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection');
        
        if (!empty($store)) {
            $optionCollection->setStoreFilter($store); //fixme need check
        }
        
        return $optionCollection
            ->setAttributeFilter($filter->getId())
            ->setPositionOrder('desc', true)
            ->load();
    }
    
    private static function _getPriceNavigationStep($store = null)
    {
        if (!$store) {
            $store = Mage::app()->getStore(0);
        }
        
        $priceRangeCalculation = $store->getConfig(Mage_Catalog_Model_Layer_Filter_Price::XML_PATH_RANGE_CALCULATION);
        
        if ($priceRangeCalculation == Mage_Catalog_Model_Layer_Filter_Price::RANGE_CALCULATION_MANUAL) {
            return $store->getConfig(Mage_Catalog_Model_Layer_Filter_Price::XML_PATH_RANGE_STEP);
        }
        
        return null;
    }

    public static function isFacet($attribute)
    {
        return $attribute->getIsFilterableInSearch();
    }

    private static function _generateFacetFromFilter($attribute, $store = null)
    {
        $item = array();

        if (self::isFacet($attribute)) {
            $attributeType = '';

            $inputType = $attribute->getData('frontend_input');

            // "Can be used only with catalog input type Dropdown, Multiple Select and Price".
            if (($inputType == 'select') || ($inputType == 'multiselect')) {
                $item['type'] = 'select';
                
            } elseif ($inputType == 'price') {
                $item['type'] = 'dynamic';
                $step = self::_getPriceNavigationStep($store);

                if (!empty($step)) {
                    $item['min_range'] = $step;
                }
            } else {
                // Nothing.
            }

            if (isset($item['type'])) {
                $item['title'] = $attribute->getStoreLabel();
                $item['position']  = ($inputType == 'price')? $attribute->getPosition() : $attribute->getPosition() + 20;
                $item['attribute'] = $attribute->getAttributeCode();
            }
        }
        
        return $item;
    }
    
    private static function _generateFacetFromCustom($title = '', $position = 0, $attribute = '', $type = '')
    {
        $facet = array();
        
        $facet['title'] = $title;
        $facet['position'] = $position;
        $facet['attribute'] = $attribute;
        $facet['type'] = $type;

        return $facet;
    }

    private static function validateProductIds($productIds, $store = null)
    {
        $validProductIds = array();
        if ($store) {
            Mage::app()->setCurrentStore($store->getId());
        } else {
            Mage::app()->setCurrentStore(0);
        }

        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('entity_id');

        if ($store) {
            $products->addStoreFilter($store);
        }

        // Already exist automatic definition 'one value' or 'array'.
        $products->addIdFilter($productIds);

        $products->load();
        if ($products) {
            // Not used because 'arrProducts' comprising 'stock_item' field and is 'array(array())'
            // $arrProducts = $products->toArray(array('entity_id'));
            foreach ($products as $product) {
                $validProductIds[] = $product->getId();
            }
        }
        // It is necessary for save memory.
        unset($products);

        return $validProductIds;
    }

    private static function _getProductsByItems($productIds, $store = null)
    {
        $products = array();
        $productIds = self::validateProductIds($productIds, $store);

        if ($productIds) {
            foreach ($productIds as $key => $productId) {
                if (empty($productId)) {
                    continue;
                }
                
                // It can use various types of data.
                if (is_array($productId)) {
                    if (isset($productId['entity_id'])) {
                        $productId = $productId['entity_id'];
                    }
                }

                try {
                    $product = Mage::getModel('catalog/product')->load($productId);

                } catch (Exception $e) {
                    Mage::helper('searchanise/ApiSe')->log($e->getMessage(), "Error: Script couldn't get product");
                    continue;
                }

                if ($product) {
                    $products[] = $product;
                }
            }
        }

        return $products;
    }

    public static function getProducts($productIds = null, $store = null, $customerGroupId = null)
    {
        $resultProducts = array();
        if (empty($productIds)) {
            return $resultProducts;
        }

        // Need for generate correct url and get right products.
        if ($store) {
            Mage::app()->setCurrentStore($store->getId());
        } else {
            Mage::app()->setCurrentStore(0);
        }

        static $arrProducts = array();

        $keyProducts = '';
        if ($productIds) {
            if (is_array($productIds)) {
                $keyProducts .= implode('_', $productIds);
            } else {
                $keyProducts .= $productIds;
            }
        }
        $keyProducts .= ':' .  ($store ? $store->getId() : '0');
        $keyProducts .= ':' .  $customerGroupId;
        $keyProducts .= ':' .  (self::$isGetProductsByItems ? '1' : '0');

        if (isset($arrProducts[$keyProducts])) {
            // Nothing
        } else {
            $products = array();
            if (self::$isGetProductsByItems) {
                $products = self::_getProductsByItems($productIds, $store);
            } else {
                $products = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addUrlRewrite();

                if ($customerGroupId != null) {
                    if ($store) {
                        $products->addPriceData($customerGroupId, $store->getWebsiteId());
                    } else {
                        $products->addPriceData($customerGroupId);
                    }
                }
                    
                if ($store) {
                    $products
                        ->setStoreId($store)
                        ->addStoreFilter($store);
                }
                
                if ($productIds !== Simtech_Searchanise_Model_Queue::NOT_DATA) {
                    // Already exist automatic definition 'one value' or 'array'.
                    $products->addIdFilter($productIds);
                }

                $products->load();
            }

            // Fixme in the future
            // Maybe create cache without customerGroupId and setCustomerGroupId after using cache.
            if ($products && ($store || $customerGroupId != null)) {
                foreach ($products as $key => &$product) {
                    if ($product) {
                        if ($store) {
                            $product->setWebsiteId($store->getWebsiteId());
                        }
                        if ($customerGroupId != null) {
                            $product->setCustomerGroupId($customerGroupId);
                        }
                    }
                }
            }
            // end fixme

            $arrProducts[$keyProducts] = $products;
        }

        return $arrProducts[$keyProducts];
    }

    // Main functions //
    public static function generateProductsFeed($productIds = null, $store = null, $checkData = true)
    {
        $items = array();

        if (Mage::helper('catalog/product_flat')->isEnabled()) {
            Mage::helper('searchanise/ApiProducts')->setIsGetProductsByItems(true);//workaround for get all attributes
            Mage::getResourceModel('catalog/product_collection')->setStore($store->getId());//workaround for magento flat products table bug
        }

        $products = self::getProducts($productIds, $store, null);

        if ($products) {
            foreach ($products as $product) {
                if ($item = self::generateProductFeed($product, $store, $checkData)) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    public static function getMinMaxProductId($store = null)
    {
        if ($store) {
            if (Mage::helper('catalog/product_flat')->isEnabled()) {
                Mage::helper('searchanise/ApiProducts')->setIsGetProductsByItems(true);//workaround for get all attributes
                Mage::getResourceModel('catalog/product_collection')->setStore($store->getId());//workaround for magento flat products table bug
            }   
        } 

        $startId = 0;
        $endId = 0;

        $productStartCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSort('entity_id', Varien_Data_Collection::SORT_ORDER_ASC)
            ->setPageSize(1);
        if ($store) {
            $productStartCollection = $productStartCollection->addStoreFilter($store);
        }
        $productStartCollection = $productStartCollection->load();

        $productEndCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSort('entity_id', Varien_Data_Collection::SORT_ORDER_DESC)
            ->setPageSize(1);
        if ($store) {
            $productEndCollection = $productEndCollection->addStoreFilter($store);
        }
        $productEndCollection = $productEndCollection->load();

        if ($productStartCollection) {
            $productArr = $productStartCollection->toArray(array('entity_id'));
            if (!empty($productArr)) {
                $firstItem = reset($productArr);
                $startId = $firstItem['entity_id'];
            }
        }

        if ($productEndCollection) {
            $productArr = $productEndCollection->toArray(array('entity_id'));
            if (!empty($productArr)) {
                $firstItem = reset($productArr);
                $endId = $firstItem['entity_id'];
            }
        }

        return array($startId, $endId);
    }
    
    public static function getProductIdsFormRange($start, $end, $step, $store = null, $isOnlyActive = false)
    {
        $arrProducts = array();
        // Need for get correct products.
        if ($store) {
            if (Mage::helper('catalog/product_flat')->isEnabled()) {
                Mage::helper('searchanise/ApiProducts')->setIsGetProductsByItems(true);//workaround for get all attributes
                Mage::getResourceModel('catalog/product_collection')->setStore($store->getId());//workaround for magento flat products table bug
            }   
            Mage::app()->setCurrentStore($store->getId());
        } else {
            Mage::app()->setCurrentStore(0);
        }
        
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addFieldToFilter('entity_id', array("from" => $start, "to" => $end))
            ->setPageSize($step);
        
        if ($store) {
            $products->addStoreFilter($store);
        }

        if ($isOnlyActive) {
            $products->addAttributeToFilter('status', array('in'=> Mage::getSingleton('catalog/product_status')->getVisibleStatusIds()));
            // It may require to disable "product visibility" filter if "is full feed".
            if (Mage::helper('searchanise/ApiSe')->getUseFullFeed()) {
                $products->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds()));
            } else {
                $products->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds()));
            }
        }
        
        $products->load();
        if ($products) {
            // Not used because 'arrProducts' comprising 'stock_item' field and is 'array(array())'
            // $arrProducts = $products->toArray(array('entity_id'));
            foreach ($products as $product) {
                $arrProducts[] = $product->getId();
            }
        }
        // It is necessary for save memory.
        unset($products);

        return $arrProducts;
    }

    private static function _getRequiredAttributes()
    {
        return array(
            'status',
            'visibility',
            'price',
        );
    }

    public static function getSchemaAttribute($attribute, $store = null)
    {
        $items = array();

        $requiredAttributes = self::_getRequiredAttributes();
        $useFullFeed = Mage::helper('searchanise/ApiSe')->getUseFullFeed();

        $attributeCode = $attribute->getAttributeCode();
        $inputType = $attribute->getData('frontend_input');
        $isSearchable = $attribute->getIsSearchable();
        $isVisibleInAdvancedSearch = $attribute->getIsVisibleInAdvancedSearch();
        $usedForSortBy = $attribute->getUsedForSortBy();
        $isFilterableInSearch = $attribute->getIsFilterableInSearch();
        $attributeName = 'attribute_' . $attribute->getId();

        $isNecessaryAttribute = $useFullFeed || $isSearchable || $isVisibleInAdvancedSearch || $usedForSortBy || $isFilterableInSearch || in_array($attributeCode, $requiredAttributes);
        
        if (!$isNecessaryAttribute) {
            return $items;
        }

        $type = '';
        $name = $attribute->getAttributeCode();
        $title = $attribute->getStoreLabel();
        $sorting = $usedForSortBy ? 'Y' : 'N';
        $textSearch = $isSearchable ? 'Y' : 'N';
        $attributeWeight = 0;

        // <system_attributes>
        if ($attributeCode == 'price') {
            $type = 'float';
            $textSearch = 'N';

        } elseif ($attributeCode == 'status' || $attributeCode == 'visibility') {
            $type = 'text';
            $textSearch = 'N';

        } elseif ($attributeCode == 'has_options') {
        } elseif ($attributeCode == 'required_options') {
        } elseif ($attributeCode == 'custom_layout_update') {
        } elseif ($attributeCode == 'tier_price') { // quantity discount
        } elseif ($attributeCode == 'image_label') {
        } elseif ($attributeCode == 'small_image_label') {
        } elseif ($attributeCode == 'thumbnail_label') {
        } elseif ($attributeCode == 'tax_class_id') {
        } elseif ($attributeCode == 'url_key') { // seo name
        } elseif ($attributeCode == 'group_price') {
        // <system_attributes>

        } elseif ($attributeCode == 'name' || $attributeCode == 'sku' || $attributeCode == 'short_description') {
            $type = 'text';
            $name  = 'se_grouped_' . $attributeCode;
            $sorting = 'N';
            $title = $attribute->getStoreLabel() . ' - Grouped';
            $attributeWeight = ($attributeCode == 'short_description')? self::WEIGHT_SHORT_DESCRIPTION : self::WEIGHT_SHORT_TITLE;

        } elseif (
            $attributeCode == 'short_description' || 
            $attributeCode == 'description' ||
            $attributeCode == 'meta_title' || 
            $attributeCode == 'meta_description' || 
            $attributeCode == 'meta_keyword') {

            if ($isSearchable) {
                if ($attributeCode == 'description') {
                    $attributeWeight = self::WEIGHT_DESCRIPTION;
                } elseif ($attributeCode == 'meta_title') {
                    $attributeWeight = self::WEIGHT_META_TITLE;
                } elseif ($attributeCode == 'meta_description') {
                    $attributeWeight = self::WEIGHT_META_DESCRIPTION;
                } elseif ($attributeCode == 'meta_keyword') {
                    $attributeWeight = self::WEIGHT_META_KEYWORDS;
                } else {
                    // Nothing.
                }
            }
            $type = 'text';
            if ($attributeCode == 'description') {
                $name = 'full_description';
                $items[] = array(
                    'name'   => 'se_grouped_full_' . $attributeCode,
                    'title'  => $attribute->getStoreLabel() . ' - Grouped',
                    'type'   => $type,
                    'weight' => $isSearchable ? self:: WEIGHT_DESCRIPTION_GROUPED : 0,
                    'text_search' => $textSearch,
                );
            }

        } elseif ($inputType == 'price') {
            $type = 'float';

        } elseif ($inputType == 'select' || $inputType == 'multiselect') {
            $type = 'text';
            $attributeWeight = $isSearchable ? self::WEIGHT_SELECT_ATTRIBUTES : 0;

        } elseif ($inputType == 'text' || $inputType == 'textarea') {
            if ($isSearchable) {
                if ($inputType == 'text') {
                    $attributeWeight = self::WEIGHT_TEXT_ATTRIBUTES;
                } elseif ($inputType == 'textarea') {
                    $attributeWeight = self::WEIGHT_TEXT_AREA_ATTRIBUTES;
                }
            }
            $type = 'text';
            
        } elseif ($inputType == 'date') {
            $type = 'int';

        } elseif ($inputType == 'media_image') {
            $type = 'text';

        } elseif ($inputType == 'gallery') {
            // Nothing.
        } else {
            // Attribute not will use.
        }

        if ($type) {
            $item = array(
                'name'   => $name,
                'title'  => $title,
                'type'   => $type,
                'sorting' => $sorting,
                'weight' => $attributeWeight,
                'text_search' => $textSearch,
            );

            if ($facet = self::_generateFacetFromFilter($attribute)) {
                $item['facet'] = $facet;
            }

            $items[] = $item;
        }

        return $items;
    }
    
    public static function getSchemaPrices($store = null)
    {
        return self::getSchema(null, $store, true);
    }

    public static function getSchemaCustomerGroupsPrices()
    {
        $items = array();

        if ($customerGroups = self::_getCustomerGroups()) {
            foreach ($customerGroups as $keyCustomerGroup => $customerGroup) {
                $label = Mage::helper('searchanise/ApiSe')->getLabelForPricesUsergroup() . $customerGroup->getId();
                $items[] = array(
                    'name'  => $label,
                    'title' => 'Price for ' .  $customerGroup->getData('customer_group_code'),
                    'type'  => 'float',
                );
            }
        }

        return $items;
    }

    public static function getSchemaCategories($store)
    {
		$category_schema = array();
        if (Mage::helper('searchanise/ApiSe')->getResultsWidgetEnabled($store)) {
            $category_schema[] = array(
                'name'        => 'categories',
                'title'       => Mage::helper('catalog')->__('Category'),
                'type'        => 'text',
                'weight'      => self::WEIGHT_CATEGORIES,
                'text_search' => 'Y',
                'facet'       => self::_generateFacetFromCustom(Mage::helper('catalog')->__('Category'), 10, 'categories', 'select'),
            );
        } else {
            $category_schema[] = array(
                'name'        => 'categories',
                'title'       => Mage::helper('catalog')->__('Category'),
                'type'        => 'text',
                'weight'      => self::WEIGHT_CATEGORIES,
                'text_search' => 'Y',
            );
            $category_schema[] = array(
                'name'        => 'category_ids',
                'title'       => Mage::helper('catalog')->__('Category') . ' - IDs',
                'type'        => 'text',
                'weight'      => 0,
                'text_search' => 'N',
                'facet'       => self::_generateFacetFromCustom(Mage::helper('catalog')->__('Category'), 10, 'category_ids', 'select'),
            );
        }

        return $category_schema;
    }

    public static function getSchemaTags()
    {
        return array(
            array(
                'name'        => 'tags',
                'title'       => Mage::helper('catalog')->__('Product Tags'),
                'type'        => 'text',
                'weight'      => self::WEIGHT_TAGS,
                'text_search' => 'Y',
            )
        );
    }

    public static function getSchema($attributeIds = Simtech_Searchanise_Model_Queue::NOT_DATA, $store, $isPrice = false)
    {
        static $schemas;

        if (isset($schemas[$store->getId()])) {
            return $schemas[$store->getId()];
        }

        Mage::app()->setCurrentStore($store->getId());

        $schema = array();

        if ($attributeIds === Simtech_Searchanise_Model_Queue::NOT_DATA) {
            $schema = self::getSchemaCustomerGroupsPrices();

            if ($items = self::getSchemaCategories($store)) {
                foreach ($items as $keyItem => $item) {
                    $schema[] = $item;
                }
            }

            if ($items = self::getSchemaTags()) {
                foreach ($items as $keyItem => $item) {
                    $schema[] = $item;
                }
            }
            $schema[] = array(
                'name'        => 'is_in_stock',
                'title'       => Mage::helper('catalog')->__('Stock Availability'),
                'type'        => 'text',
                'weight'      => 0,
                'text_search' => 'N',
            );
        }

        if ($attributes = self::getProductAttributes($attributeIds, $store, $isPrice)) {
            foreach ($attributes as $attribute) {
                if ($items = self::getSchemaAttribute($attribute, $store)) {
                    foreach ($items as $keyItem => $item) {
                        $schema[] = $item;
                    }
                }
            }
        }

        $schemas[$store->getId()] = $schema;

        return $schema;
    }
    
    public static function getHeader($store = null)
    {
        $url = '';
        
        if ($store) {
            $url = Mage::app()->getStore()->getBaseUrl();
        } else {
            $url = $store->getUrl();
        }
        $date = date('c');

        return array(
            'id'      => $url,
            'updated' => $date,
        );
    }
}
