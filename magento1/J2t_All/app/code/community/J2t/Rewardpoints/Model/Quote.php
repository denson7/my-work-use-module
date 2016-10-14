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
 * @copyright  Copyright (c) 2015 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/*if (Mage::getConfig()->getModuleConfig('Amasty_Coupons')->is('active', 'true')){
    //class J2t_Rewardpoints_Model_Quote_Abstract extends Amasty_Coupons_Model_Sales_Quote
    class J2t_Rewardpoints_Model_Quote_Abstract extends Mage_Sales_Model_Quote
    {
        
    }
} else {*/
    
    class J2t_Rewardpoints_Model_Quote_Abstract extends Mage_Sales_Model_Quote
    {

    }
    
//}


class J2t_Rewardpoints_Model_Quote extends J2t_Rewardpoints_Model_Quote_Abstract
{
    protected function _validateCouponCode()
    {
        $code = $this->_getData('coupon_code');
        if ($code) {
            $addressHasCoupon = false;
            $addresses = $this->getAllAddresses();
            if (count($addresses)>0) {
                foreach ($addresses as $address) {
                    //if ($address->hasCouponCode()) {
                    if (preg_match("/".$code."/i", $address->getCouponCode())) {
                        $addressHasCoupon = true;
                    }
                }
                if (!$addressHasCoupon) {
                    $this->setCouponCode('');
                }
            }
        }
        return $this;
    }
    
    
    public function isAllowedGuestCheckout()
    {
        if (Mage::getStoreConfig('rewardpoints/registration/referral_guestallow', $this->getStoreId()) && Mage::getSingleton('rewardpoints/session')->getReferralUser()){
            return false;
        }
        return Mage::helper('checkout')->isAllowedGuestCheckout($this, $this->getStoreId());
    }
}
