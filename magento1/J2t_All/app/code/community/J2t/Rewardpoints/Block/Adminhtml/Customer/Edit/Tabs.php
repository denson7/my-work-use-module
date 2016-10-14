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
 * @copyright  Copyright (c) 2011 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

//class J2t_Rewardpoints_Block_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs
class J2t_Rewardpoints_Block_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Abstract
{
    /*protected function _beforeToHtml()
    {
        $res =  Mage::getSingleton('admin/session')->isAllowed('admin/customer/rewardpoints');
        
        $customer = Mage::registry('current_customer');
        if ($customer->getId() && $res){
            $this->addTab('rewardpoints', array(
                'label'     => Mage::helper('rewardpoints')->__('Reward Points'),
                //'content'   => $this->getLayout()->createBlock('rewardpoints/adminhtml_customerstats')->toHtml()
                'content'   => $this->getLayout()->createBlock('rewardpoints/adminhtml_customerpoints')->initForm()->toHtml()
            ));

            $this->_updateActiveTab();
        }
        
        return parent::_beforeToHtml();
    }*/
    
    public function addRewardsTab()
    {
        $block = $this->getParentBlock();
        $res =  Mage::getSingleton('admin/session')->isAllowed('admin/customer/rewardpoints');
        $customer = Mage::registry('current_customer');
        if ($customer->getId() && $res){
            $block->addTab('rewardpoints', array(
                'label'     => Mage::helper('rewardpoints')->__('Reward Points'),
                'class'     => 'j2trewardpoints',
                'content'   => $this->getLayout()->createBlock('rewardpoints/adminhtml_customerpoints')->initForm()->toHtml(),
                'after'     => 'account',
            ));
        }

        return $this;
    }
}
