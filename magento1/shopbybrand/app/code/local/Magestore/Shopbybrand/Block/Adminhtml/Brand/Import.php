<?php

class Magestore_Shopbybrand_Block_Adminhtml_Brand_Import extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct(){
		parent::__construct();
		$this->_blockGroup = 'shopbybrand';
		$this->_controller = 'adminhtml_brand';
		$this->_mode = 'import';
                $this->_updateButton('save','label',Mage::helper('shopbybrand')->__('Import Brands'));
		$this->_removeButton('delete');
		$this->_removeButton('reset');
	}
	
	public function getHeaderText(){
		return Mage::helper('shopbybrand')->__('Import Brands');
	}
}