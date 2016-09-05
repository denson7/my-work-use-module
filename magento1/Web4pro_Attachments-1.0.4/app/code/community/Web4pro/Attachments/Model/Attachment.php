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
 * Attachment model
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Model_Attachment extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'web4pro_attachments_attachment';
    const CACHE_TAG = 'web4pro_attachments_attachment';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'web4pro_attachments_attachment';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'attachment';
    protected $_productInstance = null;

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('web4pro_attachments/attachment');
    }

    /**
     * before save attachment
     *
     * @access protected
     * @return Web4pro_Attachments_Model_Attachment
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * save attachment relation
     *
     * @access public
     * @return Web4pro_Attachments_Model_Attachment
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _afterSave()
    {
        $this->getProductInstance()->saveAttachmentRelation($this);
        return parent::_afterSave();
    }

    /**
     * get product relation model
     *
     * @access public
     * @return Web4pro_Attachments_Model_Attachment_Product
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getProductInstance()
    {
        if (!$this->_productInstance) {
            $this->_productInstance = Mage::getSingleton('web4pro_attachments/attachment_product');
        }
        return $this->_productInstance;
    }

    /**
     * get selected products array
     *
     * @access public
     * @return array
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getSelectedProducts()
    {
        if (!$this->hasSelectedProducts()) {
            $products = array();
            foreach ($this->getSelectedProductsCollection() as $product) {
                $products[] = $product;
            }
            $this->setSelectedProducts($products);
        }
        return $this->getData('selected_products');
    }

    /**
     * Retrieve collection selected products
     *
     * @access public
     * @return Web4pro_Attachments_Resource_Attachment_Product_Collection
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getSelectedProductsCollection()
    {
        $collection = $this->getProductInstance()->getProductCollection($this);
        return $collection;
    }

    /**
     * get default values
     *
     * @access public
     * @return array
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        return $values;
    }

    /**
     * get attachment url
     *
     * @access public
     * @return attachment url
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getAttachmentUrl()
    {
        return Mage::helper('web4pro_attachments/attachment')->getFileBaseUrl() . $this->getUploadedFile();
    }

    /**
     * get attachment path
     *
     * @access public
     * @return attachment path
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getAttachmentPath()
    {
        return Mage::helper('web4pro_attachments/attachment')->getFileBaseDir() . $this->getData('uploaded_file');
    }


    /**
     * get attachment extension
     *
     * @access public
     * @return attachment extension
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getFileExtension($filename, $pos = 0)
    {
        return strtolower(substr($filename, strrpos($filename, '.') + $pos));
    }


    /**
     * get attachment icon
     *
     * @access public
     * @return attachment icon
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getIcon()
    {
        $attachmentPath = $this->getData('uploaded_file');
        $ext = $this->getFileExtension($attachmentPath, 1);
        $mediaIcon = Mage::getBaseUrl('media') . '/attachment/icons/' . $ext . '.png';
        $html = '<span class="attach-img"><img src="' . $mediaIcon . '" alt="View File" style="margin-right: 5px;"/></span>';
        return $html;
    }
}
