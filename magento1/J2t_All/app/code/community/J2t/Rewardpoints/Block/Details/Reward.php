<?php

class J2t_Rewardpoints_Block_Details_Reward extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getPointsUsed()
    {
        return (int)$this->getOrder()->getRewardpointsQuantity();
    }
    
    public function getPointsOnOrder() {
        return (int)$this->getOrder()->getRewardpointsGathered();
    }
    
    public function canShow() {
        return Mage::getStoreConfig('rewardpoints/order_invoice/show_on_order_client');
    }
    
    public function isEmail() {
        return false;
    }

}
