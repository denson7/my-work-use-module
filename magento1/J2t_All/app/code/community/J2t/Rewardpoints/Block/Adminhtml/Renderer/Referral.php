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

class J2t_Rewardpoints_Block_Adminhtml_Renderer_Referral extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $customer_id = $row->getData($this->getColumn()->getIndex());
        if($customer_id){
            $customer = Mage::getModel('rewardpoints/referral')->load($customer_id);
            //$customer = Mage::getModel('customer/customer')->load($customer_id);
            if ($customer->getRewardpointsReferralChildId()){
                return "#{$customer->getRewardpointsReferralChildId()} ". $customer->getRewardpointsReferralName() . " ({$customer->getRewardpointsReferralEmail()})";
            }
        }
        return '-';
    }
}

