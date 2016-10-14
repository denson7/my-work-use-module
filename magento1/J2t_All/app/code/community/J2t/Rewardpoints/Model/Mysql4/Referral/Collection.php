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
class J2t_Rewardpoints_Model_Mysql4_Referral_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/referral');
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $select->join(
            array('cust' => $this->getTable('rewardpoints/customer_entity')),
            'rewardpoints_referral_parent_id = cust.entity_id'
        );
        return $this;
    }

    public function addEmailFilter($email)
    {
        $this->getSelect()->where('rewardpoints_referral_email = ?', $email);
        return $this;
    }

    public function addFlagFilter($status)
    {
        $this->getSelect()->where('rewardpoints_referral_status = ?', $status);
        return $this;
    }

    public function addClientFilter($id)
    {
        $this->getSelect()->where('rewardpoints_referral_parent_id = ?', $id);
        return $this;
    }
}