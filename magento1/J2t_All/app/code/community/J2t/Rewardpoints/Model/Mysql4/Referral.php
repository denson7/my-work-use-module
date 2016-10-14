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
class J2t_Rewardpoints_Model_Mysql4_Referral extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('rewardpoints/referral', 'rewardpoints_referral_id');
    }

    public function loadByEmail($customerEmail)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('rewardpoints/rewardpoints_referral'))
            ->where('rewardpoints_referral_email = ?',$customerEmail);
        $result = $this->_getReadAdapter()->fetchRow($select);
        if(!$result) {
            return array();
        }

        return $result;
    }
    
    //J2T Check referral
    public function loadByChildId($child_id)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('rewardpoints/rewardpoints_referral'))
            ->where('rewardpoints_referral_child_id = ?',$child_id);
        $result = $this->_getReadAdapter()->fetchRow($select);
        if(!$result) {
            return array();
        }
        

        return $result;
    }
    
}