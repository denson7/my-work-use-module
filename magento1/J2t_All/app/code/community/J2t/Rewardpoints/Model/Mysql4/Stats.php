<?php
/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_Model_Mysql4_Stats extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('rewardpoints/stats', 'rewardpoints_account_id');
    }
    
    //J2T Check referral
    public function loadByReferralId($referral_id, $referral_customer_id = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('rewardpoints/rewardpoints_account'))
            ->where('rewardpoints_referral_id = ?',$referral_id);
        
        if ($referral_customer_id != null){
            $select->where('customer_id <> ?', $referral_customer_id);
        }        
        $result = $this->_getReadAdapter()->fetchRow($select);        
        if(!$result) {
            return array();
        }

        return $result;
    }
    
    public function loadByChildReferralId($referral_id, $referral_customer_id = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('rewardpoints/rewardpoints_account'))
            ->where('rewardpoints_referral_id = ?',$referral_id);
        
        if ($referral_customer_id != null){
            $select->where('customer_id = ?', $referral_customer_id);
        }        
        $result = $this->_getReadAdapter()->fetchRow($select);        
        if(!$result) {
            return array();
        }

        return $result;
    }
	
	public function loadByFirstOrder($customer_id)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('rewardpoints/rewardpoints_account'))
            ->where('rewardpoints_firstorder = ?', 1);
        
		$select->where('customer_id = ?', $customer_id);
        
        $result = $this->_getReadAdapter()->fetchRow($select);        
        if(!$result) {
            return array();
        }

        return $result;
    }
    
    public function loadByOrderIncrementId($order_id, $customer_id = null, $referral = false,  $parent = false)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('rewardpoints/rewardpoints_account'))
            ->where('order_id = ?',$order_id);
        
        if ($parent){
            $select->where('customer_id <> ?', $customer_id);
        } else {
            $select->where('customer_id = ?', $customer_id);
        } 
        if ($referral){
            $select->where('rewardpoints_referral_id IS NOT NULL');
        }
        
        $result = $this->_getReadAdapter()->fetchRow($select);        
        if(!$result) {
            return array();
        }

        return $result;
    }
    
    public function getExtension($file_name)
    {
        return substr($file_name, strrpos($file_name, '.')+1);
    }
    
    public function uploadImage(Varien_Object $object)
    {
        $imageFileSmall = $_FILES['groups']['tmp_name']['design']['fields']['small_inline_image']['value'];
        $imageFileBig = $_FILES['groups']['tmp_name']['design']['fields']['big_inline_image']['value'];
        
        $absolute_path = Mage::getBaseDir('media') . DS ; 
        $relative_path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA); 
        
        if (!empty($imageFileSmall)) {
            //Mage::getDesign()->getSkinBaseDir() . DS . 'images' . DS . 'widget';
            
            // File Upload 
            try {
                $file = array();
                $file['tmp_name'] = $_FILES['groups']['tmp_name']['design']['fields']['small_inline_image']['value'];
                $file['name'] = $_FILES['groups']['name']['design']['fields']['small_inline_image']['value'];
                
                $uploader = new Varien_File_Uploader($file);
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false); 
                $test = $uploader->save($absolute_path, 'j2t_image_small.'.$this->getExtension($file['name']));
                $resizeFolder="j2t_resized";
                $imageResizedPath=Mage::getBaseDir("media").DS.$resizeFolder.DS.'j2t_image_small.'.$this->getExtension($file['name']);
                if(is_file($imageResizedPath)){
                    unlink($imageResizedPath);
                }
                if (!$test){
                    $message = Mage::helper('rewardpoints')->__('Error when submitting image');
                    Mage::getSingleton('adminhtml/session')->addError($message);
                }
                
                
            } 
            catch(Exception $e) { 
                /*Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                return $this;*/
            } 
            // Your uploaded file Url will be 
            //echo $file_url = $relative_path.$files; 
        }
        
        if (!empty($imageFileBig)) {
            // File Upload 
            try { 
                $file = array();
                $file['tmp_name'] = $_FILES['groups']['tmp_name']['design']['fields']['big_inline_image']['value'];
                $file['name'] = $_FILES['groups']['name']['design']['fields']['big_inline_image']['value'];
                
                $uploader = new Varien_File_Uploader($file);
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false); 
                $test = $uploader->save($absolute_path, 'j2t_image_big.'.$this->getExtension($file['name']));
                $imageResizedPath=Mage::getBaseDir("media").DS.$resizeFolder.DS.'j2t_image_big.'.$this->getExtension($file['name']);
                if(is_file($imageResizedPath)){
                    unlink($imageResizedPath);
                }
                if (!$test){
                    $message = Mage::helper('rewardpoints')->__('Error when submitting image');
                    Mage::getSingleton('adminhtml/session')->addError($message);
                }
            } 
            catch(Exception $e) { 
                /*Mage::getSingleton('adminhtml/session')->addError($e->getMessage()); 
                return $this;*/
            } 
        }
    }


    public function uploadAndImport(Varien_Object $object)
    {
        if (empty($_FILES['groups']['tmp_name']['dataflow_profile']['fields']['import']['value'])) {
            return $this;
        }
        
		$field_email_id = Mage::getStoreConfig('rewardpoints/dataflow_profile/field_email');
        $field_points_id = Mage::getStoreConfig('rewardpoints/dataflow_profile/field_points');
        $field_order_id = Mage::getStoreConfig('rewardpoints/dataflow_profile/field_order');
        $field_store_id = Mage::getStoreConfig('rewardpoints/dataflow_profile/field_store');
        
        if (!isset($_FILES['groups'])) {
            return false;
        }
        $csvFile = $_FILES['groups']['tmp_name']['dataflow_profile']['fields']['import']['value'];

        if (!empty($csvFile)) {
            $csv = trim(file_get_contents($csvFile));
            $table = Mage::getSingleton('core/resource')->getTableName('rewardpoints/rewardpoints_account');

            //$websiteId = $object->getScopeId();
            
            $storeCode = $object->getData('store_code');
            $websiteId = $object->getData('scope_id');
            
            $storeIds = array();
            if ($storeCode){
                $storeIds[] = Mage::app()->getStore($storeCode)->getId();
                $websiteId = Mage::app()->getStore($storeCode)->getWebsiteId();
            } else {
                $websiteModel = Mage::app()->getWebsite($websiteId);
                $websiteStores = $websiteModel->getStores();
                
                foreach ($websiteStores as $store) {
                    /*if (!$store->getIsActive()) {
                        continue;
                    }*/
                    $storeIds[] = $store->getId();
                }
            }

            if (!empty($csv)) {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);
                
                if (count($csvLine) < 3) {
                    $exceptions[0] = Mage::helper('rewardpoints')->__('Invalid File Format');
                }

                $emailAddress = array();
                //$regionCodes = array();
                foreach ($csvLines as $k=>$csvLine) {
                    $csvLine = $this->_getCsvValues($csvLine);
                    if (count($csvLine) > 0 && count($csvLine) < 3) {
                        $exceptions[0] = Mage::helper('rewardpoints')->__('Invalid File Format');
                    } /*else {
                        $emailAddress[] = $csvLine[$field_email_id];
                    }*/
                }

                if (empty($exceptions)) {
                    $data = array();
                    $emailAddressToIds = array();
                    $k = 0;
                    
                    $processed = 0;
                    
                    foreach ($csvLines as $k=>$csvLine) {
                        $csvLine = $this->_getCsvValues($csvLine);

                        $customer = null;
                        $customer_id = '';
                        $points = '';
                        $order_id = '';
                        $store_id = '';
                        
                        $error_found = false;
                        
                        if (!isset($csvLine[$field_email_id])) {
                            $exceptions[] = Mage::helper('rewardpoints')->__('Email address is missing in the Row #%s', ($k+1));
                            $error_found = true;
                        } else {
                            $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail(trim($csvLine[$field_email_id]));

                            if ($customer == null || !$customer->getId()) {
                                $exceptions[] = Mage::helper('rewardpoints')->__('Invalid email address "%s" in the Row #%s (customer might not exist)', $csvLine[$field_email_id], ($k+1));
                                $error_found = true;
                            } else {
                                $customer_id = $customer->getId();
                            }
                        }

                        if (!isset($csvLine[$field_points_id])) {
                            $exceptions[] = Mage::helper('rewardpoints')->__('Points missing in the Row #%s', ($k+1));
                            $error_found = true;
                        } else {
                            if (!is_numeric($csvLine[$field_points_id])) {
                                $exceptions[] = Mage::helper('rewardpoints')->__('Invalid point format "%s" in the Row #%s', $csvLine[$field_points_id], ($k+1));
                                $error_found = true;
                            } else {
                                $points = $csvLine[$field_points_id];
                            }
                        }
                        
                        
                        if ($field_order_id != -1){
                            if (!isset($csvLine[$field_order_id])) {
                                $exceptions[] = Mage::helper('rewardpoints')->__('Order id missing in the Row #%s', ($k+1));
                                $error_found = true;
                            } else{
                                $order_id = $csvLine[$field_order_id];
                                if ($order_id > 0){
                                    $order_check = Mage::getModel('sales/order')->loadByIncrementId($order_id);
                                    if (!$order_check->getId()){
                                        $exceptions[] = Mage::helper('rewardpoints')->__('Invalid order Id "%s" in the Row #%s', $order_id, ($k+1));
                                        $error_found = true;
                                    }
                                }
                            }
                        } else {
                            $order_id = -1;
                        }
                        
                        if ($field_store_id == -1){
                            $store_id = implode(',',$storeIds);
                        } else {
                            if (!isset($csvLine[$field_store_id])) {
                                $exceptions[] = Mage::helper('rewardpoints')->__('Store id(s) missing in the Row #%s', ($k+1));
                                $error_found = true;
                                echo "error";
                                
                            } else {
                                $store_id = $csvLine[$field_store_id];
                            }
                        }
                       
                        if (!$error_found){
                            if ($points > 0){
                                $data[] = array('customer_id' => $customer_id, 'store_id' => $store_id, 'points_current' => $points, 'order_id' => $order_id);
                            } else if (abs($points) > 0) {
                                $data[] = array('customer_id' => $customer_id, 'store_id' => $store_id, 'points_spent' => abs($points), 'order_id' => $order_id);
                            }
                        }
                        $k++;
                    }
                }
                
                if (sizeof($data)) {
                    
                    foreach($data as $k=>$dataLine) {
                        
                        try {
                            $this->_getWriteAdapter()->insert($table, $dataLine);
                        } catch (Exception $e) {
                            $exceptions[] = Mage::helper('rewardpoints')->__('Problem importing Row #%s (customer "%s")', ($k+1), $dataDetails[$k]['customer_id']);
                        }
                    }
                }
                
                if (sizeof($data)){
                    $this->_getWriteAdapter()->commit();
                    $message = Mage::helper('rewardpoints')->__('%s line(s) processed', sizeof($data));
                    Mage::getSingleton('adminhtml/session')->addSuccess($message);
                }

                if (!empty($exceptions)) {
                    //throw new Exception( "<br />" . implode("<br />", $exceptions) );
                    Mage::getSingleton('adminhtml/session')->addError(implode("<br />", $exceptions));
                } else {
                    $message = Mage::helper('rewardpoints')->__('%s line(s) processed', sizeof($data));
                    Mage::getSingleton('adminhtml/session')->addSuccess($message);
                }
            }
        }
        return $this;
    }

    protected function _getCsvValues($string, $separator=",")
    {
        
        $elements = explode($separator, trim($string));
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            if ($nquotes %2 == 1) {
                for ($j = $i+1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') > 0) {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j-$i+1, implode($separator, array_slice($elements, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
        
    }

    protected function _isPositiveDecimalNumber($n)
    {
        return preg_match ("/^[0-9]+(\.[0-9]*)?$/", $n);
    }


}
