<?php

class Company_Web_Model_Web extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('web/web');
    }
}