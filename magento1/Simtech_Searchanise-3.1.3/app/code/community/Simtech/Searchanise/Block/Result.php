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

class Simtech_Searchanise_Block_Result extends Mage_CatalogSearch_Block_Result
{
    /**
     * Retrieve search result count
     *
     * @return string
     */
    public function getResultCount()
    {
        if (!Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {
            return parent::getResultCount();
        }
        
        $collection = $this->_getProductCollection();
        
        if ((!method_exists($collection, 'checkSearchaniseResult')) || (!$collection->checkSearchaniseResult())) {
            return parent::getResultCount();
        }
        
        if (!$this->getData('result_count')) {
            $size = $collection
                ->getSearchaniseRequest()
                ->getTotalProduct();
            
            $this->_getQuery()->setNumResults($size);
            $this->setResultCount($size);
        }
        
        return $this->getData('result_count');
    }
}