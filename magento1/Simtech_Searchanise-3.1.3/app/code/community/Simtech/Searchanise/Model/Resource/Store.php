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
class Simtech_Searchanise_Model_Resource_Store extends Mage_Core_Model_Resource_Store
{
    /**
     * Check store code before save
     *
     * @param Mage_Core_Model_Abstract $model
     * @return Mage_Core_Model_Resource_Store
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $model)
    {
        $ret = parent::_beforeSave($model);
        
        Mage::dispatchEvent('searchanise_core_save_store_before', array('store' => $model));
        
        return $ret;
    }
    
    /**
     * Update Store Group data after save store
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Store
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $ret = parent::_afterSave($object);
        
        Mage::dispatchEvent('searchanise_core_save_store_after', array('store' => $object));
        
        return $ret;
    }
    
    /**
     * Remove core configuration data after delete store
     *
     * @param Mage_Core_Model_Abstract $model
     * @return Mage_Core_Model_Resource_Store
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $model)
    {
        $ret = parent::_afterDelete($model);
        
        Mage::dispatchEvent('searchanise_core_delete_store_after', array('store' => $model));
        
        return $ret;
    }
}
