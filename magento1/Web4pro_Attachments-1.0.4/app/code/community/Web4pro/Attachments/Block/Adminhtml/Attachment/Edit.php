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
 * Attachment admin edit form
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Block_Adminhtml_Attachment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'web4pro_attachments';
        $this->_controller = 'adminhtml_attachment';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('web4pro_attachments')->__('Save Attachment')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('web4pro_attachments')->__('Delete Attachment')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('web4pro_attachments')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_attachment') && Mage::registry('current_attachment')->getId()) {
            return Mage::helper('web4pro_attachments')->__(
                "Edit Attachment '%s'",
                $this->escapeHtml(Mage::registry('current_attachment')->getTitle())
            );
        } else {
            return Mage::helper('web4pro_attachments')->__('Add Attachment');
        }
    }
}
