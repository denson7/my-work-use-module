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
class Simtech_Searchanise_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute extends Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
{
    /**
     * Retrieve array with products counts per attribute option
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @return array
     */
    public function getCount($filter)
    {
        if (!Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {
            return parent::getCount($filter);
        }
        
        $collection = $filter->getLayer()->getProductCollection();
        
        if ((!method_exists($collection, 'checkSearchaniseResult')) || (!$collection->checkSearchaniseResult())) {
            return parent::getCount($filter);
        }
        
        return $collection
            ->getSearchaniseRequest()
            ->getCountAttribute($filter);
    }

    /**
     * Apply attribute filter to product collection
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        if (!Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true)) {
            return parent::applyFilterToCollection($filter, $value);
        }
        
        $collection = $filter->getLayer()->getProductCollection();
        
        if ((!method_exists($collection, 'checkSearchaniseResult')) || (!$collection->checkSearchaniseResult())) {
            return parent::applyFilterToCollection($filter, $value);
        }
        // Disable internal attribute filter.
        
        return $this;
    }
}