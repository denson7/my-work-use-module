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

class Simtech_Searchanise_Model_Config_Data extends Mage_Adminhtml_Model_Config_Data
{
    /**
     * Save config section
     * Require set: section, website, store and groups
     *
     * @return Mage_Adminhtml_Model_Config_Data
     */
    public function save()
    {
        Mage::dispatchEvent('searchanise_adminhtml_config_data_save_before', array('object' => $this));
        
        $ret = parent::save();
        
        Mage::dispatchEvent('searchanise_adminhtml_config_data_save_after', array('object' => $ret));
        
        return $ret;
    }
}
