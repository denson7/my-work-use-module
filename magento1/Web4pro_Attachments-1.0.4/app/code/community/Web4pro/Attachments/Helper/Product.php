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
 * Product helper
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Helper_Product extends Web4pro_Attachments_Helper_Data
{

    /**
     * get the selected attachments for a product
     *
     * @access public
     * @param Mage_Catalog_Model_Product $product
     * @return array()
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getSelectedAttachments(Mage_Catalog_Model_Product $product)
    {
        if (!$product->hasSelectedAttachments()) {
            $attachments = array();
            foreach ($this->getSelectedAttachmentsCollection($product) as $attachment) {
                $attachments[] = $attachment;
            }
            $product->setSelectedAttachments($attachments);
        }
        return $product->getData('selected_attachments');
    }

    /**
     * get attachment collection for a product
     *
     * @access public
     * @param Mage_Catalog_Model_Product $product
     * @return Web4pro_Attachments_Model_Resource_Attachment_Collection
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getSelectedAttachmentsCollection(Mage_Catalog_Model_Product $product)
    {
        $collection = Mage::getResourceSingleton('web4pro_attachments/attachment_collection')
            ->addProductFilter($product);
        return $collection;
    }
}
