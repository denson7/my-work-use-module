<?php

class J2t_Rewardpoints_Block_Details_Rewardinvoice extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getInvoice()
    {
        $object = Mage::registry('current_invoice');
        if (!is_object($object) || !$object->getId()){
            $orderId = (int) Mage::app()->getRequest()->getParam('order_id');
            $object = Mage::getModel('sales/order')->load($orderId);
        }
        return $object;
    }

    public function getPointsUsed()
    {
        return (int)$this->getInvoice()->getRewardpointsQuantity();
    }
    
    public function getPointsOnOrder() {
        return (int)$this->getInvoice()->getRewardpointsGathered();
    }
    
    public function canShow() {
        return Mage::getStoreConfig('rewardpoints/order_invoice/show_on_invoice_client');
    }
    
    public function isEmail()
    {
        if (Mage::app()->getRequest()->getActionName() == 'email') {
            return true;
        }
        return false;
    }
}
