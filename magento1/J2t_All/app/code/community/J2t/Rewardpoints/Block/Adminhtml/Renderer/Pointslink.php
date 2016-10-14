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

class J2t_Rewardpoints_Block_Adminhtml_Renderer_Pointslink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', Mage::app()->getStore()->getId());
        //rewardpoints_linker
        //$order_id = $row->getData($this->getColumn()->getIndex());
        $order_id = $row->getOrderId();
        $points_type = array(J2t_Rewardpoints_Model_Stats::TYPE_POINTS_GP,
            J2t_Rewardpoints_Model_Stats::TYPE_POINTS_FB,
            J2t_Rewardpoints_Model_Stats::TYPE_POINTS_PIN,
            J2t_Rewardpoints_Model_Stats::TYPE_POINTS_TT);
        
        if (in_array($order_id, $points_type) && $row->getRewardpointsLinker()){
            $product = Mage::getModel('catalog/product')->load($row->getRewardpointsLinker());
            //if ($product_name = Mage::helper('catalog/output')->productAttribute($product, $product->getName(), 'name')){
            if ($product_name = $product->getName()){
                return Mage::helper('rewardpoints')->__('Related to product: %s', $product_name);
            }
            //return Mage::helper('rewardpoints')->__('Related to product: %s', 'tata');
        }
        return Mage::helper('rewardpoints')->__('- not relevant -');
        
    }
}

