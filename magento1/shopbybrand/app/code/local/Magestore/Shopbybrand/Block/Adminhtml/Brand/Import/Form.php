<?php

class Magestore_Shopbybrand_Block_Adminhtml_Brand_Import_Form extends Mage_Adminhtml_Block_Widget_Form
{
protected function _prepareForm(){
		$form = new Varien_Data_Form(array(
			'id'	=> 'edit_form',
			'action'	=> $this->getUrl('*/*/processImport'),
			'method'	=> 'post',
			'enctype'	=> 'multipart/form-data'
		));
		
		$fieldset = $form->addFieldset('profile_fieldset',array('legend'=>Mage::helper('shopbybrand')->__('Import Information')));
		
                $fieldset->addField('is_update', 'select', array(
                    'label'     => Mage::helper('shopbybrand')->__('Import Behavior *'),
                    'name'      => 'is_update',
                    'values'    => array(
                        array(
                            'value'     => 1,
                            'label'     => Mage::helper('shopbybrand')->__('Import & Replace Existing Data'),
                        ),
                        array(
                            'value'     => 0,
                            'label'     => Mage::helper('shopbybrand')->__('Import & Keep Existing Data'),
                        ),
                    ),
                ));
                
                
		$fieldset->addField('csv_brand','file',array(
			'label'		=> Mage::helper('shopbybrand')->__('Import File'),
			'title'		=> Mage::helper('shopbybrand')->__('Import File'),
			'name'		=> 'csv_brand',
			'required'	=> true,
                        'note'      => Mage::helper('shopbybrand')->__("Only csv file is supported. Click <a href='%s'>here</a> to download the sample file.", Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'shopbybrand/shopbybrand.csv' )));
		
        
        
		
		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}