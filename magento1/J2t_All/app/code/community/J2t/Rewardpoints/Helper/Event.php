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
class J2t_Rewardpoints_Helper_Event extends Mage_Core_Helper_Abstract
{
    
    public function setCreditPoints($points_value){
        Mage::getSingleton('rewardpoints/session')->setCreditPoints($points_value);
    }

    public function getCreditPoints($quote = null){
        //return ceil(Mage::getSingleton('rewardpoints/session')->getCreditPoints());
        if ($quote == null){
             $quote = Mage::helper('checkout/cart')->getCart()->getQuote();
        }
        
        return ceil($quote->getRewardpointsQuantity());
    }
    
    public function removeCreditPoints($quote = null, $no_save = false){
        if ($quote == null){
             $quote = Mage::helper('checkout/cart')->getCart()->getQuote();
        }
        $quote->setRewardpointsQuantity(NULL)
                ->setRewardpointsDescription(NULL)
                ->setBaseRewardpoints(NULL)
                ->setRewardpoints(NULL);
        if (!$no_save){
            $quote->save();
        }        
    }
}
