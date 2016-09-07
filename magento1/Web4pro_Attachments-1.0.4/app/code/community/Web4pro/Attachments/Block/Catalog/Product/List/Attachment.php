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
 * Attachment list on product page block
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Block_Catalog_Product_List_Attachment extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * get the list of attachments
     *
     * @access protected
     * @return Web4pro_Attachments_Model_Resource_Attachment_Collection
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getAttachmentCollection()
    {
        if (!$this->hasData('attachment_collection')) {
            $product = Mage::registry('product');
            $collection = Mage::getResourceSingleton('web4pro_attachments/attachment_collection')
                ->addFieldToFilter('status', 1)
                ->addProductFilter($product);
            $collection->getSelect()->order('related_product.position', 'ASC');
            $this->setData('attachment_collection', $collection);
        }
        return $this->getData('attachment_collection');
    }


}
