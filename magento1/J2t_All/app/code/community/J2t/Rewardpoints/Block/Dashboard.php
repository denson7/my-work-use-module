<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_Block_Dashboard extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('rewardpoints/dashboard_points.phtml');
    }


    public function getPointsCurrent(){
        $store_id = Mage::app()->getStore()->getId();
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            return $reward_flat_model->collectPointsCurrent($customerId, $store_id)+0;
        }        
        
        $reward_model = Mage::getModel('rewardpoints/stats');
        return $reward_model->getPointsCurrent($customerId, $store_id)+0;
    }

    public function getPointsReceived(){
        $store_id = Mage::app()->getStore()->getId();
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            return $reward_flat_model->collectPointsReceived($customerId, $store_id)+0;
        }
        
        $reward_model = Mage::getModel('rewardpoints/stats');
        
        //return $reward_model->getPointsReceived($customerId, $store_id);
        return $reward_model->getRealPointsReceivedNoExpiry($customerId, $store_id)+0;
    }

    public function getPointsSpent(){
        $store_id = Mage::app()->getStore()->getId();
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            return $reward_flat_model->collectPointsSpent($customerId, $store_id)+0;
        }
        
        $reward_model = Mage::getModel('rewardpoints/stats');
        return $reward_model->getPointsSpent($customerId, $store_id)+0;
    }

    public function getPointsWaitingValidation(){
        $store_id = Mage::app()->getStore()->getId();
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        /*if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            return $reward_flat_model->collectPointsWaitingValidation($customerId, $store_id)+0;
        }*/
        
        $reward_model = Mage::getModel('rewardpoints/stats');
        return $reward_model->getPointsWaitingValidation($customerId, $store_id)+0;
    }
    
    public function getPointsLost() {
        $store_id = Mage::app()->getStore()->getId();
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            return $reward_flat_model->collectPointsLost($customerId, $store_id)+0;
        }        
        
        $reward_model = Mage::getModel('rewardpoints/stats');
        return $reward_model->getRealPointsLost($customerId, $store_id)+0;
    }

}
