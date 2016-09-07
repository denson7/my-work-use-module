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
 * Attachment product model
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Model_Attachment_Product extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource
     *
     * @access protected
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _construct()
    {
        $this->_init('web4pro_attachments/attachment_product');
    }

    /**
     * Save data for attachment-product relation
     * @access public
     * @param  Web4pro_Attachments_Model_Attachment $attachment
     * @return Web4pro_Attachments_Model_Attachment_Product
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function saveAttachmentRelation($attachment)
    {
        $data = $attachment->getProductsData();
        if (!is_null($data)) {
            $this->_getResource()->saveAttachmentRelation($attachment, $data);
        }
        return $this;
    }

    /**
     * get products for attachment
     *
     * @access public
     * @param Web4pro_Attachments_Model_Attachment $attachment
     * @return Web4pro_Attachments_Model_Resource_Attachment_Product_Collection
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getProductCollection($attachment)
    {
        $collection = Mage::getResourceModel('web4pro_attachments/attachment_product_collection')
            ->addAttachmentFilter($attachment);
        return $collection;
    }
}
