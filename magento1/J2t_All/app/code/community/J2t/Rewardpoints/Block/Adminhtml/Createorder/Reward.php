<?php

class J2t_Rewardpoints_Block_Adminhtml_Createorder_Reward extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_reward_form');
    }

    public function getPointsUsed()
    {
        //return $this->getQuote()->getCouponCode();
        //return (int)$this->getQuote()->getRewardpointsQuantity();
        return (int)Mage::helper('rewardpoints/event')->getCreditPoints($this->getQuote());
    }
    
    public function getPointsOnOrder() {
        //Mage::getModel('sales/quote')
        $to_validate = $this->getQuote();
        $to_validate->setQuote($this->getQuote());
        return Mage::helper('rewardpoints/data')->getPointsOnOrder($to_validate, $this->getQuote(), null, false, $this->getQuote()->getStoreId());
    }
    
    public function getClientPoints()
    {
        $customer_points = 0;
        
        $quote = $this->getQuote();
        $store_id = $quote->getStoreId();
        if ($quote->getCustomerId()){
            $customerId = $quote->getCustomerId();
        } else {
            return 0;
        }
        
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            $reward_model = Mage::getModel('rewardpoints/flatstats');
            $customer_points = $reward_model->collectPointsCurrent($customerId, $store_id);
        } else {
            $reward_model = Mage::getModel('rewardpoints/stats');
            $customer_points = $reward_model->getPointsCurrent($customerId, $store_id);
        }
        return $customer_points;
    }
}
