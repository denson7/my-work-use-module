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

class J2t_Rewardpoints_Model_Pointrules extends Mage_Rule_Model_Rule
{
    const RULE_TYPE_CART  = 1;
    const RULE_TYPE_DATAFLOW   = 2;

    const RULE_ACTION_TYPE_ADD = 1;
    const RULE_ACTION_TYPE_DONTPROCESS = 2;
    const RULE_ACTION_TYPE_DONTPROCESS_USAGE = 3;
    const RULE_ACTION_TYPE_MULTIPLY = -1;
    const RULE_ACTION_TYPE_DIVIDE = -2;

    protected $_types;
    protected $_action_types;

    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/pointrules');
        //('rewardpoints/pointrules')->checkRule($to_validate);
        $this->_types = array(
            self::RULE_TYPE_CART     => Mage::helper('rewardpoints')->__('Cart rule'),
            self::RULE_TYPE_DATAFLOW   => Mage::helper('rewardpoints')->__('Import rule'),
        );
        $this->_action_types = array(
            self::RULE_ACTION_TYPE_ADD     => Mage::helper('rewardpoints')->__('Add / remove points'),
            self::RULE_ACTION_TYPE_DONTPROCESS   => Mage::helper('rewardpoints')->__("Don't process points"),
            self::RULE_ACTION_TYPE_DONTPROCESS_USAGE   => Mage::helper('rewardpoints')->__("Don't process point usage"),
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
        return Mage::getModel('rewardpoints/rule_condition_combine');
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
        $rules = Mage::getModel('rewardpoints/pointrules')->getCollection()->setValidationFilter($websiteId, $customerGroupId, $couponCode);
        foreach($rules as $rule)
        {
            if (!$rule->getStatus()) continue;
            $rule_validate = Mage::getModel('rewardpoints/pointrules')->load($rule->getRuleId());

            if ($rule_validate->validate($to_validate)){
                //regle ok
                Mage::getModel('rewardpoints/subscriptions')->updateSegments($to_validate->getEmail(), $rule);
            } else {
                //regle ko
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

    /*
     *  $points = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered($cartLoaded, $customer_group_id, true, $points, false);
        
        $points = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered($cartLoaded, $customer_group_id, false, $points, false, true);
        
     */
    
    
    public function getAllRulePointsGathered($cart = null, $customerGroupId = null, $multiDiv = false, $points = null, $reset_message = true, $only_add_remove = false)
    {
        if ($cart == null){
            $cart = Mage::getSingleton('checkout/cart');
        }
        $points = $this->getRulePointsGathered($cart, $customerGroupId, false, $multiDiv, $points, $reset_message, $only_add_remove);
        return $points;
    }

    public function getRulePointsGathered($to_validate, $customerGroupId = null, $usage = false, $multiDiv = false, $point_value = null, $reset_message = true, $only_add_remove = false)
    {
        $points = 0;
        $storeId = $to_validate->getStoreId();
        if (!$storeId){
            $storeId = Mage::app()->getStore()->getId();
        }
        
        if (is_object($to_validate) && $to_validate->getQuote() && is_object($to_validate->getQuote()) 
                && $to_validate->getQuote()->getId() && $reset_message) {
            $to_validate->getQuote()->setRewardpointsCartRuleText(NULL);
        }
        
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        if ($to_validate->getCustomerGroupId() && $customerGroupId == null){
            $customerGroupId = $to_validate->getCustomerGroupId();
        } else if ($customerGroupId == null){
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
		
        $collection = Mage::getModel('rewardpoints/pointrules')->getCollection();
        $rules = $collection->setValidationFilter($websiteId, $customerGroupId);
        
        if ($only_add_remove) {
            $rules->addFieldToFilter('action_type', array('in' => array(self::RULE_ACTION_TYPE_ADD)));
        } else {
            if (!$multiDiv){
                $rules->addFieldToFilter('action_type', array('nin' => array(self::RULE_ACTION_TYPE_MULTIPLY, self::RULE_ACTION_TYPE_DIVIDE)));
            } else {
                $rules->addFieldToFilter('action_type', array('in' => array(self::RULE_ACTION_TYPE_MULTIPLY, self::RULE_ACTION_TYPE_DIVIDE)));
            }

            if (!$usage){
                $rules->addFieldToFilter('action_type', array('neq' => self::RULE_ACTION_TYPE_DONTPROCESS_USAGE));
            } else {
                $rules->addFieldToFilter('action_type', self::RULE_ACTION_TYPE_DONTPROCESS_USAGE);
            }
        }
        
        
        $rules_message_cart = array();
        foreach($rules as $rule)
        {
            if (!$rule->getStatus()) continue;
            $rule_validate = Mage::getModel('rewardpoints/pointrules')->load($rule->getRuleId());
            
            if ($rule_validate->validate($to_validate)){
                //rule ok
                $message = "";
                if (($labels = $rule_validate->getLabelsSummary()) && $labels_array = unserialize($rule_validate->getLabelsSummary())){
                    if(isset($labels_array[$storeId]) && trim($labels_array[$storeId]) != ""){
                        $message = $labels_array[$storeId];
                    }
                }
                
                if ($multiDiv && $point_value && $rule_validate->getActionType() == self::RULE_ACTION_TYPE_MULTIPLY && $rule_validate->getPoints()){
                    $point_value_tmp = $point_value * $rule_validate->getPoints();
                    if ($message){
                        $rules_message_cart[] = Mage::helper('rewardpoints')->__('Multiplication (%s points x %s = %s points, %s)', $point_value, $rule_validate->getPoints(), ceil($point_value_tmp), $message);
                    } else {
                        $rules_message_cart[] = Mage::helper('rewardpoints')->__('Multiplication (%s points x %s = %s points)', $point_value, $rule_validate->getPoints(), ceil($point_value_tmp));
                    }
                    $point_value = $point_value_tmp;
                } else if ($multiDiv && $point_value && $rule_validate->getActionType() == self::RULE_ACTION_TYPE_DIVIDE && $rule_validate->getPoints()) {
                    $point_value_tmp = $point_value / $rule_validate->getPoints();
                    if ($message){
                        $rules_message_cart[] = Mage::helper('rewardpoints')->__('Division (%s points / %s = %s points, %s)', $point_value, $rule_validate->getPoints(), ceil($point_value_tmp), $message);
                    } else {
                        $rules_message_cart[] = Mage::helper('rewardpoints')->__('Division (%s points / %s = %s points)', $point_value, $rule_validate->getPoints(), ceil($point_value_tmp));
                    }
                    $point_value = $point_value_tmp;
                } else if ($rule_validate->getActionType() == self::RULE_ACTION_TYPE_DONTPROCESS || $rule_validate->getActionType() == self::RULE_ACTION_TYPE_DONTPROCESS_USAGE){
                    return false;
                } else if (($rule_validate->getActionType() != self::RULE_ACTION_TYPE_DONTPROCESS && ($point_value || $point_value === 0)) /*|| ($rule_validate->getActionType() == self::RULE_ACTION_TYPE_ADD)*/) {
                    $points_tmp = $rule_validate->getPoints() + $point_value; 
                    if ($rule_validate->getPoints() > 0){
                        if ($message){
                            $rules_message_cart[] = Mage::helper('rewardpoints')->__("Addition (%s points + %s = %s points, %s)", $point_value, $rule_validate->getPoints(), ceil($points_tmp), $message);
                        } else {
                            $rules_message_cart[] = Mage::helper('rewardpoints')->__("Addition (%s points + %s = %s points)", $point_value, $rule_validate->getPoints(), ceil($points_tmp));
                        }
                    } else {
                        if ($message){
                            $rules_message_cart[] = Mage::helper('rewardpoints')->__("Substraction (%s points - %s = %s points, %s)", $point_value, $rule_validate->getPoints(), ceil($points_tmp), $message);
                        } else {
                            $rules_message_cart[] = Mage::helper('rewardpoints')->__("Substraction (%s points - %s = %s points)", $point_value, $rule_validate->getPoints(), ceil($points_tmp));
                        }
                    }
                    $point_value = $points_tmp;
                } 
            } else {
                //rule ko
                
            }
        }
        
        if (sizeof($rules_message_cart) && is_object($to_validate) && $to_validate->getQuote() && is_object($to_validate->getQuote()) && $to_validate->getQuote()->getId()) {
            $to_validate->getQuote()->setRewardpointsCartRuleText(serialize($rules_message_cart));
        }
        
        if ($point_value){
            return $point_value;
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