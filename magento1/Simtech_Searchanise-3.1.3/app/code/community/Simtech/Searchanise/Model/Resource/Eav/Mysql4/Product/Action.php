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
class Simtech_Searchanise_Model_Resource_Eav_Mysql4_Product_Action extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Action
{
    /**
     * Update attribute values for entity list per store
     *
     * @param array $entityIds
     * @param array $attrData
     * @param int $storeId
     * @return Mage_Catalog_Model_Product_Action
     */
    public function updateAttributes($entityIds, $attrData, $storeId)
    {
        if (version_compare(Mage::getVersion(), '1.6', '<')) {  
            Mage::dispatchEvent('searchanise_product_attribute_update_before', array(
                'attributes_data' => &$attrData,
                'product_ids'     => &$entityIds,
                'store_id'        => &$storeId
            ));
        }

        return parent::updateAttributes($entityIds, $attrData, $storeId);
    }
}
