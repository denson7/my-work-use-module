<?php

class Cdr_OrderComment_Block_Sales_Order_View_Ordercomment extends Mage_Core_Block_Template
{
    public function isActive()
    {
        return Mage::getStoreConfigFlag('ordercomment/settings/active');
    }
    
    /**
     * Retrieve current orders order comment
     *
     * @return string
     */
    public function getOrderComment()
    {
        return nl2br(Mage::registry('current_order')->getCdrOrderComment());
    }
}