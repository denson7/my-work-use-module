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
class J2t_Rewardpoints_Model_Mysql4_Stats_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_countAttribute = 'main_table.customer_id';
    //main_table.rewardpoints_account_id
    protected $_allowDisableGrouping = true;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/stats');
    }
    
    public function setCountAttribute($value)
    {
        $this->_countAttribute = $value;
        return $this;
    }
    
   
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        if ($this->_allowDisableGrouping) {
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->columns('COUNT(DISTINCT ' . $this->getCountAttribute() . ')');
        }
        return $countSelect;
    }
    
    public function getCountAttribute()
    {
        return $this->_countAttribute;
    }
    
    public function addStartEndDays()
    {
        $select = $this->getSelect();
        $date = date('Y-m-d');
        //$date_end = $this->getResource()->formatDate(mktime(0, 0, 0, date("m"), date("d")+1, date("Y"));
        $select->where('(date_end = ? AND date_start IS NOT NULL) OR (date_start = ? AND date_start IS NOT NULL)', $date, $date);
        
        return $this;
    }
    
    public function addListRestriction()
    {
        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', Mage::app()->getStore()->getId());
        $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses', Mage::app()->getStore()->getId());        
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', Mage::app()->getStore()->getId());

        $order_states = explode(",", $statuses);
        $order_states_used = explode(",", $statuses_used);
        
        parent::_initSelect();
        $select = $this->getSelect();
        
        
        $select
            ->from($this->getTable('rewardpoints/rewardpoints_account'),array(new Zend_Db_Expr('SUM('.$this->getTable('rewardpoints/rewardpoints_account').'.points_current) AS all_points_accumulated'),new Zend_Db_Expr('SUM('.$this->getTable('rewardpoints/rewardpoints_account').'.points_spent) AS all_points_spent')))
            ->where($this->getTable('rewardpoints/rewardpoints_account').'.customer_id = e.entity_id');


        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            /*$select->where(" (".Mage::getModel("rewardpoints/stats")->constructSqlPointsType($this->getTable('rewardpoints/rewardpoints_account'))."
                   or ".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?)
                 ) ", $order_states);*/
            $select->where(" (".Mage::getModel("rewardpoints/stats")->constructSqlPointsType($this->getTable('rewardpoints/rewardpoints_account'))."
                   or (".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?)  AND ".$this->getTable('rewardpoints/rewardpoints_account').".points_current > 0)
                   or (".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?)  AND ".$this->getTable('rewardpoints/rewardpoints_account').".points_spent > 0)
                 ) ", $order_states, $order_states_used);
        } else {
            /*$select->where(" (".Mage::getModel("rewardpoints/stats")->constructSqlPointsType($this->getTable('rewardpoints/rewardpoints_account'))."
                   or ".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_state in ((?)
                                        ) ", $order_states);*/
            $select->where(" (".Mage::getModel("rewardpoints/stats")->constructSqlPointsType($this->getTable('rewardpoints/rewardpoints_account'))."
                   or (".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?)  AND ".$this->getTable('rewardpoints/rewardpoints_account').".points_current > 0)
                   or (".$this->getTable('rewardpoints/rewardpoints_account').".rewardpoints_$status_field in (?)  AND ".$this->getTable('rewardpoints/rewardpoints_account').".points_spent > 0)
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
        return $this;
    }
    
    
    

    public function setPriorityOrder($dir = 'ASC')
    {
        $this->setOrder('main_table.priority', $dir);
        return $this;
    }

    public function addClientFilter($id)
    {
        $this->_countAttribute = 'main_table.rewardpoints_account_id';
        $this->getSelect()->where('customer_id = ?', $id);
        return $this;
    }
	
	public function addOrderFilter($id)
    {
        $this->getSelect()->where('order_id = ?', $id);
        return $this;
    }

    
    public function groupByCustomer()
    {
        $this->getSelect()->group('main_table.customer_id');
        $this->_allowDisableGrouping = false;

        return $this;
    }
    
    public function addFinishFilter($days)
    {
        //for example, DATEDIFF('1997-12-30','1997-12-25') returns 5
        $this->getSelect()->where('( DATEDIFF(main_table.date_end, NOW()) = ? AND main_table.date_end IS NOT NULL)', $days);
        return $this;
    }
    
    
    public function showCustomerInfo()
    {
        $customer = Mage::getModel('customer/customer');
        /* @var $customer Mage_Customer_Model_Customer */
        $firstname  = $customer->getAttribute('firstname');
        $lastname   = $customer->getAttribute('lastname');

//        $customersCollection = Mage::getModel('customer/customer')->getCollection();
//        /* @var $customersCollection Mage_Customer_Model_Entity_Customer_Collection */
//        $firstname = $customersCollection->getAttribute('firstname');
//        $lastname  = $customersCollection->getAttribute('lastname');

        $this->getSelect()
            ->joinLeft(
                array('customer_lastname_table'=>$lastname->getBackend()->getTable()),
                'customer_lastname_table.entity_id=main_table.customer_id
                 AND customer_lastname_table.attribute_id = '.(int) $lastname->getAttributeId() . '
                 ',
                array('customer_lastname'=>'value')
             )
             ->joinLeft(
                array('customer_firstname_table'=>$firstname->getBackend()->getTable()),
                'customer_firstname_table.entity_id=main_table.customer_id
                 AND customer_firstname_table.attribute_id = '.(int) $firstname->getAttributeId() . '
                 ',
                array('customer_firstname'=>'value')
             );
        
        

        return $this;
    }
    
    
    
    public function joinEavTablesIntoCollection($mainTableForeignKey, $eavType){
 
        
        $entityType = Mage::getModel('eav/entity_type')->loadByCode($eavType);
        $attributes = $entityType->getAttributeCollection();
        $entityTable = $this->getTable($entityType->getEntityTable());
 
        //Use an incremented index to make sure all of the aliases for the eav attribute tables are unique.
        $index = 1;
        
        
        $fields = array();
        foreach (Mage::getConfig()->getFieldset('customer_account') as $code=>$node) {
            if ($node->is('name')) {
                //$this->addAttributeToSelect($code);
                $fields[$code] = $code;
            }
        }
        
        $expr = 'CONCAT('
            .(isset($fields['prefix']) ? 'IF({{prefix}} IS NOT NULL AND {{prefix}} != "", CONCAT({{prefix}}," "), ""),' : '')
            .'{{firstname}}'.(isset($fields['middlename']) ?  ',IF({{middlename}} IS NOT NULL AND {{middlename}} != "", CONCAT(" ",{{middlename}}), "")' : '').'," ",{{lastname}}'
            .(isset($fields['suffix']) ? ',IF({{suffix}} IS NOT NULL AND {{suffix}} != "", CONCAT(" ",{{suffix}}), "")' : '')
        .')';
        
        
        foreach ($attributes->getItems() as $attribute){
            $alias = 'table'.$index;
            if ($attribute->getBackendType() != 'static'){
                $table = $entityTable. '_'.$attribute->getBackendType();
                $field = $alias.'.value';
                $this->getSelect()
                ->joinLeft(array($alias => $table),
                       'main_table.'.$mainTableForeignKey.' = '.$alias.'.entity_id and '.$alias.'.attribute_id = '.$attribute->getAttributeId(),
                array($attribute->getAttributeCode() => $field)
                );
                $expr = str_replace('{{'.$attribute->getAttributeCode().'}}', $field, $expr);
                
            }
            $index++;
        }
        
        
        
        //Join in all of the static attributes by joining the base entity table.
        $this->getSelect()->joinLeft($entityTable, 'main_table.'.$mainTableForeignKey.' = '.$entityTable.'.entity_id');
        
        $this->getSelect()->columns(array('name' => $expr));
        
        
        
        return $this;
    }
    
    
    public function addClientEntries()
    {
        $this->getSelect()->joinLeft(
            array('cust' => $this->getTable('customer/entity')),
            'main_table.customer_id = cust.entity_id'
        );
        
        return $this;
    }
    
    
    /*public function addpointsbydate($store_id, $customer_id, $date){
        $this->getSelect()->where("customer_id = ?", $customer_id);
        
        $this->getSelect()->where("( ? >= main_table.date_start )", $date);
        $this->getSelect()->where("( main_table.date_end >= ? )", $date);
        
        $this->addValidPoints($store_id, true);
        return $this;
    }*/
    
    public function addUsedpointsbydate($store_id, $customer_id){
        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', $store_id);
        $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses', $store_id);
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', $store_id);
        
        $order_states = explode(",", $statuses);
        $order_states_used = explode(",", $statuses_used);
        
        $cols['points_spent'] = 'SUM(main_table.points_spent) as nb_credit_spent';
        //$cols['date_order'] = 'DATE_FORMAT(main_table.date_order, "%Y-%m-%d") as date_order';
        //date_insertion replaces date_order in order to eliminate any issues (for example, when inserting point usage from admin - user area)
        $cols['date_order'] = 'DATE_FORMAT(main_table.date_insertion, "%Y-%m-%d") as date_order';
        
        
        //selection de tous les points utilisés à x date
        $this->getSelect()->from($this->getResource()->getMainTable().' as child_table', $cols);
        //$cols_order['date_order'] = 'DATE_FORMAT(orders.created_at, "%Y-%m-%d") as date_order';
        //$this->getSelect()->from($this->getTable('sales/order').' as orders', $cols_order);
        
        
        foreach (Mage::getModel("rewardpoints/stats")->getPointsDefaultTypeToArray() as $key => $value){
            //remove admin point
            if ($key != J2t_Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN){
                $only_valid_types[] = $key;
            }
        }
        
        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            //$this->getSelect()->where("main_table.rewardpoints_state in (?,'new') OR main_table.rewardpoints_state IS NULL", $order_states);
            $this->getSelect()->where("(main_table.rewardpoints_state in (?,'new') AND main_table.points_current > 0) OR (main_table.rewardpoints_state in (?,'new') AND main_table.points_spent > 0) OR main_table.rewardpoints_state IS NULL", $order_states, $order_states_used);
            $this->getSelect()->where("main_table.customer_id = ?", $customer_id);
            $this->getSelect()->where("main_table.points_spent > 0");
            $this->getSelect()->where("main_table.order_id NOT IN (?)", $only_valid_types);
        } else {
            //J2T magento 1.3.x fix
            //$this->getSelect()->where("main_table.rewardpoints_state in (?,'new') OR main_table.rewardpoints_state IS NULL", $order_states);
            $this->getSelect()->where("(main_table.rewardpoints_state in (?,'new') AND main_table.points_current > 0) OR (main_table.rewardpoints_state in (?,'new') AND main_table.points_spent > 0) OR main_table.rewardpoints_state IS NULL", $order_states, $order_states_used);
            $this->getSelect()->where("main_table.customer_id = ?", $customer_id);
            $this->getSelect()->where("main_table.points_spent > 0");
            $this->getSelect()->where("main_table.order_id NOT IN (?)", $only_valid_types);
            
        }
        
        $this->getSelect()->where('main_table.rewardpoints_account_id = child_table.rewardpoints_account_id');

        if (Mage::getStoreConfig('rewardpoints/default/store_scope', $store_id)){
            $this->getSelect()->where('find_in_set(?, main_table.store_id)', $store_id);
        }

        $this->getSelect()->group('date_order');
        
        /*echo $this->getSelect()->__toString();
        die;*/
        
        return $this;
        
    }
    
    public function addValidPoints($store_id, $unset_date_limits = false, $no_sum = false)
    {
        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', $store_id);
        $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses', $store_id);
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', $store_id);
        
        $order_states = explode(",", $statuses);
        $order_states_used = explode(",", $statuses_used);
        
        if (!$no_sum){
            $cols['points_current'] = 'SUM(main_table.points_current) as nb_credit';
            $cols['points_spent'] = 'SUM(main_table.points_spent) as nb_credit_spent';
            $cols['points_available'] = '(SUM(main_table.points_current) - SUM(main_table.points_spent)) as nb_credit_available';
            $this->getSelect()->from($this->getResource()->getMainTable().' as child_table', $cols);
        }


        // checking if module rewardshare is available
        $sql_share = "";
        $sql_required = "";
        //J2T magento 1.3.x fix
        if (class_exists('J2t_Rewardshare_Model_Stats', false)){
            $sql_share = "main_table.order_id = '".J2t_Rewardshare_Model_Stats::TYPE_POINTS_SHARE."' or";
        }
        
        if (Mage::getConfig()->getModuleConfig('J2t_Rewardproductvalue')->is('active', 'true')){
            if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
                $sql_required = "(  
                                    main_table.order_id = '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REQUIRED ."'
                                    AND main_table.rewardpoints_$status_field in (?,'new')    
                                 ) or ";
                
            }
        }
        
        
        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            $this->getSelect()->where(" (".Mage::getModel("rewardpoints/stats")->constructSqlPointsType("main_table")."
                        OR ( main_table.rewardpoints_$status_field = 'new' AND main_table.points_spent > 0)
                        OR ( main_table.rewardpoints_$status_field in (?) AND main_table.points_current > 0 )
                        OR ( main_table.rewardpoints_$status_field in (?) AND main_table.points_spent > 0 )    
                        )", $order_states, $order_states_used);
            //$order_states_used
        } else {
            
            //J2T magento 1.3.x fix
            
            $table_sales_order = $this->getTable('sales/order').'_varchar';
            
            $this->getSelect()->where(" (".Mage::getModel("rewardpoints/stats")->constructSqlPointsType("main_table")."
                        OR ( main_table.rewardpoints_state = 'new' AND main_table.points_spent > 0)
                        OR ( main_table.rewardpoints_state in (?) AND main_table.points_current > 0 )
                        OR ( main_table.rewardpoints_state in (?) AND main_table.points_spent > 0 )
                        )", $order_states, $order_states_used);
            
        }
        
        if (!$no_sum){
            $this->getSelect()->where('main_table.rewardpoints_account_id = child_table.rewardpoints_account_id');
        }

        if (Mage::getStoreConfig('rewardpoints/default/store_scope', $store_id)){
            $this->getSelect()->where('find_in_set(?, main_table.store_id)', $store_id);
        }

        //v.2.0.0
        if (Mage::getStoreConfig('rewardpoints/default/points_delay', $store_id) && !$unset_date_limits){
            $this->getSelect()->where('( NOW() >= main_table.date_start OR main_table.date_start IS NULL)');
        }

        if (Mage::getStoreConfig('rewardpoints/default/points_duration', $store_id) && !$unset_date_limits){
            $this->getSelect()->where('( main_table.date_end >= NOW() or main_table.date_end IS NULL)');
        }
        
        if (!$no_sum){
            $this->getSelect()->group('main_table.customer_id');
        }
        
        /*echo $this->getSelect()->__toString();
        die;*/
        
        return $this;
    }


    public function joinValidOrders($customer_id, $order_states)
    {

        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', Mage::app()->getStore()->getId());
        /*$this->getSelect()->joinLeft(
            array('ord' => $this->getTable('sales/order')),
            'main_table.order_id = ord.entity_id'
        );
        $this->getSelect()->where('ord.customer_id = ?', $customer_id);
        $this->getSelect()->where($status_field.' in (?)', $order_states);
        */
        
        return $this;
    }

    public function joinFullCustomerPoints($customer_id, $store_id){

        $cols['points_current'] = 'SUM(main_table.points_current) as nb_credit';

        $this->getSelect()->from($this->getResource()->getMainTable().' as child_table', $cols)
                ->where('main_table.customer_id=?', $customer_id)
                ->where('main_table.rewardpoints_account_id = child_table.rewardpoints_account_id');

        if (Mage::getStoreConfig('rewardpoints/default/store_scope', $store_id) == 1){
            $this->getSelect()->where('find_in_set(?, main_table.store_id)', $store_id);
        }
        
        
        //v.2.0.0
        /*if (Mage::getStoreConfig('rewardpoints/default/points_delay', $store_id)){
            $this->getSelect()->where('( NOW() >= main_table.date_start OR main_table.date_start IS NULL)');
        }*/

        if (Mage::getStoreConfig('rewardpoints/default/points_duration', $store_id)){
            $this->getSelect()->where('( main_table.date_end >= NOW() OR main_table.date_end IS NULL)');
        }
        $this->getSelect()->group('main_table.customer_id');

        return $this;
    }

   
    public function joinValidPointsOrder($customer_id, $store_id, $order_states, $order_states_used, $spent = false, $remove_end = false, $no_groupby = false, $specific_order_ids = array(), $no_sum = false, $remove_start = false)
    {
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', $store_id);
        $sql_required = "";
        
        $adapter = $this->getSelect()->getAdapter();
        
        if (Mage::getConfig()->getModuleConfig('J2t_Rewardproductvalue')->is('active', 'true') && $specific_order_ids == array()){
            $sql_required = sprintf("(  
                                main_table.order_id = '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REQUIRED ."'
                                AND main_table.rewardpoints_$status_field in (%s)
                             ) or ", $adapter->quote($order_states_used));
        }
        
        if (Mage::getConfig()->getModuleConfig('J2t_Rewardshare')->is('active', 'true') && $specific_order_ids == array()){
            $sql_required .= " main_table.order_id = '".J2t_Rewardshare_Model_Stats::TYPE_POINTS_SHARE."' or";
        }
        
        if (!$no_sum){
            if ($spent){
                $cols['points_spent'] = 'SUM(main_table.points_spent) as nb_credit';
            } else {
                $cols['points_current'] = 'SUM(main_table.points_current) as nb_credit';
                $cols['points_spent'] = 'SUM(main_table.points_spent)';
            }
            $this->getSelect()->from($this->getResource()->getMainTable().' as child_table', $cols);
        }
        
        if ($specific_order_ids == array()) {
            $this->getSelect()->where(sprintf(" ( $sql_required ".Mage::getModel("rewardpoints/stats")->constructSqlPointsType("main_table")."
                        OR (main_table.rewardpoints_$status_field in (%s) AND main_table.points_current > 0)
                        OR (main_table.rewardpoints_$status_field in (%s) AND main_table.points_spent > 0)
                        )", $adapter->quote($order_states), $adapter->quote($order_states_used)));
        } else {
            $this->getSelect()->where(" ( $sql_required ".Mage::getModel("rewardpoints/stats")->constructSqlPointsType("main_table", $specific_order_ids)."
                        )");
        }
       
        if ($customer_id !== false){
            $this->getSelect()->where('main_table.customer_id = ?', $customer_id);
        }
        if (!$no_sum){
            $this->getSelect()->where('main_table.rewardpoints_account_id = child_table.rewardpoints_account_id');
        }

        if (Mage::getStoreConfig('rewardpoints/default/store_scope', Mage::app()->getStore()->getId()) == 1 && $store_id !== false){
            $this->getSelect()->where('find_in_set(?, main_table.store_id)', $store_id);
        }

        //v.2.0.0
        if ((Mage::getStoreConfig('rewardpoints/default/points_delay', $store_id) && !$spent) && !$remove_start){
            $this->getSelect()->where('( NOW() >= main_table.date_start OR main_table.date_start IS NULL)');
        }

        if ((Mage::getStoreConfig('rewardpoints/default/points_duration', $store_id) && !$spent) && !$remove_end){
            $this->getSelect()->where('( main_table.date_end >= NOW() or main_table.date_end IS NULL)');
        }
        if (!$no_groupby){
            $this->getSelect()->group('main_table.customer_id');
        } else if ($specific_order_ids != array()){
            $this->getSelect()->group('main_table.order_id');
        }

        //echo $this->getSelect()->__toString();
        //die;
        
        return $this;
    }
    
    
    public function pointsByDate($dir = self::SORT_ORDER_DESC)
    {
        $this->setOrder('date_start ',  $dir);
        return $this;
    }
    
    
    public function prepareSummary($range, $customStart, $customEnd, $isFilter = 0)
    {
        $this->_prepareSummaryAggregated($range, $customStart, $customEnd, $isFilter);
        return $this;
    }
    
    public function addCreateAtPeriodFilter($period)
    {
        list($from, $to) = $this->getDateRange($period, 0, 0, true);

        $fieldToFilter = 'main_table.date_insertion';

        $this->addFieldToFilter($fieldToFilter, array(
            'from'  => $from->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
            'to'    => $to->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
        ));

        return $this;
    }
    
    
    public function calculateTotals($onlyNonOrders = false)
    {
        $this->setMainTable('rewardpoints/stats');
        $this->removeAllFieldsFromSelect();
        $adapter = $this->getConnection();

        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses');
        //$statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses', $store_id);
        $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses'); 
        if (!$onlyNonOrders){
            $this->getSelect()->columns(
                array(
                    'all_points_gathered'   => new Zend_Db_Expr('SUM(main_table.points_current)'),
                    'all_points_spent'      => new Zend_Db_Expr('SUM(main_table.points_spent)')
                )
            );
            $this->joinValidPointsOrder(false, false, explode(",", $statuses), explode(",", $statuses_used), false, true, true, array(), true);
        } else {
            $this->getSelect()->columns(
                array(
                    'all_points_gathered'   => new Zend_Db_Expr('SUM(main_table.points_current)'),
                    'all_points_spent'      => new Zend_Db_Expr('SUM(main_table.points_spent)'),
                    'order_id'              => 'main_table.order_id'
                )
            );
            $this->joinValidPointsOrder(false, false, explode(",", $statuses), explode(",", $statuses_used), false, true, true, Mage::getModel("rewardpoints/stats")->getOnlyPointsTypesArray(), true);
        }
        
        //echo $this->getSelect()->__toString();
        //die;

        return $this;
    }
   
    protected function getConcatSql(array $data, $separator = null)
    {
        $format = empty($separator) ? 'CONCAT(%s)' : "CONCAT_WS('{$separator}', %s)";
        return new Zend_Db_Expr(sprintf($format, implode(', ', $data)));
    }
    
    protected function getDateFormatSql($date, $format)
    {
        $expr = sprintf("DATE_FORMAT(%s, '%s')", $date, $format);
        return new Zend_Db_Expr($expr);
    } 
    
    protected function _getRangeExpression($range)
    {
        switch ($range)
        {
            case '24h':
                /*$expression = $this->getConnection()->getConcatSql(array(
                    $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d %H:'),
                    $this->getConnection()->quote('00')
                ));*/
		$expression = $this->getConcatSql(array(
                    $this->getDateFormatSql('{{attribute}}', '%Y-%m-%d %H:'),
                    $this->getConnection()->quote('00')
                ));
                break;
            case '7d':
            case '1m':
                //$expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d');
                $expression = $this->getDateFormatSql('{{attribute}}', '%Y-%m-%d');
		break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                //$expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m');
                $expression = $this->getDateFormatSql('{{attribute}}', '%Y-%m');
		break;
        }

        return $expression;
    }
    
    protected function _getRangeExpressionForAttribute($range, $attribute)
    {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), $expression);
    }
    
    protected function _prepareSummaryAggregated($range, $customStart, $customEnd)
    {
        $this->setMainTable('rewardpoints/stats');
        /**
         * Reset all columns, because result will group only by 'date_insertion' or 'period' field
         */
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        
        if ($range == '24h'){
            $rangePeriod = $this->_getRangeExpressionForAttribute($range, 'main_table.date_insertion');
            $tableName = $this->getConnection()->quoteIdentifier('main_table.date_insertion');
        } else {
            $rangePeriod = $this->_getRangeExpressionForAttribute($range, 'main_table.period');
            $tableName = $this->getConnection()->quoteIdentifier('main_table.period');
        }

        $rangePeriod2 = str_replace($tableName, "MIN($tableName)", $rangePeriod);
        $this->getSelect()->columns(array(
            'points_current'  => 'SUM(main_table.points_current)',
            'points_spent' => 'SUM(main_table.points_spent)',
            'range' => $rangePeriod2,
        ))
        ->order('range')
        ->group($rangePeriod);

        if ($range == '24h'){
            $this->getSelect()->where(
                $this->_getConditionSql('main_table.date_insertion', $this->getDateRange($range, $customStart, $customEnd))
            );
        } else {
            $this->getSelect()->where(
                $this->_getConditionSql('main_table.period', $this->getDateRange($range, $customStart, $customEnd))
            );
        }
        
        
        
        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses');
        $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses');
        $this->joinValidPointsOrder(false, false, explode(",", $statuses), explode(",", $statuses_used), false, true, true, array(), true);

        /*$statuses = Mage::getSingleton('sales/config')
            ->getOrderStatusesForState(Mage_Sales_Model_Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }
        $this->addFieldToFilter('main_table.order_status', array('nin' => $statuses));*/
        
        /*
        echo $this->getSelect()->__toString();
        die;*/

        return $this;
    }

    
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd   = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);

        switch ($range)
        {
            case '24h':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subDay(1);
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;

            case '1m':
                $dateStart->setDay(Mage::getStoreConfig('reports/dashboard/mtd_start'));
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd   = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(',', Mage::getStoreConfig('reports/dashboard/ytd_start'));
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                if ($range == '2y') {
                    $dateStart->subYear(1);
                }
                break;
        }

        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');

        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
        }
    }

}
