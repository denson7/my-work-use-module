<?php
class Web4pro_Attachments_Model_Observer
{
    /**
     * Flag to stop observer executing more than once
     *
     * @var static bool
     */
    static protected $_singletonFlag = false;

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
     * This method will run when the product is saved from the Magento Admin
     * Use this function to update the product model, process the
     * data or anything you like
     *
     * @param Varien_Event_Observer $observer
     */
    public function uploadFile(Varien_Event_Observer $observer)
    {
        if (!self::$_singletonFlag) {
            self::$_singletonFlag = true;

            $product = $observer->getEvent()->getProduct();
            $post = Mage::app()->getRequest()->getPost();
            try {

                if($_FILES['uploaded_file']['name'] != null && $_FILES['uploaded_file']['tmp_name'] != null) {
                    //表1数据
                    $attachmentdata = array();
                    $attachmentdata['title'] = $post['attachmentstitle'];
                    $attachmentdata['status'] = $post['attachmentsstatus'];

                    $attachment = Mage::getModel('web4pro_attachments/attachment');
                    $attachmentProduct = Mage::getModel('web4pro_attachments/attachment_product');

                    $attachmentHelper = Mage::helper('web4pro_attachments/attachment');
                    $uploadedFileName = $this->_uploadAndGetName(
                        'uploaded_file',
                        $attachmentHelper->getFileBaseDir(),
                        $attachmentdata
                    );
                    $attachmentdata['uploaded_file'] = $uploadedFileName;
                    //end

                    try{
                        $attachment->setData($attachmentdata);
                        $attachment->save();
                        //表2数据
                        $data = null;
                        if(Mage::app()->getRequest()->getPost('attachments', -1)){
                            $data = Mage::app()->getRequest()->getPost('attachments', -1);
                            $data = Mage::helper('adminhtml/js')->decodeGridSerializedInput($data);
                        }
                        $data[$attachment->getId()] = array('position'=>0);
//                        print_r($data);
//                        die();
                        $attachmentProduct = Mage::getResourceSingleton('web4pro_attachments/attachment_product')
                            ->saveProductRelation($product, $data);

                    } catch  (Exception $e) {
                        Mage::getSingleton('adminhtmlssion')->addError("There was an error uploading the file, please try again!");
                    }

                }

                /**
                 * Uncomment the line below to save the product
                 *
                 */
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtmlssion')->addError($e->getMessage());
            }
        }
    }

    /**
     * Retrieve the product model
     *
     * @return Mage_Catalog_Model_Product $product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * Shortcut to getRequest
     *
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
}
