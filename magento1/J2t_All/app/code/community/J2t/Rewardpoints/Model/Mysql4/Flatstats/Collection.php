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
class J2t_Rewardpoints_Model_Mysql4_Flatstats_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/flatstats');
        
    }
    
    public function addCustomerId($id)
    {
        $this->getSelect()->where('user_id = ?', $id);
    }
   
    public function addStoreId($id)
    {
        $this->getSelect()->where('store_id = ?', $id);
    }
    
    public function addPointsRange($points_min, $points_max)
    {
        $this->getSelect()->where('points_current >= ?', $points_min);
        $this->getSelect()->where('points_current <= ?', $points_max);
    }
    
    public function addCheckNotificationDate($duration)
    {
        if (is_numeric($duration)){
            //$this->getResource()->formatDate(time());
            $date_duration = $this->getResource()->formatDate(mktime(0, 0, 0, date("m"), date("d")-$duration, date("Y")));
            $this->getSelect()->where('(notification_date < ? OR notification_date IS NULL)', $date_duration);
        }
        
    }
    
    public function addClientEntries()
    {
        $this->getSelect()->joinLeft(
            array('cust' => $this->getTable('customer/entity')),
            'main_table.user_id = cust.entity_id'
        );
        
        $this->getSelect()->joinLeft(
            array('fl_table' => $this->getTable('rewardpoints/flatstats')),
            'main_table.flat_account_id = fl_table.flat_account_id',
            array('current_store_id' => 'fl_table.store_id', 'current_customer_id' => 'fl_table.user_id')
        );
        
        return $this;
    }
    
    public function showCustomerInfo()
    {
        $customer = Mage::getModel('customer/customer');
        /* @var $customer Mage_Customer_Model_Customer */
        $firstname  = $customer->getAttribute('firstname');
        $lastname   = $customer->getAttribute('lastname');

        $this->getSelect()
            ->joinLeft(
                array('customer_lastname_table'=>$lastname->getBackend()->getTable()),
                'customer_lastname_table.entity_id=main_table.user_id
                 AND customer_lastname_table.attribute_id = '.(int) $lastname->getAttributeId() . '
                 ',
                array('customer_lastname'=>'value')
             )
             ->joinLeft(
                array('customer_firstname_table'=>$firstname->getBackend()->getTable()),
                'customer_firstname_table.entity_id=main_table.user_id
                 AND customer_firstname_table.attribute_id = '.(int) $firstname->getAttributeId() . '
                 ',
                array('customer_firstname'=>'value')
             );
        
        return $this;
    }
}