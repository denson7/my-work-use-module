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
abstract class J2t_Rewardpoints_Model_Mysql4_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Primery key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = true;

    /**
	 *  Magento bug fix
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->_beforeSave($object);
        $this->_checkUnique($object);

        if (!is_null($object->getId())) {
            $condition = $this->_getWriteAdapter()->quoteInto($this->getIdFieldName().'=?', $object->getId());
            /**
             * Not auto increment primary key support
             */
            if ($this->_isPkAutoIncrement) {
                $this->_getWriteAdapter()->update($this->getMainTable(), $this->_prepareDataForSave($object), $condition);
            } else {
                $select = $this->_getWriteAdapter()->select()->from($this->getMainTable(), array($this->getIdFieldName()))
                    ->where($condition);
                if ($this->_getWriteAdapter()->fetchOne($select) !== false) {
                    $this->_getWriteAdapter()->update($this->getMainTable(), $this->_prepareDataForSave($object), $condition);
                } else {
                    $this->_getWriteAdapter()->insert($this->getMainTable(), $this->_prepareDataForSave($object));
                }
            }
        } else {
            $this->_getWriteAdapter()->insert($this->getMainTable(), $this->_prepareDataForSave($object));
            $object->setId($this->_getWriteAdapter()->lastInsertId($this->getMainTable()));
        }

        $this->_afterSave($object);

        return $this;
    }

    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        if ($this->_isPkAutoIncrement && !$object->getId()) {
            $object->setCreatedAt(now());
        } else {
            $object->setCreatedAt(now());
        }
        $object->setUpdatedAt(now());
        $data = parent::_prepareDataForSave($object);
        return $data;
    }

}