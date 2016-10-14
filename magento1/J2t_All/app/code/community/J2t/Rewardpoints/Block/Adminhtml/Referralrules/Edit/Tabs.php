<?php


class J2t_Rewardpoints_Block_Adminhtml_Referralrules_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('rule_id');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('rewardpoints')->__('Segments rules'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('rewardpoints')->__('Rule Information'),
            'title'     => Mage::helper('rewardpoints')->__('Rule Information'),
            'content'   => $this->getLayout()->createBlock('rewardpoints/adminhtml_referralrules_edit_tab_main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('conditions_section', array(
            'label'     => Mage::helper('rewardpoints')->__('Conditions'),
            'title'     => Mage::helper('rewardpoints')->__('Conditions'),
            'content'   => $this->getLayout()->createBlock('rewardpoints/adminhtml_referralrules_edit_tab_conditions')->toHtml(),
        ));

        $this->addTab('actions_section', array(
            'label'     => Mage::helper('rewardpoints')->__('Actions'),
            'title'     => Mage::helper('rewardpoints')->__('Actions'),
            'content'   => $this->getLayout()->createBlock('rewardpoints/adminhtml_referralrules_edit_tab_actions')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}
