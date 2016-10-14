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
class J2t_Rewardpoints_Block_Points extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('referafriend/points.phtml');
        //->addAttributeToSort('rewardpoints_account_id', 'ASC')
        $points = Mage::getModel('rewardpoints/stats')->getCollection()
            ->addClientFilter(Mage::getSingleton('customer/session')->getCustomer()->getId());
        $points->getSelect()->order('rewardpoints_account_id DESC');
        $this->setPoints($points);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'rewardpoints.points')
            ->setCollection($this->getPoints());
        $this->setChild('pager', $pager);
        $this->getPoints()->load();

        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }


    public function getTypeOfPoint($_point, $referral_id = null)
    {
        $order_id = $_point->getOrderId();
        $referral_id = $_point->getRewardpointsReferralId();
        $quote_id = $_point->getQuoteId();
        $description = ($_point->getRewardpointsDescription()) ? ' - '.$_point->getRewardpointsDescription() : '';
        $description_dyn = ($_point->getRewardpointsDescription()) ? $this->__($_point->getRewardpointsDescription()) : $this->__('Event Points');
        
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', Mage::app()->getStore()->getId());

        $toHtml = '';
        if ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REFERRAL_REGISTRATION){
            //rewardpoints_linker
            $model = Mage::getModel('customer/customer')->load($_point->getRewardpointsLinker());
            if ($model->getName()){
                $toHtml .= '<div class="j2t-in-title">'.$this->__('Referral registration points (%s)', $model->getName()).'</div>';
            } else {
                $toHtml .= '<div class="j2t-in-title">'.$this->__('Referral registration points').'</div>';
            }
        } else if($referral_id){
            $referrer = Mage::getModel('rewardpoints/referral')->load($referral_id);
            $model = Mage::getModel('customer/customer')->load($_point->getRewardpointsLinker());
            //rewardpoints_referral_parent_id
            //rewardpoints_referral_child_id
            if ($referrer->getRewardpointsReferralParentId() && Mage::getSingleton('customer/session')->getCustomer() 
                    && is_object(Mage::getSingleton('customer/session')->getCustomer()) 
                    && $referrer->getRewardpointsReferralParentId() != Mage::getSingleton('customer/session')->getCustomer()->getId()
                    && ($customer_model = Mage::getModel('customer/customer')->load($referrer->getRewardpointsReferralParentId()))){
                $toHtml .= '<div class="j2t-in-title">'.$this->__('Referral points (%s)',$customer_model->getName()).'</div>';
            } else if ($referrer->getRewardpointsReferralParentId() && Mage::getSingleton('customer/session')->getCustomer() 
                    && is_object(Mage::getSingleton('customer/session')->getCustomer()) 
                    && $model->getRewardpointsReferralChildId() != Mage::getSingleton('customer/session')->getCustomer()->getId()
                    && ($customer_model = Mage::getModel('customer/customer')->load($referrer->getRewardpointsReferralChildId()))){
                $toHtml .= '<div class="j2t-in-title">'.$this->__('Referral points (%s)',$customer_model->getName()).'</div>';
            } else {
                $toHtml .= '<div class="j2t-in-title">'.$this->__('Referral points (%s)',$referrer->getRewardpointsReferralEmail()).'</div>';
            }
            
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
            //$toHtml .=  '<div class="j2t-in-txt">'.$this->__('Referral order state: %s',$this->__($order->getData($status_field))).'</div>';
            $toHtml .=  '<div class="j2t-in-txt">'.$this->__('Referral order (#%s) state: %s', $order_id, $this->__($order->getData($status_field))).'</div>';
        } elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW){
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Review points').'</div>';
        } elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_DYN) {
            $toHtml .= '<div class="j2t-in-title">'.$description_dyn.'</div>';
        } elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_NEWSLETTER){
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Newsletter points').'</div>';
        } elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_POLL){
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Poll participation points').'</div>';
        } elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_TAG){
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Tag points').'</div>';
        } /*elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_FIRST_ORDER){
			$toHtml .= '<div class="j2t-in-title">'.$this->__('First order points').'</div>';
		}*/
        elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_GP){
            if ($_point->getRewardpointsLinker()){
                $extra_relation = "";
                $product = Mage::getModel('catalog/product')->load($_point->getRewardpointsLinker());
                if ($product_name = Mage::helper('catalog/output')->productAttribute($product, $product->getName(), 'name')){
                    $extra_relation = "<div>".$this->__('Related to product: %s', $product_name)."</div>";
                }
            }
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Google Plus points').'</div>'.$extra_relation;
        } elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_FB){
            if ($_point->getRewardpointsLinker()){
                $extra_relation = "";
                $product = Mage::getModel('catalog/product')->load($_point->getRewardpointsLinker());
                if ($product_name = Mage::helper('catalog/output')->productAttribute($product, $product->getName(), 'name')){
                    $extra_relation = "<div>".$this->__('Related to product: %s', $product_name)."</div>";
                }
            }
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Facebook Like points').'</div>'.$extra_relation;
        } elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_PIN){
            if ($_point->getRewardpointsLinker()){
                $extra_relation = "";
                $product = Mage::getModel('catalog/product')->load($_point->getRewardpointsLinker());
                if ($product_name = Mage::helper('catalog/output')->productAttribute($product, $product->getName(), 'name')){
                    $extra_relation = "<div>".$this->__('Related to product: %s', $product_name)."</div>";
                }
            }
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Pinterest points').'</div>'.$extra_relation;
        } elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_TT){
            if ($_point->getRewardpointsLinker()){
                $extra_relation = "";
                $product = Mage::getModel('catalog/product')->load($_point->getRewardpointsLinker());
                
                if ($product_name = Mage::helper('catalog/output')->productAttribute($product, $product->getName(), 'name')){
                    $extra_relation = "<div>".$this->__('Related to product: %s', $product_name)."</div>";
                }
            }
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Twitter points').'</div>'.$extra_relation;
        } elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REQUIRED){
            $current_order = Mage::getModel('sales/order')->loadByAttribute('quote_id', $quote_id);
            $points_txt = $this->__('Points used on products for order %s', $current_order->getIncrementId());
            $toHtml .= '<div class="j2t-in-title">'.$points_txt.'</div>';
        } elseif ($order_id == J2t_Rewardpoints_Model_Stats::TYPE_POINTS_BIRTHDAY){
            if (isset($points_name[$order_id])){
                $toHtml .= '<div class="j2t-in-title">'.$points_name[$order_id].'</div>';
            } else {
                $toHtml .= '<div class="j2t-in-title">'.$this->__('Birthday points').'</div>';
            }
        }
        elseif ($order_id < 0){
            $points_name = array(J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW => $this->__('Review points'), J2t_Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN => $this->__('Store input %s', $description), J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REGISTRATION => $this->__('Registration points'));
            if (isset($points_name[$order_id])){
                $toHtml .= '<div class="j2t-in-title">'.$points_name[$order_id].'</div>';
            } else {
                $toHtml .= '<div class="j2t-in-title">'.$this->__('Gift').'</div>';
            }
            //$toHtml .= '<div class="j2t-in-title">'.$this->__('Gift').'</div>';
        } else {
			$desc = ($_point->getRewardpointsDescription()) ? '<div class="rewardpoints-description">'.$_point->getRewardpointsDescription().'</div>' : '';
			
			if ($_point->getRewardpointsFirstorder()){
				$toHtml .= '<div class="j2t-in-title">'.$this->__('First Order Points: %s', $order_id).'</div>';
			} else {
				$toHtml .= '<div class="j2t-in-title">'.$this->__('Order: %s', $order_id).'</div>';
			}
			
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
            $toHtml .= '<div class="j2t-in-txt">'.$this->__('Order state: %s',$this->__($order->getData($status_field))).'</div>';
			$toHtml .= $desc;
        }
        
        if (Mage::getConfig()->getModuleConfig('J2t_Rewardshare')->is('active', 'true')){
            if ($order_id == J2t_Rewardshare_Model_Stats::TYPE_POINTS_SHARE){
                $toHtml = '<div class="j2t-in-title">'.Mage::helper('j2trewardshare')->__('Gift (shared points)').'</div>';
            }
        } 

        return $toHtml;
    }
    
}