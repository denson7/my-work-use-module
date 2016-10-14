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
class J2t_Rewardpoints_Model_Mysql4_Flatstats extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('rewardpoints/flatstats', 'flat_account_id');
    }
    
    public function loadByCustomerStore($customerId, $storeId, $date=null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('rewardpoints/flatstats'))
            ->where('store_id = ?', $storeId)
            ->where('user_id = ?', $customerId);
        if ($date != null){
            $select->where("main_table.last_check = ?", $date);
        }
        $result = $this->_getReadAdapter()->fetchRow($select);
        if(!$result) {
            return array();
        }

        return $result;
    }
}