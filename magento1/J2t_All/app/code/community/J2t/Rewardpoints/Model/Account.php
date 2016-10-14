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
class J2t_Rewardpoints_Model_Account extends Mage_Core_Model_Abstract {
            protected $customerId = -1;
            public $storeId = -1;
            protected $pointsCurrent = NULL;
            protected $pointsSpent = NULL;
            protected $rewardpointsAccountId = NULL;
            
            public function saveCheckedOrder($orderId, $customerId, $storeId, $pointsCurrent, $referral = null, $no_check = false){
                $this->customerId = $customerId;
                $this->storeId = $storeId;
                $this->pointsCurrent = $pointsCurrent;
                $this->save($orderId, $no_check, $referral);
            }

            public function save($orderId = null, $no_check = false, $referral = null) {
                if (!$no_check){
                    $reward_model = Mage::getModel('rewardpoints/stats')
                                ->getResourceCollection()
                                ->addCustomerFilter($this->customerId)
                                ->addOrderFilter($orderId)
                                ->addStoreFilter($this->storeId);

                    $data['nb_rewards'] = $reward_model->getSize();
                } else {
                    $data['nb_rewards'] = 0;
                }

                if($data['nb_rewards'] == 0){
                    $reward_model = Mage::getModel('rewardpoints/stats');
                    $fields = array();
                    $fields['customer_id'] = $this->customerId;
                    $fields['store_id'] = $this->storeId;
                    $fields['points_current'] = $this->pointsCurrent;
                    //$fields['points_received'] = $this->pointsReceived;
                    if ($referral != null){
                        $fields['rewardpoints_referral_id'] = $referral;
                    }
                    $fields['points_spent'] = $this->pointsSpent;
                    $fields['order_id'] = $orderId;
                    try {
                        if (is_null($this->rewardpointsAccountId)) {
                            $fields['date_start'] = date('Y-m-d');
                            $extra_days = Mage::getStoreConfig('rewardpoints/default/points_duration', Mage::app()->getStore()->getId());
                            if ($extra_days){
                                $fields['date_end'] = date("Y-m-d", mktime(date("H"), date("i"), date("s"), date("n"), date("j") + $extra_days, date("Y")));
                            }
                        }
                        $reward_model->setData($fields);
                        $reward_model->setId($this->rewardpointsAccountId);
                        $reward_model->save();

                    }
                    catch (Exception $e) {
                            $connection->rollBack();
                            throw $e;
                    }
                }

                return $this;
            }

            public function load($id, $field=null) {
                    if ($field === null) {
                            $field = 'customer_id';
                    }
                    $this->customerId = $id;
                    $this->storeId = Mage::app()->getStore()->getId();

                    $reward_model = Mage::getModel('rewardpoints/stats');
                    
                    return $this;
            }

            //add and subtract points methods
            public function addPoints($p) {
                    $this->pointsCurrent += $p;
                    
            }

            public function subtractPoints($p) {
                    $this->pointsSpent = $p;
            }

            /**
             * Setters
             *
             * @param unknown_type $value
             */
            public function setRewardpointsAccountId($value){
                    $this->rewardpointsAccountId = $value;
            }

            public function setCustomerId($value){
                    $this->cutomerId = $value;
            }

            public function setStoreId($value){
                    $this->storeId = $value;
            }

            public function setPointsCurrent($value){
                    $this->pointsCurrent = $value;
            }

            

            public function setPointsSpent($value){
                    $this->pointsSpent = $value;
            }


            /**
             * Getters
             *
             * @return unknown
             */
            public function getPointsCurrent(){

                    $total = $this->getPointsReceived() - $this->getPointsSpent();
                    if ($total > 0){
                            return $total;
                    } else {
                            return 0;
                    }
            }

            public function getPointsReceived(){
                    $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', Mage::app()->getStore()->getId());
                    $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', Mage::app()->getStore()->getId());
                    
                    $order_states = explode(",", $statuses);
                    //$order_states = array("processing","complete");
                    
                    $connection = Mage::getSingleton('core/resource')
                                                            ->getConnection('rewardpoints_read');

                    
                    $select = $connection->select()->from(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account'),array(new Zend_Db_Expr('SUM('.Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.points_current) AS nb_credit'),new Zend_Db_Expr('SUM('.Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.points_spent)')));
                    if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
                        //$select->where(" (".Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').".order_id = '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW."' or '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_FB."' or '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_BIRTHDAY."' or '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN."' or ".Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').".order_id = '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REGISTRATION."'
                        $select->where(" (".Mage::getModel("rewardpoints/stats")->constructSqlPointsType(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account'))."
                                    or ".Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').".order_id in (SELECT increment_id
                                       FROM ".Mage::getSingleton('core/resource')->getTableName('sales_order')." AS orders
                                       WHERE orders.".$status_field." IN (?))
                                         ) ", $order_states);
                    } else {
                        //$select->where(" (".Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').".order_id = '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW."' or '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_FB."' or '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_BIRTHDAY."' or '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN."' or ".Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').".order_id = '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REGISTRATION."'
                        $select->where(" (".Mage::getModel("rewardpoints/stats")->constructSqlPointsType(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account'))."

                                    or ".Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').".order_id in (SELECT increment_id
                                       FROM ".Mage::getSingleton('core/resource')->getTableName('sales_order')." AS orders
                                       WHERE orders.entity_id IN (
                                           SELECT order_state.entity_id
                                           FROM ".Mage::getSingleton('core/resource')->getTableName('sales_order_varchar')." AS order_state
                                           WHERE order_state.value <> 'canceled'
                                           AND order_state.value in (?))
                                        ) ) ", $order_states);
                    }
                                                            

                    $select->where(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.customer_id=?', $this->customerId);
                    if (Mage::getStoreConfig('rewardpoints/default/store_scope', Mage::app()->getStore()->getId()) == 1){
                        $select->where('find_in_set(?, '.Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.store_id)', Mage::app()->getStore()->getId());
                    }

                    if (Mage::getStoreConfig('rewardpoints/default/points_duration', Mage::app()->getStore()->getId())){
                        $select->where('( date_end >= NOW() or date_end IS NULL)');
                    }

                    $select->group(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.customer_id');

                    $data = $connection->fetchRow($select);

                    return $data['nb_credit'];

            }

            public function getPointsWaitingValidation(){

                    $connection = Mage::getSingleton('core/resource')
                                    ->getConnection('rewardpoints_read');
                    $select = $connection->select()
                                ->from(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account'), array('SUM(points_current) AS nb_credit'))
                                ->where(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.customer_id=?', $this->customerId);
                                if (Mage::getStoreConfig('rewardpoints/default/store_scope', Mage::app()->getStore()->getId()) == 1){
                                    $select->where('find_in_set(?, '.Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.store_id)', Mage::app()->getStore()->getId());
                                }
                                //$select->where(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').".order_id <> '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN."'");
                                //$select->where(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').".order_id <> '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REGISTRATION."'");

                                if (Mage::getStoreConfig('rewardpoints/default/points_duration', Mage::app()->getStore()->getId())){
                                    $select->where('( date_end >= NOW() OR date_end IS NULL)');
                                }

                                $select->group(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.customer_id');

                    $data = $connection->fetchRow($select);
                    //return $data['nb_credit'] - $this->getPointsCurrent();
                    return $data['nb_credit'] - $this->getPointsReceived();
            }

            public function getPointsSpent(){
                    $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', Mage::app()->getStore()->getId());
                    $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', Mage::app()->getStore()->getId());

                    $order_states = explode(",", $statuses);
                    $order_states[] = 'new';

                    //$order_states = array("processing","complete","new");
                    $orders = Mage::getModel('sales/order')->getCollection()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('customer_id', $this->customerId)
                        ->addAttributeToFilter($status_field, array('in' => $order_states))
                        ->joinAttribute('status', 'order/status', 'entity_id', null, 'left');

                    $orders_array =array();

                    $orders_array[] = "'".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN."'";
                    $orders_array[] = "'".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_BIRTHDAY."'";
                    
                    $orders_array[] = "'".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REGISTRATION."'";
                    $orders_array[] = "'".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW."'";
                    
                    $orders_array[] = "'".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REFERRAL_REGISTRATION."'";
                    


                    foreach ($orders as $order){
                            $orders_array[] = "'".$order->getIncrementId()."'";
                    }

                    $connection = Mage::getSingleton('core/resource')
                                                            ->getConnection('rewardpoints_read');
                    $select = $connection->select()
                            ->from(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account'), array('SUM(points_spent) AS nb_credit'))
                            ->where(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.customer_id=?', $this->customerId)
                            ->where(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').".order_id IN (".implode(',',$orders_array).')');
                            if (Mage::getStoreConfig('rewardpoints/default/store_scope', Mage::app()->getStore()->getId()) == 1){
                                $select->where('find_in_set(?, '.Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.store_id)', Mage::app()->getStore()->getId());
                            }

                            //if (Mage::getStoreConfig('rewardpoints/default/points_duration', Mage::app()->getStore()->getId())){
                                //WHERE TO_DAYS(NOW()) - TO_DAYS(date_col) <= 30;
                                //$select->where('( date_end >= NOW() OR date_end IS NULL)');
                                /*$select->where('( (date_start IS NULL AND date_end IS NULL)
                                            OR ( (TO_DAYS(date_end) - TO_DAYS(date_start)) - (TO_DAYS(NOW()) - TO_DAYS(date_start)) ) <= '.Mage::getStoreConfig('rewardpoints/default/points_duration', Mage::app()->getStore()->getId()).'
                                            OR (NOW() >= date_start AND date_end IS NULL)
                                                )');*/
                            //}

                    $select->group(Mage::getSingleton('core/resource')->getTableName('rewardpoints_account').'.customer_id');

                    $data = $connection->fetchRow($select);

                    return $data['nb_credit'];

            }
	}
