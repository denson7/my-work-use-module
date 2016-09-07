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
 * Adminhtml observer
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Model_Adminhtml_Observer
{
    /**
     * check if tab can be added
     *
     * @access protected
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _canAddTab($product)
    {
        if ($product->getId()) {
            return true;
        }
        if (!$product->getAttributeSetId()) {
            return false;
        }
        $request = Mage::app()->getRequest();
        if ($request->getParam('type') == 'configurable') {
            if ($request->getParam('attributes')) {
                return true;
            }
        }
        return false;
    }

    /**
     * add the attachment tab to products
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Web4pro_Attachments_Model_Adminhtml_Observer
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function addProductAttachmentBlock($observer)
    {
        $block = $observer->getEvent()->getBlock();
        $product = Mage::registry('product');
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs && $this->_canAddTab($product)) {
            $block->addTab(
                'attachments',
                array(
                    'label' => Mage::helper('web4pro_attachments')->__('Attachments'),
                    'url'   => Mage::helper('adminhtml')->getUrl(
                        'adminhtml/attachments_attachment_catalog_product/attachments',
                        array('_current' => true)
                    ),
                    'class' => 'ajax',
                )
            );
        }
        return $this;
    }


    protected function _uploadAndGetName($input, $destinationFolder, $data)
    {
        try {
            $uploader = new Varien_File_Uploader($input);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($destinationFolder);
            return $result['file'];

        } catch (Exception $e) {
            if ($e->getCode() != Varien_File_Uploader::TMP_NAME_EMPTY) {
                throw $e;
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }




    /**
     * save attachment - product relation
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Web4pro_Attachments_Model_Adminhtml_Observer
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function saveProductAttachmentData($observer)
    {

        $post = Mage::app()->getRequest()->getPost('attachments', -1);

        $product = Mage::registry('product');

        $aps = Mage::getResourceModel("web4pro_attachments/attachment_product");

        if ($post != '-1') {
            $post = Mage::helper('adminhtml/js')->decodeGridSerializedInput($post);

            $attachmentProduct = $aps->saveProductRelation($product, $post);
        }

        //add upload single file save
        $attachment = Mage::getModel('web4pro_attachments/attachment');

        $data['title'] = Mage::app()->getRequest()->getPost('attachments_title');
        $data['status'] = Mage::app()->getRequest()->getPost('attachments_status');

        if(isset($_FILES['uploaded_file'])){
            $attachmentHelper = Mage::helper('web4pro_attachments/attachment');
            $uploadedFileName = $this->_uploadAndGetName(
                'uploaded_file',
                $attachmentHelper->getFileBaseDir(),
                $data
            );
            $data['uploaded_file'] = $uploadedFileName;
        }
        $attachment->addData($data);
        $attachment->save();

        $data = array();
        $data[$attachment->getId()] = array('position'=>0);
        $writer = Mage::getSingleton('core/resource')->getConnection('write');

        try {
            $writer->insert(
                $aps->getMainTable(),
                array(
                    'attachment_id' => $attachment->getId(),
                    'product_id' => $product->getId(),
                    'position' => 0
                )
            );
        }
        catch(Exception $e)
        {
           $imgurl = Mage::getBaseDir('media').'/attachment/file'.$attachment->load($attachment->getId())->getUploadedFile();
            unlink($imgurl);
            $attachment->setId($attachment->getId())->delete();
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('web4pro_attachments')->__('There was an error about upload file.')
            );
        }

        return $this;
    }



}
