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
 * Attachment admin controller
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Adminhtml_Attachments_AttachmentController extends Web4pro_Attachments_Controller_Adminhtml_Attachments
{
    /**
     * init the attachment
     *
     * @access protected
     * @return Web4pro_Attachments_Model_Attachment
     */
    protected function _initAttachment()
    {
        $attachmentId  = (int) $this->getRequest()->getParam('id');
        $attachment    = Mage::getModel('web4pro_attachments/attachment');
        if ($attachmentId) {
            $attachment->load($attachmentId);
        }
        Mage::register('current_attachment', $attachment);
        return $attachment;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('web4pro_attachments')->__('Web4pro'))
             ->_title(Mage::helper('web4pro_attachments')->__('Attachments'));
        $this->renderLayout();
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit attachment - action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function editAction()
    {
        $attachmentId    = $this->getRequest()->getParam('id');
        $attachment      = $this->_initAttachment();
        if ($attachmentId && !$attachment->getId()) {
            $this->_getSession()->addError(
                Mage::helper('web4pro_attachments')->__('This attachment no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getAttachmentData(true);
        if (!empty($data)) {
            $attachment->setData($data);
        }
        Mage::register('attachment_data', $attachment);
        $this->loadLayout();
        $this->_title(Mage::helper('web4pro_attachments')->__('Web4pro'))
             ->_title(Mage::helper('web4pro_attachments')->__('Attachments'));
        if ($attachment->getId()) {
            $this->_title($attachment->getTitle());
        } else {
            $this->_title(Mage::helper('web4pro_attachments')->__('Add attachment'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new attachment action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save attachment - action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('attachment')) {

            try {
                $attachment = $this->_initAttachment();
                $attachment->addData($data);
                $attachmentHelper = Mage::helper('web4pro_attachments/attachment');
                $uploadedFileName = $this->_uploadAndGetName(
                    'uploaded_file',
                    $attachmentHelper->getFileBaseDir(),
                    $data
                );
                $attachmentHelper->deleteAttachemntFileIfNeed($data);
                $attachment->setData('uploaded_file', $uploadedFileName);

                $products = $this->getRequest()->getPost('products', -1);

                if ($products != -1) {
                    $attachment->setProductsData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($products));
                }
                print_r($attachment->getData());
                die();
                $attachment->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('web4pro_attachments')->__('Attachment was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $attachment->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                if (isset($data['uploaded_file']['value'])) {
                    $data['uploaded_file'] = $data['uploaded_file']['value'];
                }
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAttachmentData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                if (isset($data['uploaded_file']['value'])) {
                    $data['uploaded_file'] = $data['uploaded_file']['value'];
                }
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('web4pro_attachments')->__('There was a problem saving the attachment.')
                );
                Mage::getSingleton('adminhtml/session')->setAttachmentData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('web4pro_attachments')->__('Unable to find attachment to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete attachment - action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $attachment = Mage::getModel('web4pro_attachments/attachment');
                $attachment->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('web4pro_attachments')->__('Attachment was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('web4pro_attachments')->__('There was an error deleting attachment.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('web4pro_attachments')->__('Could not find attachment to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete attachment - action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function massDeleteAction()
    {
        $attachmentIds = $this->getRequest()->getParam('attachment');
        if (!is_array($attachmentIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('web4pro_attachments')->__('Please select attachments to delete.')
            );
        } else {
            try {
                foreach ($attachmentIds as $attachmentId) {
                    $attachment = Mage::getModel('web4pro_attachments/attachment');
                    $attachment->setId($attachmentId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('web4pro_attachments')->__('Total of %d attachments were successfully deleted.', count($attachmentIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('web4pro_attachments')->__('There was an error deleting attachments.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function massStatusAction()
    {
        $attachmentIds = $this->getRequest()->getParam('attachment');
        if (!is_array($attachmentIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('web4pro_attachments')->__('Please select attachments.')
            );
        } else {
            try {
                foreach ($attachmentIds as $attachmentId) {
                $attachment = Mage::getSingleton('web4pro_attachments/attachment')->load($attachmentId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d attachments were successfully updated.', count($attachmentIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('web4pro_attachments')->__('There was an error updating attachments.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * get grid of products action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function productsAction()
    {
        $this->_initAttachment();
        $this->loadLayout();
        $this->getLayout()->getBlock('attachment.edit.tab.product')
            ->setAttachmentProducts($this->getRequest()->getPost('attachment_products', null));
        $this->renderLayout();
    }

    /**
     * get grid of products action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function productsgridAction()
    {
        $this->_initAttachment();
        $this->loadLayout();
        $this->getLayout()->getBlock('attachment.edit.tab.product')
            ->setAttachmentProducts($this->getRequest()->getPost('attachment_products', null));
        $this->renderLayout();
    }

    /**
     * export as csv - action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function exportCsvAction()
    {
        $fileName   = 'attachment.csv';
        $content    = $this->getLayout()->createBlock('web4pro_attachments/adminhtml_attachment_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function exportExcelAction()
    {
        $fileName   = 'attachment.xls';
        $content    = $this->getLayout()->createBlock('web4pro_attachments/adminhtml_attachment_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function exportXmlAction()
    {
        $fileName   = 'attachment.xml';
        $content    = $this->getLayout()->createBlock('web4pro_attachments/adminhtml_attachment_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('web4pro_attachments/attachment');
    }
}
