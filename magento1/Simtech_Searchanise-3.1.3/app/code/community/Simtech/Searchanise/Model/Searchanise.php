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

class Simtech_Searchanise_Model_Searchanise extends Mage_Core_Model_Abstract
{
    /**
     * Mysql4_Product_Collection
     *
     * @var Mage_Catalog_Model_Resource_Product_Collection [v1.6] [v1.7] [v1.8] [v1.9], Mage_Catalog_Model_Mysql4_Product_Collection [v1.5]
     */
    protected $_collection = null;

    /**
     * Searchanise request
     *
     * @var Simtech_Searchanise_Model_Request
     */
    protected $_searchaniseRequest = null;

    public function initSearchaniseRequest()
    {
        $this->_searchaniseRequest = Mage::getModel('searchanise/request');
        
        return $this;
    }

    /**
     * Set colection
     *
     * @param Mage_Catalog_Model_Mysql4_Product_Collection
     * @return Simtech_Searchanise_Model_Mysql4_Product_CollectionSearhanise
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        
        return $this;
    }

    /**
     * Get colection
     *
     * @return Mage_Catalog_Model_Mysql4_Product_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }
    
    public function checkSearchaniseResult()
    {
        return Mage::helper('searchanise/ApiSe')->checkSearchaniseResult($this->_searchaniseRequest);
    }
    
    public function setSearchaniseRequest($request)
    {
        $this->_searchaniseRequest = $request;
    }
    
    public function getSearchaniseRequest()
    {
        return $this->_searchaniseRequest;
    }
    
    public function addSearchaniseFilter()
    {
        $this->_collection->addFieldToFilter('entity_id', array('in' => $this->getSearchaniseRequest()->getProductIds()));
        
        return $this->_collection;
    }

    /**
     * Retrieve collection last page number
     *
     * @return int
     */
    public function getLastPageNumber()
    {
        if (!$this->checkSearchaniseResult()) {
            return $this->_collection->getLastPageNumberParent();
        }
        
        $collectionSize = (int) $this
            ->getSearchaniseRequest()
            ->getTotalProduct();

        if (0 === $collectionSize) {
            return 1;
        } else {
            $pageSize = $this->_collection->getPageSize();

            if ($pageSize) {
                return ceil($collectionSize/$pageSize);
            }
        }
        
        return 1;
    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        if (!$this->checkSearchaniseResult()) {
            return $this->_collection->setOrderParent($attribute, $dir);
        }

        $product_ids = $this
            ->getSearchaniseRequest()
            ->getProductIdsString();

        if (!empty($product_ids)) {
            $sortBy = "FIELD(e.entity_id, {$product_ids}) asc";
            $this->_collection->getSelect()->order(new Zend_Db_Expr($sortBy));
        }
        
        return $this;
    }
}
