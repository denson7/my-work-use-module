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
class J2t_Rewardpoints_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_ACTIVE = 'rewardpoints/default/active';
    const XML_PATH_ACTIVE_GROUP = 'rewardpoints/default/exclude_groups';
    const XML_PATH_REFERRAL_SHOW = 'rewardpoints/registration/referral_show';
    const XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SHOW = 'rewardpoints/design/small_inline_image_show';
    const XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SIZE = 'rewardpoints/design/small_inline_image_size';
    const XML_PATH_POINT_MATH_GATHER = 'rewardpoints/default/math_method';
    const XML_PATH_POINT_MATH_USAGE = 'rewardpoints/default/math_method_usage';
    const XML_PATH_CUSTOM_POINT_VALUE = 'rewardpoints/custom_point/custom_point_value';
    const XML_PATH_CUSTOM_POINT_USAGE = 'rewardpoints/custom_point/custom_point_usage';
    const XML_PATH_ADVANCED_REFERRAL_POINTS = 'rewardpoints/referral_advanced/referral_steps';
    const XML_PATH_BUNDLE_CHILD = 'rewardpoints/default/bundle_rule_child';
    const XML_PATH_MAX_ORDER_POINTS = 'rewardpoints/default/max_point_percent_order';
    const XML_PATH_DISABLE_POINTS_COUPON = 'rewardpoints/default/disable_points_coupon';
    const XML_PATH_PROCESS_TAX = 'rewardpoints/default/process_tax';
    const XML_PATH_MONEY_POINTS = 'rewardpoints/default/money_points';
    const XML_PATH_MIN_POINT_COLLECTION = 'rewardpoints/default/min_point_collect';
    const XML_PATH_GATHER_STEP = 'rewardpoints/default/gather_step';

    public function canProcessTax($store_id) {
        return (Mage::getStoreConfig(self::XML_PATH_PROCESS_TAX, $store_id) == 1 && Mage::getStoreConfig('tax/calculation/apply_after_discount', $store_id) == 0);
    }

    public function getPointGroup($product, $group_id = null) {
        if ($group_id === null) {
            $group_id = Mage::getModel('checkout/cart')->getQuote()->getCustomerGroupId();
        }
        $point = 0;
        if ($product->getRewardPointsGroups() && strpos($product->getRewardPointsGroups(), '{') !== false) {
            $data_array = Mage::helper('core')->jsonDecode($product->getRewardPointsGroups());
            foreach ($data_array as $value) {
                if ($value["group_id"] == $group_id) {
                    $point = (int) $value['point'];
                }
            }
        }
        return $point;
    }

    public function percentPointMax($quote = null) {
        $quote = ($quote != null) ? $quote : Mage::getModel('checkout/cart')->getQuote();
        $store_id = $quote->getStoreId();
        $percent_use = (int) Mage::getStoreConfig(self::XML_PATH_MAX_ORDER_POINTS, $store_id);
        $percent_use = ($percent_use > 100 || $percent_use <= 0) ? 100 : $percent_use;


        $cart_amount = Mage::getModel('rewardpoints/discount')->getCartAmount($quote);
        $currency_rate = $this->getCurrentCurrencyRate($quote);

        //$cart_amount = $cart_amount / $currency_rate;
        $cart_amount = ( $cart_amount * $percent_use ) / 100;
        $cart_amount = Mage::helper('rewardpoints/data')->processMathValue($cart_amount);
        $max_value = Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount, false, $quote, true);
        return $max_value;
    }

    public function canShowIf() {
        $canShow = true;
        $canShow = (!$this->isModuleActive() || Mage::getStoreConfig(self::XML_PATH_REFERRAL_SHOW)) ? false : true;
        return $canShow;
    }

    public function canBundleChildrendRule($store_id = null) {
        $store_id = ($store_id) ? $store_id : Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(self::XML_PATH_BUNDLE_CHILD, $store_id);
    }

    public function isModuleActive($store_id = null, $group_id = null) {
        $store_id = ($store_id) ? $store_id : Mage::app()->getStore()->getId();

        $group_excluded = explode(',', Mage::getStoreConfig(self::XML_PATH_ACTIVE_GROUP, $store_id));
        $customerGroupId = 0;
        
        if (!$group_id){
            $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
            if ($isLoggedIn) {
                $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            }
        } else {
            $customerGroupId = $group_id;
        }

        if (count($group_excluded) && in_array($customerGroupId, $group_excluded)) {
            return false;
        }

        return Mage::getStoreConfig(self::XML_PATH_ACTIVE, $store_id);
    }

    public function processReferralInsertion($order, $rewardPoints, $rewardPointsChild, $escape_status_verification = false) {
        $referralModel = Mage::getModel('rewardpoints/referral');
        if ($referralModel->isSubscribed($order->getCustomerEmail())) {

            if (!$referralModel->isConfirmed($order->getCustomerEmail(), $escape_status_verification)) {
                $referralModel->loadByEmail($order->getCustomerEmail());
                $referralModel->setData('rewardpoints_referral_status', true);
                $referralModel->setData('rewardpoints_referral_child_id', $order->getCustomerId());
                $referralModel->save();

                $parent = Mage::getModel('customer/customer')->load($referralModel->getData('rewardpoints_referral_parent_id'));
                $child = Mage::getModel('customer/customer')->load($referralModel->getData('rewardpoints_referral_child_id'));

                try {
                    if ($rewardPoints > 0) {
                        $reward_model = Mage::getModel('rewardpoints/stats');
                        $post = array('order_id' => $order->getIncrementId(), 'customer_id' => $referralModel->getData('rewardpoints_referral_parent_id'),
                            'store_id' => $order->getStoreId(), 'points_current' => $rewardPoints, 'rewardpoints_referral_id' => $referralModel->getData('rewardpoints_referral_id'));
                        $reward_model->setData($post);
                        $reward_model->save();
                    }

                    if ($rewardPointsChild > 0) {
                        $reward_model = Mage::getModel('rewardpoints/stats');
                        $post = array('order_id' => $order->getIncrementId(), 'customer_id' => $referralModel->getData('rewardpoints_referral_child_id'),
                            'store_id' => $order->getStoreId(), 'points_current' => $rewardPointsChild, 'rewardpoints_referral_id' => $referralModel->getData('rewardpoints_referral_id'));
                        $reward_model->setData($post);
                        $reward_model->save();
                    }
                } catch (Exception $e) {
                    //Mage::getSingleton('session')->addError($e->getMessage());
                }
                $referralModel->sendConfirmation($parent, $child, $parent->getEmail());
            }
        }
    }

    public function referralPointsEntry($order, $referralPointMethod, $rewardPoints = 0) {
        if (!$order->getBaseSubtotalInclTax()) {
            $order->setBaseSubtotalInclTax($order->getBaseSubtotal() + $order->getBaseTaxAmount());
        }
        if ($referralPointMethod == J2t_Rewardpoints_Model_Calculationtype::RATIO_POINTS) {
            $rate = $order->getBaseToOrderRate();
            if ($rewardPoints > 0) {
                $rewardPoints = $this->getPointsOnOrder($order, null, $rate, false, $order->getStoreId(), $rewardPoints);
            }
        } else if ($referralPointMethod == J2t_Rewardpoints_Model_Calculationtype::CART_SUMMARY) {
            if (($base_subtotal = $order->getBaseSubtotalInclTax()) && $rewardPoints > 0) {
                $summary_points = $base_subtotal * $rewardPoints;
                //$summary_points = $base_subtotal * $rewardPointsChild;
                if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $order->getStoreId())) {
                    $summary_points = $summary_points - $order->getBaseTaxAmount();
                }
                $rewardPoints = $this->processMathValue($summary_points);
            }
        }
        //returns J2t_Rewardpoints_Model_Calculationtype::STATIC_VALUE in no ratio nor cart summary
        return $rewardPoints;
    }

    public function getAdvancedPointValue($order, $customer_id, $referrer = true) {
        $custom_points = Mage::getStoreConfig(self::XML_PATH_ADVANCED_REFERRAL_POINTS, $order->getStoreId());
        $custom_points_array = unserialize($custom_points);

        if (sizeof($custom_points_array)) {
            //Get number of valid orders
            //$nb_valid_orders = 0;

            $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', $order->getStoreId());
            $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', $order->getStoreId());
            $order_states = explode(",", $statuses);

            $orders = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToFilter($status_field, array('in' => $order_states))
                    ->addAttributeToFilter('customer_id', $customer_id)
                    ->addAttributeToSelect('increment_id')
                    ->getColumnValues('increment_id');

            //$nb_valid_orders = $orders->getSize();
            $nb_valid_orders = sizeof($orders);

            foreach ($custom_points_array as $custom_point) {
                if (isset($custom_point['min_order_qty']) && isset($custom_point['max_order_qty']) && isset($custom_point['point_value_referrer']) && isset($custom_point['calculation_type_referrer']) && isset($custom_point['point_value_referred']) && isset($custom_point['calculation_type_referred']) && isset($custom_point['date_from']) && isset($custom_point['date_end'])) {
                    $nowDate = date("Y-m-d");
                    $nowDate = new Zend_Date($nowDate, Varien_Date::DATE_INTERNAL_FORMAT);
                    if ($custom_point['date_from'] != "") {
                        $fromDate = new Zend_Date($custom_point['date_from'], Varien_Date::DATE_INTERNAL_FORMAT);
                        //verify if now > fromDate; retuns 0 if they are equal; 1 if this object's part was more recent than $date's part; otherwise -1.
                        if ($fromDate->compare($nowDate) === 1 || $fromDate->compare($nowDate) === 0) {
                            continue;
                        }
                    }
                    if ($custom_point['date_end'] != "") {
                        $endDate = new Zend_Date($custom_point['date_end'], Varien_Date::DATE_INTERNAL_FORMAT);
                        if ($nowDate->compare($endDate) === 1 || $nowDate->compare($endDate) === 0) {
                            continue;
                        }
                    }
                    if ((int) $custom_point['point_value_referred'] == 0 && !$referrer) {
                        continue;
                    }

                    if ((int) $custom_point['point_value_referrer'] == 0 && $referrer) {
                        continue;
                    }

                    if ((int) $custom_point['min_order_qty'] != 0 && (int) $custom_point['min_order_qty'] > $nb_valid_orders) {
                        continue;
                    }
                    if ((int) $custom_point['max_order_qty'] != 0 && (int) $custom_point['max_order_qty'] < $nb_valid_orders) {
                        continue;
                    }

                    if ($referrer) {
                        $referralPointMethod = $custom_point['calculation_type_referrer'];
                        $rewardPoints = $custom_point['point_value_referrer'];
                    } else {
                        $referralPointMethod = $custom_point['calculation_type_referred'];
                        $rewardPoints = $custom_point['point_value_referred'];
                    }

                    return $this->referralPointsEntry($order, $referralPointMethod, $rewardPoints);
                }
            }
        }
        return 0;
    }

    protected function getCurrentCurrencyRate($quote = null) {
        if ($quote == null) {
            $currentCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        } else {
            $currentCode = $quote->getStore()->getCurrentCurrency()->getCurrencyCode();
        }
        if ($currentCode == "") {
            $currentCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        }

        $baseCode = Mage::app()->getBaseCurrencyCode();
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCode, array_values($allowedCurrencies));

        $current_rate = (isset($rates[$currentCode])) ? $rates[$currentCode] : 1;
        return $current_rate;
    }

    public function getCustomPointValue($cartLoaded = null, $cartQuote = null, $money_points = 0, $gather = true) {
        if ($cartQuote != null) {
            $store_id = $cartQuote->getStoreId();
            $cart_amount = Mage::getModel('rewardpoints/discount')->getCartAmount($cartQuote);
            $currency_rate = $this->getCurrentCurrencyRate($cartQuote);
            //$cart_amount = $cart_amount / $currency_rate;
            $customer_group_id = $cartQuote->getCustomerGroupId();
        } elseif ($cartLoaded != null) {
            $store_id = $cartLoaded->getStoreId();
            $customer_group_id = $cartLoaded->getCustomerGroupId();
            $base_subtotal = $cartLoaded->getBaseSubtotal() - $cartLoaded->getBaseSubtotalRefunded();
            $base_taxamount = $cartLoaded->getBaseTaxAmount() - $cartLoaded->getBaseTaxAmountRefunded();
            $base_shippingamount = $cartLoaded->getBaseShippingAmount() - $cartLoaded->getBaseShippingAmountRefunded();
            $base_discountamount = $cartLoaded->getBaseDiscountAmount() - $cartLoaded->getBaseDiscountAmountRefunded();
            $cart_amount = $base_subtotal;
            if (Mage::getStoreConfig('rewardpoints/default/process_tax', $store_id)) {
                $cart_amount += $base_taxamount;
            }
            if (Mage::getStoreConfig('rewardpoints/default/process_shipping', $store_id)) {
                $cart_amount += $base_shippingamount;
            }
            $cart_amount -= $base_discountamount;
        } else {
            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            $store_id = $quote->getStoreId();
            $cart_amount = Mage::getModel('rewardpoints/discount')->getCartAmount($quote);
            $currency_rate = $this->getCurrentCurrencyRate($quote);
            //$cart_amount = $cart_amount / $currency_rate;
            $customer_group_id = $quote->getCustomerGroupId();
        }

        //echo "store: $store_id / cart: $cart_amount / group: $customer_group_id";
        //die;

        if ($gather) {
            $custom_points = Mage::getStoreConfig(self::XML_PATH_CUSTOM_POINT_VALUE, $store_id);
        } else {
            $custom_points = Mage::getStoreConfig(self::XML_PATH_CUSTOM_POINT_USAGE, $store_id);
        }
        $custom_points_array = unserialize($custom_points);

        if ($custom_points_array !== false && sizeof($custom_points_array)) {
            foreach ($custom_points_array as $custom_point) {
                if (isset($custom_point['min_cart_value']) && isset($custom_point['max_cart_value']) && isset($custom_point['point_value']) && isset($custom_point['group_id']) && isset($custom_point['date_from']) && isset($custom_point['date_end'])
                ) {

                    $nowDate = date("Y-m-d");
                    $nowDate = new Zend_Date($nowDate, Varien_Date::DATE_INTERNAL_FORMAT);
                    if ($custom_point['date_from'] != "") {
                        $fromDate = new Zend_Date($custom_point['date_from'], Varien_Date::DATE_INTERNAL_FORMAT);
                        //verify if now > fromDate; retuns 0 if they are equal; 1 if this object's part was more recent than $date's part; otherwise -1.
                        if ($fromDate->compare($nowDate) === 1 || $fromDate->compare($nowDate) === 0) {
                            continue;
                        }
                    }
                    if ($custom_point['date_end'] != "") {
                        $endDate = new Zend_Date($custom_point['date_end'], Varien_Date::DATE_INTERNAL_FORMAT);
                        if ($nowDate->compare($endDate) === 1 || $nowDate->compare($endDate) === 0) {
                            continue;
                        }
                    }
                    if ($custom_point['min_cart_value'] && $custom_point['min_cart_value'] > $cart_amount) {
                        continue;
                    }
                    if ($custom_point['max_cart_value'] && $custom_point['max_cart_value'] < $cart_amount) {
                        continue;
                    }
                    if (!in_array($customer_group_id, $custom_point['group_id'])) {
                        continue;
                    }
                    return $custom_point['point_value'];
                }
            }
        }
        return $money_points;
    }

    public function getReferalUrl() {
        return $this->_getUrl('rewardpoints/');
    }

    public function showCurrentCustomerPoints($show_img = false, $show_login = false) {
        $customer_points = 0;
        $store_id = Mage::app()->getStore()->getId();
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        $img = '';
        $img_size = Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SIZE, Mage::app()->getStore()->getId());
        if (Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SHOW, Mage::app()->getStore()->getId()) && $show_img && $img_size) {
            $img = '<img src="' . $this->getResizedUrl('j2t_image_small.png', $img_size, $img_size) . '" alt="' . Mage::helper('rewardpoints')->__("Reward points") . '" width="' . $img_size . '" height="' . $img_size . '" /> ';
        }

        if (!$customerId && $show_login) {
            return $img . Mage::helper('rewardpoints')->__("Sign-in to see your points.");
        } elseif (!$customerId && !$show_login) {
            return '';
        }
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)) {
            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            $customer_points = $reward_flat_model->collectPointsCurrent($customerId, $store_id) + 0;
        } else {
            $reward_model = Mage::getModel('rewardpoints/stats');
            $customer_points = $reward_model->getPointsCurrent($customerId, $store_id) + 0;
        }

        return $img . Mage::helper('rewardpoints')->__("You have %s point(s)", $customer_points);
    }

    public function processRecordFlatAction($customerId, $store_id, $check_date = false) {
        if (Mage::getModel('customer/customer')->load($customerId)) {
            $reward_model = Mage::getModel('rewardpoints/stats');
            $points_current = $reward_model->getPointsCurrent($customerId, $store_id);
            $points_received = $reward_model->getRealPointsReceivedNoExpiry($customerId, $store_id);
            $points_spent = $reward_model->getPointsSpent($customerId, $store_id);
            $points_awaiting_validation = $reward_model->getPointsWaitingValidation($customerId, $store_id);
            $points_lost = $reward_model->getRealPointsLost($customerId, $store_id);

            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            $reward_flat_model->loadByCustomerStore($customerId, $store_id);
            $reward_flat_model->setPointsCollected($points_received);
            $reward_flat_model->setPointsUsed($points_spent);
            $reward_flat_model->setPointsWaiting($points_awaiting_validation);
            $reward_flat_model->setPointsCurrent($points_current);
            $reward_flat_model->setPointsLost($points_lost);
            $reward_flat_model->setStoreId($store_id);
            $reward_flat_model->setUserId($customerId);

            if ($check_date && ($date_check = $reward_flat_model->getLastCheck())) {
                $date_array = explode("-", $reward_flat_model->getLastCheck());
                if ($reward_flat_model->getLastCheck() == date("Y-m-d")) {
                    return false;
                }
            }
            $reward_flat_model->setLastCheck(date("Y-m-d"));
            $reward_flat_model->save();
        }
    }

    public function getResizedUrl($imgName, $x, $y = NULL) {

        if ($imgName == 'j2t_image_small.png' && Mage::getStoreConfig('rewardpoints/design/small_inline_image') && file_exists(Mage::getBaseDir("media") . DS . Mage::getStoreConfig('rewardpoints/design/small_inline_image'))) {
            $imgName = Mage::getStoreConfig('rewardpoints/design/small_inline_image');
        } else if ($imgName == 'j2t_image_big.png' && Mage::getStoreConfig('rewardpoints/design/big_inline_image') && file_exists(Mage::getBaseDir("media") . DS . Mage::getStoreConfig('rewardpoints/design/big_inline_image'))) {
            $imgName = Mage::getStoreConfig('rewardpoints/design/big_inline_image');
        }

        $imgPathFull = Mage::getBaseDir("media") . DS . $imgName;

        $widht = $x;
        $y ? $height = $y : $height = $x;
        $resizeFolder = "j2t_resized";
        $imageResizedPath = Mage::getBaseDir("media") . DS . $resizeFolder . DS . $x . '-' . $imgName;

        if (!file_exists($imageResizedPath) && file_exists($imgPathFull)) {
            $imageObj = new Varien_Image($imgPathFull);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepTransparency(true);
            $imageObj->resize($widht, $height);
            $imageObj->save($imageResizedPath);
        }

        //return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$resizeFolder.DS.$imgName;
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $resizeFolder . '/' . $x . '-' . $imgName;
    }

    public function getProductPointsText($product, $noCeil = false, $from_list = false, $customer_group_id = null) {
        $math_method = Mage::getStoreConfig(self::XML_PATH_POINT_MATH_GATHER, Mage::app()->getStore()->getId());
        /* if ($math_method == 1 || $math_method == 2){
          $noCeil = true;
          } */

        //$points = $this->getProductPoints($product, $noCeil, $from_list);
        //getProductPoints($product, $noCeil = false, $from_list = false, $money_points = false, $tierprice_incl_tax = null, $tierprice_excl_tax = null, $customer_group_id = null){
        $point_no_ceil = $this->getProductPoints($product, true, $from_list, false, null, null, $customer_group_id);
        $points = $point_no_ceil;
        $img = '';

        $img_size = Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SIZE, Mage::app()->getStore()->getId());
        if (Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SHOW, Mage::app()->getStore()->getId()) && $img_size) {
            $img = '<img src="' . $this->getResizedUrl('j2t_image_small.png', $img_size, $img_size) . '" alt="' . Mage::helper('rewardpoints')->__("Reward points") . '" width="' . $img_size . '" height="' . $img_size . '" /> ';
        }
        //J2T CEIL MODIFICATION
        $points = ceil($points);

        if (!$points && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            //list all products in grouped item
            $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            $points = 0;
            foreach ($associatedProducts as $single_product) {
                $points += $this->getProductPoints($single_product, true, $from_list, false, null, null, $customer_group_id);
            }
        }

        if ($points && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $extraPointDetails = '';
            if ($cms_page = Mage::getStoreConfig('rewardpoints/product_page/cms_page')) {
                $extraPointDetails = ' <a class="about-point-scheme" href="' . Mage::getUrl($cms_page) . '" title="' . Mage::helper('rewardpoints')->__('Find more about this!') . '">' . Mage::helper('rewardpoints')->__('Find more about this!') . '</a>';
            }

            if ($from_list) {
                return '<p class="j2t-loyalty-points inline-points">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn up to %d loyalty point(s).", $points) . $this->getEquivalence($points) . $extraPointDetails . '</p>';
            } else {
                return '<p class="j2t-loyalty-points inline-points" style="display:none;">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn up to <span id='j2t-pts'>%d</span> loyalty point(s).", $points) . $this->getEquivalence($points) . $extraPointDetails . '</p>';
            }
        }

        if ($points && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
            //$return = '<p class="j2t-loyalty-points inline-points">'.$img. Mage::helper('rewardpoints')->__("With this product, you earn <span id='j2t-pts'>%d</span> loyalty point(s).", $points) . $this->getEquivalence($points) . '</p>';
            list($points_min, $points_max) = $this->_getMinimalBundleOptionsPoint($product, true, $from_list, false, $customer_group_id);

            $points_min = ceil($points_min + $point_no_ceil);
            $points_max = ceil($points_max + $point_no_ceil);

            $extraPointDetails = '';
            if ($cms_page = Mage::getStoreConfig('rewardpoints/product_page/cms_page')) {
                $extraPointDetails = ' <a class="about-point-scheme" href="' . Mage::getUrl($cms_page) . '" title="' . Mage::helper('rewardpoints')->__('Find more about this!') . '">' . Mage::helper('rewardpoints')->__('Find more about this!') . '</a>';
            }

            if ($from_list && $points_min == $points_max && $points_min == 0) {
                return '';
            } else if ($from_list && $points_min == $points_max) {
                return '<p class="j2t-loyalty-points inline-points">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn %d loyalty point(s).", $points_min) . $this->getEquivalence($points_min) . $extraPointDetails . '</p>';
            } else if ($from_list) {
                return '<p class="j2t-loyalty-points inline-points">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn from %d to %d loyalty point(s).", $points_min, $points_max) . $this->getEquivalence($points_min, $points_max) . $extraPointDetails . '</p>';
            } else {
                return '<p class="j2t-loyalty-points inline-points" style="display:none;">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn <span id='j2t-pts'>%d</span> loyalty point(s).", $points_min) . $this->getEquivalence($points_min) . $extraPointDetails . '</p>';
            }
        } else if ($points) {
            //$details_url = "";
            $extraPointDetails = "";
            if (Mage::registry('current_product') && Mage::registry('current_product')->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE && Mage::getStoreConfig('rewardpoints/default/show_details', Mage::app()->getStore()->getId())) {
                $point_details = unserialize(Mage::registry('current_product')->getPointDetails());
                if ($point_details && is_array($point_details) && sizeof($point_details)) {
                    foreach ($point_details as $point_detail) {
                        if ($point_detail && is_array($point_detail) && sizeof($point_detail)) {
                            foreach ($point_detail as $details) {
                                $extraPointDetails .= '<span class="inline-catalog-points-details">' . $details . '</span>';
                            }
                        }
                    }

                    $point_diff = $points - ceil(Mage::registry('current_product')->getPointRuleTotal());
                    if ($point_diff != 0) {
                        $extraPointDetails .= '<span class="inline-catalog-points-details">' . Mage::helper('rewardpoints')->__('%s point(s) calculation adjustment', '<span class="inline-point-items">' . $point_diff . '</span>') . '</span>';
                    }
                }
            }
            if ($extraPointDetails) {
                $extraPointDetails = ' <a style="display:none;" class="hide-details-points-url" href="javascript:hidePointDetailsViewPage(); void(0);" title="' . Mage::helper('rewardpoints')->__('Hide details') . '">' . Mage::helper('rewardpoints')->__('Hide details') . '</a><a class="show-details-points-url" href="javascript:showPointDetailsViewPage(); void(0);" title="' . Mage::helper('rewardpoints')->__('Show details') . '">' . Mage::helper('rewardpoints')->__('Show details') . '</a><span class="catalog-points-details" style="display:none;">' . $extraPointDetails . '</span>';
            }

            if ($cms_page = Mage::getStoreConfig('rewardpoints/product_page/cms_page')) {
                $extraPointDetails = ' <a class="about-point-scheme" href="' . Mage::getUrl($cms_page) . '" title="' . Mage::helper('rewardpoints')->__('Find more about this!') . '">' . Mage::helper('rewardpoints')->__('Find more about this!') . '</a>' . $extraPointDetails;
            }
            $return = '<p class="j2t-loyalty-points inline-points">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn <span id='j2t-pts'>%s</span> loyalty point(s).", $points) . $this->getEquivalence($points) . $extraPointDetails . '</p>';
            return $return;
        } else if ($from_list) {
            //try to get from price
            $attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', Mage::app()->getStore()->getId());
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED && !$attribute_restriction) {
                $product_default_points = $this->getDefaultProductPoints($product, Mage::app()->getStore()->getId(), $from_list, $noCeil);
                $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($product, $product_default_points);

                if ($catalog_points !== false) {
                    $_associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                    $product_points = array();
                    foreach ($_associatedProducts as $curent_asso_product) {
                        $product_points[] = $this->getProductPoints($curent_asso_product, $noCeil, $from_list, false, null, null, $customer_group_id);
                    }
                    if (sizeof($product_points)) {
                        $points_min = ceil(min($product_points));
                        return '<p class="j2t-loyalty-points inline-points">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn %d loyalty point(s).", $points_min) . $this->getEquivalence($points_min) . '</p>';
                    }
                }
            } else if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && !$attribute_restriction) {
                $product_default_points = $this->getDefaultProductPoints($product, Mage::app()->getStore()->getId(), $from_list, $noCeil);
                $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($product, $product_default_points);

                //Fix bundle prices
                if ($catalog_points !== false || $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    //if ($catalog_points !== false){
                    //get points
                    //Fix bundle prices
                    if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                        list($points_min, $points_max) = $this->_getMinimalBundleOptionsPoint($product, $noCeil, $from_list, false, $customer_group_id);
                    } else {
                        $_priceModel = $product->getPriceModel();
                        //list($_minimalPriceTax, $_maximalPriceTax) = $_priceModel->getTotalPrices($product, null, null, false);
                        //list($_minimalPriceInclTax, $_maximalPriceInclTax) = $_priceModel->getTotalPrices($product, null, true, false);
                        if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', Mage::app()->getStore()->getId())) {
                            if (version_compare(Mage::getVersion(), '1.5.0', '<')) {
                                list($_minimalPrice, $_maximalPrice) = $_priceModel->getPrices($product);
                            } else {
                                list($_minimalPrice, $_maximalPrice) = $_priceModel->getTotalPrices($product, null, null, false);
                            }
                        } else {
                            if (version_compare(Mage::getVersion(), '1.5.0', '<')) {
                                list($_minimalPrice, $_maximalPrice) = $_priceModel->getPrices($product);
                                $_minimalPrice = Mage::helper('tax')->getPrice($product, $_minimalPrice);
                                $_maximalPrice = Mage::helper('tax')->getPrice($product, $_maximalPrice, true);
                            } else {
                                list($_minimalPrice, $_maximalPrice) = $_priceModel->getTotalPrices($product, null, true, false);
                            }
                        }
                        /* $points_min = $this->convertProductMoneyToPoints($_minimalPrice);
                          $points_max = $this->convertProductMoneyToPoints($_maximalPrice); */
                        //J2T CEIL MODIFICATION
                        $points_min = ceil($this->convertProductMoneyToPoints($_minimalPrice));
                        $points_max = ceil($this->convertProductMoneyToPoints($_maximalPrice));
                    }
                    if ($from_list && $points_min == 0 && $points_max == 0) {
                        return '<p class="j2t-loyalty-points inline-points">' . $img . Mage::helper('rewardpoints')->__("With this product, earned points will depend on product configuration.") . '</p>';
                    } else if ($from_list && $points_min == $points_max) {
                        return '<p class="j2t-loyalty-points inline-points">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn %d loyalty point(s).", $points_min) . $this->getEquivalence($points_min) . '</p>';
                    } else if ($from_list) {
                        return '<p class="j2t-loyalty-points inline-points">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn from %d to %d loyalty point(s).", $points_min, $points_max) . $this->getEquivalence($points_min, $points_max) . '</p>';
                    } else {
                        return '<p class="j2t-loyalty-points inline-points" style="display:none;">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn <span id='j2t-pts'>%d</span> loyalty point(s).", $points_min) . $this->getEquivalence($points_min) . '</p>';
                    }
                }
            }
        }
        //J2T CEIL MODIFICATION
        $points_min = 0;
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            list($points_min, $points_max) = $this->_getMinimalBundleOptionsPoint($product, true, $from_list, false, $customer_group_id);
        }
        $points = ceil($points + $points_min);
        //$points = ceil($points);
        return '<p class="j2t-loyalty-points inline-points" style="display:none;">' . $img . Mage::helper('rewardpoints')->__("With this product, you earn <span id='j2t-pts'>%d</span> loyalty point(s).", $points) . $this->getEquivalence($points) . '</p>';
    }

    public function checkBundleMandatoryPrice($product, $type = 'min', $from_list = false, $customer_group_id = null) {
        $points_min = 0;
        $points_max = 0;
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            list($points_min, $points_max) = $this->_getMinimalBundleOptionsPoint($product, true, $from_list, true, $customer_group_id);
        }
        if ($type == 'min') {
            return $points_min;
        }
        return $points_max;
    }

    /**
     * Bundle product point min/max
     * @param object $product
     * @param boolean $noCeil
     * @param boolean $from_list
     * @param boolean $onlyUnicMandatory
     * @return int
     */
    protected function _getMinimalBundleOptionsPoint($product, $noCeil, $from_list, $onlyUnicMandatory = false, $customer_group_id = null) {
        if (version_compare(Mage::getVersion(), '1.8.0', '<')) {
            $optionCollection = $product->getTypeInstance()->getOptionsCollection();
            $selectionCollection = $product->getTypeInstance()->getSelectionsCollection($product->getTypeInstance()->getOptionsIds());
            $options = $optionCollection->appendSelections($selectionCollection);
        } else {
            $options = $product->getTypeInstance()->getOptions($product);
        }
        //$options = $product->getTypeInstance()->getOptions($product);
        $minimalPrice = 0;
        $minimalPriceWithTax = 0;
        $hasRequiredOptions = false;
        if ($options) {
            foreach ($options as $option) {
                if ($option->getRequired()) {
                    $hasRequiredOptions = true;
                }
            }
        }


        $selectionMinimalPoints = array();
        $selectionMinimalPointsWithTax = array();


        if (!$options) {
            return $minimalPrice;
        }

        $isPriceFixedType = ($product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED);

        $min_acc = 0;
        $max_acc = 0;

        foreach ($options as $option) {
            /* @var $option Mage_Bundle_Model_Option */
            $selections = $option->getSelections();
            if ($selections) {
                $current_val = 0;
                $current_vals = array();
                foreach ($selections as $selection) {
                    /* @var $selection Mage_Bundle_Model_Selection */
                    if (!$selection->isSalable()) {
                        continue;
                    }
                    //$item = $isPriceFixedType ? $product : $selection;
                    //$item = $selection;
                    $subprice = $product->getPriceModel()->getSelectionPreFinalPrice($product, $selection, 1);
                    //$subprice = $selection->getPrice();
                    //echo "{$selection->getId()} : $subprice // ";

                    $tierprice_incl_tax = Mage::helper('tax')->getPrice($product, $subprice, true);
                    $tierprice_excl_tax = Mage::helper('tax')->getPrice($product, $subprice);

                    $current_point = $this->getProductPoints($selection, $noCeil, $from_list, false, $tierprice_incl_tax, $tierprice_excl_tax, $customer_group_id);

                    //$current_point = $this->getProductPoints($item, $noCeil, $from_list);

                    $current_vals[] = $current_point;
                }

                if ($option->getRequired() && !$onlyUnicMandatory || ($option->getRequired() && $onlyUnicMandatory && sizeof($selections) == 1)) {
                    //if ($option->getRequired()){
                    $min_acc += min($current_vals);
                }
                $max_acc += max($current_vals);
            }
        }

        return array($min_acc, $max_acc);
    }

    public function getEquivalence($points, $points_max = 0) {
        $equivalence = '';
        $points = (int) $points;
        //if ($points > 0){
        if (Mage::getStoreConfig('rewardpoints/default/point_equivalence', Mage::app()->getStore()->getId())) {
            $formattedPrice = Mage::helper('core')->currency($this->convertPointsToMoneyEquivalence(floor($points)), true, false);
            if ($points_max) {
                $formattedMaxPrice = Mage::helper('core')->currency($this->convertPointsToMoneyEquivalence(floor($points_max)), true, false);
                $equivalence = ' <span class="j2t-point-equivalence">' . Mage::helper('rewardpoints')->__("%d points = %s and %d points = %s.", $points, $formattedPrice, $points_max, $formattedMaxPrice) . '</span>';
            } else {
                $equivalence = ' <span class="j2t-point-equivalence">' . Mage::helper('rewardpoints')->__("%d points = %s.", $points, $formattedPrice) . '</span>';
            }
        }
        //}

        return $equivalence;
    }

    public function checkPointsInsertionCustomer($customer_id, $store_id, $type, $linker = null) {
        $collection = Mage::getModel("rewardpoints/stats")->getCollection();
        $collection->getSelect()->columns(array("item_qty" => "COUNT(main_table.rewardpoints_account_id)"));
        $collection->getSelect()->where("main_table.customer_id = ?", $customer_id);
        $collection->getSelect()->where("main_table.store_id = ?", $store_id);
        $collection->getSelect()->where("main_table.order_id = ?", $type);

        if ($linker != null) {
            $collection->getSelect()->where("main_table.rewardpoints_linker = ?", $linker);
        }

        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $result = $db->query($collection->getSelect()->__toString());

        if (!$result) {
            return 0;
        }
        $rows = $result->fetch(PDO::FETCH_ASSOC);

        if (!$rows) {
            return 0;
        }
        return $rows['item_qty'];
    }

    public function processMathBaseValue($amount, $specific_rate = null, $usage = false) {
        $math_method = Mage::getStoreConfig(self::XML_PATH_POINT_MATH_GATHER, Mage::app()->getStore()->getId());
        if ($usage) {
            $math_method = Mage::getStoreConfig(self::XML_PATH_POINT_MATH_USAGE, Mage::app()->getStore()->getId());
        }
        if ($math_method == 1) {
            $amount = round($amount);
        } elseif ($math_method == 0) {
            $amount = floor($amount);
        }
        return $amount;
    }

    public function processMathValue($amount, $specific_rate = null, $usage = false) {
        $math_method = Mage::getStoreConfig(self::XML_PATH_POINT_MATH_GATHER, Mage::app()->getStore()->getId());
        if ($usage) {
            $math_method = Mage::getStoreConfig(self::XML_PATH_POINT_MATH_USAGE, Mage::app()->getStore()->getId());
        }
        if ($math_method == 1) {
            $amount = round($amount);
        } elseif ($math_method == 0) {
            $amount = floor($amount);
        }
        return $this->ratePointCorrection($amount, $specific_rate);
    }

    public function processMathValueCart($amount, $specific_rate = null, $usage = false) {
        $math_method = Mage::getStoreConfig(self::XML_PATH_POINT_MATH_GATHER, Mage::app()->getStore()->getId());
        if ($usage) {
            $math_method = Mage::getStoreConfig(self::XML_PATH_POINT_MATH_USAGE, Mage::app()->getStore()->getId());
        }
        if ($math_method == 1) {
            $amount = round($amount);
        } elseif ($math_method == 0) {
            $amount = floor($amount);
        }
        return $amount;
        //return $this->ratePointCorrection($amount, $specific_rate);
    }

    public function ratePointCorrection($points, $rate = null) {
        if ($rate == null) {
            $baseCurrency = Mage::app()->getBaseCurrencyCode();
            $currentCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
            $rate = Mage::getModel('directory/currency')->load($baseCurrency)->getRate($currentCurrency);
        }
        if (Mage::getStoreConfig('rewardpoints/default/process_rate', Mage::app()->getStore()->getId())) {
            /* if ($rate > 1){
              return $points * $rate;
              } else { */
            return $points / $rate;
            //}
        } else {
            return $points;
        }
    }

    public function rateMoneyCorrection($money, $rate = null) {
        if ($rate == null) {
            $baseCurrency = Mage::app()->getBaseCurrencyCode();
            $currentCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
            $rate = Mage::getModel('directory/currency')->load($baseCurrency)->getRate($currentCurrency);
        }

        if (Mage::getStoreConfig('rewardpoints/default/process_rate', Mage::app()->getStore()->getId())) {
            /* if ($rate < 1){
              return $money / $rate;
              } else {
              return $money * $rate;
              } */

            return $money * $rate;
        } else {
            return $money;
        }
    }

    public function isCustomProductPoints($product) {
        $product_default_points = $this->getDefaultProductPoints($product, Mage::app()->getStore()->getId());
        $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($product, $product_default_points);
        if ($catalog_points === false) {
            return true;
        }
        $attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', Mage::app()->getStore()->getId());
        $product_points = $product->getData('reward_points');

        $group_points = $this->getPointGroup($product);
        $product_points = ($group_points) ? $group_points : $product_points;

        if ($product_points > 0) {
            return true;
        }
        return false;
    }

    public function getProductPoints($product, $noCeil = false, $from_list = false, $money_points = false, $tierprice_incl_tax = null, $tierprice_excl_tax = null, $customer_group_id = null) {
        if ($from_list) {
            $product = Mage::getModel('catalog/product')->load($product->getId());
        }

        //special price verification
        /* if (Mage::getStoreConfig('rewardpoints/default/exclude_special', $storeId)){
          $specialprice = $product->getSpecialPrice();
          $specialPriceFromDate = $product->getSpecialFromDate();
          $specialPriceToDate = $product->getSpecialToDate();
          $today =  time();

          if ($specialprice){
          if($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)){
          return 0;
          }
          }
          } */

        //J2T TIER PRICE UPDATE
        $product_default_points = $this->getDefaultProductPoints($product, Mage::app()->getStore()->getId(), $money_points, $noCeil, false, null, $tierprice_incl_tax, $tierprice_excl_tax);
        $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($product, $product_default_points);

        if ($catalog_points === false) {
            return 0;
        }

        $attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', Mage::app()->getStore()->getId());
        $product_points = $product->getRewardPoints();

        $group_points = $this->getPointGroup($product, $customer_group_id);
        $product_points = ($group_points) ? $group_points : $product_points;

        $points_tobeused = 0;

        if ($product_points > 0) {
            $points_tobeused = $product_points + (int) $catalog_points;
            if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())) {
                if ((int) Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $points_tobeused) {
                    return Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
                }
            }
            return ($points_tobeused);
        } else if (!$attribute_restriction) {
            //get product price include vat

            $_finalPriceInclTax = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
            $_weeeTaxAmount = Mage::helper('weee')->getAmount($product);

            // fix for amount issue
            if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', Mage::app()->getStore()->getId())) {
                $price = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), false);
                $price = ($tierprice_excl_tax) ? $tierprice_excl_tax : $price;
            } else {
                $price = $_finalPriceInclTax + $_weeeTaxAmount;
                $price = ($tierprice_incl_tax !== null) ? $tierprice_incl_tax : $price;
            }
            // fix rounding points
            //$price = ceil($price);

            if ($money_points !== false) {
                $money_to_points = $money_points;
            } else {
                $money_to_points = Mage::getStoreConfig(self::XML_PATH_MONEY_POINTS, Mage::app()->getStore()->getId());
            }

            if ($money_to_points > 0) {
                if ($price) {
                    $price = $price * $money_to_points;
                }
                $points_tobeused = $this->processMathValue($price + (int) $catalog_points);
            } else {
                $points_tobeused += (int) $catalog_points;
            }


            if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())) {
                if ((int) Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $points_tobeused) {
                    return Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
                }
            }

            /* if ($noCeil)
              return $points_tobeused;
              else {
              return ceil($points_tobeused);
              } */
            //J2T CEIL MODIFICATION
            return $points_tobeused;
        }
        return 0;
    }

    public function convertMoneyToPoints($money, $no_correction = false, $quote = null, $verify_custom_usage = false, $apply_math = true) {
        $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
        if ($quote && $verify_custom_usage) {
            $points_to_get_money = $this->getCustomPointValue(null, $quote, $points_to_get_money, false);
        }
        $money_amount = ($apply_math) ? $this->processMathValue($money * $points_to_get_money) : ($money * $points_to_get_money);

        if ($no_correction) {
            return $money_amount;
        }
        return $this->rateMoneyCorrection($money_amount);
    }

    public function convertBaseMoneyToPoints($money) {
        $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
        $money_amount = $this->processMathBaseValue($money * $points_to_get_money);

        return $money_amount;
    }

    public function convertProductMoneyToPoints($money, $money_points = false) {
        if ($money_points !== false) {
            $points_to_get_money = $money_points;
        } else {
            $points_to_get_money = Mage::getStoreConfig(self::XML_PATH_MONEY_POINTS, Mage::app()->getStore()->getId());
        }

        $money_amount = $this->processMathValue($money * $points_to_get_money);
        return $this->rateMoneyCorrection($money_amount);
        //return $money_amount;
    }

    public function convertPointsToMoneyEquivalence($points_to_be_used, $store_id = null) {
        $store_id = ($store_id) ? $store_id : Mage::app()->getStore()->getId();
        $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/points_money', $store_id);
        //$return_value = $this->processMathValueCart($points_to_be_used/$points_to_get_money);
        $return_value = $points_to_be_used / $points_to_get_money;
        return $return_value;
    }

    public function convertPointsToMoney($points_to_be_used, $customer_id = null, $quote = null, $verify_custom_usage = false, $apply_math = true) {
        if ($customer_id != null) {
            $customerId = $customer_id;
        } else {
            $customerId = Mage::getModel('customer/session')
                    ->getCustomerId();
        }

        if ($quote == null) {
            $quote = Mage::helper('checkout/cart')->getCart()->getQuote();
        }


        $reward_model = Mage::getModel('rewardpoints/stats');
        $current = $reward_model->getPointsCurrent($customerId, $quote->getStoreId());

        // when allow direct usage is on
        if (Mage::getStoreConfig('rewardpoints/default/allow_direct_usage', $quote->getStoreId())) {
            $current += $this->getPointsOnOrder(null, $quote);
        }

        if ($current < $points_to_be_used) {
            Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('Not enough points available.'));
            Mage::helper('rewardpoints/event')->setCreditPoints(0);
            $quote
                    ->setRewardpointsQuantity(NULL)
                    ->setRewardpointsDescription(NULL)
                    ->setBaseRewardpoints(NULL)
                    ->setRewardpoints(NULL)
                    ->save();
            return 0;
        }
        $step = Mage::getStoreConfig('rewardpoints/default/step_value', $quote->getStoreId());
        $step_apply = Mage::getStoreConfig('rewardpoints/default/step_apply', $quote->getStoreId());
        if ($step > $points_to_be_used && $step_apply && !Mage::app()->getStore()->isAdmin()) {
            Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('The minimum required points is not reached.'));
            Mage::helper('rewardpoints/event')->setCreditPoints(0);
            $quote
                    ->setRewardpointsQuantity(NULL)
                    ->setRewardpointsDescription(NULL)
                    ->setBaseRewardpoints(NULL)
                    ->setRewardpoints(NULL)
                    ->save();
            return 0;
        }

        if ($step_apply && !Mage::app()->getStore()->isAdmin()) {
            if (($points_to_be_used % $step) != 0) {
                Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('Amount of points wrongly used.'));
                Mage::helper('rewardpoints/event')->setCreditPoints(0);
                $quote
                        ->setRewardpointsQuantity(NULL)
                        ->setRewardpointsDescription(NULL)
                        ->setBaseRewardpoints(NULL)
                        ->setRewardpoints(NULL)
                        ->save();
                return 0;
            }
        }

        $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/points_money', $quote->getStoreId());
        if ($quote && $verify_custom_usage && $points_to_get_money > 0) {
            $points_to_get_money = $this->getCustomPointValue(null, $quote, $points_to_get_money, false);
        }

        $discount_amount = $this->processMathValueCart(($points_to_be_used / $points_to_get_money), null, !$apply_math);

        return $discount_amount;
    }

    public function getPointsOnOrder($cartLoaded = null, $cartQuote = null, $specific_rate = null, $exclude_rules = false, $storeId = false, $money_points = false) {
        $rewardPoints = 0;
        $rewardPointsAtt = 0;

        $diable_points_coupon = false;
        $coupon = null;
        $j2tmulticoupon = null;
        if ($cartLoaded == null) {
            $quote = Mage::getModel('checkout/cart')->getQuote();
            $coupon = $quote->getCouponCode();
            $j2tmulticoupon = $quote->getJ2tMultiCoupon();
            $diable_points_coupon = Mage::getStoreConfig(self::XML_PATH_DISABLE_POINTS_COUPON, $quote->getStoreId());
            $storeId = (!$storeId && $quote->getStoreId()) ? $quote->getStoreId() : $storeId;
        } elseif ($cartQuote != null) {
            $coupon = $cartQuote->getCouponCode();
            $j2tmulticoupon = $cartQuote->getJ2tMultiCoupon();
            $diable_points_coupon = Mage::getStoreConfig(self::XML_PATH_DISABLE_POINTS_COUPON, $cartQuote->getStoreId());
            $storeId = (!$storeId && $cartQuote->getStoreId()) ? $cartQuote->getStoreId() : $storeId;
        } else {
            $coupon = $cartLoaded->getCouponCode();
            $j2tmulticoupon = $cartLoaded->getJ2tMultiCoupon();
            $diable_points_coupon = Mage::getStoreConfig(self::XML_PATH_DISABLE_POINTS_COUPON, $cartLoaded->getStoreId());
            $storeId = (!$storeId && $cartLoaded->getStoreId()) ? $cartLoaded->getStoreId() : $storeId;
        }

        if (($coupon || $j2tmulticoupon) && $diable_points_coupon) {
            return 0;
        }


        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        //verify if points from referral program and verify custom point value
        if (!$money_points) {
            $money_points = $this->getCustomPointValue($cartLoaded, $cartQuote, $money_points, true);
        }

        $customer_group_id = null;
        if ($cartLoaded == null) {
            $cartHelper = Mage::helper('checkout/cart');
            $customer_group_id = $cartHelper->getCart()->getCustomerGroupId();
            //J2T Fix Magento 1.3
            if (!$customer_group_id) {
                $customer_group_id = Mage::getModel('checkout/cart')->getQuote()->getCustomerGroupId();
            }
        } elseif ($cartQuote != null) {
            $customer_group_id = $cartQuote->getCustomerGroupId();
        } else {
            $customer_group_id = $cartLoaded->getCustomerGroupId();
        }

        //get points cart rule
        if (!$exclude_rules) {
            if ($cartLoaded != null) {
                $points_rules = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered($cartLoaded, $customer_group_id);
            } else {
                $points_rules = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered(null, $customer_group_id);
            }
            if ($points_rules === false) {
                return 0;
            }
            $rewardPoints += $this->processMathBaseValue($points_rules); //(int)$points_rules;
        }


        if ($cartLoaded == null) {
            //J2T Fix Magento 1.3
            if (version_compare(Mage::getVersion(), '1.4.0', '<')) {
                $items = Mage::getSingleton('checkout/cart')->getItems();
            } else {
                $cartHelper = Mage::helper('checkout/cart');
                $items = $cartHelper->getCart()->getItems();
            }
        } elseif ($cartQuote != null) {
            $items = $cartQuote->getAllItems();
        } else {
            $items = $cartLoaded->getAllItems();
        }

        $attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', $storeId);
        foreach ($items as $_item) {
            $item_points = 0;
            if ($_item->getParentItemId()) {
                if (!is_object($_item->getParentItem())) {
                    continue;
                }
                if ($cartLoaded == null || $cartQuote != null) {
                    $item_qty = $_item->getParentItem()->getQty();
                } else {
                    $item_qty = $_item->getParentItem()->getQtyOrdered();
                }
            } else {
                if ($cartLoaded == null || $cartQuote != null) {
                    $item_qty = $_item->getQty();
                } else {
                    $item_qty = $_item->getQtyOrdered();
                }
            }
            //fix to unloaded product into item
            if (!is_object($_item->getProduct()) && $_item->getProductId()) {
                $_item->setProduct(Mage::getModel('catalog/product')->load($_item->getProductId()));
            }

            $_item->setRewardpointsGathered(0);

            $_item->setRewardpointsCatalogRuleText(NULL);

            //BUNDLE FIX PRICE FIX
            if ($_item->getParentItemId()) {
                if (!is_object($_item->getParentItem()) || !is_object($_item->getParentItem()->getProduct())) {
                    continue;
                }
                //BUNDLE PRICE CALCULATION ON CHILDREN
                if (!$attribute_restriction && $_item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getParentItem()->getProduct()->getPrice() && $_item->getParentItem()->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                    continue;
                }
            } else if (!is_object($_item->getProduct()) || ($_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC)) {
                //TODO: check if add/remove points catalog point rules
                $child_points = 0;
                //if ($this->canBundleChildrendRule($storeId)){
                $child_points = Mage::getModel('rewardpoints/catalogpointrules')->getCatalogRulePointsGathered($_item->getProduct(), 0, $storeId, $item_qty, $customer_group_id, false, $_item, false, true);
                //} 


                $group_points = $this->getPointGroup($_item->getProduct(), $customer_group_id);
                if ($child_points === false) {
                    continue;
                } if (!$attribute_restriction && !$_item->getProduct()->getRewardPoints() && !$group_points) {
                    $_item->setRewardpointsGatheredFloat($child_points);
                    $_item->setRewardpointsGathered(ceil($child_points));
                    $rewardPoints += $child_points;
                } elseif ($group_points) {
                    $rewardPoints += $group_points * $item_qty;
                } elseif ($_item->getProduct()->getRewardPoints()) {
                    $rewardPoints += $_item->getProduct()->getRewardPoints() * $item_qty;
                }
                continue;
            }

            if (!is_object($_item->getProduct())) {
                continue;
            }

            $item_default_points = $this->getItemPoints($_item, $storeId, $money_points, true, $customer_group_id);

            //J2T Fix Missing object
            if (!is_object($_item->getProduct()) && $_item->getProductId()) {
                $_item->setProduct(Mage::getModel('catalog/product')->load($_item->getProductId()));
            }

            //BUNDLE FIX PRICE FIX
            if ($_item->getHasChildren() &&
                    (
                    ($_item->getProduct()->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE || $_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getProduct()->getPrice() == 0) ||
                    ($_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED)
                    )
            ) {
                $child_points = 0;
                //if ($this->canBundleChildrendRule($storeId)){
                //TODO: check catalog rule points on bundle fix price
                $child_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($_item->getProduct(), $item_default_points, $storeId, $item_qty, $customer_group_id, $_item);
                //}

                $group_points = $this->getPointGroup($_item->getProduct(), $customer_group_id);
                if ($child_points === false) {
                    continue;
                } elseif (!$attribute_restriction && !$_item->getProduct()->getRewardPoints() && !$group_points) {
                    $item_points += $this->getItemPoints($_item, $storeId, $money_points, true, $customer_group_id);
                    //check catalog points' rules and add/remove value to fixed price bundle products
                    $item_points += $child_points;
                    $rewardPoints += $item_points;
                } elseif ($group_points) {
                    $rewardPoints += $group_points * $item_qty;
                } elseif ($_item->getProduct()->getRewardPoints()) {
                    $rewardPoints += $_item->getProduct()->getRewardPoints() * $item_qty;
                }

                $_item->setRewardpointsGatheredFloat($item_points);
                $_item->setRewardpointsGathered(ceil($item_points));


                continue;
            } else if ($_item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getProduct()->getPrice()) {
                $child_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($_item->getProduct(), $item_default_points, $storeId, $item_qty, $customer_group_id, $_item);
            }

            $_product = Mage::getModel('catalog/product')->load($_item->getProductId());
            $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($_product, $item_default_points, $storeId, $item_qty, $customer_group_id, $_item);

            $xtra_points = 0;
            if ($catalog_points === false) {
                continue;
            } else if (!$attribute_restriction && $catalog_points) {
                $xtra_points = $this->processMathBaseValue($catalog_points * $item_qty);
                $item_points += $xtra_points;
                //$rewardPoints += $item_points;
            }
            $product_points = $_product->getData('reward_points');

            $group_points = $this->getPointGroup($_product, $customer_group_id);
            $product_points = ($group_points) ? $group_points : $product_points;


            if ($product_points > 0) {
                if ($_item->getQty() > 0 || $_item->getQtyOrdered() > 0) {
                    $item_points += $this->processMathBaseValue($product_points * $item_qty);
                    $rewardPointsAtt += $item_points;
                }
            } else if (!$attribute_restriction) {
                //check if product is option product (bundle product)
                if (!$_item->getParentItemId() && $_item->getProduct()->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    $item_points += $this->getItemPoints($_item, $storeId, $money_points, true, $customer_group_id);
                    $rewardPoints += $item_points;
                } else if ($_item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    $item_points += $this->getItemPoints($_item, $storeId, $money_points, true, $customer_group_id);
                    $rewardPoints += $item_points;
                }
            }

            //$_item->setRewardpointsGathered(ceil($item_points+$xtra_points));
            $_item->setRewardpointsGatheredFloat($item_points);
            $_item->setRewardpointsGathered(ceil($item_points));
        }

        $rewardPoints = $this->processMathBaseValue($this->processMathValue($rewardPoints, $specific_rate) + $rewardPointsAtt);

        if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $storeId)) {
            if ((int) Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $storeId) < $rewardPoints) {
                return ceil(Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $storeId));
            }
        }

        $rewardPoints = $this->extraMathPoints($rewardPoints, $storeId);
        $points = ceil($rewardPoints);

        $object_tovalidate = $cartLoaded;
        if ($object_tovalidate === null && $cartQuote != null) {
            $object_tovalidate = $cartQuote;
        } else if (is_object(Mage::getSingleton('checkout/cart'))) {
            $object_tovalidate = Mage::getSingleton('checkout/cart')->getQuote();
        }

        $points = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered($cartLoaded, $customer_group_id, true, $points, false);
        //Only add/remove
        $points = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered($cartLoaded, $customer_group_id, false, $points, false, true);

        $points = $this->extraMathPoints($points, $storeId);

        return ceil($points);
    }

    protected function extraMathPoints($points, $storeId) {
        $min = Mage::getStoreConfig(self::XML_PATH_MIN_POINT_COLLECTION, $storeId);
        if ($min && $min > $points) {
            $points = 0;
        }

        $steps = Mage::getStoreConfig(self::XML_PATH_GATHER_STEP, $storeId);
        if ($steps && $steps < $points && $points > 0) {
            $current = $steps;
            while ($current < $points) {
                $current += $steps;
            }
            if ($current > $points) {
                $current -= $steps;
            }
            $points = $current;
        }

        return $points;
    }

    protected function getDefaultProductPoints($product, $storeId, $money_points = false, $noCeil = true, $check_discount = false, $_item = null, $tierprice_incl_tax = null, $tierprice_excl_tax = null) {
        $points = 0;
        if (!is_object($product)) {
            return 0;
        }

        //special price verification
        //J2T TIER PRICE UPDATE
        if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $storeId) && $tierprice_excl_tax !== null) {
            return $tierprice_excl_tax;
        } else if ($tierprice_incl_tax !== null) {
            return $tierprice_incl_tax;
        }

        $_finalPriceInclTax = ($_item && $_item->getBasePriceInclTax()) ? $_item->getBasePriceInclTax() : Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
        $_weeeTaxAmount = ($_item && $_item->getBaseWeeeTaxAppliedAmount()) ? $_item->getBaseWeeeTaxAppliedAmount() : Mage::helper('weee')->getAmount($product);


        $item_qty = 1;
        if ($_item != null) {
            if ($_item->getQty()) {
                $item_qty = $_item->getQty();
            } elseif ($_item->getQtyOrdered()) {
                $item_qty = $_item->getQtyOrdered();
            }
        }

        if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $storeId)) {
            if ($_item != null && (
                    !$_item->getParentItemId() || (
                    $_item->getParentItemId() && $_item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getParentItem()->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC)
                    )
            ) {
                //if ($_item != null && !$_item->getParentItemId()){
                //J2T Fix Magento 1.3
                if (version_compare(Mage::getVersion(), '1.4.0', '<')) {
                    $price = ($_item->getBaseRowTotal() - $_item->getBaseDiscountAmount()) / $item_qty;
                } else {
                    $price = $_item->getBasePrice(); // / $item_qty;
                }
            } else {
                $price = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), false);
            }
        } else {
            if ($_item != null && (
                    !$_item->getParentItemId() || (
                    $_item->getParentItemId() && $_item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $_item->getParentItem()->getProduct()->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC)
                    )
            ) {
                //J2T Fix Magento 1.3
                if (version_compare(Mage::getVersion(), '1.4.0', '<')) {
                    $price = ($_item->getBaseRowTotal() - $_item->getBaseDiscountAmount() + $_item->getBaseTaxAmount()) / $item_qty;
                } else {
                    $price = $_item->getBasePriceInclTax(); // / $item_qty;
                }
            } else {
                $price = $_finalPriceInclTax + $_weeeTaxAmount;
            }
        }

        if ($check_discount && $_item != null && Mage::getStoreConfig('rewardpoints/default/remove_discount', $storeId)) {
            if ($_item->getBaseDiscountAmount()) {
                $price -= $_item->getBaseDiscountAmount() / $item_qty;
                //base_tax_refunded
                if (!Mage::getStoreConfig('rewardpoints/default/exclude_tax', $storeId)) {
                    $price -= $_item->getBaseTaxRefunded() / $item_qty;
                }
            } else if (/* $_item->getHasChildren() && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && */($children = $_item->getChildren())) {
                //check discount on children
                $total_child_discount = 0;
                foreach ($children as $child_item) {
                    if ($child_item->getQty()) {
                        $child_item_qty = $child_item->getQty();
                    } elseif ($child_item->getQtyOrdered()) {
                        $child_item_qty = $child_item->getQtyOrdered();
                    }
                    if (!$child_item_qty)
                        $child_item_qty = 1;
                    $total_child_discount += $child_item->getBaseDiscountAmount() / $child_item_qty;
                }
                if ($total_child_discount) {
                    $price -= $total_child_discount;
                }
            }
        }


        //FIX Refund recalculation
        if ($_item != null) {
            if ($_item->getBaseAmountRefunded()) {
                $price -= $_item->getBaseAmountRefunded() / $item_qty;
            }
        }
        if ($price <= 0) {
            return 0;
        }
        //END FIX Refund recalculation

        if ($money_points !== false) {
            $points = $this->processMathBaseValue($money_points * $price);
        } else {
            $points = $this->processMathBaseValue(Mage::getStoreConfig(self::XML_PATH_MONEY_POINTS, $storeId) * $price);
        }
        //if (!$noCeil){
        /* $points = ceil($points); */
        //J2T CEIL MODIFICATION
        //}
        return $points;
    }

    protected function getItemPoints($_item, $storeId, $money_points = false, $check_discount = false, $customer_group_id = null) {
        //$_product = Mage::getModel('catalog/product')->load($_item->getProductId());
        //$points = $_product->getData('reward_points');
        //if ($points > 0){
        /* $price = $this->getSubtotalInclTax($_item, $storeId);

          if ($money_points !== false){
          $points = $this->processMathBaseValue($money_points * $price);
          } else {
          $points = $this->processMathBaseValue(Mage::getStoreConfig('rewardpoints/default/money_points', $storeId) * $price);
          }
          $points = ceil($points); */
        //}
        //return $points;


        $item_qty = 1;
        $bundle_qty = 1;
        if ($_item->getParentItemId()) {
            if ($_item->getParentItem()->getQty()) {
                $item_qty = $_item->getParentItem()->getQty();
            } elseif ($_item->getParentItem()->getQtyOrdered()) {
                $item_qty = $_item->getParentItem()->getQtyOrdered();
            }
        } else {
            if ($_item->getQty()) {
                $item_qty = $_item->getQty();
            } elseif ($_item->getQtyOrdered()) {
                $item_qty = $_item->getQtyOrdered();
            }
        }
        //fix to unloaded product into item
        if ($_item->getParentItem() && !is_object($_item->getParentItem()->getProduct()) && $_item->getParentItem()->getProductId()) {
            $_item->getParentItem()->setProduct(Mage::getModel('catalog/product')->load($_item->getParentItem()->getProductId()));
        }

        if ($_item->getParentItem() && $_item->getParentItem()->getProduct() && $_item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && ($_item->getQty() || $_item->getQtyOrdered())) {
            $bundle_qty = ($_item->getQtyOrdered()) ? $_item->getQtyOrdered() : $_item->getQty();
        }
        $points_acc = $this->getDefaultProductPoints($_item->getProduct(), $storeId, $money_points, true, $check_discount, $_item);
        if ($_item->getParentItem() && $_item->getParentItem()->getProduct() && $_item->getParentItem()->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $this->canBundleChildrendRule($storeId)) {

            $cat_points = Mage::getModel('rewardpoints/catalogpointrules')->getCatalogRulePointsGathered($_item->getParentItem()->getProduct(), $points_acc, $storeId, 1, null, true, $_item->getParentItem(), true);
            $attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', $storeId);

            $group_points = $this->getPointGroup($_item->getProduct(), $customer_group_id);

            if ($cat_points === false) {
                $points_acc = 0;
            } else if (!$attribute_restriction && !$_item->getProduct()->getRewardPoints() && !$group_points) {
                $points_acc += $cat_points;
            } else if ($group_points) {
                $points_acc += $group_points;
            } else {
                $points_acc += $_item->getProduct()->getRewardPoints();
            }
        }
        $points_acc = $points_acc * $bundle_qty * $item_qty;
        return $points_acc;
    }

    protected function getSubtotalInclTax($item, $storeId) {
        $baseTax = ($item->getTaxBeforeDiscount() ? $item->getTaxBeforeDiscount() : ($item->getTaxAmount() ? $item->getTaxAmount() : 0));
        $tax = ($item->getBaseTaxBeforeDiscount() ? $item->getBaseTaxBeforeDiscount() : ($item->getBaseTaxAmount() ? $item->getBaseTaxAmount() : 0));
        $discount_amount = $item->getBaseDiscountAmount();

        if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $storeId)) {
            return $item->getBaseRowTotal() - $discount_amount;
        }
        //Zend_Debug::dump($item->debug());
        return $item->getBaseRowTotal() + $tax - $discount_amount;
    }

}
