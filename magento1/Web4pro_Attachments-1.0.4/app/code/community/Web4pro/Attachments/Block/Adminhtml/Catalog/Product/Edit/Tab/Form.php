<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-30
 * Time: 下午5:03
 */

class Web4pro_Attachments_Block_Adminhtml_Catalog_Product_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'attachments_form',
            array('legend'=>Mage::helper('web4pro_attachments')->__('Upload'))
        );

        $fieldset->addField(
            'title',
            'text',
            array(
                'label' => Mage::helper('web4pro_attachments')->__('Title'),
                'name'  => 'attachments_title',
                'required'  => true,
                'class' => 'required-entry',
            )
        );

        $fieldset->addField(
            'uploaded_file',
            'file',
            array(
                'label' => Mage::helper('web4pro_attachments')->__('Uploaded file'),
                'name'  => 'uploaded_file',
                'required'  => true,
                'class' => 'required-entry',
            )
        );

        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('web4pro_attachments')->__('Status'),
                'name'   => 'attachments_status',
                'values' => array(
                    array(
                        'value' => 0,
                        'label' => Mage::helper('web4pro_attachments')->__('Disabled'),
                    ),
                    array(
                        'value' => 1,
                        'label' => Mage::helper('web4pro_attachments')->__('Enabled'),
                        'selected' => 'selected',
                    ),
                ),
            )
        );

        return parent::_prepareForm();

    }

}

