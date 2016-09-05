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
 * Attachment helper
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Helper_Attachment extends Mage_Core_Helper_Abstract
{

    /**
     * check if breadcrumbs can be used
     *
     * @access public
     * @return bool
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getUseBreadcrumbs()
    {
        return Mage::getStoreConfigFlag('web4pro_attachments/attachment/breadcrumbs');
    }

    /**
     * get base files dir
     *
     * @access public
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getFileBaseDir()
    {
        return Mage::getBaseDir('media').DS.'attachment'.DS.'file';
    }

    /**
     * get base file url
     *
     * @access public
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getFileBaseUrl()
    {
        return Mage::getBaseUrl('media').'attachment'.'/'.'file';
    }


    /**
     * get file size
     *
     * @access public
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getFileSize($file)
    {
        $size = filesize($file);
        $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        if ($size == 0) {
            return('n/a');
        } else {
            return (round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);
        }
    }


    /**
     * delete the file if need
     * @param $postData
     */
    public function deleteAttachemntFileIfNeed($postData)
    {
        if(isset($postData['uploaded_file']['delete']) && isset($postData['uploaded_file']['value'])
            && $postData['uploaded_file']['delete'] == 1 && !empty($postData['uploaded_file']['value'])){
            $pathToDelete = $this->getFileBaseDir().$postData['uploaded_file']['value'];
            unlink($pathToDelete);
        }
    }
}
