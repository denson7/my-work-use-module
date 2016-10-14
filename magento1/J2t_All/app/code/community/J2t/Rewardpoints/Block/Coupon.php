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

class J2t_Rewardpoints_Block_Coupon extends Mage_Checkout_Block_Cart_Abstract
{
    /*public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }*/
    
    const XML_PATH_DESIGN_BIG_INLINE_IMAGE_SHOW       = 'rewardpoints/design/big_inline_image_show';
    const XML_PATH_DESIGN_BIG_INLINE_IMAGE_SIZE       = 'rewardpoints/design/big_inline_image_size';
	const XML_PATH_CONVERSION_VALUE				      = 'rewardpoints/default/points_money';
    
    protected $points_current;
    protected $points_on_order = null;
    
	public function getConversionValue(){
		return Mage::getStoreConfig(self::XML_PATH_CONVERSION_VALUE, Mage::app()->getStore()->getId());
	}
	
    public function getIllustrationImage(){
        $img = '';
        $img_size = Mage::getStoreConfig(self::XML_PATH_DESIGN_BIG_INLINE_IMAGE_SIZE, Mage::app()->getStore()->getId());
        if (Mage::getStoreConfig(self::XML_PATH_DESIGN_BIG_INLINE_IMAGE_SHOW, Mage::app()->getStore()->getId()) && $img_size){
            $img_url = Mage::helper('rewardpoints/data')->getResizedUrl("j2t_image_big.png", $img_size, $img_size);
            $img = '<img class="j2t-cart-points-image" style="float:left; padding-right:5px;" src="'.$img_url .'" alt="" width="'.$img_size.'" height="'.$img_size.'" /> ';
        }
        return $img;
    }

    public function isUsable() {
		if (!$this->getConversionValue()){
			return false;
		}
        $isUsable = false;
        $minimumBalance = $this->getMinimumBalance();
        $currentBalance = $this->getCustomerPoints();
        if($currentBalance >= $minimumBalance) {
            $isUsable = true;
        }
        return $isUsable;
    }

    public function getMinimumBalance() {
        $minimumBalance = Mage::getStoreConfig('rewardpoints/default/min_use', Mage::app()->getStore()->getId());
        return $minimumBalance;
    }

    public function getAutoUse(){
        return Mage::getStoreConfig('rewardpoints/default/auto_use', Mage::app()->getStore()->getId());
    }
    
    public function useSlider(){
        return Mage::getStoreConfig('rewardpoints/default/step_slide', Mage::app()->getStore()->getId());
    }
    
    public function showLogin(){
        return Mage::getStoreConfig('rewardpoints/default/show_login', Mage::app()->getStore()->getId());
    }
    public function showDetails(){
        return sizeof($this->getItemPoints()) && Mage::getStoreConfig('rewardpoints/default/show_details', Mage::app()->getStore()->getId());
    }
    
    public function isCouponPointsRemoved(){
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        return ($quote->getCouponCode() && Mage::getStoreConfig('rewardpoints/default/disable_points_coupon', Mage::app()->getStore()->getId()));
    }
    
    public function getQuoteCartRuleText() {
        $details_items_line = array();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($quote_text_rule_point = unserialize($quote->getRewardpointsCartRuleText())){
            foreach ($quote_text_rule_point as $rule_point_text){
                $details_items_line[] = $rule_point_text;
            }
        }
        return $details_items_line;
    }
    
    public function getItemPoints(){
        $details_items_line = array();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $cartItems = $quote->getAllItems();
        $ceiled_points  = 0;
        $points = 0;
        $end = array();
        foreach ($cartItems as $item)
        {
            if ($item->getRewardpointsGathered() > 0){
                if ($item->getRewardpointsCatalogRuleText() && ($catalog_rule_details = unserialize($item->getRewardpointsCatalogRuleText())) && is_array($catalog_rule_details) && sizeof($catalog_rule_details)){
                    $catalog_rule_details_txt = '<ul class="catalog-points-details">';
                    foreach($catalog_rule_details as $details){
                        $catalog_rule_details_txt .= '<li class="inline-catalog-points-details">'.$details.'</li>';
                    }
                    $catalog_rule_details_txt .= '</ul>';
                    $details_items_line[] = $this->__('+ %s points: %s %s', '<span class="inline-point-items">'.$item->getRewardpointsGathered().'</span>', $item->getName(), $catalog_rule_details_txt);
                } else {
                    $details_items_line[] = $this->__('+ %s points: %s', '<span class="inline-point-items">'.$item->getRewardpointsGathered().'</span>', $item->getName());
                } 
                $ceiled_points += $item->getRewardpointsGathered();
                $points += $item->getRewardpointsGatheredFloat();
            } else if($item->getRewardpointsCatalogRuleText() && ($catalog_rule_details = unserialize($item->getRewardpointsCatalogRuleText())) && is_array($catalog_rule_details) && sizeof($catalog_rule_details)){
                foreach($catalog_rule_details as $details){
                    $end[] = $details;
                }
            }
        }
        $point_diff = ceil($points) - $ceiled_points;
        if ($point_diff != 0){
            $details_items_line[] = $this->__('%s point(s) calculation adjustment', '<span class="inline-point-items">'.$point_diff.'</span>');
        }
        $details_items_line = array_merge($details_items_line, $end);
        return $details_items_line;
    }
    
    public function showOnepageSummary(){
        return Mage::getStoreConfig('rewardpoints/default/onepage_summary', Mage::app()->getStore()->getId());
    }

    public function getPointsOnOrder() {
        if ($this->points_on_order === null){
            $this->points_on_order = Mage::helper('rewardpoints/data')->getPointsOnOrder();
        }
        return $this->points_on_order;
        //return Mage::helper('rewardpoints/data')->getPointsOnOrder();
    }

    public function getCustomerId() {
        return Mage::getModel('customer/session')->getCustomerId();
    }

    /*
     * required point entry
     */
    public function getRequiredPoints()
    {
        $required_points = 0; 
        if (Mage::getConfig()->getModuleConfig('J2t_Rewardproductvalue')->is('active', 'true')){
            $cartHelper = Mage::helper('checkout/cart');
            $items = $cartHelper->getCart()->getItems();
            foreach ($items as $item) {
                $required_points += Mage::getModel('catalog/product')->load($item->getProductId())->getData('j2t_rewardvalue') * $item->getQty();
            }
        }
        return $required_points;
    }

    public function getPointsCurrentlyUsed() {
        return Mage::helper('rewardpoints/event')->getCreditPoints();
    }

    public function canUseCouponCode(){
        return Mage::getStoreConfig('rewardpoints/default/coupon_codes', Mage::app()->getStore()->getId());
    }

    public function getCustomerPoints() {
        
        if ($this->points_current){
            return $this->points_current;
        }
        
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        $store_id = Mage::app()->getStore()->getId();        
        
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            $this->points_current = $reward_flat_model->collectPointsCurrent($customerId, $store_id);
            return $this->points_current;
        }
        
        $reward_model = Mage::getModel('rewardpoints/stats');    
        
        $customerPoints = $reward_model->getPointsCurrent($customerId, $store_id);
        if (Mage::getStoreConfig('rewardpoints/default/allow_direct_usage', Mage::app()->getStore()->getId())){
            $customerPoints += $this->getPointsOnOrder();
        }
        
        $this->points_current = $customerPoints;
        
        return $this->points_current;
    }

    public function getPointsInfo() {
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        $reward_model = Mage::getModel('rewardpoints/stats');
        $store_id = Mage::app()->getStore()->getId();
        
        $customerPoints = $this->getCustomerPoints();
        
        //points required to get 1 €
        $points_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
        //step to reach to get discount
        $step = Mage::getStoreConfig('rewardpoints/default/step_value', Mage::app()->getStore()->getId());
        //check if step needs to apply
        $step_apply = Mage::getStoreConfig('rewardpoints/default/step_apply', Mage::app()->getStore()->getId());
        $full_use = Mage::getStoreConfig('rewardpoints/default/full_use', Mage::app()->getStore()->getId());

        
        //$this->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        
        $order_details = $this->getQuote()->getSubtotal();
        /*if (!$order_details){
            $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals();
            $order_details = $totals["subtotal"]->getValue();   
        }*/
      
        $min_use = Mage::getStoreConfig('rewardpoints/default/min_use', Mage::app()->getStore()->getId());
        

        /*if (Mage::getStoreConfig('rewardpoints/default/process_tax', Mage::app()->getStore()->getId()) == 1){
            $order_details = $this->getQuote()->getSubtotalInclTax();
        }*/
        //$order_details = Mage::getModel('rewardpoints/discount')->getCartAmount();
        //J2T MOD. getCartAmount
        $order_details = Mage::getModel('rewardpoints/discount')->getCartAmount($this->getQuote());
        $cart_amount = Mage::helper('rewardpoints/data')->processMathValue($order_details);
        $max_use = min(Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount), $customerPoints);
        
        return array('min_use' => $min_use, 'customer_points' => $customerPoints, 'points_money' => $points_money, 'step' => $step, 'step_apply' => $step_apply, 'full_use' => $full_use, 'max_use' => $max_use);
    }

    public function pointsToAddOptions($customer_points, $step, $slider = false){
        $toHtml = '';
        $toHtmlArr = array();
        $creditToBeAdded = 0;
        //points required to get 1 €
        $points_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
        $max_points_tobe_used = Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId());
        $max_order_points = Mage::helper('rewardpoints')->percentPointMax();
        $max_points_tobe_used = (($max_points_tobe_used == 0 || $max_order_points < $max_points_tobe_used) && $max_order_points > 0) ? $max_order_points : $max_points_tobe_used;
        
        $step_multiplier = Mage::getStoreConfig('rewardpoints/default/step_multiplier', Mage::app()->getStore()->getId());
        
        if (Mage::getStoreConfig('rewardpoints/default/process_rate', Mage::app()->getStore()->getId())){
            $order_details = $this->getQuote()->getBaseGrandTotal();
            $cart_amount = Mage::helper('rewardpoints/data')->convertBaseMoneyToPoints($order_details); 
            //$toHtml .= "<option>$cart_amount</option>";
        } else {
            $order_details = $this->getQuote()->getGrandTotal();
            $cart_amount = Mage::helper('rewardpoints/data')->convertMoneyToPoints($order_details);
        }        
        $customer_points_origin = $customer_points;
        
        while ($customer_points > 0){
            
            //$creditToBeAdded += $step;            
            $creditToBeAdded = ($creditToBeAdded > 0 && $step_multiplier > 1) ? ($creditToBeAdded*$step_multiplier) : ($creditToBeAdded+$step);  
            $customer_points-=$step;            
            //$toHtml .= "<option>$cart_amount < $creditToBeAdded</option>";
            
            if ($creditToBeAdded > $customer_points_origin || $cart_amount < $creditToBeAdded || ($max_points_tobe_used != 0 && $max_points_tobe_used < $creditToBeAdded)){
                //$toHtml .= "<option>$cart_amount < $creditToBeAdded</option>";
                break;
            }
            //check if credits always lower than total cart amount
            if ($slider){
                $toHtmlArr[] = $creditToBeAdded;
            } else {
                $toHtml .= '<option value="'. $creditToBeAdded .'">'. $this->__("%d loyalty point(s)",$creditToBeAdded) .'</option>';
            }
        }
        if ($toHtmlArr != array()){
            if (sizeof($toHtmlArr) == 1){
                $toHtmlArr[] = $toHtmlArr[0];
                $toHtmlArr[0] = 0;
            }
            $toHtml = implode(',',$toHtmlArr);
        }
        
        return $toHtml;
    }
    
    public function canShowRemoveLink(){
        $auto_use = Mage::getStoreConfig('rewardpoints/default/auto_use', Mage::app()->getStore()->getId());
        $remove_link = Mage::getStoreConfig('rewardpoints/default/remove_link', Mage::app()->getStore()->getId());
        if (!$auto_use && $remove_link){
            return true;
        }
        return false;
    }

}
