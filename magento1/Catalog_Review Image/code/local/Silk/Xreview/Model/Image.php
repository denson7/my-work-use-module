<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2016/7/22
 * Time: 14:48
 */
class Silk_Xreview_Model_Image extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init("xreview/image");
    }
}