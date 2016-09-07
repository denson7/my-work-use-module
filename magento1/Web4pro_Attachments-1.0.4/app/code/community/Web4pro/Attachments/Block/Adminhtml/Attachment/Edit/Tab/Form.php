<?php
/**
 * WEB4PRO - Creating profitable online stores
 * 
 * @author WEB4PRO <srepin@corp.web4pro.com.ua>
 * @category  WEB4PRO
 * @package   Web4pro_Attachments
 * @copyright Copyright (c) 2015 WEB4PRO (http://www.web4pro.net)
 * @license   http://www.web4pro.net/license.txt
 */
/**
 * Attachment edit form tab
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Block_Adminhtml_Attachment_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Adminhtml_Attachment_Edit_Tab_Form
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('attachment_');
        $form->setFieldNameSuffix('attachment');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'attachment_form',
            array('legend' => Mage::helper('web4pro_attachments')->__('Attachment'))
        );
        $fieldset->addType(
            'file',
            Mage::getConfig()->getBlockClassName('web4pro_attachments/adminhtml_attachment_helper_file')
        );

        $fieldset->addField(
            'title',
            'text',
            array(
                'label' => Mage::helper('web4pro_attachments')->__('Title'),
                'name'  => 'title',
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

           )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('web4pro_attachments')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('web4pro_attachments')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('web4pro_attachments')->__('Disabled'),
                    ),
                ),
            )
        );
        $formValues = Mage::registry('current_attachment')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getAttachmentData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getAttachmentData());
            Mage::getSingleton('adminhtml/session')->setAttachmentData(null);
        } elseif (Mage::registry('current_attachment')) {
            $formValues = array_merge($formValues, Mage::registry('current_attachment')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
