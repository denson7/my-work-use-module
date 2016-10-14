<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class J2t_Rewardpoints_Block_Adminhtml_Referralrules extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_referralrules';
        $this->_blockGroup = 'rewardpoints';
        $this->_headerText = Mage::helper('rewardpoints')->__('Referral Point Rules');
        //$this->_addButton ('apply_rules', array('label'=> Mage::helper('rewardpoints')->__('Apply all rules'), 'class' => 'save', 'onclick'   => 'setLocation(\''.$this->getApplyRulesUrl().'\')'));
        parent::__construct();
    }

    public function getApplyRulesUrl()
    {
        return $this->getUrl('*/*/applyall');
    }
}
