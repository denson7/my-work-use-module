<?php

class Magestore_Shopbybrand_Model_Mysql4_Brandproducts extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('shopbybrand/brandproducts', 'bp_id');
    }
}