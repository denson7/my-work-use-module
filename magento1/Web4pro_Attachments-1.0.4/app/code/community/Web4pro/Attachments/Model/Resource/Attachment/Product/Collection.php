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
 * Attachment - product relation resource model collection
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Model_Resource_Attachment_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * remember if fields have been joined
     *
     * @var bool
     */
    protected $_joinedFields = false;

    /**
     * join the link table
     *
     * @access public
     * @return Web4pro_Attachments_Model_Resource_Attachment_Product_Collection
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function joinFields()
    {
        if (!$this->_joinedFields) {
            $this->getSelect()->join(
                array('related' => $this->getTable('web4pro_attachments/attachment_product')),
                'related.product_id = e.entity_id',
                array('position')
            );
            $this->_joinedFields = true;
        }
        return $this;
    }

    /**
     * add attachment filter
     *
     * @access public
     * @param Web4pro_Attachments_Model_Attachment | int $attachment
     * @return Web4pro_Attachments_Model_Resource_Attachment_Product_Collection
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function addAttachmentFilter($attachment)
    {
        if ($attachment instanceof Web4pro_Attachments_Model_Attachment) {
            $attachment = $attachment->getId();
        }
        if (!$this->_joinedFields ) {
            $this->joinFields();
        }
        $this->getSelect()->where('related.attachment_id = ?', $attachment);
        return $this;
    }
}
