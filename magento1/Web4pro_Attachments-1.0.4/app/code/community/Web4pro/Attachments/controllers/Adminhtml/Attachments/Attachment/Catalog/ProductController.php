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
 * Attachment - product controller
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
require_once ("Mage/Adminhtml/controllers/Catalog/ProductController.php");
class Web4pro_Attachments_Adminhtml_Attachments_Attachment_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    /**
     * construct
     *
     * @access protected
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Web4pro_Attachments');
    }

    /**
     * attachments in the catalog page
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function attachmentsAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('product.edit.tab.attachment')
            ->setProductAttachments($this->getRequest()->getPost('product_attachments', null));
        $this->renderLayout();
    }

    /**
     * attachments grid in the catalog page
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function attachmentsGridAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('product.edit.tab.attachment')
            ->setProductAttachments($this->getRequest()->getPost('product_attachments', null));
        $this->renderLayout();
    }

}
