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
class J2t_Rewardpoints_Model_Stats extends Mage_Core_Model_Abstract
{
    const TARGET_PER_ORDER     = 1;
    const TARGET_FREE   = 2;
    const APPLY_ALL_ORDERS  = '-1';

    const TYPE_POINTS_ADMIN  = '-1';
    const TYPE_POINTS_REVIEW  = '-2';
    const TYPE_POINTS_REGISTRATION  = '-3';
    const TYPE_POINTS_REQUIRED  = '-10';
    const TYPE_POINTS_BIRTHDAY  = '-20';
    const TYPE_POINTS_FB  = '-30';
    const TYPE_POINTS_GP  = '-40';
    const TYPE_POINTS_PIN  = '-50';
    const TYPE_POINTS_TT  = '-60';
    
    const TYPE_POINTS_NEWSLETTER  = '-70';
    const TYPE_POINTS_POLL  = '-80';
    const TYPE_POINTS_TAG  = '-90';
    const TYPE_POINTS_DYN  = '-99';
    
    const TYPE_POINTS_REFERRAL_REGISTRATION  = '-33';

    protected $_targets;

    protected $_eventPrefix = 'rewardpoints_account';
    protected $_eventObject = 'stats';

    protected $points_received;
    protected $points_received_no_exp;
    // J2T points validation date
    protected $points_received_reajust;
    protected $points_spent;
    
    protected $points_lost;
    protected $points_waiting;
    
    const XML_PATH_NOTIFICATION_EMAIL_TEMPLATE			= 'rewardpoints/notifications/notification_email_template';
    const XML_PATH_NOTIFICATION_EMAIL_IDENTITY			= 'rewardpoints/notifications/notification_email_identity';
    
    const XML_PATH_NOTIFICATION_ADMIN_EMAIL_TEMPLATE    = 'rewardpoints/admin_notifications/notification_admin_email_template';
    const XML_PATH_NOTIFICATION_ADMIN_EMAIL_IDENTITY    = 'rewardpoints/admin_notifications/notification_admin_email_identity';
	
	const XML_PATH_START_DATE							= 'rewardpoints/default/start_date';

    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/stats');

        $this->_targets = array(
            self::TARGET_PER_ORDER     => Mage::helper('rewardpoints')->__('Related to Order ID'),
            self::TARGET_FREE   => Mage::helper('rewardpoints')->__('Not related to Order ID'),
        );
    }
    
    public function constructSqlPointsType($table_prefix, $specific_types = array()){
        $arr_sql = array();
        foreach ($this->getPointsDefaultTypeToArray() as $key => $value){
            if ($specific_types == array()){
                $arr_sql[] = $table_prefix.".order_id = '".$key."' ";
            } elseif (in_array($key, $specific_types)) {
                $arr_sql[] = $table_prefix.".order_id = '".$key."' ";
            }
        }
        return implode(" or ", $arr_sql);
    }
    
    public function getOnlyPointsTypesArray(){
        $arr = array();
        foreach ($this->getPointsDefaultTypeToArray() as $key => $value){
            $arr[] = $key;
        }
        return $arr;
    }
    
    public function getPointsDefaultTypeToArray(){
        $return_value = array(self::TYPE_POINTS_FB => Mage::helper('rewardpoints')->__('Facebook Like points'), //OK
            self::TYPE_POINTS_PIN => Mage::helper('rewardpoints')->__('Pinterest points'), //OK
            self::TYPE_POINTS_TT => Mage::helper('rewardpoints')->__('Twitter points'), //OK
            self::TYPE_POINTS_GP => Mage::helper('rewardpoints')->__('Google Plus points'), //OK
            self::TYPE_POINTS_BIRTHDAY => Mage::helper('rewardpoints')->__('Birthday points'), //OK
            self::TYPE_POINTS_REQUIRED => Mage::helper('rewardpoints')->__('Required points usage'), //OK
            self::TYPE_POINTS_REVIEW => Mage::helper('rewardpoints')->__('Review points'), //OK
            self::TYPE_POINTS_DYN => Mage::helper('rewardpoints')->__('Event points'), //OK
            self::TYPE_POINTS_NEWSLETTER => Mage::helper('rewardpoints')->__('Newsletter points'), //OK
            self::TYPE_POINTS_POLL => Mage::helper('rewardpoints')->__('Poll points'), //OK
            self::TYPE_POINTS_TAG => Mage::helper('rewardpoints')->__('Tag points'), //OK
            self::TYPE_POINTS_ADMIN => Mage::helper('rewardpoints')->__('Admin gift'), //OK
            self::TYPE_POINTS_REGISTRATION => Mage::helper('rewardpoints')->__('Registration points'),
            self::TYPE_POINTS_REFERRAL_REGISTRATION => Mage::helper('rewardpoints')->__('Referral registration points')); //OK
        
        if (Mage::getConfig()->getModuleConfig('J2t_Rewardshare')->is('active', 'true')){
            $return_value[J2t_Rewardshare_Model_Stats::TYPE_POINTS_SHARE] = Mage::helper('j2trewardshare')->__('Gift (shared points)');
        }
        
        return $return_value;
        //J2t_Rewardshare_Model_Stats::TYPE_POINTS_SHARE => Mage::helper('j2trewardshare')->__('Gift (shared points)')
        
    }
    
    public function getPointsTypeToArray(){
        $return_value = array(self::TYPE_POINTS_FB => Mage::helper('rewardpoints')->__('Facebook Like points'), //OK
            self::TYPE_POINTS_GP => Mage::helper('rewardpoints')->__('Google Plus points'), //OK
            self::TYPE_POINTS_PIN => Mage::helper('rewardpoints')->__('Pinterest points'), //OK
            self::TYPE_POINTS_TT => Mage::helper('rewardpoints')->__('Twitter points'), //OK
            self::TYPE_POINTS_BIRTHDAY => Mage::helper('rewardpoints')->__('Birthday points'), //OK
            self::TYPE_POINTS_REVIEW => Mage::helper('rewardpoints')->__('Review points'), //OK
            self::TYPE_POINTS_DYN => Mage::helper('rewardpoints')->__('Event points'), //OK
            self::TYPE_POINTS_NEWSLETTER => Mage::helper('rewardpoints')->__('Newsletter points'), //OK
            self::TYPE_POINTS_POLL => Mage::helper('rewardpoints')->__('Poll points'), //OK
            self::TYPE_POINTS_TAG => Mage::helper('rewardpoints')->__('Tag points'), //OK
            self::TYPE_POINTS_ADMIN => Mage::helper('rewardpoints')->__('Admin gift'), //OK
            self::TYPE_POINTS_REQUIRED => Mage::helper('rewardpoints')->__('Points used on products'), 
            self::TYPE_POINTS_REGISTRATION => Mage::helper('rewardpoints')->__('Referral registration points'),
            self::TYPE_POINTS_REFERRAL_REGISTRATION => Mage::helper('rewardpoints')->__('Registration points')); //OK
        
        if (Mage::getConfig()->getModuleConfig('J2t_Rewardshare')->is('active', 'true')){
            $return_value[J2t_Rewardshare_Model_Stats::TYPE_POINTS_SHARE] = Mage::helper('j2trewardshare')->__('Gift (shared points)');
        }
        
        return $return_value;
    }

    public function getTargetsArray()
    {
        return $this->_targets;
    }

    public function targetsToOptionArray()
    {
        return $this->_toOptionArray($this->_targets);
    }

    protected function _toOptionArray($array)
    {
        $res = array();
        foreach ($array as $value => $label) {
        	$res[] = array('value' => $value, 'label' => $label);
        }
        return $res;
    }
    
    
    //J2T Check referral
    public function loadByReferralId($referral_id, $referral_customer_id = null)
    {
        $this->addData($this->getResource()->loadByReferralId($referral_id, $referral_customer_id));
        return $this;
    }
    
    
    public function loadByChildReferralId($referral_id, $referral_customer_id = null)
    {
        $this->addData($this->getResource()->loadByChildReferralId($referral_id, $referral_customer_id));
        return $this;
    }
    
    public function loadByOrderIncrementId($order_id, $customer_id = null, $referral = false, $parent = false)
    {
        $this->addData($this->getResource()->loadByOrderIncrementId($order_id, $customer_id, $referral, $parent));
        return $this;
    }
    
	public function loadByFirstOrder($customer_id)
	{
		$this->addData($this->getResource()->loadByFirstOrder($customer_id));
        return $this;
	}
    
    public function loadpointsbydate($store_id, $customer_id, $date){
        $collection = $this->getCollection();
        $collection->getSelect()->where("main_table.customer_id = ?", $customer_id);        
        $collection->getSelect()->where("( ? >= main_table.date_start )", $date);
        $collection->getSelect()->where("( main_table.date_end >= ? )", $date);
        $collection->getSelect()->where("( main_table.date_end <= NOW() )");
        $collection->addValidPoints($store_id, true);
        
        //echo $collection->getSelect()->__toString();
        //die;

        $row = $collection->getFirstItem();
        if (!$row) return $this;
        return $row;
    }
    
    public function getDobPoints($store_id, $customer_id) 
    {
        //self::TYPE_POINTS_BIRTHDAY
        $collection = $this->getCollection();
        $collection->getSelect()->where("main_table.customer_id = ?", $customer_id);        
        //$collection->getSelect()->where("( ? >= main_table.date_start )", $date);
        $collection->getSelect()->where("main_table.order_id  = ?", self::TYPE_POINTS_BIRTHDAY);
        $collection->pointsByDate();
        
        $row = $collection->getFirstItem();
        if (!$row) return $this;
        return $row;
    }

    public function loadByCustomerId($customer_id)
    {
        $collection = $this->getCollection();
        $collection->getSelect()->where('customer_id = ?', $customer_id);

        return $collection;
        
        /*$row = $collection->getFirstItem();
        if (!$row) return $this;
        return $row;*/
    }
    
    public function loadCustomerOrderQuote($customer_id, $order_id, $quote_id)
    {
        $collection = $this->getCollection();
        $collection->getSelect()->where('customer_id = ?', $customer_id);
        $collection->getSelect()->where('order_id = ?', $order_id);
        $collection->getSelect()->where('quote_id = ?', $quote_id);
        
        $row = $collection->getFirstItem();
        if (!$row) return $this;
        return $row;
    }
    
    public function loadReferrer($customer_id, $order_id)
    {
        $collection = $this->getCollection();
        $collection->getSelect()->where('customer_id <> ?', $customer_id);
        $collection->getSelect()->where('order_id = ?', $order_id);
        
        $row = $collection->getFirstItem();
        if (!$row) return $this;
        return $row;
    }

    public function checkProcessedOrder($customer_id, $order_id, $isCredit = true, $link_id = false, $exclude_referral = true, $process_once = false, $last_entry_gap = 0, $object_name = null, $sum = false)
    {
        $collection = $this->getCollection();
        
        if ($sum){
            $collection->getSelect()->from(Mage::getSingleton('core/resource')->getTableName('rewardpoints/rewardpoints_account'), array(new Zend_Db_Expr('SUM('.Mage::getSingleton('core/resource')->getTableName('rewardpoints/rewardpoints_account').'.points_current) AS points_accumulated')));
        }
        
        $collection->getSelect()->where('main_table.customer_id = ?', $customer_id);
        $collection->getSelect()->where('main_table.order_id = ?', $order_id);
        if ($link_id && !$process_once){
            $collection->getSelect()->where('rewardpoints_linker = ?', $link_id);
        }
        
        if ($object_name != null){
            $collection->getSelect()->where('main_table.object_name LIKE ?', $object_name);
        }
        if ($last_entry_gap > 0){
            //$collection->getSelect()->where('date_insertion = ?', $link_id);
            $date_duration = $collection->getResource()->formatDate(mktime(0, 0, 0, date("m"), date("d")-$last_entry_gap, date("Y")));
            $collection->getSelect()->where('(main_table.date_insertion >= ? AND main_table.date_insertion IS NOT NULL)', $date_duration);
        }
        
        if ($isCredit){
            $collection->getSelect()->where('main_table.points_current > 0');
        } else {
            $collection->getSelect()->where('main_table.points_spent > 0');
        }
        
        if ($exclude_referral){
            $collection->getSelect()->where('main_table.rewardpoints_referral_id IS NULL');
        }
        
        if ($sum){
            $collection->getSelect()->group(Mage::getSingleton('core/resource')->getTableName('rewardpoints/rewardpoints_account').'.customer_id');
            return $collection->getFirstItem();
        }
        
        $row = $collection->getFirstItem();
        if (!$row) return $this;
        return $row;
    }


    public function getPointsUsed($order_id, $customer_id)
    {
        $collection = $this->getCollection();
        $collection->getSelect()->where('customer_id = ?', $customer_id);
        $collection->getSelect()->where('order_id = ?', $order_id);
        $collection->getSelect()->where('points_spent > ?', '0');

        $row = $collection->getFirstItem();
        if (!$row) return $this;
        return $row;
    }


    /*public function getPointsWaitingValidation($customer_id, $store_id){
        $collection = $this->getCollection()->joinFullCustomerPoints($customer_id, $store_id);
        $row = $collection->getFirstItem();
        return $row->getNbCredit() - $this->getPointsReceived($customer_id, $store_id) + $this->getPointsReceivedReajustment($customer_id, $store_id);
    }*/
    
    public function getPointsWaitingValidation($customer_id, $store_id){
        
        if ($this->points_waiting != null){
            return $this->points_waiting;
        } else {
            $collection = $this->getCollection();
            $collection->joinValidPointsOrder($customer_id, $store_id, array("new") , false, false, false, false, array(), false, true);
            //joinValidPointsOrder(           $customer_id, $store_id, $order_states, $order_states_used, $spent = false, $remove_end = false, $no_groupby = false, $specific_order_ids = array(), $no_sum = false, $remove_start = false)
            //$collection->joinValidPointsOrder($customer_id, $store_id, $order_states, $order_states_used, false, true, false, array(), false, true);
            
            
            foreach($this->getPointsTypeToArray() as $point_type => $point_text){
                $collection->getSelect()->where("main_table.order_id <> '$point_type'");
            }
            //echo $collection->getSelect();
            //die;
            
            $row = $collection->getFirstItem();
            $this->points_waiting = $row->getNbCredit();
            //$this->points_waiting = $row->getNbCredit() - $this->getRealPointsReceivedNoExpiry($customer_id, $store_id);
            return $this->points_waiting;
        }
        
        /*return $this->getRealPointsReceivedNoExpiry($customer_id, $store_id) 
                - $this->getPointsSpent($customer_id, $store_id) 
                - $this->getPointsCurrent($customer_id, $store_id) 
                - $this->getRealPointsLost($customer_id, $store_id);*/
        /*$collection = $this->getCollection()->joinFullCustomerPoints($customer_id, $store_id);
        $row = $collection->getFirstItem();
        return $row->getNbCredit() - $this->getPointsReceived($customer_id, $store_id) + $this->getPointsReceivedReajustment($customer_id, $store_id);*/
    }
    
    public function sendAdminNotification(Mage_Customer_Model_Customer $customer, $store_id, $points, $description)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        //Start environment emulation of the specified store
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store_id);
        
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        $template = Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_ADMIN_EMAIL_TEMPLATE, $store_id);
        $recipient = array(
            'email' => $customer->getEmail(),
            'name'  => $customer->getName()
        );

        $sender  = Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_ADMIN_EMAIL_IDENTITY, $store_id);
        $email->setDesignConfig(array('area'=>'frontend', 'store'=>$store_id))
                ->sendTransactional(
                    $template,
                    $sender,
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'points'   => $points,
                        'description'   => $description,
                        'customer' => $customer,
                        'rewardpoint'  => $this,
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
    
    
    public function sendNotification(Mage_Customer_Model_Customer $customer, $store_id, $points, $days)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        //Start environment emulation of the specified store
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store_id);
        
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        $template = Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_EMAIL_TEMPLATE, $store_id);
        $recipient = array(
            'email' => $customer->getEmail(),
            'name'  => $customer->getName()
        );

        $sender  = Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_EMAIL_IDENTITY, $store_id);
        $email->setDesignConfig(array('area'=>'frontend', 'store'=>$store_id))
                ->sendTransactional(
                    $template,
                    $sender,
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'points'   => $points,
                        'days'   => $days,
                        'customer' => $customer,
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
    
    
    
    
    /*
     * FIX - expiry dates of points
     */
    public function loadallpointsbydate($customer_id, $store_id, $date_end = null){
        $collection = $this->getCollection();
        $collection->getSelect()->where("main_table.customer_id = ?", $customer_id);
        if ($date_end){
            $collection->getSelect()->where("( ? <= main_table.date_end )", $date_end);
        }
        /*if ($last_date){
            $collection->getSelect()->where("( ? <= main_table.date_start )", $last_date);
        }*/
        
        //NEED to get all valid points collected
        /*$collection->getSelect()->where("( main_table.date_start <= NOW() )");
        $collection->getSelect()->where("( main_table.date_start IS NOT NULL )");
        $collection->getSelect()->where("( main_table.date_end IS NOT NULL )");*/
        //VERIFY THIS
        $collection->getSelect()->where("( main_table.date_start IS NOT NULL OR main_table.date_end IS NOT NULL )");
        
        $collection->addValidPoints($store_id, true, true);
        
        
        $collection->setOrder('date_start ',  'ASC');
        $collection->setOrder('points_current ',  'DESC');
        $collection->setOrder('date_end ',  'ASC');
        
        /*echo $collection->getSelect()->__toString();
        die;*/
        
        
        return $collection->load();
    }
    
    
    /*protected function linearPointMath($all_valid_points, $acc_points = 0){
        return $acc_points;
    }*/
    
    /**
     * 
     * @param type $used_point: all points that have been used
     * @param type $list_points: list of all gathered points ordered by date
     * @param type $left_over: quantity that is left over
     * @param type $left_over_datestamp: date of quantity that is left over
     */
    protected function calculateLostPointsLeft($used_points, $list_points){
        $expired_unused_points = 0;
        $today_stamp = Mage::getModel('core/date')->timestamp(time());
        $original_list_points = $list_points;
        //1. list all used points
        //2. verify if all gathered points have been used for used points
        $len = count($list_points);
        $len2 = count($used_points);
        if ($len && $len2){
            $valid_points = $this->getPointsDefaultTypeToArray();
            foreach($used_points as &$used_point){
                $i = 0;
                $end = false;
                $date_used_iso = new Zend_Date($used_point['date_used'], Zend_Date::ISO_8601);
                $date_used_stamp = $date_used_iso->getTimestamp();
                //while ($used_point['value'] > 0 && !$end){
                while (!$end){
                    foreach ($list_points as &$point){
                        //verify point usage date against point gathering 
                        //and check if order is different / point gathered through the same order cannot be used
                        $date_form_iso = new Zend_Date($point['date_from'], Zend_Date::ISO_8601);
                        $from_datestamp = $date_form_iso->getTimestamp();

                        $date_to_iso = new Zend_Date($point['date_end'], Zend_Date::ISO_8601);
                        $end_datestamp = $date_to_iso->getTimestamp();

                        if ($point['points'] > 0 && $date_used_stamp >= $from_datestamp 
                                && $date_used_stamp <= $end_datestamp
                                && ( ( $point['order_id'] != null && isset($valid_points[$point['order_id']]) ) 
                                        || ($used_point['order_id'] != $point['order_id'] || $point['order_id'] == null) ) ){
                            $points_left = $point['points'] - $used_point['value'];
                            //if user has used more points that he has collected > set 0 in point gathered value for this date and modify used points value according to what's left
                            if ($points_left < 0){
                                $used_point['value'] = $used_point['value'] - $point['points'];
                                //echo "New used point value {$used_point['value']} ({$used_point['value']} - {$point['points']}) <br />";
                                $point['points'] = 0;

                            } 
                            //if still have point's left (or 0), modify used point value
                            else {
                                $point['points'] = $points_left;
                                $used_point['value'] = 0;
                                $end = true;
                                break;
                            }
                        }
                        //check if while loop isn't going into enless loop
                        if ($i == $len - 1) {
                            //last element - end while
                            $end = true;
                        }
                        $i++;
                    }
                }
            }
            
            //sum of all expired point left prior today's date
            foreach ($list_points as $point_current){
                $date_to_iso = new Zend_Date($point_current['date_end'], Zend_Date::ISO_8601);
                $end_datestamp = $date_to_iso->getTimestamp();
                
                $date_from_iso = new Zend_Date($point_current['date_from'], Zend_Date::ISO_8601);
                $from_datestamp = $date_from_iso->getTimestamp();
                if ($today_stamp < $from_datestamp || $today_stamp >= $end_datestamp){
                    $expired_unused_points += $point_current['points'];
                }
            }
        } else if ($len){
            //sum of all points that are not valid yet or expired
            foreach ($original_list_points as $point_current){
                $date_from_iso = new Zend_Date($point_current['date_from'], Zend_Date::ISO_8601);
                $from_datestamp = $date_from_iso->getTimestamp();
                $date_to_iso = new Zend_Date($point_current['date_end'], Zend_Date::ISO_8601);
                $end_datestamp = $date_to_iso->getTimestamp();
                if ($today_stamp < $from_datestamp || $today_stamp >= $end_datestamp){
                    $expired_unused_points += $point_current['points'];
                }
            }
        }
        return -$expired_unused_points;
    }
    
    protected function getPointsReceivedReajustment($customer_id, $store_id) {
        $acc_fix_points = 0;
        
        if ($this->points_received_reajust != null){
            return $this->points_received_reajust;
        } else {
            //1. get all points gathered for this user (valid points) and put in array(points_backup, points_calc, date_from, date_end, store_id)
            //2. get all used points
            $points = $this
                        ->getResourceCollection()
                        ->addUsedpointsbydate($store_id, $customer_id);
            //echo $points->getSelect()->__toString();
            //die;
            $valid_points = $this->loadallpointsbydate($customer_id, $store_id);
            $arr_points_collection = array();
            
            //echo $valid_points->getSelect()->__toString();
            //die;

            /*
             * $arr_points_collection : all gathered points having begining and ending date
             */
            if ($valid_points->count()){
                foreach ($valid_points as $valid_point){
                    $arr_points_collection[] = array("points" => $valid_point->getData('points_current'), 
                        "points_calculated" => $valid_point->getData('points_current'),
                        "points_spent" => $valid_point->getData('points_spent'),
                        "order_id" => $valid_point->getData('order_id'),
                        "date_from" => ($valid_point->getData('date_start')) ? $valid_point->getData('date_start') : date('Y-m-d', mktime(0, 0, 0, 1, 1, 1970)),
                        "date_end" => ($valid_point->getData('date_end')) ? $valid_point->getData('date_end') : date('Y-m-d', mktime(0, 0, 0, 1, 1, date("Y")+1))
                            );
                }
            }
            
            $arr_points_collection_backup = $arr_points_collection;
            
            
            $today_stamp = Mage::getModel('core/date')->timestamp(time());
            $acc_fix_points = 0;
            $extra = 0;
            $last_date = null;
            
            /*
             * $points : all used points groupped by used dates (date_insertion used instead of date_order, 
             * in order to avoid any issues related to missing dates - e.g. missing date_order when inserting points through admin)
             */
            /*$up = array();
            foreach ($points as $valid_point){
                $up[] = $valid_point->getData();
            }
            echo '<pre>';
            print_r($up);
            echo "</pre>";
            die;*/
            $used_points = array();
            
            if ($points->getSize() && sizeof($arr_points_collection)){
                foreach ($points as $used_point){
                    $date_start = ($used_point->getData('date_start')) ? $used_point->getData('date_start') : date("Y-m-d"); 
		    $used_points[] = array(
                        "points_used" => $used_point->getData('points_spent'),
                        "value" => $used_point->getData('points_spent'),
                        "order_id" => $used_point->getData('order_id'),
                        "date_used" => ($used_point->getData('date_order')) ? $used_point->getData('date_order') : $date_start
                            );
                }
            }
            
            return $this->calculateLostPointsLeft($used_points, $arr_points_collection);
            
            if ($points->getSize() && sizeof($arr_points_collection)){
                $left_over = 0;
                //$points = all used points >> addUsedpointsbydate
                /*
                 * browse all used points ($points var)
                 */
                $previous_points = 0;
                foreach ($points as $current_point){
                    $point_left = $current_point->getData('nb_credit_spent');
                    
                    $date_used = $current_point->getData('date_order');
                    $date_used = ($date_used) ? $date_used : $current_point->getData('date_start');
                    
                    $date_used = ($date_used) ? $date_used : date("Y-m-d");
                    $date_used_iso = new Zend_Date($date_used, Zend_Date::ISO_8601);
                    $date_used_stamp = $date_used_iso->getTimestamp();
                    
                    $checked = false;
                    //$extra = 0;
                    //$arr_points_collection = all valid points
                    /*
                     * browse all gathered points which contain starting/ending date
                     */
                    foreach($arr_points_collection as $key => $point_collection_value){
                        $date_from = $point_collection_value['date_from'];
                        $date_from_iso = new Zend_Date($date_from, Zend_Date::ISO_8601);
                        $date_from_stamp = $date_from_iso->getTimestamp();
                        
                        $date_end = $point_collection_value['date_end'];
                        $date_end_iso = new Zend_Date($date_end, Zend_Date::ISO_8601);
                        $date_end_stamp = $date_end_iso->getTimestamp();
                        
                        /*
                         * Compare used point date to points collection date
                         * in order to check if readjustment is required
                         * All expired points ($arr_points_collection) are verified against points used ($current_point from $points) 
                         * during the point validity duration
                         */
                        
                        if (
                                $date_from_stamp <= $date_used_stamp 
                                && $date_end_stamp >= $date_used_stamp 
                                && $today_stamp > $date_end_stamp 
                                && !$checked 
                                && $arr_points_collection[$key]['points_calculated'] > 0
                                ){ 
                                /* 
                                 * if expired points, modify point_calculated in order 
                                 * to set the value to be removed from point summary value left must be
                                 * expired points qty - total points used for this date
                                 * 
                                 */
                           
                        //if ($date_from_stamp <= $date_used_stamp && $date_end_stamp >= $date_used_stamp && $today_stamp > $date_end_stamp && !$checked && $arr_points_collection[$key]['points_calculated'] > 0){
                        //if ($date_from_stamp <= $date_used_stamp && $date_end_stamp >= $date_used_stamp && $today_stamp > $date_end_stamp && !$checked && $arr_points_collection[$key]['points_calculated'] > 0){
                            if ($point_left > 0){
                                //calc = expired gathered points for specific date (-) point spent
                                //e.g. calc = 500 - 850 >> -350 (350 left points) means calc < 0, $checked is now true
                                // and points_calulated is 0 but extra needs to be 350
                                // next verification adds extra as point spent which shouldn't be removed as was fully used with expired points
                                // 600 - 850 = -250 >> must add 600 to points_calculated and 350 (600 - 250) reported to next math
                                // 585 - 850 + 350 = 85 >> must add 585 to points_calculated
                                
                                /*$calc = $arr_points_collection[$key]['points_calculated'] - $current_point->getData('nb_credit_spent')+$extra;
                                $arr_points_collection[$key]['points_calculated'] = ($calc > 0) ? $calc : 0;
                                //if ($calc < 0){
                                if ($calc >= 0){
                                    $extra = $calc;
                                    $checked = false;
                                } else {
                                    $extra = 0;
                                    $checked = true;
                                }*/
                                
                            }
                            
                            $point_usage = $arr_points_collection[$key]['points_calculated'] - $current_point->getData('nb_credit_spent');
                            //echo "$point_usage = ".$arr_points_collection[$key]['points_calculated']." - ".$current_point->getData('nb_credit_spent')."<br />";
                            
                            $point_usage_plus_last = $point_usage + $previous_points;
                            //echo "$point_usage_plus_last = $point_usage + $previous_points <br /><br />";
                            
                            if ($point_usage <= 0 && $point_usage_plus_last <= 0){
                                //echo "0 inserted for key $key<br /><br />";
                                $arr_points_collection[$key]['points_calculated'] = 0;
                                $checked = false;
                                $previous_points += $arr_points_collection[$key]['points'];
                            } else if ($point_usage <= 0 && $point_usage_plus_last > 0){
                                //echo "previous_point: $point_usage_plus_last inserted for key $key <br /><br />";
                                $arr_points_collection[$key]['points_calculated'] = $point_usage_plus_last;
                                $checked = true;
                                $previous_points = $arr_points_collection[$key]['points'];
                            } else if ($point_usage > 0){
                                //echo "point_usage: $point_usage inserted for key $key<br /><br />";
                                $arr_points_collection[$key]['points_calculated'] = $point_usage;
                                $checked = true;
                                $previous_points = $arr_points_collection[$key]['points'];
                            } 
                            
                            $point_left -= $arr_points_collection[$key]['points'];
                            //$checked = true;
                        } else if ($today_stamp <= $date_end_stamp) { // if points still available
                            $arr_points_collection[$key]['points_calculated'] = 0;
                        } 
                    }
                }
                
                /*
                 * Calculate points to be readjusted and set used values to 0
                 */
                
                foreach($arr_points_collection as $key_p => $point_collection_value){
                    $acc_fix_points -= $point_collection_value['points_calculated'];
                    $arr_points_collection[$key_p]['points_calculated'] = 0;
                } 
            }
            
            //verify if points left are expired. If yes, replace back points_calculated = points
            
            /*
             * flush any residual expired points
             * and add them to readjusment
             */
            
            if (sizeof($arr_points_collection)){
                foreach($arr_points_collection as $key => $point_collection_value){
                    
                    $date_from = $point_collection_value['date_from'];
                    $date_from_iso = new Zend_Date($date_from, Zend_Date::ISO_8601);
                    $date_from_stamp = $date_from_iso->getTimestamp();
                    
                    $date_end = $point_collection_value['date_end'];
                    $date_end_iso = new Zend_Date($date_end, Zend_Date::ISO_8601);
                    $date_end_stamp = $date_end_iso->getTimestamp();
                    
                    if ($today_stamp > $date_end_stamp || $today_stamp < $date_from_stamp){
                        $acc_fix_points -= $point_collection_value['points_calculated'];
                    }
                }
            }
            
            //remove points that are not valid yet
            if (sizeof($arr_points_collection_backup)){
                foreach($arr_points_collection_backup as $key => $point_collection_value){
                    $date_from = $point_collection_value['date_from'];
                    $date_from_iso = new Zend_Date($date_from, Zend_Date::ISO_8601);
                    $date_from_stamp = $date_from_iso->getTimestamp();
                    
                    if ($today_stamp < $date_from_stamp){
                        $acc_fix_points -= $point_collection_value['points_calculated'];
                    }
                }
            }
        }
        
        /*echo $acc_fix_points;
        die;*/
        
        $this->points_received_reajust = $acc_fix_points;
        return $acc_fix_points;
    }
    
    /**
     * J2T modification fixing issue related to points validation dates
     * getPointsReceivedReajustment protected function allowing to readjust points regarding points validation dates
     * @param int $customer_id
     * @param int $store_id
     * @return int 
     */
    protected function _getPointsReceivedReajustment($customer_id, $store_id) {
        /*$points = Mage::getModel('rewardpoints/stats')
                                ->getResourceCollection()
                                ->addUsedpointsbydate($store_id, $customer_id);*/
        
        if ($this->points_received_reajust != null){
            return $this->points_received_reajust;
        } else {
            //get all points used groupped by date
            $points = $this
                                ->getResourceCollection()
                                ->addUsedpointsbydate($store_id, $customer_id);
            $acc_fix_points = 0;
            if ($points->getSize()){
                foreach ($points as $current_point){
                    //validate points per date
                    $points_accum = Mage::getModel('rewardpoints/stats')->loadpointsbydate($store_id, $customer_id, $current_point->getData('date_order'));
                    //if ($points_accum->getData('nb_credit') >= $current_point->getData('nb_credit_spent')){
                    //FIX POINTS READJUST!!!!
                    if ($points_accum->getData('nb_credit') >= $current_point->getData('nb_credit_spent')){
                        $acc_fix_points += $current_point->getData('nb_credit_spent');
                    } 
                }
            }
            $this->points_received_reajust = $acc_fix_points;
            return $acc_fix_points;
        }        
    }
    
    
    public function getRealPointsLost($customerId, $store_id) {
        if ($this->points_lost){
            return $this->points_lost;
        }
        //FIX Point Date Range
        $this->points_lost = $this->getRealPointsReceivedNoExpiry($customerId, $store_id) - $this->getPointsReceived($customerId, $store_id);
        //$this->points_lost = $this->getRealPointsReceivedNoExpiry($customerId, $store_id) - $this->getPointsReceived($customerId, $store_id) - $this->getPointsSpent($customerId, $store_id);
        return $this->points_lost;
    }


    public function getPointsReceived($customer_id, $store_id){
        if ($this->points_received){
            return $this->points_received;
        }
        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', Mage::app()->getStore()->getId());
        $order_states = explode(",", $statuses);
        
        $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses', Mage::app()->getStore()->getId());
        $order_states_used = explode(",", $statuses_used);

        //$order_states = array("'processing'","'complete'");
        $collection = $this->getCollection();
        //$collection->joinValidPointsOrder($customer_id, $store_id, $order_states);
        //FIX J2T - exp date
        $collection->joinValidPointsOrder($customer_id, $store_id, $order_states, $order_states_used, false, true, false, array(), false, true);
        
        /*$collection->printlogquery(true);
        die;*/
        $row = $collection->getFirstItem();
        
        $this->points_received = $row->getNbCredit() + $this->getPointsReceivedReajustment($customer_id, $store_id);
        
        //J2T modification fixing issue related to points validation dates
        //return $row->getNbCredit();
        //echo $collection->getSelect()->__toString();
        //die;
        
        return $this->points_received;
    }
    
    public function getPointsLost($customerId, $store_id) {
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            return $reward_flat_model->collectPointsLost($customerId, $store_id)+0;
        } 
        $reward_model = Mage::getModel('rewardpoints/stats');
        return $reward_model->getRealPointsLost($customerId, $store_id)+0;
    }
    
    public function getRealPointsReceivedNoExpiry($customer_id, $store_id){
        if ($this->points_received_no_exp){
            return $this->points_received_no_exp;
        }
        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', Mage::app()->getStore()->getId());
        $order_states = explode(",", $statuses);
        
        $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses', Mage::app()->getStore()->getId());
        $order_states_used = explode(",", $statuses_used);

        //$order_states = array("'processing'","'complete'");
        $collection = $this->getCollection();
        $collection->joinValidPointsOrder($customer_id, $store_id, $order_states, $order_states_used, false, true, false, array(), false, true);
        
        /*$collection->printlogquery(true);
        die;*/
        $row = $collection->getFirstItem();
        $this->points_received_no_exp = $row->getNbCredit();
        
        //J2T modification fixing issue related to points validation dates
        //return $row->getNbCredit();
        //echo $collection->getSelect()->__toString();
        //die;
        return $row->getNbCredit();
    }

    public function getPointsSpent($customer_id, $store_id){
        
        if ($this->points_spent){
            return $this->points_spent;
        }

        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', Mage::app()->getStore()->getId());
        $order_states = explode(",", $statuses);
        $order_states[] = 'new';
        
        $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses', Mage::app()->getStore()->getId());
        $order_states_used = explode(",", $statuses_used);
        $order_states_used[] = 'new';


        //$order_states = array("'processing'","'complete'","'new'");

        $collection = $this->getCollection();
        $collection->joinValidPointsOrder($customer_id, $store_id, $order_states, $order_states_used, true);
        
        //echo $collection->getSelect();
        //die;
        
        $row = $collection->getFirstItem();

        $this->points_spent = $row->getNbCredit();
        return $this->points_spent;
    }

    public function getPointsCurrent($customer_id, $store_id){
        //echo (int)$this->getPointsReceived($customer_id, $store_id).' - '.(int)$this->getPointsSpent($customer_id, $store_id);
        $total = (int)$this->getPointsReceived($customer_id, $store_id) - (int)$this->getPointsSpent($customer_id, $store_id);
        //$total = $this->getPointsReceived($customer_id, $store_id);
        if ($total > 0){
                return (int)$total;
        } else {
                return 0;
        }
    }
    
    public function recordPoints($pointsInt, $customerId, $orderId, $store_id, $force_nodelay = false) {
        $post = array(
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'store_id' => $store_id,
            'points_current' => $pointsInt,
            'convertion_rate' => Mage::getStoreConfig('rewardpoints/default/points_money', $store_id)
            );
        //v.2.0.0
        $add_delay = 0;
        if ($delay = Mage::getStoreConfig('rewardpoints/default/points_delay', $store_id) && $force_nodelay){
            if (is_numeric($delay)){
                $post['date_start'] = $this->getResource()->formatDate(mktime(0, 0, 0, date("m"), date("d")+$delay, date("Y")));
                $add_delay = $delay;
            }
        }

        if ($duration = Mage::getStoreConfig('rewardpoints/default/points_duration', $store_id)){
            if (is_numeric($duration)){
                if (!isset($post['date_start'])){
                    $post['date_start'] = $this->getResource()->formatDate(time());
                }
                $post['date_end'] = $this->getResource()->formatDate(mktime(0, 0, 0, date("m"), date("d")+$duration+$add_delay, date("Y")));
            }
        }
        $this->setData($post);
        
        
        if ($order = Mage::getModel('sales/order')->load($orderId)){
            $this->setRewardpontsStatus($order->getStatus());
            $this->setRewardpontsState($order->getState());
        }
        
        $this->save();
    }
    
    
    public function _beforeSave()
    {

        if ($this->getOrderId() != self::TYPE_POINTS_REQUIRED && ($order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId())) && $order->getId()){
            
			$this->_dataSaveAllowed = true;
			$start_date  = Mage::getStoreConfig(self::XML_PATH_START_DATE, $order->getStoreId());
			$date_creation = $order->getCreatedAt();
			if ($date_creation){
				$date_creation = strtotime($date_creation);
			} else {
				$date_creation = strtotime(date('Y-m-d'));
			}
			if($start_date && $date_creation < strtotime($start_date)) 
			{
				$this->_dataSaveAllowed = false;
			}
			
            $data['rewardpoints_status'] = $order->getStatus();
            $data['rewardpoints_state'] = $order->getState();
            $data['date_order'] = $order->getCreatedAt();
            if ($this->getDateInsertion() == null){
                $data['date_insertion'] = $order->getCreatedAt();
            }
            if ($this->getPeriod() == null){
                $data['period'] = $order->getCreatedAt();
            }
            $this->addData($data);
        } else {
            $now = Mage::getModel('core/date')->timestamp(time());
            $now_datetime = date('Y-m-d h:i:s', $now);
            $now_notime = date('Y-m-d', $now);

            if ($this->getDateInsertion() == null){
                $this->setDateInsertion($now_datetime);
                //$data['date_insertion'] = $now_datetime;
            }
            if ($this->getPeriod() == null){
                $this->setPeriod($now_notime);
                //$data['period'] = $now_notime;
            }
        }
        
		if (!$this->getStoreId() && $this->getCustomerId() 
				&& ($customer = Mage::getModel('customer/customer')->load($this->getCustomerId()))
				&& ($store_id = $customer->getStoreId())
				){
			$this->setStoreId($store_id);
		}
        
        return parent::_beforeSave();
    }
    
    public function _afterSave()
    {
        if ($customer_id = $this->getCustomerId()){
            $store_ids = explode(',', $this->getStoreId());
            $process_points = true;
            foreach ($store_ids as $store_id){
                if ($store_id){
                    $model = Mage::getModel('rewardpoints/flatstats');
                    $model->processRecordFlat($customer_id, Mage::app()->getStore($store_id)->getId(), false, true);
                    $process_points = false;
                }
            }
            if ($process_points) {
                $allStores = Mage::app()->getStores();
                foreach ($allStores as $_eachStoreId => $val) {
                    $model = Mage::getModel('rewardpoints/flatstats');
                    $model->processRecordFlat($customer_id, Mage::app()->getStore($_eachStoreId)->getId(), false, true);
                }
            }
        }
		Mage::dispatchEvent('rewardpoints_update_after_all', array('point_data', $this));
        return parent::_afterSave();
    }
    
    public function _afterDelete()
    {
        if ($customer_id = $this->getCustomerId()){
            $store_ids = explode(',', $this->getStoreId());
            $process_points = true;
            foreach ($store_ids as $store_id){
                if ($store_id){
                    $model = Mage::getModel('rewardpoints/flatstats');
                    $model->processRecordFlat($customer_id, Mage::app()->getStore($store_id)->getId(), false, true);
                }
            }
            if ($process_points){    
                $allStores = Mage::app()->getStores();
                foreach ($allStores as $_eachStoreId => $val) {
                    $model = Mage::getModel('rewardpoints/flatstats');
                    $model->processRecordFlat($customer_id, Mage::app()->getStore($_eachStoreId)->getId(), false, true);
                }
            }
        }
        return parent::_afterDelete();
    }

}

