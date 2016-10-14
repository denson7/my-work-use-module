<?php

class Company_Web_Block_Adminhtml_Web_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('web_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('web')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('web')->__('Item Information'),
          'title'     => Mage::helper('web')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('web/adminhtml_web_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}