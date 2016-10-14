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
class Simtech_Searchanise_Model_Resource_Layer_Filter_Price extends Mage_Catalog_Model_Resource_Layer_Filter_Price
{
    public function getCount($filter, $range)
    {
        if (!Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {
            return parent::getCount($filter, $range);
        }
        
        $collection = $filter->getLayer()->getProductCollection();
        
        if ((!method_exists($collection, 'checkSearchaniseResult')) || (!$collection->checkSearchaniseResult())) {
            return parent::getCount($filter, $range);
        }
        
        return $collection
            ->getSearchaniseRequest()
            ->getCountAttributePrice($filter, $range);
    }

    /**
     * Apply price range filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @return Mage_Catalog_Model_Resource_Layer_Filter_Price
    */
    public function applyPriceRange($filter)
    {
        if (!Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {
            return parent::applyPriceRange($filter);
        }
        
        $collection = $filter->getLayer()->getProductCollection();
        
        if ((!method_exists($collection, 'checkSearchaniseResult')) || (!$collection->checkSearchaniseResult())) {
            return parent::applyPriceRange($filter);
        }
        // Disable internal price filter.
        
        return $this;
    }
}
