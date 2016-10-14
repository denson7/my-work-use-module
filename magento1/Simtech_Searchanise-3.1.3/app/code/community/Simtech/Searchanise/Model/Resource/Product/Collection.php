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

// [v1.6] [v1.7] [v1.8] [v1.9]
class Simtech_Searchanise_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Searchanise Collection Product 
     *
     * @var Simtech_Searchanise_Model_Searchanise
     */
    protected $_searchaniseCollection = null;

    /**
     * Initialize resource
     * @return Simtech_Searchanise_Model_Mysql4_Product_CollectionSearhanise
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_searchaniseCollection = Mage::getModel('searchanise/searchanise');
        $this->_searchaniseCollection->setCollection($this);
    }

    public function __construct($resource = null, array $args = array())
    {
        parent::__construct($resource, $args);

        if (Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {
            $this->setSearchaniseRequest(Mage::helper('searchanise')->getSearchaniseRequest());
            if ($this->checkSearchaniseResult()) {
                $this->addSearchaniseFilter();
            }
        }
    }

    public function initSearchaniseRequest()
    {
        return $this->_searchaniseCollection->initSearchaniseRequest();
    }
    
    public function checkSearchaniseResult()
    {
        return $this->_searchaniseCollection->checkSearchaniseResult();
    }
    
    public function setSearchaniseRequest($request)
    {
        return $this->_searchaniseCollection->setSearchaniseRequest($request);
    }
    
    public function getSearchaniseRequest()
    {
        return $this->_searchaniseCollection->getSearchaniseRequest();
    }
    
    public function addSearchaniseFilter()
    {
        return $this->_searchaniseCollection->addSearchaniseFilter();
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
        $this->_searchaniseCollection->setOrder($attribute, $dir);
        return $this;
    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection
     */
    public function setOrderParent($attribute, $dir = 'desc')
    {
        return parent::setOrder($attribute, $dir);
    }

    /**
     * Retrieve collection last page number
     *
     * @return int
     */
    public function getLastPageNumber()
    {
        return $this->_searchaniseCollection->getLastPageNumber();
    }

    /**
     * Retrieve collection last page number
     *
     * @return int
     */
    public function getLastPageNumberParent()
    {
        return parent::getLastPageNumber();
    }

    public function addIdFilter($productId, $exclude = false)
    {
        // Reset Searchanise filter to let other product collections set id filters
        if (Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {
            $this->getSelect()->reset(Zend_Db_Select::WHERE);
        }
        return parent::addIdFilter($productId, $exclude);
    }
}
