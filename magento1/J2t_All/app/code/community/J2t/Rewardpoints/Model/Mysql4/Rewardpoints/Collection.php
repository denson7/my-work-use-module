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
class J2t_Rewardpoints_Model_Mysql4_Rewardpoints_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_selectedColumns = array(
        'rewardpoints_account_id'   => 'rewardpoints_account_id',
        'customer_id'               => 'customer_id',
        'cust.email'                     => 'cust.email',
        'order_id'                  => 'order_id',
        'points_current'            => 'points_current',
        'points_spent'              => 'points_spent',
        'rewardpoints_description'  => 'rewardpoints_description',
        'rewardpoints_linker'       => 'rewardpoints_linker',
		'rewardpoints_firstorder'       => 'rewardpoints_firstorder',
        'store_ids'                 => 'main_table.store_id'
    );

    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/stats');
    }


    

    protected function _initSelect()
    {
        /*parent::_initSelect();
        
        $select = $this->getSelect();
        $select->join(
            array('cust' => $this->getTable('rewardpoints/customer_entity')),
            'customer_id = cust.entity_id'
        );
        return $this;*/

        $select = $this->getSelect();
        //$select->from(array('main_table' => $this->getMainTable()), $this->_selectedColumns);
        //$select->from(array('main_table' => $this->getTable('rewardpoints/stats')), $this->_selectedColumns);
        $select->from(array('main_table' => $this->getResource()->getMainTable()), $this->_selectedColumns);

        $select->join(
            array('cust' => $this->getTable('rewardpoints/customer_entity')),
            'main_table.customer_id = cust.entity_id'
        );

        return $this;
    }


    public function joinUser()
    {
        /*$this->getSelect()->join(
            array('cust' => $this->getTable('j2tbooster/customer_entity')),
            'main_table.customer_id = cust.entity_id'
        );*/

        $this->getSelect()
            ->joinLeft($this->getTable('j2tbooster/customer_entity'),
                $this->getTable('j2tbooster/customer_entity').".entity_id=main_table.customer_id",
            array('email'));


        return $this;
    }


    public function joinValidOrders($customer_id)
    {
        $order_states = array("processing","complete","new");
        
        $this->getSelect()->joinLeft(
            array('ord' => $this->getTable('sales/order')),
            'main_table.order_id = ord.entity_id'
        );

        $this->getSelect()->where('ord.customer_id = ?', $customer_id);
        $this->getSelect()->where('state in (?)', $order_states);


        return $this;
    }

    public function addCustomerFilter($id)
    {
        $this->getSelect()->where('customer_id = ?', $id);
        return $this;
    }

    public function addOrderFilter($id)
    {
        $this->getSelect()->where('order_id = ?', $id);
        return $this;
    }
    
    public function addStoreFilter($id)
    {
        //$this->getSelect()->where('main_table.store_id in (?)', $id);
        $this->getSelect()->where('FIND_IN_SET(?, main_table.store_id) > 0', $id);
        return $this;
    }
    
    /*public function groupByCustomer()
    {
        $this->group('main_table.customer_id');
        return $this;
    }*/



    public function addStoreData()
    {
        foreach ($this as $item) {
            $item->setStores(explode(',',$item->getStoreIds()));
        }

        return $this;
    }


    public function addFieldToFilter($field, $condition=null)
    {
        if ($field == 'stores') {
            return $this->addStoresFilter($condition);
        }
        else {
            return parent::addFieldToFilter($field, $condition);
        }
    }


    public function addStoresFilter($store)
    {
        return $this->addStoreFilter($store);
    }


    


}