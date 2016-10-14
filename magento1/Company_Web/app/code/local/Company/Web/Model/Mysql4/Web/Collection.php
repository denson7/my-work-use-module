<?php

class Company_Web_Model_Mysql4_Web_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('web/web');
    }
}