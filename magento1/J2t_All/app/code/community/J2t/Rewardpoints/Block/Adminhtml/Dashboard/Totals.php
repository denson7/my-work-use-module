<?php

class J2t_Rewardpoints_Block_Adminhtml_Dashboard_Totals extends Mage_Adminhtml_Block_Dashboard_Bar
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('rewardpoints/dashboard/totalbar.phtml');
    }

    protected function _prepareLayout()
    {
        //$isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');
        $period = $this->getRequest()->getParam('period', '24h');

        /* @var $collection Mage_Reports_Model_Mysql4_Order_Collection */
        /*$collection = Mage::getResourceModel('reports/order_collection')
            ->addCreateAtPeriodFilter($period)
            ->calculateTotals($isFilter);
        */
        
        $collection = Mage::getResourceModel('rewardpoints/stats_collection')
                ->addCreateAtPeriodFilter($period)
                ->calculateTotals(false);
        
        $collection_other_than_orders = Mage::getResourceModel('rewardpoints/stats_collection')
                ->addCreateAtPeriodFilter($period)
                ->calculateTotals(true); 
        
        if (Mage::getStoreConfig('rewardpoints/default/store_scope')){
            if ($this->getParam('store')) {
            	$findset = array('finset' => array($this->getParam('store')));
                $collection->addFieldToFilter('store_id', $findset);
                $collection_other_than_orders->addFieldToFilter('store_id', $findset);
	    } else if ($this->getParam('website')){
                $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
                foreach ($storeIds as $storeId){
                    $findset = array('finset' => array($storeId));
                    $collection->addFieldToFilter('store_id', $findset);
                    $collection_other_than_orders->addFieldToFilter('store_id', $findset);
		}
            } else if ($this->getParam('group')){
                $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
                foreach ($storeIds as $storeId){
                    $findset = array('finset' => array($storeId));
                    $collection->addFieldToFilter('store_id', $findset);
                    $collection_other_than_orders->addFieldToFilter('store_id', $findset);
		}
            } else {
            	$findset = array('finset' => array(Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId()));
                $collection->addFieldToFilter('store_id', $findset);
                $collection_other_than_orders->addFieldToFilter('store_id', $findset);
	    }
        }
        
        $collection->load();

        $totals = $collection->getFirstItem();

        $this->addTotal($this->__('Accumulated Points'), $totals->getAllPointsGathered() + 0, true);
        $this->addTotal($this->__('Spent Points'), $totals->getAllPointsSpent() + 0, true);
        
        $array_types = Mage::getModel("rewardpoints/stats")->getPointsDefaultTypeToArray();
        $loaded_collection = $collection_other_than_orders->load();
        
        $shown = array();
        if ($loaded_collection->getSize()){
            foreach ($loaded_collection as $point_type){
                if (isset($array_types[$point_type->getOrderId()])){
                    $this->addTotal($array_types[$point_type->getOrderId()], $point_type->getAllPointsGathered() + 0, true);
                    $shown[] = $point_type->getOrderId();
                }
            }
        }
        
        foreach ($array_types as $key => $value){
            if (!in_array($key, $shown)){
                $this->addTotal($value, 0, true);
            }
        }
        
        ////Mage::getModel("rewardpoints/stats")->getOnlyPointsTypesArray()
        
        //$this->addTotal($this->__('Shipping'), $totals->getShipping());
        //$this->addTotal($this->__('Quantity'), $totals->getQuantity()*1, true);
    }
}
