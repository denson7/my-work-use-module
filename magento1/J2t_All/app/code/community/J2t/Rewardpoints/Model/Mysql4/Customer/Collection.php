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
class J2t_Rewardpoints_Model_Mysql4_Customer_Collection extends Mage_Customer_Model_Entity_Customer_Collection
{
    public function restrictRewardPoints()
    {

        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', Mage::app()->getStore()->getId());
        $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses', Mage::app()->getStore()->getId());
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', Mage::app()->getStore()->getId());

        $order_states = explode(",", $statuses);
        $order_states_used = explode(",", $statuses_used);

        //parent::_initSelect();
        $select = $this->getSelect();
        $select
            ->from($this->getTable('rewardpoints/rewardpoints_account'),array(new Zend_Db_Expr('SUM('.$this->getTable('rewardpoints/rewardpoints_account').'.points_current) AS all_points_accumulated'),new Zend_Db_Expr('SUM('.$this->getTable('rewardpoints/rewardpoints_account').'.points_spent) AS all_points_spent')))
            ->where($this->getTable('rewardpoints/rewardpoints_account').'.customer_id = e.entity_id');

        
        $sql_share = "";
        if (class_exists (J2t_Rewardshare_Model_Stats)){
            $sql_share = $this->getTable('rewardpoints/rewardpoints_account').".order_id = '".J2t_Rewardshare_Model_Stats::TYPE_POINTS_SHARE."' or";
        }

        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            /*$select->where(" ($sql_share ".Mage::getModel("rewardpoints/stats")->constructSqlPointsType($this->getTable('rewardpoints/rewardpoints_account'))."
                   or ".$this->getTable('rewardpoints/rewardpoints_account').".order_id in  (SELECT increment_id
                       FROM ".$this->getTable('sales/order')." AS orders
                       WHERE orders.$status_field IN (?))
                 ) ", $order_states);*/
            
            /*$select->where(" ($sql_share ".Mage::getModel("rewardpoints/stats")->constructSqlPointsType($this->getTable('rewardpoints/rewardpoints_account'))."
                   or ".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?)
                 ) ", $order_states);*/
            $select->where(" ($sql_share ".Mage::getModel("rewardpoints/stats")->constructSqlPointsType($this->getTable('rewardpoints/rewardpoints_account'))."
                   or (".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?) AND ".$this->getTable('rewardpoints/rewardpoints_account').".points_current > 0 ) 
                   or (".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?) AND ".$this->getTable('rewardpoints/rewardpoints_account').".points_spent > 0 ) 
                 ) ", $order_states, $order_states_used);
            
            //$order_states_used
            
            //main_table.rewardpoints_$status_field in (?,'new')
            
        } else {
            $table_sales_order = $this->getTable('sales/order').'_varchar';
            /*$select->where(" ($sql_share ".Mage::getModel("rewardpoints/stats")->constructSqlPointsType($this->getTable('rewardpoints/rewardpoints_account'))."
                   or ".$this->getTable('rewardpoints/rewardpoints_account').".order_id in (SELECT increment_id
                                       FROM ".$this->getTable('sales/order')." AS orders
                                       WHERE orders.entity_id IN (
                                           SELECT order_state.entity_id
                                           FROM ".$table_sales_order." AS order_state
                                           WHERE order_state.value <> 'canceled'
                                           AND order_state.value in (?))
                                        ) ) ", $order_states);*/
            
            /*$select->where(" ($sql_share ".Mage::getModel("rewardpoints/stats")->constructSqlPointsType($this->getTable('rewardpoints/rewardpoints_account'))."
                   or ".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?)
                 ) ", $order_states);*/
            $select->where(" ($sql_share ".Mage::getModel("rewardpoints/stats")->constructSqlPointsType($this->getTable('rewardpoints/rewardpoints_account'))."
                   or (".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?) AND ".$this->getTable('rewardpoints/rewardpoints_account').".points_current > 0 ) 
                   or (".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?) AND ".$this->getTable('rewardpoints/rewardpoints_account').".points_spent > 0 ) 
                 ) ", $order_states, $order_states_used);
            
        }


        //v.2.0.0
        if (Mage::getStoreConfig('rewardpoints/default/points_delay', Mage::app()->getStore()->getId())){
            $this->getSelect()->where('( NOW() >= '.$this->getTable('rewardpoints/rewardpoints_account').'.date_start OR '.$this->getTable('rewardpoints/rewardpoints_account').'.date_start IS NULL)');
        }
        
        if (Mage::getStoreConfig('rewardpoints/default/points_duration', Mage::app()->getStore()->getId())){
            $select->where('( '.$this->getTable('rewardpoints/rewardpoints_account').'.date_end >= NOW() OR '.$this->getTable('rewardpoints/rewardpoints_account').'.date_end IS NULL)');
        }

        $select->group($this->getTable('rewardpoints/rewardpoints_account').'.customer_id');

        /*echo $this->getSelect()->__toString();
        die;*/
        
        return $this;
    }

}