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
class J2t_Rewardpoints_Block_Referral extends Mage_Core_Block_Template
{
    public function __construct()
    {   
        parent::__construct();
        $this->setTemplate('rewardpoints/referral.phtml');
        $referred = Mage::getResourceModel('rewardpoints/referral_collection')
            ->addClientFilter(Mage::getSingleton('customer/session')->getCustomer()->getId());
        $referred->getSelect()->order('rewardpoints_referral_id DESC');
        $this->setReferred($referred);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'rewardpoints.referral')
            ->setCollection($this->getReferred());
        $this->setChild('pager', $pager);
        $this->getReferred()->load();

        return $this;
        //return parent::_prepareLayout();
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getReferringUrl()
    {
        $userId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        //return $this->getUrl('rewardpoints/index/goReferral')."referrer/$userId";
        $real_url = $this->getUrl('rewardpoints/index/goReferral', array("referrer" => $userId));
        return $this->getUrl('', array("referral-program" => str_replace('/','-',base64_encode($userId.'j2t'))));
    }

    public function isPermanentLink()
    {
        $return_val = Mage::getStoreConfig('rewardpoints/registration/referral_permanent', Mage::app()->getStore()->getId());
        return $return_val;
    }

    public function isAddthis()
    {
        if (Mage::getStoreConfig('rewardpoints/registration/referral_addthis', Mage::app()->getStore()->getId())
                && Mage::getStoreConfig('rewardpoints/registration/referral_addthis_account', Mage::app()->getStore()->getId()) != ""){
            return true;
        }
        return false;
    }

    public function getReferrerPoints()
    {
        return Mage::getStoreConfig('rewardpoints/registration/referral_points', Mage::app()->getStore()->getId());
    }

    public function getFriendPoints()
    {
        return Mage::getStoreConfig('rewardpoints/registration/referral_child_points', Mage::app()->getStore()->getId());
    }
    
    public function getReferrerRegistrationPoints()
    {
        return Mage::getStoreConfig('rewardpoints/registration/referrer_registration_points', Mage::app()->getStore()->getId());
    }

    public function getFriendRegistrationPoints()
    {
        return Mage::getStoreConfig('rewardpoints/registration/referred_registration_points', Mage::app()->getStore()->getId());
    }
    
    public function getReferrerCalculationType()
    {
        return Mage::getStoreConfig('rewardpoints/registration/referral_points_method', Mage::app()->getStore()->getId());
    }
    
    public function getFriendCalculationType()
    {
        return Mage::getStoreConfig('rewardpoints/registration/referral_child_points_method', Mage::app()->getStore()->getId());
    }
    
    public function getMinOrderAmount()
    {
        return Mage::getStoreConfig('rewardpoints/registration/referral_min_order', Mage::app()->getStore()->getId());
    }
}