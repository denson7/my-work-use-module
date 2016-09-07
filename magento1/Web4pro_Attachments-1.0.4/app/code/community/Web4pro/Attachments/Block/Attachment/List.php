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
 * Attachment list block
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Block_Attachment_List extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function __construct()
    {
        parent::__construct();
        $attachments = Mage::getResourceModel('web4pro_attachments/attachment_collection')
                         ->addFieldToFilter('status', 1);
        $attachments->setOrder('title', 'asc');
        $this->setAttachments($attachments);
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Attachment_List
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock(
            'page/html_pager',
            'web4pro_attachments.attachment.html.pager'
        )
        ->setCollection($this->getAttachments());
        $this->setChild('pager', $pager);
        $this->getAttachments()->load();
        return $this;
    }

    /**
     * get the pager html
     *
     * @access public
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
