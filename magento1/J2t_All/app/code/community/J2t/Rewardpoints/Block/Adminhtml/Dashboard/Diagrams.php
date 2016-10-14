<?php

class J2t_Rewardpoints_Block_Adminhtml_Dashboard_Diagrams extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rewardpoints_diagram_tab');
        $this->setDestElementId('reward_diagram_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    protected function _prepareLayout() 
    {
        $this->addTab('gather', array(
            'label'     => $this->__('Gathered Points'),
            'content'   => $this->getLayout()->createBlock('rewardpoints/adminhtml_dashboard_tab_gather')->toHtml(),
            'active'    => true
        ));

        $this->addTab('spend', array(
            'label'     => $this->__('Points Used'),
            'content'   => $this->getLayout()->createBlock('rewardpoints/adminhtml_dashboard_tab_spend')->toHtml(),
        ));
        
        return parent::_prepareLayout();
    }
}
