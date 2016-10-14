<?php
/**
 * J2T RewardsPoint2
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

class J2t_Rewardpoints_Block_Adminhtml_Renderer_Pointstype extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', Mage::app()->getStore()->getId());
        
        $order_id = $row->getData($this->getColumn()->getIndex());
        
        $model = Mage::getModel('rewardpoints/stats');
        
        $points_type = $model->getPointsDefaultTypeToArray();
        $points_type[J2t_Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN] = Mage::helper('rewardpoints')->__('Store input %s', ($row->getRewardpointsDescription()) ? ' - '.$row->getRewardpointsDescription() : '');
        
        if ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REFERRAL_REGISTRATION){
            $current_model = $model->load($row->getRewardpointsAccountId());
            $model = Mage::getModel('customer/customer')->load($current_model->getRewardpointsLinker());
            if ($model->getName()){
                return Mage::helper('rewardpoints')->__('Referral registration points (%s)', $model->getName());
            }
        }
        
        if (J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REQUIRED == $order_id){
            $current_model = $model->load($row->getRewardpointsAccountId());
            if ($current_model->getQuoteId()){
                $order_model = Mage::getModel('sales/order')->loadByAttribute('quote_id', $current_model->getQuoteId());
                if ($order_model->getIncrementId()){
                    $points_type[J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REQUIRED] = Mage::helper('rewardpoints')->__('Required point usage for order #%s (%s)', $order_model->getIncrementId(), Mage::helper('rewardpoints')->__($order_model->getData($status_field)));
                }
            }
        }
        
		$desc = ($row->getRewardpointsDescription()) ? '<div class="rewardpoints-description">'.$row->getRewardpointsDescription().'</div>' : '';
		
        if ( ($order_id > 0) || ($order_id != "" && !is_numeric($order_id)) ){
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
			
			if($row->getData('rewardpoints_firstorder')){
				return Mage::helper('rewardpoints')->__('First order (#%s) points (%s)', $order_id, Mage::helper('rewardpoints')->__($order->getData($status_field))).$desc;
			}
			
            return Mage::helper('rewardpoints')->__('Points related to order #%s (%s)', $order_id, Mage::helper('rewardpoints')->__($order->getData($status_field))).$desc;
        } elseif (isset($points_type[$order_id])) {
            return $points_type[$order_id].$desc;
        } else {
            return null;
        }
    }
}

