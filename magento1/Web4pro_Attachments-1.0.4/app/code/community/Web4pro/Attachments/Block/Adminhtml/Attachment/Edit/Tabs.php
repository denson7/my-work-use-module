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
 * Attachment admin edit tabs
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Block_Adminhtml_Attachment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('attachment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('web4pro_attachments')->__('Attachment'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Adminhtml_Attachment_Edit_Tabs
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_attachment',
            array(
                'label'   => Mage::helper('web4pro_attachments')->__('Attachment'),
                'title'   => Mage::helper('web4pro_attachments')->__('Attachment'),
                'content' => $this->getLayout()->createBlock(
                    'web4pro_attachments/adminhtml_attachment_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        $this->addTab(
            'products',
            array(
                'label' => Mage::helper('web4pro_attachments')->__('Associated products'),
                'url'   => $this->getUrl('*/*/products', array('_current' => true)),
                'class' => 'ajax'
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve attachment entity
     *
     * @access public
     * @return Web4pro_Attachments_Model_Attachment
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getAttachment()
    {
        return Mage::registry('current_attachment');
    }
}
