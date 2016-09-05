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
 * Attachment widget block
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Block_Attachment_Widget_View extends Mage_Core_Block_Template implements
    Mage_Widget_Block_Interface
{
    protected $_htmlTemplate = 'web4pro_attachments/attachment/widget/view.phtml';

    /**
     * Prepare a for widget
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Attachment_Widget_View
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $attachmentId = $this->getData('attachment_id');
        if ($attachmentId) {
            $attachment = Mage::getModel('web4pro_attachments/attachment')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($attachmentId);
            if ($attachment->getStatus()) {
                $this->setCurrentAttachment($attachment);
                $this->setTemplate($this->_htmlTemplate);
            }
        }
        return $this;
    }
}
