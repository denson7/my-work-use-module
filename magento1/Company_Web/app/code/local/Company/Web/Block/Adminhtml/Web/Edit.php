<?php

class Company_Web_Block_Adminhtml_Web_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'web';
        $this->_controller = 'adminhtml_web';
        
        $this->_updateButton('save', 'label', Mage::helper('web')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('web')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('web_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'web_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'web_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('web_data') && Mage::registry('web_data')->getId() ) {
            return Mage::helper('web')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('web_data')->getTitle()));
        } else {
            return Mage::helper('web')->__('Add Item');
        }
    }
}