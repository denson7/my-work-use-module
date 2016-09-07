<?php

class Magestore_Shopbybrand_Model_Mysql4_Brandproducts_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('shopbybrand/brandproducts', 'bp_id');
    }
}