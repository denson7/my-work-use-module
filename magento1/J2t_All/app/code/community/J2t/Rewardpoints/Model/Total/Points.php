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
 * @copyright  Copyright (c) 2012 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

//Mage_Core_Model_Config::getModuleConfig
if (Mage::getConfig()->getModuleConfig('J2t_Multicoupon')->is('active', 'true')){
    // >>in case of tax calculation issue redefine extends Mage_Sales_Model_Quote_Address_Total_Discount in app/code/community/J2t/Multicoupon/Model/Quote/Discount.php
    class J2t_Rewardpoints_Model_Total_Points_Abstract extends J2t_Multicoupon_Model_Quote_Discount //magento 1.4.x and greater
    {
        
    }
} else {
    // >>in case of tax calculation issue, uncomment the appropriate line
    //class J2t_Rewardpoints_Model_Total_Points_Abstract extends Mage_Sales_Model_Quote_Address_Total_Discount //magento 1.3.x
    if (version_compare(Mage::getVersion(), '1.4.0', '<')){
        class J2t_Rewardpoints_Model_Total_Points_Abstract extends Mage_Sales_Model_Quote_Address_Total_Abstract
        {

        }
    } else {
        class J2t_Rewardpoints_Model_Total_Points_Abstract extends Mage_SalesRule_Model_Quote_Discount //magento 1.4.x and greater
        {

        }
    }
}

// >>in case of tax calculation issue, uncomment the appropriate line
//class J2t_Rewardpoints_Model_Total_Points extends Mage_Sales_Model_Quote_Address_Total_Discount //magento 1.3.x
//DEFAULT class declaration
//class J2t_Rewardpoints_Model_Total_Points extends Mage_SalesRule_Model_Quote_Discount //magento 1.4.x and greater
//When using J2T Multicoupon:
//class J2t_Rewardpoints_Model_Total_Points extends J2t_Multicoupon_Model_Quote_Discount
// ... and comment the following line
//class J2t_Rewardpoints_Model_Total_Points extends Mage_Sales_Model_Quote_Address_Total_Abstract
class J2t_Rewardpoints_Model_Total_Points extends J2t_Rewardpoints_Model_Total_Points_Abstract
{
    
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if ($address->getQuote()->getVoidRewardsTotal()){
            return parent::collect($address);
        }
	parent::collect($address);
        
        if (is_object(Mage::app()) && is_object(Mage::app()->getStore()) && Mage::app()->getStore()->getCurrentCurrencyCode() && 
            $address->getQuote()->getQuoteCurrencyCode() != Mage::app()->getStore()->getCurrentCurrencyCode()) {
            return $this;
        }
        
        if (version_compare(Mage::getVersion(), '1.4.0', '>=') && method_exists($this, '_getAddressItems')){
            $items = $this->_getAddressItems($address);
        } else {
            $items = $address->getAllItems();
        }
        if (!count($items)) {
            return $this;
        }
        
        $totalPPrice = 0;
        $totalPBasePrice = 0;
        
        $this->checkAutoUse($address->getQuote());
        $creditPoints = $this->getCreditPoints($address->getQuote());
        
        
        $subtotalWithDiscount = 0;
        $baseSubtotalWithDiscount = 0;
        
        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;
        
        
        // verify max percent usage
        $creditPoints = $this->percentPointMax($address->getQuote(), $creditPoints);
        
        if (Mage::getSingleton('rewardpoints/session')->getReferralUser() == $address->getQuote()->getCustomerId()){
            Mage::getSingleton('rewardpoints/session')->setReferralUser(null);
            $address->getQuote()->setRewardpointsReferrer(null);
        }
		
        if ($userId = Mage::getSingleton('rewardpoints/session')->getReferralUser()){
            $address->getQuote()->setRewardpointsReferrer($userId);
        }
        
        
        //verify if dont process rule
        if ($creditPoints > 0 && Mage::getModel('rewardpoints/pointrules')->getRulePointsGathered($address->getQuote(), $address->getQuote()->getCustomerGroupId(), true) === false){
            $creditPoints = 0;
            Mage::getSingleton('checkout/session')->addNotice(Mage::helper('rewardpoints')->__('Your current cart configuration does not allow point usage.'));
        }
        
        
        if ($creditPoints > 0 && $this->checkMinUse($address->getQuote())){
            //coupon code restriction 
	    if (Mage::getStoreConfig('rewardpoints/default/coupon_codes', $address->getQuote()->getStoreId()) && $address->getCouponCode()){
	   	$address->setCouponCode(''); 
	    } 
	    if ($address->getCustomerId()){
                $pointsAmount = Mage::helper('rewardpoints/data')->convertPointsToMoney($creditPoints, $address->getCustomerId(), $address->getQuote(), true, false);
            } elseif ($address->getQuote()->getCustomerId()) {
                $pointsAmount = Mage::helper('rewardpoints/data')->convertPointsToMoney($creditPoints, $address->getQuote()->getCustomerId(), $address->getQuote(), true, false);
            } else {
                $pointsAmount = 0;//continue;
            }
            
            $no_discount = array();
            foreach ($items as $item) {
                /*if ($item->getProduct()->isVirtual()) {
                    continue;
                }*/
                //echo $item->getProduct()->getData('reward_no_discount');
                //die;
                
                //get price to be removed from discount
                $remove_price = 0;
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        //$i = $i + $child->getQty();
                        if ($product = $child->getProduct()) {
                            if (!$product->getData('reward_no_discount')) {
                                //TAX AFTER TEST
                                if (Mage::helper('rewardpoints')->canProcessTax($address->getQuote()->getStoreId())){
				//if (Mage::getStoreConfig('rewardpoints/default/process_tax', $address->getQuote()->getStoreId()) == 1 && Mage::getStoreConfig('tax/calculation/apply_after_discount', $address->getQuote()->getStoreId()) == 0){
                                    $tax = ($child->getTaxBeforeDiscount() ? $child->getTaxBeforeDiscount() : $child->getTaxAmount());
                                    $row_base_total = $child->getBaseRowTotal() + $tax;
                                } else {
                                    $row_base_total = $child->getBaseRowTotal();
                                }
                                $remove_price += $row_base_total;
                            } else {
                                $no_discount[$product->getId()] = '"'.$product->getName().'"';
                            }
                        }
                    }
                }
                if ($product = $item->getProduct()) {
                    if ($product->getData('reward_no_discount')) {
                        $no_discount[$product->getId()] = '"'.$product->getName().'"';
                        continue;
                    }
                }
                if (Mage::helper('rewardpoints')->canProcessTax($address->getQuote()->getStoreId())){
                //if (Mage::getStoreConfig('rewardpoints/default/process_tax', $address->getQuote()->getStoreId()) == 1 && Mage::getStoreConfig('tax/calculation/apply_after_discount', $address->getQuote()->getStoreId()) == 0){
                    $tax = ($item->getTaxBeforeDiscount() ? $item->getTaxBeforeDiscount() : $item->getTaxAmount());
                    $row_base_total = $item->getBaseRowTotal() + $tax;
                } else {
                    $row_base_total = $item->getBaseRowTotal();
                }
                
                $row_base_total -= $remove_price;
                $baseDiscountAmount = min($row_base_total - $item->getBaseDiscountAmount(), $pointsAmount);
                
                if ($max_usage = $product->getData('reward_points_limit_usage')) {
                    //$max_money_limit = Mage::helper('rewardpoints/data')->convertPointsToMoneyEquivalence($max_usage, $address->getQuote()->getStoreId());
                    $baseDiscountAmount = min($baseDiscountAmount, $max_usage);
                }
                
                
                if ($baseDiscountAmount > 0){
                    //$rewardpoints_used = Mage::helper('rewardpoints/data')->convertMoneyToPoints(abs($baseDiscountAmount), false, $address->getQuote(), true);
                    $rewardpoints_used = Mage::helper('rewardpoints/data')->convertMoneyToPoints(abs($baseDiscountAmount), false, $address->getQuote(), true, false);
                    $item->setRewardpointsUsed($rewardpoints_used);
                    
                    $points = -$baseDiscountAmount;
                    $totalPBasePrice += $points;
                    $discountAmount = $address->getQuote()->getStore()->convertPrice($points, false);
                    $totalPPrice += $discountAmount;
                    
                    if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
                        $item->setDiscountAmount(abs($discountAmount)+$item->getDiscountAmount());
                        $item->setBaseDiscountAmount(abs($baseDiscountAmount)+$item->getBaseDiscountAmount());
                    } else {
                        $item->setDiscountAmount(abs($discountAmount)+$item->getDiscountAmount());
                        $item->setBaseDiscountAmount(abs($baseDiscountAmount)+$item->getBaseDiscountAmount());
                        
                        
                        $item->setRowTotalWithDiscount($item->getRowTotal()-$item->getDiscountAmount());
                        $item->setBaseRowTotalWithDiscount($item->getBaseRowTotal()-$item->getBaseDiscountAmount());

                        $subtotalWithDiscount += $item->getRowTotalWithDiscount();
                        $baseSubtotalWithDiscount += $item->getBaseRowTotalWithDiscount();
                    }
                    $totalDiscountAmount += abs($discountAmount);
                    $baseTotalDiscountAmount += abs($baseDiscountAmount);
                    
                    
                } else {
                    $item->setRewardpointsUsed(0);
                }
                
                $pointsAmount -= $baseDiscountAmount;
            }
            
            //J2T process shipping address
            $shipping_process = Mage::getStoreConfig('rewardpoints/default/process_shipping', $address->getQuote()->getStoreId());
            if (version_compare(Mage::getVersion(), '1.4.0', '>=') && $shipping_process){
                $shipping_tax = 0;
                if (Mage::helper('rewardpoints')->canProcessTax($address->getQuote()->getStoreId())){
                //if (Mage::getStoreConfig('rewardpoints/default/process_tax', $address->getQuote()->getStoreId()) == 1 && Mage::getStoreConfig('tax/calculation/apply_after_discount', $address->getQuote()->getStoreId()) == 0){
                    $shipping_tax = $address->getBaseShippingTaxAmount();
                }
                
                $baseShippingDiscountAmount = min(( ($address->getBaseShippingAmount() + $shipping_tax - $address->getBaseShippingDiscountAmount())), $pointsAmount);
                $points = -$baseShippingDiscountAmount;
                $totalPBasePrice += $points;
                $totalPPrice += $address->getQuote()->getStore()->convertPrice($points, false);
                $pointsAmount -= $baseShippingDiscountAmount;
                
                $address->setShippingDiscountAmount($address->getQuote()->getStore()->convertPrice($baseShippingDiscountAmount, false) + $address->getShippingDiscountAmount());
                $address->setBaseShippingDiscountAmount($baseShippingDiscountAmount + $address->getBaseShippingDiscountAmount());
                
            }
            //J2T end process shipping address
           
            if (sizeof($no_discount) && Mage::app()->getRequest()->getRouteName() == 'checkout'){
                Mage::getSingleton('checkout/session')->addNotice(Mage::helper('rewardpoints')->__('Points are not usable on the following product(s): %s.', implode(", ", $no_discount)));
            }
            if ($pts = Mage::helper('rewardpoints/event')->getCreditPoints($address->getQuote())){
                $address->getQuote()
                        ->setRewardpointsQuantity($pts)
                        ->setBaseRewardpoints(-$totalPBasePrice)
                        ->setRewardpoints(-$totalPPrice);
                        //->save();
            }
            
            
            if (abs($totalPBasePrice) > 0){
                //$points_used = Mage::helper('rewardpoints/data')->convertMoneyToPoints(abs($totalPBasePrice), false, $address->getQuote(), true);
                $points_used = Mage::helper('rewardpoints/data')->convertMoneyToPoints(abs($totalPBasePrice), false, $address->getQuote(), true, false);
                $points_session = Mage::helper('rewardpoints/event')->getCreditPoints($address->getQuote());
                if ($points_used < $points_session){
                    Mage::helper('rewardpoints/event')->setCreditPoints($points_used);
                    
                    $address->getQuote()
                            ->setRewardpointsQuantity($points_used)
                            ->setBaseRewardpoints(-$totalPBasePrice)
                            ->setRewardpoints(-$totalPPrice);
                                //->save();
                    
                }
            } else {
                //remove all reward points within this cart
                if ($referrer_id = Mage::getSingleton('rewardpoints/session')->getReferralUser()){
                    Mage::getSingleton('rewardpoints/session')->unsetAll();
                    Mage::getSingleton('rewardpoints/session')->setReferralUser($referrer_id);
                } else {
                    Mage::getSingleton('rewardpoints/session')->unsetAll();
                }
                Mage::helper('rewardpoints/event')->removeCreditPoints($address->getQuote(), true);
            }

            
            if ($pts = Mage::helper('rewardpoints/event')->getCreditPoints($address->getQuote())){
                $title = Mage::helper('rewardpoints')->__('%s points used', $pts);
                //echo $pts;
                //die;
                
                $address->getQuote()->setRewardpointsDescription($title);
                //$title_base = $title;
                
                $auto_use = Mage::getStoreConfig('rewardpoints/default/auto_use', $address->getQuote()->getStoreId());
                $remove_link = Mage::getStoreConfig('rewardpoints/default/remove_link', $address->getQuote()->getStoreId());
                if (!$auto_use && $remove_link && !Mage::getSingleton('admin/session')->isLoggedIn()){
                    //$title .= ' <a href="javascript:$(\'discountFormPoints2\').submit();" title="'.Mage::helper('rewardpoints')->__('Remove Points').'"><img src="'.Mage::getDesign()->getSkinUrl('images/j2t_delete.gif').'" alt="'.Mage::helper('rewardpoints')->__('Remove Points').'" /></a>';
                    //$title .= '<span id="link_j2t_rewards"></span>';
                }
                
                if ($address->getDiscountDescription() != ''){
                    $desc_array = $address->getDiscountDescriptionArray();
                    $desc_array[] = $title;
                    $address->setDiscountDescriptionArray($desc_array);
                    //$address->setDiscountDescriptionArray($couponCode);
                    $address->setDiscountDescription($address->getDiscountDescription().', '.$title);
                } else {
                    $address->setDiscountDescription($title);
                    $address->setDiscountDescriptionArray(array($title));
                }
                
                
                //if (version_compare(Mage::getVersion(), '1.6.0', '>=')){
                //if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
                if (version_compare(Mage::getVersion(), '1.4.0.1', '>=')){
                    
                    $address->setDiscountAmount($address->getDiscountAmount()+$totalPPrice);                
                    $address->setBaseDiscountAmount($address->getBaseDiscountAmount()+$totalPBasePrice);
                    
                    $this->_addAmount($totalPPrice);
                    $this->_addBaseAmount($totalPBasePrice);
                } else {
                    $address->setDiscountAmount($address->getDiscountAmount()+$totalDiscountAmount);
                    $address->setSubtotalWithDiscount($subtotalWithDiscount);
                    $address->setBaseDiscountAmount($address->getBaseDiscountAmount()+$baseTotalDiscountAmount);
                    $address->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
                    if ($coupon = $address->getCouponCode()){
                        $address->setCouponCode($address->getCouponCode().', '.$title);
                    } else {
                        $address->setCouponCode($title);
                    }
                    $address->setGrandTotal($address->getGrandTotal() - $totalDiscountAmount);
                    $address->setBaseGrandTotal($address->getBaseGrandTotal()-$baseTotalDiscountAmount);
                }
                
                //if ($address->getQuote()->getRewardpointsQuantity() != $pts && $pts > 0){
            }
            
        } else {
            //remove all reward points within this cart
            if ($referrer_id = Mage::getSingleton('rewardpoints/session')->getReferralUser()){
                Mage::getSingleton('rewardpoints/session')->unsetAll();
                Mage::getSingleton('rewardpoints/session')->setReferralUser($referrer_id);
            } else {
                Mage::getSingleton('rewardpoints/session')->unsetAll();
            }
            Mage::helper('rewardpoints/event')->removeCreditPoints($address->getQuote(), true);
            
            //set all item points usage to 
            foreach ($items as $item) {
                $item->setRewardpointsUsed(0);
            }
        }
		
		$spread_equally = Mage::getStoreConfig('rewardpoints/default/spread_equally', $address->getQuote()->getStoreId());
		
		if ($address->getQuote()->getRewardpointsQuantity() && $spread_equally) {
			$base_row_total = 0;
			$base_discount_amount_total = 0;
			foreach ($items as $item) {
				/*$spread_arr[$item->getId()] = array(
					'base_row_total' => $item->getBaseRowTotal()
					'base_discount' => $item->getBaseDiscountAmount()
						);*/
				
				$base_row_total += $item->getBaseRowTotal();
				$base_discount_amount_total += $item->getBaseDiscountAmount();
			}
			if($base_discount_amount_total){
				foreach ($items as $item) {
					$percent_apply = ($item->getBaseRowTotal() * 100) / $base_row_total;
					$discount_apply_value = ($base_discount_amount_total * $percent_apply) / 100;
					if ($item->getBaseDiscountAmount() != $discount_apply_value){
						$item->setBaseDiscountAmount($discount_apply_value);
						$item->setDiscountAmount($address->getQuote()->getStore()->convertPrice($discount_apply_value, false));
					}
				}
			}
		}
		
		
        $quote_order = null;
        if(Mage::getSingleton('admin/session')->isLoggedIn()){
            $quote_order = $address->getQuote();
        }
	if ($quote_order == null){
	    return $this;
	}
        $address->getQuote()->setRewardpointsGathered(Mage::helper('rewardpoints/data')->getPointsOnOrder($quote_order, $quote_order));
        //$address->getQuote()->setRewardpointsGathered(Mage::helper('rewardpoints/data')->getPointsOnOrder());
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (Mage::getConfig()->getModuleConfig('Amasty_Rules')->is('active', 'true')){
            if (!Mage::getStoreConfig('amrules/general/breakdown'))
            return parent::fetch($address);
        
            $amount = $address->getDiscountAmount();
            if ($amount != 0) {
                $address->addTotal(array(
                    'code'      => $this->getCode(),
                    'title'     => Mage::helper('sales')->__('Discount'),
                    'value'     => $amount,
                    'full_info' => $address->getFullDescr(),
                ));
            }
            return $this;
        }
        return parent::fetch($address);
    }
 
    /*public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $pts = $this->getCreditPoints();
        $amount = $address->getRewardpointsAmount();
        
        if ($amount != 0 && $address->getAddressType() == 'shipping') {
            $title = Mage::helper('rewardpoints')->__('%s points used', $pts);
            //skin/frontend/default/default/images/j2t_delete.gif
            $auto_use = Mage::getStoreConfig('rewardpoints/default/auto_use', Mage::app()->getStore()->getId());
            if (!$auto_use){
                $title .= ' <a href="javascript:$(\'discountFormPoints2\').submit();" title="'.Mage::helper('rewardpoints')->__('Remove Points').'"><img src="'.Mage::getDesign()->getSkinUrl('images/j2t_delete.gif').'" alt="'.Mage::helper('rewardpoints')->__('Remove Points').'" /></a>';
            }
            
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }
        return $this;
    }*/

    
    /*public function getLabel()
    {
        return Mage::helper('rewardpoints')->__('Points');
    }*/
    
    protected function getCreditPoints($quote)
    {
        return Mage::helper('rewardpoints/event')->getCreditPoints($quote);
    }
    
    protected function getCurrentCurrencyRate($quote = null)
    {
        if ($quote == null) {
            $currentCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        } else {
            $currentCode = $quote->getStore()->getCurrentCurrency()->getCurrencyCode();
        }
        if ($currentCode == ""){
            $currentCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        }
        
        $baseCode = Mage::app()->getBaseCurrencyCode();      
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCode, array_values($allowedCurrencies));
        
        $current_rate = (isset($rates[$currentCode])) ? $rates[$currentCode] : 1;
        return $current_rate;
    }
    
    
    protected function percentPointMax($quote, $current_points_usage)
    {
        $return_value = $current_points_usage;
        //max_point_percent_order
        $store_id = $quote->getStoreId();
        $percent_use = (int)Mage::getStoreConfig('rewardpoints/default/max_point_percent_order', $store_id);
        $percent_use = ($percent_use > 100 || $percent_use <= 0) ? 100 : $percent_use;
        
        //todo use base total
        $cart_amount = Mage::getModel('rewardpoints/discount')->getCartAmount($quote);
        $currency_rate = $this->getCurrentCurrencyRate($quote);
        
        //$cart_amount = $cart_amount / $currency_rate;
        //TODO - check if we need to use multiply for higher rate (CHF for example)
        $cart_amount = ( $cart_amount * $percent_use ) / 100;
        //$cart_amount = Mage::helper('rewardpoints/data')->processMathValue($cart_amount);
        //$points_value = Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount, false, $quote, true);
        $points_value = Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount, false, $quote, true, false);
        
        if ($points_value < $current_points_usage){
            $return_value = $points_value;
        }
        return $return_value;
    }
    
    protected function checkMinUse($quote)
    {
        $store_id = $quote->getStoreId();
        if ($quote->getCustomerId()){
            $customerId = $quote->getCustomerId();
        } else {
            $customerId = Mage::getModel('customer/session')->getCustomerId();
        }
        $min_use = Mage::getStoreConfig('rewardpoints/default/min_use', $store_id);
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            $reward_model = Mage::getModel('rewardpoints/flatstats');
            $customer_points = $reward_model->collectPointsCurrent($customerId, $store_id);
        } else {
            $reward_model = Mage::getModel('rewardpoints/stats');
            $customer_points = $reward_model->getPointsCurrent($customerId, $store_id);
        }
        if ($min_use > $customer_points){
            return false;
        }
        return true;
    }
    
    protected function checkAutoUse($quote){
        $customer = Mage::getSingleton('customer/session');
        $store_id = $quote->getStoreId();
        if ($customer->isLoggedIn()){
            
            if ($quote->getCustomerId()){
                $customerId = $quote->getCustomerId();
            } else {
                $customerId = Mage::getModel('customer/session')->getCustomerId();
            }
            $auto_use = Mage::getStoreConfig('rewardpoints/default/auto_use', $store_id);
            if ($auto_use){
                //MODIFICATION SPENT = COLLECT
                $order = Mage::getModel('sales/order')->loadByAttribute('quote_id', $quote->getId());
                if (!$order->getId()){
                    if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
                        $reward_model = Mage::getModel('rewardpoints/flatstats');
                        $customer_points = $reward_model->collectPointsCurrent($customerId, $store_id);
                    } else {
                        $reward_model = Mage::getModel('rewardpoints/stats');
                        $customer_points = $reward_model->getPointsCurrent($customerId, $store_id);
                    }
                    if ($customer_points && $customer_points > Mage::helper('rewardpoints/event')->getCreditPoints($quote)){
                        $cart_amount = Mage::getModel('rewardpoints/discount')->getCartAmount($quote);
                        //todo use base total
                        //$cart_amount = $cart_amount / $this->getCurrentCurrencyRate($quote);
                        $cart_amount = Mage::helper('rewardpoints/data')->processMathValue($cart_amount);
                        //$points_value = min(Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount), (int)$customer_points, $quote, true);
                        //convertMoneyToPoints($money, $no_correction = false, $quote = null, $verify_custom_usage = false, $apply_math = true)
                        $points_value = min(Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount, false, $quote, true, false), (int)$customer_points);

                        Mage::getSingleton('customer/session')->setProductChecked(0);
                        Mage::helper('rewardpoints/event')->setCreditPoints($points_value);

                        $quote->setRewardpointsQuantity($points_value);
                        //->save();
                    }
                }
            }
        }
    }
}
