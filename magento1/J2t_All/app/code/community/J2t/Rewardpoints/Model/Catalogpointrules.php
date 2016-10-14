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

class J2t_Rewardpoints_Model_Catalogpointrules extends Mage_Rule_Model_Rule
{
    const RULE_TYPE_CART  = 1;
    const RULE_TYPE_DATAFLOW   = 2;

    const RULE_ACTION_TYPE_ADD = 1;
    const RULE_ACTION_TYPE_DONTPROCESS = 2;
    const RULE_ACTION_TYPE_MULTIPLY = -1;
    const RULE_ACTION_TYPE_DIVIDE = -2;

    protected $_types;
    protected $_action_types;

    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/catalogpointrules');
        //('rewardpoints/catalogpointrules')->checkRule($to_validate);
        $this->_types = array(
            self::RULE_TYPE_CART     => Mage::helper('rewardpoints')->__('Cart rule'),
            self::RULE_TYPE_DATAFLOW   => Mage::helper('rewardpoints')->__('Import rule'),
        );
        $this->_action_types = array(
            self::RULE_ACTION_TYPE_ADD     => Mage::helper('rewardpoints')->__('Add / remove points'),
            self::RULE_ACTION_TYPE_DONTPROCESS   => Mage::helper('rewardpoints')->__("Don't process points"),
            self::RULE_ACTION_TYPE_MULTIPLY   => Mage::helper('rewardpoints')->__("Multiply By"),
            self::RULE_ACTION_TYPE_DIVIDE   => Mage::helper('rewardpoints')->__("Divide By"),
        );
    }


    public function ruletypesToOptionArray()
    {
        return $this->_toOptionArray($this->_types);
    }

    public function ruletypesToArray()
    {
        return $this->_toArray($this->_types);
    }

    public function ruleActionTypesToOptionArray()
    {
        return $this->_toOptionArray($this->_action_types);
    }

    public function ruleActionTypesToArray()
    {
        return $this->_toArray($this->_action_types);
    }

    protected function _toOptionArray($array)
    {
        $res = array();
        foreach ($array as $value => $label) {
        	$res[] = array('value' => $value, 'label' => $label);
        }
        return $res;
    }

    protected function _toArray($array)
    {
        $res = array();
        foreach ($array as $value => $label) {
            $res[$value] = $label;
        }
        return $res;
    }


    public function getConditionsInstance()
    {
        return Mage::getModel('rewardpoints/catalogpointrule_condition_combine');
    }


    public function checkRule($to_validate)
    {

        $storeId = Mage::app()->getStore($request->getStore())->getId();
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        if ($to_validate->getCustomerGroupId()){
            $customerGroupId = $to_validate->getCustomerGroupId();
        } else {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }

        $rules = Mage::getModel('rewardpoints/catalogpointrules')->getCollection()->setValidationFilter($websiteId, $customerGroupId, $couponCode);

        //$rules = Mage::getModel('rewardpoints/catalogpointrules')->getCollection();
        foreach($rules as $rule)
        {
            //echo "<br /> RULE ID : {$rule->getRuleId()}<br/>";
            if (!$rule->getStatus()) continue;
            $rule_validate = Mage::getModel('rewardpoints/catalogpointrules')->load($rule->getRuleId());

            if ($rule_validate->validate($to_validate)){
                //regle ok
                //echo "ok";
                Mage::getModel('rewardpoints/subscriptions')->updateSegments($to_validate->getEmail(), $rule);
            } else {
                //regle ko
                //echo "ko";
                Mage::getModel('rewardpoints/subscriptions')->unsubscribe($to_validate->getEmail(), $rule);

            }
        }

    }

    public function getPointrulesByIds($ids)
    {
        $segmentsids = explode(',', $ids);
        $segmentstitles = array();
        foreach ($segmentsids as $segmentid)
        {
            $collection = $this->getCollection();
            $collection->getSelect()
                       ->where('rule_id = ?', $segmentid);
            $row = $collection->getFirstItem();
            $segmentstitles[] = $row->getTitle();
        }
        return implode(',', $segmentstitles);
    }

    public function getSegmentsRule()
    {
        $segments = array();
        $collection = $this->getCollection();
        $collection->getSelect()
                   ->order('title');
        $collection->load();

        foreach ($collection as $key=>$values)
        {
            $segments[]=array('label'=>$values->getTitle() ,'value'=>$values->getRuleId());
        }
        return $segments;
    }
    
    public function getCatalogPointsByCart(){
        $points = 0;
        $_cart_products = Mage::getModel("checkout/cart")->getItems();
        foreach($items as $item) {
            if($item->getProduct()->getId()) {
                //get product et cart quantity
                $product = Mage::getModel("catalog/product")->load($item->getProduct()->getId());
                //JON
                $item_default_points = $this->getItemPoints($item, Mage::app()->getStore()->getId());
                $points = getAllCatalogRulePointsGathered($product, $item_default_points);
                if ($points === false){
                    return false;
                } elseif ($points > 0){
                    $points = $points * $item->getQty();
                }
            }
        }
        return $points;
    }


    public function getAllCatalogRulePointsGathered($product = null, $item_default_points = null, $storeId = false, $default_qty = 1, $customerGroupId = null, $quote_item = null)
    {
        $points = $this->getCatalogRulePointsGathered($product, $item_default_points, $storeId, $default_qty, null, null, $quote_item);   
        return $points;
    }
    
    public function getCatalogRulePointsJson($to_validate, $storeId = false, $default_qty = 1, $customerGroupId = null)
    {
        if (!$storeId){
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        if ($to_validate->getCustomerGroupId() && $customerGroupId == null){
            $customerGroupId = $to_validate->getCustomerGroupId();
        } else if ($customerGroupId == null){
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }

        $rules = Mage::getModel('rewardpoints/catalogpointrules')->getCollection()->setValidationFilter($websiteId, $customerGroupId);
        $return_val = array();
        foreach($rules as $rule)
        {
            if (!$rule->getStatus()) continue;
            $return_val[] = $rule->getData();
        }
        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            return Mage::helper('core')->jsonEncode($return_val);
        } else {
            return Zend_Json::encode($return_val);
        }
    }
    
    public function validate(Varien_Object $object) {
        return parent::validate($object);
    }
    
    //BUNDLE FIX PRICE FIX - $onlyMultiplyDivide
    public function getCatalogRulePointsGathered($to_validate, $item_default_points = null, $storeId = false, $default_qty = 1, $customerGroupId = null, $onlyMultiplyDivide = false, $quote_item = null, $forceStop = false, $onlyAddRemove = false)
    {
        $points = 0;
        
        if (!$storeId){
            $storeId = Mage::app()->getStore()->getId();
        }
        
        
        
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        if ($to_validate->getCustomerGroupId() && $customerGroupId == null){
            $customerGroupId = $to_validate->getCustomerGroupId();
        } else if ($customerGroupId == null){
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }

        $rules = Mage::getModel('rewardpoints/catalogpointrules')->getCollection()->setValidationFilter($websiteId, $customerGroupId);
        $points_temp = 0;
        
        $rules_message_cart = array();
        $cpt = 0;
        $multiply = 0;
        $divide = 0;
        
        $mulordiv = false;
        
        
        foreach($rules as $rule)
        {
            if (!$rule->getStatus()) continue;
            
            $rule_validate = Mage::getModel('rewardpoints/catalogpointrules')->load($rule->getRuleId());
            
            if ($rule_validate->validate($to_validate)){
                $cpt++;
                $message = "";
                if (($labels = $rule_validate->getLabelsSummary()) && $labels_array = unserialize($rule_validate->getLabelsSummary())){
                    if(isset($labels_array[$storeId]) && trim($labels_array[$storeId]) != ""){
                        $message = $labels_array[$storeId];
                    }
                }
                
                if (($rule_validate->getActionType() == self::RULE_ACTION_TYPE_DONTPROCESS && !$onlyMultiplyDivide) || ( $rule_validate->getActionType() == self::RULE_ACTION_TYPE_DONTPROCESS && $forceStop )){
                    return false;
                } else if ($rule_validate->getActionType() == self::RULE_ACTION_TYPE_MULTIPLY && !$onlyAddRemove){
                    $multiply = ($rule_validate->getPoints() <= 0) ? 1 : $rule_validate->getPoints();
                    $item_default_points = $item_default_points / $default_qty;
                    $points_temp = ($item_default_points * $multiply);
                    if ($mulordiv){
                        $points += $points_temp;
                    } else {
                        $points += $points_temp - $item_default_points;
                    }
                    if ($message){
                        $rules_message_cart[] = Mage::helper('rewardpoints')->__('points multiplied by %s, %s (%s points)', $rule_validate->getPoints(), $message, ceil($points_temp));
                    } else {
                        $rules_message_cart[] = Mage::helper('rewardpoints')->__('points multiplied by %s', $rule_validate->getPoints());
                    }
                    $mulordiv = true;
                } else if ($rule_validate->getActionType() == self::RULE_ACTION_TYPE_DIVIDE && !$onlyAddRemove){
                    $divide = ($rule_validate->getPoints() <= 0) ? 1 : $rule_validate->getPoints();
                    
                    $item_default_points = $item_default_points / $default_qty;
                    $points_temp = $item_default_points / $divide;
                    
                    if ($mulordiv){
                        $points += $points_temp;
                    } else {
                        $points += $points_temp - $item_default_points;
                    }
                    
                    if ($message){
                        $rules_message_cart[] = Mage::helper('rewardpoints')->__('points divided by %s, %s (%s points)', $rule_validate->getPoints(), $message, ceil($points_temp));
                    } else {
                        $rules_message_cart[] = Mage::helper('rewardpoints')->__('points divided by %s', $rule_validate->getPoints());
                    }
                    $mulordiv = true;
                    
                } else if ((!$onlyMultiplyDivide || $onlyAddRemove) && $rule_validate->getActionType() == self::RULE_ACTION_TYPE_ADD) {
                    $points += $rule_validate->getPoints();
                    
                    if ($rule_validate->getPoints() > 0){
                       if ($message){
                           $rules_message_cart[] = Mage::helper('rewardpoints')->__("%s extra points added, %s", $rule_validate->getPoints(), $message);
                       } else {
                           $rules_message_cart[] = Mage::helper('rewardpoints')->__("%s extra points added", $rule_validate->getPoints());
                       }
                   } else {
                       if ($message){
                           $rules_message_cart[] = Mage::helper('rewardpoints')->__("%s points substracted, %s", $rule_validate->getPoints(), $message);
                       } else {
                           $rules_message_cart[] = Mage::helper('rewardpoints')->__("%s points substracted", $rule_validate->getPoints());
                       }
                   }
                }
            } else {
                
            }
            
        }
        
        if (sizeof($rules_message_cart) && is_object($to_validate) && $quote_item){
            //rewardpoints_catalog_rule_text
            $quote_item->setRewardpointsCatalogRuleText(serialize($rules_message_cart));
        } else if (sizeof($rules_message_cart) && is_object($to_validate) && get_class($to_validate) == "Mage_Catalog_Model_Product" && Mage::registry('current_product') && is_object(Mage::registry('current_product')) && Mage::registry('current_product')->getId()){
            $point_details = unserialize(Mage::registry('current_product')->getPointDetails());
            if ($point_details && is_array($point_details) && sizeof($point_details)){
                $point_details[$to_validate->getId()] = $rules_message_cart;
                Mage::registry('current_product')->setPointDetails(serialize($point_details));
                Mage::registry('current_product')->setPointRuleTotal($points+$item_default_points);
            } else {
                Mage::registry('current_product')->setPointDetails(serialize(array($to_validate->getId() => $rules_message_cart)));
                Mage::registry('current_product')->setPointRuleTotal($points+$item_default_points);
            }
        }
        return $points;
    }


    public function validateVarienData(Varien_Object $object)
    {
        if($object->getData('from_date') && $object->getData('to_date')){
            $dateStartUnixTime = strtotime($object->getData('from_date'));
            $dateEndUnixTime   = strtotime($object->getData('to_date'));

            if ($dateEndUnixTime < $dateStartUnixTime) {
                return array(Mage::helper('rule')->__("End Date should be greater than Start Date"));
            }
        }
        return true;
    }

}
