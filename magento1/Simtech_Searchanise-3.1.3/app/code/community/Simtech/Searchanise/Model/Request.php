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

class Simtech_Searchanise_Model_Request extends Mage_Core_Model_Abstract
{
    protected $searchResult    = null;
    protected $productIdsSting = null;
    protected $attributesCount = array();
    
    const SEPARATOR_ITEMS = "'";
    
    protected $searchParams = array();
    
    protected $apiKey     = '';
    protected $privateKey = '';
    protected $store      = null;
    
    protected function _construct()
    {
        return $this;
    }

    public function setStore($value)
    {
        $this->store = $value;
        
        return $this;
    }
    
    public function getStore()
    {
        return $this->store;
    }
    
    public function getPrivateKey()
    {
        return Mage::helper('searchanise/ApiSe')->getPrivateKey($this->store);
    }
    
    public function getApiKey()
    {
        return Mage::helper('searchanise/ApiSe')->getApiKey($this->store);
    }
    
    public function checkApiKey()
    {
        if ($this->getApiKey()) {
            return true;
        }
        
        return false;
    }
    
    public function checkSearchResult()
    {
        if (!empty($this->searchResult)) {
            return true;
        }
        
        return false;
    }
    
    public function setSearchResult($value = array())
    {
        $this->searchResult = $value;
        
        $this->setProductIdsString();
        $this->setAttributesCount();
        
        return $this;
    }
    
    public function getSearchResult()
    {
        return $this->searchResult;
    }
    
    public function setAttributesCount($value = array())
    {
        $this->attributesCount = $value;
        
        return $this;
    }
    
    public function getAttributesCount()
    {
        return $this->attributesCount;
    }
    
    public function setAttributesCountLabel($value = '', $label = '')
    {
        if (empty($this->attributesCount)) {
            $this->attributesCount = array();
        }
        
        $this->attributesCount[$label] = $value;
        
        return $this;
    }
    
    public function getAttributesCountLabel($label = '')
    {
        if (!empty($label)) {
            return $this->attributesCount[$label];
        }
        
        return null;
    }
    
    public function checkAttributesCountLabel($label = '')
    {
        if (isset($this->attributesCount[$label])) {
            return true;
        }
        
        return false;
    }
    
    public function getProductIds()
    {
        $res = $this->getSearchResult();
        
        return empty($res['items']) ? array() : $res['items'];
    }

    public function setProductIdsString($value = '')
    {
        $this->productIdsString = $value;
        
        return $this;
    }
    
    public function getProductIdsString()
    {
        if (empty($this->productIdsString))
        {
            $res = $this->getSearchResult();
            $productIdsString = '';
            
            if (!empty($res['items'])) {
                foreach ($res['items'] as $k => $item)
                {
                    if (!empty($item['product_id'])) {
                        if (empty($productIdsString)) {
                            $productIdsString = self::SEPARATOR_ITEMS . $item['product_id'] . self::SEPARATOR_ITEMS;
                        } else {
                            $productIdsString .= ',' . self::SEPARATOR_ITEMS . $item['product_id'] . self::SEPARATOR_ITEMS;
                        }
                    }
                }
            }
            
            $this->setProductIdsString($productIdsString);
        }

        return $this->productIdsString;
    }
    
    public function getTotalProduct()
    {
        $res = $this->getSearchResult();
        
        return empty($res['totalItems']) ? 0 : $res['totalItems'];
    }
    
    public function getSuggestions()
    {
        $res = $this->getSearchResult();
        
        return empty($res['suggestions']) ? null : $res['suggestions'];
    }
    
    public function setSearchParams($params = array())
    {
        $this->searchParams = $params;
        
        return $this;
    }
    
    public function setSearchParam($key, $value)
    {
        if (empty($this->searchParams)) {
            $this->searchParams = array();
        }
        
        $this->searchParams[$key] = $value;
        
        return $this;
    }
    
    public function getSearchParams()
    {
        return $this->searchParams;
    }

    protected function getStrFromParams($params = array(), $mainKey = null)
    {
        $ret = '';

        if (!empty($params)) {
            foreach ($params as $key => $param) {
                if (is_array($param)) {
                    $ret .= $this->getStrFromParams($param, $key);
                } else {
                    if (!$mainKey) {
                        $ret .= $key . '=' . $param . '&'; 
                    } else {
                        $ret .= $mainKey . '[' . $key . ']=' . $param . '&'; 
                    }
                }
            }
        }

        return $ret;
    }

    public function getSearchParamsStr()
    {
        return $this->getStrFromParams($this->getSearchParams());
    }
    
    public function mergeSearchParams($new_params = array())
    {
        return $this->setSearchParams(array_merge($new_params, $this->getSearchParams()));
    }
    
    public function unsetSearchParams($key = '')
    {
        if (isset($this->searchParams[$key])) {
            unset($this->searchParams[$key]);
        }
        
        return $this;
    }
    
    public function checkSearchParams($key = '')
    {
        if (empty($this->searchParams[$key])) {
            return $this->unsetSearchParams($key);
        }
        
        return $this;
    }
    
    public function sendSearchRequest()
    {
        $this->setSearchResult();
        
        if (!$this->checkApiKey()) {
            return $this;
        }
        
        $default_params = array(
            'items'  => 'true',
            'facets' => 'true',
            'output' => 'json',
        );
        
        $this
            ->mergeSearchParams($default_params)
            ->checkSearchParams('restrictBy')
            ->checkSearchParams('union');
        
        $query = Mage::helper('searchanise/ApiSe')->buildQuery($this->getSearchParams());
        $this->setSearchParam('api_key', $this->getApiKey());
        if (Mage::helper('searchanise')->checkDebug()) {
            Mage::helper('searchanise/ApiSe')->printR(Mage::helper('searchanise/ApiSe')->getServiceUrl() . '/search?api_key=' . $this->getApiKey() . '&' . $this->getSearchParamsStr());
            Mage::helper('searchanise/ApiSe')->printR($this->getSearchParams());
        }

        if (strlen($query) > Mage::helper('searchanise/ApiSe')->getMaxSearchRequestLength()) {
            list($header, $received) = Mage::helper('searchanise/ApiSe')->httpRequest(
                Zend_Http_Client::POST,
                Mage::helper('searchanise/ApiSe')->getServiceUrl() . '/search?api_key=' . $this->getApiKey(),
                $this->getSearchParams(),
                array(),
                array(),
                Mage::helper('searchanise/ApiSe')->getSearchTimeout()
            );
        } else {
            list($header, $received) = Mage::helper('searchanise/ApiSe')->httpRequest(
                Zend_Http_Client::GET,
                Mage::helper('searchanise/ApiSe')->getServiceUrl() . '/search',
                $this->getSearchParams(),
                array(),
                array(),
                Mage::helper('searchanise/ApiSe')->getSearchTimeout()
            );
        }
        
        if (empty($received)) {
            return $this;
        }

        try {
            $result = Mage::helper('core')->jsonDecode($received);
        } catch (Exception $e) {
            return $this;
        }

        if (Mage::helper('searchanise')->checkDebug()) {
            Mage::helper('searchanise/ApiSe')->printR($result);
        }

        if (isset($result['error'])) {
            if ($result['error'] == 'EMPTY_API_KEY') {
                // nothing
            } elseif ($result['error'] == 'INVALID_API_KEY') {
                if ($this->getStore()) {
                    Mage::helper('searchanise/ApiSe')->deleteKeys($this->getStore());
                    
                    if (Mage::helper('searchanise/ApiSe')->signup($this->getStore(), false) == true) {
                        Mage::helper('searchanise/ApiSe')->queueImport($this->getStore(), false);
                    }
                }
            } elseif ($result['error'] == 'TO_BIG_START_INDEX') {
                // nothing
            } elseif ($result['error'] == 'SEARCH_DATA_NOT_IMPORTED') {
                // nothing
            } elseif ($result['error'] == 'FULL_IMPORT_PROCESSED') {
                // nothing
            } elseif ($result['error'] == 'FACET_ERROR_TOO_MANY_ATTRIBUTES') {
                // nothing
            } elseif ($result['error'] == 'NEED_RESYNC_YOUR_CATALOG') {
                Mage::helper('searchanise/ApiSe')->queueImport($this->getStore(), false);
            } elseif ($result['error'] == 'FULL_FEED_DISABLED') {
                Mage::helper('searchanise/ApiSe')->setUseFullFeed(false);
            }

            Mage::helper('searchanise/ApiSe')->log($result['error']);
            
            return $this;
        }
        
        if (empty($result) || !is_array($result) || !isset($result['totalItems'])) { 
            return $this; 
        }
        
        $this->setSearchResult($result);

        return $this;
    }
    
    /**
     * 
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCountAttribute($filter)
    {
        $ret = array();
        if (empty($filter)) {
            return $ret;
        }
        
        $attribute = $filter->getAttributeModel();
        if (empty($attribute)) {
            return $ret;
        }
        
        $label = $attribute->getAttributeCode();

        if (!$this->checkAttributesCountLabel($label)) {
            $vals = array();
            $res = $this->getSearchResult();
            
            if (!empty($res['facets'])) {
                foreach ($res['facets'] as $facet) {
                    if ($facet['attribute'] == $label) {
                        if (!empty($facet['buckets'])) {
                            foreach ($facet['buckets'] as $bucket) {
                                if ($bucket['count'] > 0) {
                                    $vals[$bucket['value'] ] = $bucket['count'];
                                }
                            }
                        }
                    }
                }
            }
            
            $this->setAttributesCountLabel($vals, $label);
        }
        
        return $this->getAttributesCountLabel($label);
    }
    
    public function getCurrentCurrencyRange($range, $store = null)
    {
        $rate = $this->getCurrentCurrencyRate($store);
        
        if (!empty($rate)) {
            return $range / $rate;
        }
        
        return $range;
    }
    
    public function getCurrentCurrencyRate($store = null)
    {
        if (empty($store)) {
            $store = Mage::app()->getStore();
        }
        
        if (!empty($store)) {
            return $store->getCurrentCurrencyRate();
        }
        
        return 0;
    }
    
    /**
     * Retrieve array with products counts per price range
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param int $range
     * @return array
     */
    public function getCountAttributePrice($filter, $range)
    {
        $ret = array();
        
        $rate = $this->getCurrentCurrencyRate($this->store);
        
        if ((empty($filter)) || (empty($rate))) {
            return $ret;
        }
        
        $attribute = $filter->getAttributeModel();
        if (empty($attribute)) {
            return $ret;
        }

        // hook, it is need for 'union' and this attribute defined in the 'price' field
        if ($attribute->getAttributeCode() == 'price') {
            $label = 'price';    
        } else {
            $label = 'attribute_' . $attribute->getId();    
        }
        
        if (!$this->checkAttributesCountLabel($label)) {
            $vals = array();
            $res = $this->getSearchResult();
            
            if (!empty($res['facets'])) {
                foreach ($res['facets'] as $facet) {
                    if ($facet['attribute'] == $label) {
                        if (!empty($facet['buckets'])) {
                            foreach ($facet['buckets'] as $bucket) {
                                // Example
                                //~ [value] => 1000-2000
                                //~ [title] => 1000 - 2000
                                //~ [from] => 1000
                                //~ [to] => 2000
                                //~ [count] => 2
                                $numberStep = round($bucket['to'] * $rate / $range);
                                
                                if ($numberStep > 0) {
                                    $vals[$numberStep] = $bucket['count'];
                                }
                            }
                        }
                    }
                }
            }
            
            $this->setAttributesCountLabel($vals, $label);
        }
        
        return $this->getAttributesCountLabel($label);
    }

    /**
     * Retrieve array with products counts per price range
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return array
     */
    public function getPriceRangeFromAttribute($attribute)
    {
        $ret = 0;
        
        if (!$attribute) {
            return $ret;
        }
        
        // hook, it is need for 'union' and this attribute defined in the 'price' field
        if ($attribute->getAttributeCode() == 'price') {
            $label = 'price';
        } else {
            $label = 'attribute_' . $attribute->getId();
        }
        $vals = array();
        $res = $this->getSearchResult();

        if (!empty($res['facets'])) {
            foreach ($res['facets'] as $facet) {
                if ($facet['attribute'] == $label) {
                    if (!empty($facet['buckets'])) {
                        foreach ($facet['buckets'] as $bucket) {
                            // Example
                            //~ [value] => 1000-2000
                            //~ [title] => 1000 - 2000
                            //~ [from] => 1000
                            //~ [to] => 2000
                            //~ [count] => 2

                            return $bucket['to'] - $bucket['from'];
                        }
                    }
                }
            }
        }

        return $ret;
    }
    
    public function getCountProductCategory($category)
    {
        $ret = null;
        if (empty($category)) {
            return $ret;
        }
        
        $label = 'category' . $category->getId();
        
        if (!$this->checkAttributesCountLabel($label)) {
            $val = 0;
            $res = $this->getSearchResult();
            
            if (!empty($res['facets'])) {
                $categoryIds = Mage::helper('searchanise/ApiCategories')->getAllChildrenCategories($category->getId());
                foreach ($res['facets'] as $facet) {
                    if ($facet['attribute'] == 'category_ids') {
                        if (!empty($facet['buckets'])) {
                            foreach ($facet['buckets'] as $bucket) {
                                if (in_array($bucket['value'], $categoryIds)) {
                                    $val += $bucket['count'];
                                }
                            }
                        }
                    }
                }
            }
            
            if ($val > $this->getTotalProduct()) {
                $val = $this->getTotalProduct();
            }

            $this->setAttributesCountLabel($val, $label);
        }

        return $this->getAttributesCountLabel($label);
    }
}