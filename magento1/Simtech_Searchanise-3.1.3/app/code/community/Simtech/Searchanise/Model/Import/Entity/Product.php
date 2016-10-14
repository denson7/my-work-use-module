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

class Simtech_Searchanise_Model_Import_Entity_Product extends Mage_ImportExport_Model_Import_Entity_Product
{
    /**
     * Delete products.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _deleteProducts()
    {
        $idToDelete = null;
        
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $idToDelete = array();
            
            foreach ($bunch as $rowNum => $rowData) {
                if ($this->validateRow($rowData, $rowNum) && self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
                    $idToDelete[] = $this->_oldSku[$rowData[self::COL_SKU]]['entity_id'];
                }
            }
        }
        
        Mage::dispatchEvent('searchanise_import_delete_product_entity_after', array('idToDelete' => $idToDelete));
        
        return parent::_deleteProducts();
    }
    
    /**
     * Update and insert data in entity table.
     *
     * @param array $entityRowsIn Row for insert
     * @param array $entityRowsUp Row for update
     * @return Mage_ImportExport_Model_Import_Entity_Product
     */
    protected function _saveProductEntity(array $entityRowsIn, array $entityRowsUp)
    {
        $ret = parent::_saveProductEntity($entityRowsIn, $entityRowsUp);
        
        Mage::dispatchEvent('searchanise_import_save_product_entity_after', array('_newSku' => ($ret->_newSku)));
        
        return $ret;
    }
}