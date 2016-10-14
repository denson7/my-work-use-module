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
 * This file is kept to assure backward compatibility!
 * 
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_Model_Rule extends Mage_SalesRule_Model_Rule
{
	public function validate(Varien_Object $object) {
            if (substr($this->getCouponCode(),0,6) != 'points') {
                    return parent::validate($object);
            }
            $customerId = Mage::getModel('customer/session')
                                            ->getCustomerId();

            $reward_model = Mage::getModel('rewardpoints/stats');
            $current = $reward_model->getPointsCurrent($customerId, Mage::app()->getStore()->getId());


            if ($current < $this->getPointsAmt()) {
                    Mage::getSingleton('checkout/session')->addError('Not enough points available.');
                    Mage::helper('checkout/cart')->getCart()->getQuote()
                        ->setRewardpointsQuantity(NULL)
                        ->setRewardpointsDescription(NULL)
                        ->setBaseRewardpoints(NULL)
                        ->setRewardpoints(NULL)
                        ->save();
                    return false;
            }

            $step_apply = Mage::getStoreConfig('rewardpoints/default/step_apply', Mage::app()->getStore()->getId());
            $step = Mage::getStoreConfig('rewardpoints/default/step_value', Mage::app()->getStore()->getId());
            if ($step > $this->getPointsAmt() && $step_apply){
                    Mage::getSingleton('checkout/session')->addError('The minimum required points is not reached.');
                    return false;
            }


            if ($step_apply){
                    if (($this->getPointsAmt() % $step) != 0){
                            Mage::getSingleton('checkout/session')->addError('Amount of points wrongly used.');
                            return false;
                    }
            }


            return true;
	}
	
	
	public function getDiscountAmount() {
		if (substr($this->getCouponCode(),0,6) == 'points') {
			$step = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
			return ($this->getPointsAmt() / $step);
		}
		$test = new Mage_SalesRule_Model_Rule();
		if (method_exists($test,'getDiscountAmount'))
			return parent::getDiscountAmount();
		if ($this->discount_amount){
			return $this->discount_amount;
		}
	}

        public function getPointsOnOrder(){
            $cartHelper = Mage::helper('checkout/cart');
            $items = $cartHelper->getCart()->getItems();
            $rewardPoints = 0;
            $cart_amount = 0;

            $attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', Mage::app()->getStore()->getId());
            foreach ($items as $_item){
                $_product = Mage::getModel('catalog/product')->load($_item->getProductId());
                $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($_product);
                if ($catalog_points === false){
                    continue;
                } else if(!$attribute_restriction) {
                    $rewardPoints += (int)$catalog_points * $_item->getQty();
                }
                $product_points = $_product->getData('reward_points');
                
                $group_points = $this->getPointGroup($product, $cartHelper->getCart()->getCustomerGroupId());
                $product_points = ($group_points) ? $group_points : $product_points;

                if ($product_points > 0){
                    if ($_item->getQty() > 0){
                        $rewardPoints += (int)$product_points * $_item->getQty();

                    }
                } else if(!$attribute_restriction) {
                    $price = $_item->getRowTotal() + $_item->getTaxAmount() - $_item->getDiscountAmount();
                    $rewardPoints += (int)Mage::getStoreConfig('rewardpoints/default/money_points', Mage::app()->getStore()->getId()) * $price;
                }
                $cart_amount += $_item->getRowTotal() + $_item->getTaxAmount() - $_item->getDiscountAmount();
            }
            
            //get points cart rule
            $points_rules = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered();
            if ($points_rules === false){
                return 0;
            }
            $rewardPoints += (int)$points_rules;

            $rewardPoints = Mage::helper('rewardpoints/data')->processMathValue($rewardPoints);
            
            if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())){
                if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $rewardPoints){
                    $rewardPoints = Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
                }
            }


            return $rewardPoints;
        }
	
}
