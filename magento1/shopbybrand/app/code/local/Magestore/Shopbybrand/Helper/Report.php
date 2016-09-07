<?php

class Magestore_Shopbybrand_Helper_Report extends Mage_Adminhtml_Helper_Dashboard_Abstract {

    protected function _initCollection() {
        $this->_collection = Mage::getResourceModel('shopbybrand/brand_collection');
        $customStart = Mage::helper('shopbybrand')->getZendDate(Mage::app()->getRequest()->getParam('from'));
        $customEnd = Mage::helper('shopbybrand')->getZendDate(Mage::app()->getRequest()->getParam('to'));
        if ($customStart) {
            $customStart->setHour(0);
            $customStart->setMinute(0);
            $customStart->setSecond(0);
        }
        if ($customEnd) {
            $customEnd->setHour(23);
            $customEnd->setMinute(59);
            $customEnd->setSecond(59);
        }
        $this->_collection->prepareReportBrandSales($this->getParam('period'), $customStart, $customEnd);
        if ($this->getParam('store'))
            $this->_collection->addFieldToFilter('sfoi.store_id', $this->getParam('store'));

        $this->_collection->load();
    }

}