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

class Simtech_Searchanise_Model_Mysql4_Queue_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
    }
    
    public function delete()
    {
        $delete_collection = $this->toArray();
        
        if (!empty($delete_collection['items'])) {
            foreach ($delete_collection['items'] as $item) {
                Mage::getModel('searchanise/queue')->load($item['queue_id'])->delete();
            }
        }
    }
}