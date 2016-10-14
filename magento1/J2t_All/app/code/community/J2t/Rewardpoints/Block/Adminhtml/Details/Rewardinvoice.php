<?php

class J2t_Rewardpoints_Block_Adminhtml_Details_Rewardinvoice extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function isEmail()
    {
        if (Mage::app()->getRequest()->getActionName() == 'email') {
            return true;
        }
        return false;
    }
    
    public function getInvoice()
    {
        if ($invoiceId = Mage::app()->getRequest()->getParam('invoice_id')) {
            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
                return $invoice;
            }
        }
        
        return Mage::registry('current_invoice');
    }

    public function getPointsUsed()
    {
        return (int)$this->getInvoice()->getRewardpointsQuantity();
    }
    
    public function getPointsOnOrder() {
        return (int)$this->getInvoice()->getRewardpointsGathered();
    }
    
    public function canShow() {
	if (!is_object($this->getInvoice())) return false;
        return Mage::getStoreConfig('rewardpoints/order_invoice/show_on_invoice_admin');
    }
}
