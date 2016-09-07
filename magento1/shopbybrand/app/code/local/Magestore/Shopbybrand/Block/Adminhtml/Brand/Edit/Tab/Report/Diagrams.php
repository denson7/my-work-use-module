<?php
class Magestore_Shopbybrand_Block_Adminhtml_Brand_Edit_Tab_Report_Diagrams extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
        parent::__construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }
    
    protected function _prepareLayout(){
    	$this->addTab('graph',array(
    		'label'		=> $this->__('Number of loyal members'),
    		'content'	=> $this->getLayout()->createBlock('shopbybrand/adminhtml_brand_edit_tab_report_graph')->toHtml(),
    		'active'	=> true,
    	));
        
    	return parent::_prepareLayout();
    }
}