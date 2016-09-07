<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2016/7/25
 * Time: 11:23
 */
class Silk_Xreview_Adminhtml_XreviewController extends Mage_Adminhtml_Controller_Action
{
    public function uploadAction()
    {
        try {
            //Mage::log($_FILES,null,'file.log');
            $uploader = new Mage_Core_Model_File_Uploader('review_image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save($this->getBaseTmpMediaPath());
            Mage::dispatchEvent('catalog_product_gallery_upload_image_after', array(
                'result' => $result,
                'action' => $this
            ));

            /**
             * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
             */
            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);

            $result['url'] =$this->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'];
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );

        } catch (Exception $e) {
            $result = array(
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode());
        }
        //Mage::log($result,null,'cookie.log');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    private function getBaseTmpMediaPath() {
        return Mage::getBaseDir('media') . DS . 'review';
    }

    private function getTmpMediaUrl($file) {
        $file = str_replace(DS, '/', $file);

        if (substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }
        return  Mage::getBaseUrl('media') . 'review/'. $file;
    }
}