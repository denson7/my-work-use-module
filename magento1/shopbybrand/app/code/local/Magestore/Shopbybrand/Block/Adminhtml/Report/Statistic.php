<?php

class Magestore_Shopbybrand_Block_Adminhtml_Report_Statistic extends Mage_Core_Block_Template {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('shopbybrand/report/grid.phtml');
    }

    public function _prepareLayout() {
        parent::_prepareLayout();
        $gridcontent = $this->getLayout()->createBlock('shopbybrand/adminhtml_report_statistic_grid');
        $store_switcher = $this->getLayout()->createBlock('adminhtml/store_switcher')->setUseConfirm(0)->setTemplate('store/switcher.phtml');
        $this->setChild('store_switcher', $store_switcher);
        $this->setChild('grid_content', $gridcontent);
        return $this;
    }

    public function getTiltle() {
        return Mage::helper('shopbybrand')->__('Sales Report by Brand');
    }
    
    public function getDateFormat() {
        return $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);//FORMAT_TYPE_MEDIUM
    }

    public function getLocale() {
        if (!$this->_locale) {
            $this->_locale = Mage::app()->getLocale();
        }
        return $this->_locale;
    }

    public function getFilter($nameFilter) {
        $filter = Mage::app()->getRequest()->getParam('filter');
        $date = Mage::helper('adminhtml')->prepareFilterString($filter);
        return isset($date[$nameFilter]) ? $date[$nameFilter] : null;
    }
}
