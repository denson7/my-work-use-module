<?php

class J2t_Rewardpoints_Helper_Dashboard_Stats extends Mage_Adminhtml_Helper_Dashboard_Abstract
{

    protected function _initCollection()
    {
        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');

        $this->_collection = Mage::getResourceSingleton('rewardpoints/stats_collection')
            ->prepareSummary($this->getParam('period'), 0, 0, $isFilter);

        if (Mage::getStoreConfig('rewardpoints/default/store_scope')){
            if ($this->getParam('store')) {
            	$findset = array('finset' => array($this->getParam('store')));
                $this->_collection->addFieldToFilter('store_id', $findset);
	    } else if ($this->getParam('website')){
                $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            	foreach ($storeIds as $storeId){
                    $findset = array('finset' => array($storeId));
                    $this->_collection->addFieldToFilter('store_id', $findset);
                }
	    } else if ($this->getParam('group')){
                $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
           	foreach ($storeIds as $storeId){
                    $findset = array('finset' => array($storeId));
                    $this->_collection->addFieldToFilter('store_id', $findset);
                } 
	    } else {
	    	$findset = array('finset' => array(Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId()));
                $this->_collection->addFieldToFilter('store_id', $findset);
	    }
        }

        $this->_collection->load();
    }

}
