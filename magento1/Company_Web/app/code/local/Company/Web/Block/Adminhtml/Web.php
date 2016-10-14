<?php
class Company_Web_Block_Adminhtml_Web extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_web';
    $this->_blockGroup = 'web';
    $this->_headerText = Mage::helper('web')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('web')->__('Add Item');
    parent::__construct();
  }
}