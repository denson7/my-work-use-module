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
class J2t_Rewardpoints_Block_Adminhtml_Stats_Checkpoints extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'rewardpoints';
        $this->_controller = 'adminhtml_stats';


        $this->_updateButton('save', 'label', Mage::helper('rewardpoints')->__('Submit checking'));


        $this->_formScripts[] = "";
    }

    public function getHeaderText()
    {
        return Mage::helper('rewardpoints')->__('Reprocess point collection (points are calculated according to module configuration applied on orders)');
    }

    public function getFormHtml()
    {
        return $this->getLayout()
            ->createBlock('rewardpoints/adminhtml_stats_edit_checkform')
            ->setAction($this->getSaveUrl())
            ->toHtml();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/savecheck', array('_current'=>true));
    }
}