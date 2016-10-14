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

class Simtech_Searchanise_Model_Tag_Relation extends Mage_Tag_Model_Tag_Relation
{
    /**
     * Processing object after save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        $ret = parent::_afterSave();
        
        Mage::dispatchEvent('searchanise_tag_relation_save_after', array('object' => $this));
        
        return $ret;
    }
}
