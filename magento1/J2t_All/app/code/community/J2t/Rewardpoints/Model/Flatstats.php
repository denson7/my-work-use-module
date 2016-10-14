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
class J2t_Rewardpoints_Model_Flatstats extends Mage_Core_Model_Abstract
{
    protected $points_current;
    protected $points_collected;
    protected $points_received;
    protected $points_spent;
    protected $points_waiting;
    
    protected $points_lost;
    
    const XML_PATH_CUSTOMER_NOTIFICATION_EMAIL_TEMPLATE     = 'rewardpoints/status_notification/points_notification_email_template';
    const XML_PATH_NOTIFICATION_EMAIL_IDENTITY              = 'rewardpoints/notifications/notification_email_identity';
    
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/flatstats');
    }
    
    
    public function sendCustomerNotification(Mage_Customer_Model_Customer $customer, $store_id, $points, $point_model, $sender, $email_template = null)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        //Start environment emulation of the specified store
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store_id);
        
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        $template = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_NOTIFICATION_EMAIL_TEMPLATE, $store_id);
        
        if ($email_template != null){
            $template = $email_template;
        }
        
        $recipient = array(
            'email' => $customer->getEmail(),
            'name'  => $customer->getName()
        );
        
        //$sender  = Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_EMAIL_IDENTITY, $store_id);
        $email->setDesignConfig(array('area'=>'frontend', 'store'=>$store_id))
                ->sendTransactional(
                    $template,
                    $sender,
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'points'   => $points,
                        'customer' => $customer,
                        'point_model' => $point_model,
                        'store_name'    => Mage::getModel('core/store')->load(Mage::app()->getStore($store_id)->getCode())->getName(),
                    )
                );
        
        $translate->setTranslateInline(true);
        //return $email->getSentSuccess();
        $return_val = $email->getSentSuccess();
        //Stop environment emulation and restore original store
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        return $return_val;
    }
    
    
    public function processRecordFlat($customerId, $store_id, $check_date = false, $force_process = false){
        //if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id) || $force_process){
        if ($customerId){
            $reward_model = Mage::getModel('rewardpoints/stats');
            $points_current = $reward_model->getPointsCurrent($customerId, $store_id);
            $points_received = $reward_model->getRealPointsReceivedNoExpiry($customerId, $store_id);
            $points_spent = $reward_model->getPointsSpent($customerId, $store_id);
            $points_awaiting_validation = $reward_model->getPointsWaitingValidation($customerId, $store_id);
            $points_lost = $reward_model->getRealPointsLost($customerId, $store_id);

            
            
            $this->loadByCustomerStore($customerId, $store_id);
            
	    if ((!$points_received && !$points_spent && !$points_awaiting_validation && !$points_current && !$points_lost)
                    || ($points_received == $this->getPointsCollected() && $points_spent == $this->getPointsUsed() 
                    && $points_awaiting_validation == $this->getPointsWaiting() && $points_current == $this->getPointsCurrent() 
                    && $points_lost == $this->getPointsLost())) {
                if ($this->getId() && !$points_received && !$points_spent && !$points_awaiting_validation && !$points_current && !$points_lost){
                    //remove line
                    $this->delete();
                    if ($website_id = Mage::getModel('core/store')->load($store_id)->getWebsiteId()){
                        $customer = Mage::getModel('customer/customer')
                           ->setWebsiteId($website_id)
                           ->load($customerId);
                        if ($customer->getId()){
                            $customer->setRewardpointsAccumulated(0);
                            $customer->setRewardpointsAvailable(0);
                            $customer->setRewardpointsSpent(0);
                            $customer->setRewardpointsLost(0);
                            $customer->setRewardpointsWaiting(0);
                            $customer->save();
                        }
                    }
                }
		return false;
            }

	    $this->setPointsCollected($points_received);
            $this->setPointsUsed($points_spent);
            $this->setPointsWaiting($points_awaiting_validation);
            $this->setPointsCurrent($points_current);
            $this->setPointsLost($points_lost);
            $this->setStoreId($store_id);
            $this->setUserId($customerId);
            
            if ($check_date && ($date_check = $reward_flat_model->getLastCheck())){
                $date_array = explode("-", $reward_flat_model->getLastCheck());
                if ($this->getLastCheck() == date("Y-m-d")){
                    return false;
                }
            }            
            $this->setLastCheck(date("Y-m-d"));            
            $this->save();
            
            /*$customer = Mage::getModel('customer/customer')
                ->setWebsiteId($store_id)
                ->load($customerId);*/
            
            if ($website_id = Mage::getModel('core/store')->load($store_id)->getWebsiteId()){
                $customer = Mage::getModel('customer/customer')
                   ->setWebsiteId($website_id)
                   ->load($customerId);
                if ($customer->getId()){
                    $customer->setRewardpointsAccumulated($points_received);
                    $customer->setRewardpointsAvailable($points_current);
                    $customer->setRewardpointsSpent($points_spent);
                    $customer->setRewardpointsLost($points_lost);
                    $customer->setRewardpointsWaiting($points_awaiting_validation);
                    $customer->save();
                }
            }
        }
        //}
    }
    
    
    
    public function loadByCustomerStore($customerId, $storeId, $date=null)
    {
        $this->addData($this->getResource()->loadByCustomerStore($customerId, $storeId, $date=null));
        return $this;
    }
    
    public function loadByCustomerId($customer_id)
    {
        $collection = $this->getCollection();
        $collection->getSelect()->where('user_id = ?', $customer_id);

        return $collection;
    }
    
    
    protected function collectVariablesValues($customer_id, $store_id)
    {
        $this->loadByCustomerStore($customer_id, $store_id);
        $this->points_current = $this->getPointsCurrent();
        $this->points_collected = $this->getPointsCollected();
        $this->points_waiting = $this->getPointsWaiting();
        $this->points_spent = $this->getPointsUsed();
        $this->points_lost = $this->getPointsLost();
        
    }
    
    public function collectPointsCurrent($customer_id, $store_id){        
        if ($this->points_current != null){
            return $this->points_current;
        }        
        $this->collectVariablesValues($customer_id, $store_id);        
        return $this->points_current;
    }

    public function collectPointsReceived($customer_id, $store_id){
        if ($this->points_collected != null){
            return $this->points_collected;
        }        
        $this->collectVariablesValues($customer_id, $store_id);        
        return $this->points_collected;
    }

    public function collectPointsSpent($customer_id, $store_id){
        if ($this->points_spent != null){
            return $this->points_spent;
        }        
        $this->collectVariablesValues($customer_id, $store_id);        
        return $this->points_spent;
    }

    public function collectPointsWaitingValidation($customer_id, $store_id){
        if ($this->points_waiting != null){
            return $this->points_waiting;
        }        
        $this->collectVariablesValues($customer_id, $store_id);        
        return $this->points_waiting;
    }
    
    public function collectPointsLost($customer_id, $store_id) {
        if ($this->points_lost != null){
            return $this->points_lost;
        }        
        $this->collectVariablesValues($customer_id, $store_id);        
        return $this->points_lost;
    }
    
    

}

