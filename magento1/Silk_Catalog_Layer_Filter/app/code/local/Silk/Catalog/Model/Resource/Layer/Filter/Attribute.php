<?php

/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-10-20
 * Time: 下午1:43
 * rewrite Mage_Catalog_Model_Resource_Layer_Filter_Attribute
 */
class Silk_Catalog_Model_Resource_Layer_Filter_Attribute extends Mage_Catalog_Model_Resource_Layer_Filter_Attribute
{
    /**
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter
     * @param int $value
     * @return $this
     */
    public function applyFilterToCollection($filter, $value)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $attribute  = $filter->getAttributeModel();
        $connection = $this->_getReadAdapter();
        $tableAlias = $attribute->getAttributeCode() . '_idx_'.$value;
//        Mage::log('123',null,'test.log',true);
        $conditions = array(
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
            $connection->quoteInto("{$tableAlias}.value = ?", $value)
        );

        $collection->getSelect()->join(
            array($tableAlias => $this->getMainTable()),
            implode(' AND ', $conditions),
            array()
        );

        return $this;
    }

}