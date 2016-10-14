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
class J2t_Rewardpoints_Block_Adminhtml_Stats extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_customer;


    public function __construct()
    {
        /*$this->_controller = 'adminhtml_stats';
        $this->_blockGroup = 'rewardpoints';
        $this->_headerText = Mage::helper('rewardpoints')->__('Statistics');
        parent::__construct();
        */

        $this->_controller = 'adminhtml_stats';
        $this->_blockGroup = 'rewardpoints';
        $this->_headerText = Mage::helper('rewardpoints')->__('Statistics');
        parent::__construct();
        $this->_addButtonLabel = Mage::helper('rewardpoints')->__('Add Points');
        $this->_addButton ('check_all_points', array('label'=> Mage::helper('rewardpoints')->__('Reprocess point collection'), 'class' => 'save', 'onclick'   => 'setLocation(\''.$this->getCheckPointsUrl().'\')'));
        $this->_addButton ('check_user_points', array('label'=> Mage::helper('rewardpoints')->__('Refresh all customer points'), 'class' => 'save', 'onclick'   => 'if (confirm(\''.Mage::helper('rewardpoints')->__('This operation will gather all collected customer points in order to be managed in a faster way. This process may take a long time, do you want to proceed?').'\')) {setLocation(\''.$this->getCheckAllPointsUrl().'\')}'));
    }

    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = Mage::registry('current_customer');
        }
        return $this->_customer;
    }

    public function getStoreId()
    {
        return $this->getCustomer()->getStoreId();
    }

    public function getCheckPointsUrl()
    {
        return $this->getUrl('*/*/checkpoints');
    }
    
    public function getCheckAllPointsUrl()
    {
        return $this->getUrl('*/*/allcustomerpoints');
    }

}