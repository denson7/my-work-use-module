<?php

class Sandipan_Productfileupload_Model_Mysql4_Productfileupload_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('productfileupload/productfileupload');
    }
}