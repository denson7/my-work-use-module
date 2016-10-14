<?php

class J2t_Rewardpoints_Block_Global extends Mage_Core_Block_Template {
    public function canShowPoints(){
        return Mage::getStoreConfig('rewardpoints/default/show_customer_summary', Mage::app()->getStore()->getId());
    }
}