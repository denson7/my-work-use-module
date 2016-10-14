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
class J2t_Rewardpoints_Model_Discount extends Mage_Core_Model_Abstract
{

    protected $_discount;
    protected $_quote;
    protected $_couponCode;


    public function getCartAmount($quote = null){
        $tax = 0;
        $subtotalPrice = 0;
	$shipping = 0;

	if ($quote != null){
            $this->_quote = $quote;
        }
	
	if (!$this->_quote){
	    $this->_quote = Mage::helper('checkout/cart')->getCart()->getQuote();
	}

        if ($this->_quote->getShippingAddress()->getBaseSubtotal() <= 0){
            $this->_quote->setVoidRewardsTotal(true);
            $this->_quote->setTotalsCollectedFlag(false)->collectTotals();
            $this->_quote->setVoidRewardsTotal(false);
        }
        
        $processTax = Mage::helper('rewardpoints')->canProcessTax($this->_quote->getStoreId());
	if ($processTax){
	    //$tax = $this->_quote->getShippingAddress()->getBaseTaxAmount();
            //$tax = $this->_quote->getShippingAddress()->getTaxAmount();
            $subtotalPrice = $this->_quote->getShippingAddress()->getBaseSubtotalInclTax();
	} else {
            $subtotalPrice = $this->_quote->getShippingAddress()->getBaseSubtotalTotal();
        }
        
        $shipping_process = Mage::getStoreConfig('rewardpoints/default/process_shipping', $this->_quote->getStoreId());
	if (version_compare(Mage::getVersion(), '1.4.0', '>=') && $shipping_process){
            if ($processTax){
                $shipping = $this->_quote->getShippingAddress()->getBaseShippingInclTax();
            } else {
                $shipping = $this->_quote->getShippingAddress()->getBaseShippingAmount();
            }
            //$shipping = $this->_quote->getShippingAddress()->getShippingAmount();
        }
	
	//$subtotalPrice = $this->_quote->getShippingAddress()->getBaseSubtotal();
        
        //$subtotalPrice = $this->_quote->getShippingAddress()->getSubtotal();
        
	if (!$subtotalPrice){
            $quote = Mage::getModel('checkout/session')->getQuote();
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($processTax){
                    $subtotalPrice += $item->getBasePriceInclTax()*$item->getQty();
                } else {
                    $subtotalPrice += $item->getBasePrice()*$item->getQty();
                }
            }
        }

	return $shipping + $subtotalPrice;
	//return $tax + $shipping + $subtotalPrice;
        
        if ($quote != null){
            $this->_quote = $quote;
        }
        if ($this->_quote){
	    $this->_quote->setVoidRewardsTotal(true);
            $totalPrices = $this->_quote->getTotals();
	    $this->_quote->setVoidRewardsTotal(false);
        } else {
	    Mage::helper('checkout/cart')->getCart()->getQuote()->setVoidRewardsTotal(true);
            $totalPrices = Mage::helper('checkout/cart')->getCart()->getQuote()->getTotals();
            Mage::helper('checkout/cart')->getCart()->getQuote()->setVoidRewardsTotal(false);
	}
        $tax = 0;
        $subtotalPrice = 0;
        
        //TAX AFTER TEST
        if (Mage::helper('rewardpoints')->canProcessTax(Mage::app()->getStore()->getId())){
        //if (Mage::getStoreConfig('rewardpoints/default/process_tax', Mage::app()->getStore()->getId()) == 1 && Mage::getStoreConfig('tax/calculation/apply_after_discount', Mage::app()->getStore()->getId()) == 0){
            if (isset($totalPrices['tax'])){
                $tax_val = $totalPrices['tax'];
                $tax = $tax_val->getData('value');
            }
        }
        
        $shipping = 0;

        $shipping_process = Mage::getStoreConfig('rewardpoints/default/process_shipping', Mage::app()->getStore()->getId());
        if (version_compare(Mage::getVersion(), '1.4.0', '>=') && $shipping_process){
            if (isset($totalPrices['shipping'])){
                $shipping_val = $totalPrices['shipping'];
                $shipping = $shipping_val->getData('value');
            }
        }        

        $subtotalPrice = $totalPrices['subtotal'];
        if ($val_sub = $subtotalPrice->getData('value_excl_tax')){
            $order_details = $val_sub + $tax + $shipping;
        } else {
            $order_details = $subtotalPrice->getData('value') + $tax + $shipping;
        }
        
        return $order_details;
    }


    public function checkMaxPointsToApply($points, $quote = null){
        //J2T MOD. getCartAmount
        if ($quote != null){
            $order_details = $this->getCartAmount($quote);
        } else {
            $order_details = $this->getCartAmount();
        }
        $cart_amount = Mage::helper('rewardpoints/data')->processMathValue($order_details);
        $maxpoints = min(Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount), $points);
        return $maxpoints;
    }


    public function applyShipping(Mage_Sales_Model_Quote_Address $address)
    {

        
        $shippingAmount = $address->getShippingAmountForDiscount();
        if ($shippingAmount!==null) {
            $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
        } else {
            $shippingAmount     = $address->getShippingAmount();
            $baseShippingAmount = $address->getBaseShippingAmount();
        }


        $points_apply = (int) Mage::helper('rewardpoints/event')->getCreditPoints();


        $this->_quote = $quote = $address->getQuote();
        $customer = $quote->getCustomer();
        $customerId = $customer->getId();

        if ($points_apply > 0 && $customerId != null){
            

            $points_apply = Mage::helper('rewardpoints/data')->convertMoneyToPoints(Mage::getSingleton('rewardpoints/session')->getDiscountleft());

            $points_apply_amount = Mage::getSingleton('rewardpoints/session')->getDiscountleft();
            if (!$this->_discount){
                //$reward_model = Mage::getModel('rewardpoints/stats');
                
                if (Mage::getStoreConfig('rewardpoints/default/flatstats', Mage::app()->getStore()->getId())){
                    $reward_model = Mage::getModel('rewardpoints/flatstats');
                    $customer_points = $reward_model->collectPointsCurrent($customerId, Mage::app()->getStore()->getId());
                } else {
                    $reward_model = Mage::getModel('rewardpoints/stats');
                    $customer_points = $reward_model->getPointsCurrent($customerId, Mage::app()->getStore()->getId());
                }
                
                
                //if ($points_apply > $reward_model->getPointsCurrent($customerId, Mage::app()->getStore()->getId())){
                if ($points_apply > $customer_points){
                    return false;
                } else {
                    $discounts = $points_apply_amount;
                }

                if (Mage::getSingleton('rewardpoints/session')->getDiscountleft() && (!Mage::getSingleton('rewardpoints/session')->getShippingChecked() && $discounts > 0) || Mage::getSingleton('rewardpoints/session')->getProductChecked() == 0){
                    Mage::getSingleton('rewardpoints/session')->setShippingChecked(0);
                    Mage::getSingleton('rewardpoints/session')->setDiscountleft($points_apply_amount);
                    $this->_discount = $discounts;
                    $this->_couponCode = $points_apply;
                } else {
                    $this->_discount = Mage::getSingleton('rewardpoints/session')->getDiscountleft();
                    $this->_couponCode = $points_apply;
                }
            }

            $discountAmount = 0;
            $baseDiscountAmount = 0;

            ////////////////////////////

            $discountAmount = min($shippingAmount - $address->getShippingDiscountAmount(), $quote->getStore()->convertPrice($this->_discount));
            $baseDiscountAmount = min($baseShippingAmount - $address->getBaseShippingDiscountAmount(), $this->_discount);

            

            //$discountAmount = Mage::helper('rewardpoints/data')->ratePointCorrection($discountAmount);
            //$baseDiscountAmount = Mage::helper('rewardpoints/data')->ratePointCorrection($baseDiscountAmount);

            Mage::getSingleton('rewardpoints/session')->setShippingChecked(1);
            //$quote_id = Mage::helper('checkout/cart')->getCart()->getQuote()->getId();
            Mage::getSingleton('rewardpoints/session')->setDiscountleft(Mage::getSingleton('rewardpoints/session')->getDiscountleft() - $baseDiscountAmount);
            $discountAmount     = min($discountAmount + $address->getShippingDiscountAmount(), $shippingAmount);
            $baseDiscountAmount = min($baseDiscountAmount + $address->getBaseShippingDiscountAmount(), $baseShippingAmount);

            $address->setShippingDiscountAmount($discountAmount);
            $address->setBaseShippingDiscountAmount($baseDiscountAmount);
        }
    }

    public function getFullItemNumber(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        } 
        $i = 0;
        foreach ($items as $item) {
            if (!$item->getParentItemId()) {
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $i = $i + $child->getQty();
                    }
                } else {
                    $i = $i + $item->getQty();
                }
            }
        }
        return $i;
    }


    public function apply(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $points_apply = (int) Mage::helper('rewardpoints/event')->getCreditPoints();

        $this->_quote = $quote = $item->getQuote();
        $customer = $quote->getCustomer();
        $customerId = $customer->getId();

        if ($points_apply > 0 && $customerId != null){
            $test_points = $this->checkMaxPointsToApply($points_apply, $quote);

            if ($points_apply > $test_points){
                $points_apply = $test_points;
                Mage::helper('rewardpoints/event')->setCreditPoints($points_apply);
            }            
            $points_apply_amount = Mage::helper('rewardpoints/data')->convertPointsToMoney($points_apply, null, $quote);
            
            $address = $this->_getAddress($item);

            //$cart_summury_count = Mage::helper('checkout/cart')->getSummaryCount();
            $cart_summury_count = $this->getFullItemNumber($address);

            if (!$this->_discount){
                //$reward_model = Mage::getModel('rewardpoints/stats');
                
                if (Mage::getStoreConfig('rewardpoints/default/flatstats', Mage::app()->getStore()->getId())){
                    $reward_model = Mage::getModel('rewardpoints/flatstats');
                    $customer_points = $reward_model->collectPointsCurrent($customerId, Mage::app()->getStore()->getId());
                } else {
                    $reward_model = Mage::getModel('rewardpoints/stats');
                    $customer_points = $reward_model->getPointsCurrent($customerId, Mage::app()->getStore()->getId());
                }
                
                //if ($points_apply > $reward_model->getPointsCurrent($customerId, Mage::app()->getStore()->getId())){
                if ($points_apply > $customer_points){
                    return false;
                } else {
                    $discounts = $points_apply_amount;
                }

                //echo "remise $discounts ";
                
                if ((Mage::getSingleton('rewardpoints/session')->getProductChecked() >= $cart_summury_count && $discounts > 0) || !Mage::getSingleton('rewardpoints/session')->getProductChecked() || Mage::getSingleton('rewardpoints/session')->getProductChecked() == 0){
                    Mage::getSingleton('rewardpoints/session')->setProductChecked(0);
                    Mage::getSingleton('rewardpoints/session')->setDiscountleft($points_apply_amount);

                    $this->_discount = $discounts;
                    $this->_couponCode = $points_apply;
                } else {
                    $this->_discount = Mage::getSingleton('rewardpoints/session')->getDiscountleft();
                    $this->_couponCode = $points_apply;
                }
            }


            $discountAmount = 0;
            $baseDiscountAmount = 0;

            //process_tax
            //TAX AFTER TEST
            if (Mage::helper('rewardpoints')->canProcessTax(Mage::app()->getStore()->getId())){
            //if (Mage::getStoreConfig('rewardpoints/default/process_tax', Mage::app()->getStore()->getId()) == 1 && Mage::getStoreConfig('tax/calculation/apply_after_discount', Mage::app()->getStore()->getId()) == 0){
                $row_total = $item->getRowTotal() + $item->getTaxAmount();
                $tax = ($item->getTaxBeforeDiscount() ? $item->getTaxBeforeDiscount() : $item->getTaxAmount());
                $row_base_total = $item->getBaseRowTotal() + $tax;
            } else {
                $row_total = $item->getRowTotal();
                $row_base_total = $item->getBaseRowTotal();
            }

            $discountAmount = min($row_total - $item->getDiscountAmount(), $quote->getStore()->convertPrice($this->_discount));
            $baseDiscountAmount = min($row_base_total - $item->getBaseDiscountAmount(), $this->_discount);

           
            Mage::getSingleton('rewardpoints/session')->setProductChecked(Mage::getSingleton('rewardpoints/session')->getProductChecked() + $item->getQty());
            //$quote_id = Mage::helper('checkout/cart')->getCart()->getQuote()->getId();
            Mage::getSingleton('rewardpoints/session')->setDiscountleft(Mage::getSingleton('rewardpoints/session')->getDiscountleft() - $baseDiscountAmount);

            

            $discountAmount     = min($discountAmount + $item->getDiscountAmount(), $row_total);
            $baseDiscountAmount = min($baseDiscountAmount + $item->getBaseDiscountAmount(), $row_base_total);


            $item->setDiscountAmount($discountAmount);
            $item->setBaseDiscountAmount($baseDiscountAmount);
            //store_labels
            $couponCode = explode(', ', $address->getCouponCode());

            
            $descriptionPromo = $address->getDiscountDescriptionArray();
            if (sizeof($descriptionPromo)){
                $return_array = array();
                foreach($descriptionPromo as $val_desc){
                    $return_array[] = $val_desc;
                }
                $descriptionPromo = $return_array;
            }
            if (sizeof($couponCode)){
                foreach($couponCode as $key_promo => $value_promo){
                    if (isset($descriptionPromo[$key_promo])){
                        $couponCode[$key_promo] = $descriptionPromo[$key_promo];
                    }
                }
            }

            $couponCode[] = Mage::helper('rewardpoints/data')->__('%s credit points', ceil($this->_couponCode));
            $couponCode = array_unique(array_filter($couponCode));
            if (version_compare(Mage::getVersion(), '1.4.0', '<')){
                $address->setCouponCode(implode(', ', $couponCode));
            }
            //$address->setCouponCode(implode(', ', $couponCode));
            $address->setDiscountDescriptionArray($couponCode);
        }
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }


    /**
     * Get address object which can be used for discount calculation
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_Sales_Model_Quote_Address
     */
    protected function _getAddress(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $address = $item->getAddress();
        } elseif ($item->getQuote()->isVirtual()) {
            $address = $item->getQuote()->getBillingAddress();
        } else {
            $address = $item->getQuote()->getShippingAddress();
        }
        return $address;
    }

}
?>
