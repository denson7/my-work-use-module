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

// [v1.5]
class Simtech_Searchanise_Model_Resource_Eav_Mysql4_Layer_Filter_Price extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Price
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
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     * @param int $range
     * @param int $index    the range factor
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $range, $index)
    {
        if (!Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {
            return parent::applyFilterToCollection($filter, $range, $index);
        }
        
        $collection = $filter->getLayer()->getProductCollection();
        
        if ((!method_exists($collection, 'checkSearchaniseResult')) || (!$collection->checkSearchaniseResult())) {
            return parent::applyFilterToCollection($filter, $range, $index);
        }
        // Disable internal price filter.
        
        return $this;
    }
}