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
 * @copyright  Copyright (c) 2014 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_Model_Config_Serialverification extends Mage_Core_Model_Config_Data {
    public function _afterSave()
    {
        $scope = $this->getScope();
        //echo " - " . $this->getWebsiteCode();
        $scope_id = $this->getScopeId();
        
        $force_store = null;
        
        switch ($this->getScope()) {
            case 'stores':
                //change scope for store code value (french, etc.)
                $scope = "stores";
                $store_loaded = Mage::getModel('core/store')->load($this->getStoreCode());
                $force_store = $store_loaded->getId();
                break;
            case 'websites':
                //scope will be 'websites'
                break;
            case 'default':
                //scope will be 'default'
                break;
        }
        
        //$key_validation = $this->getData('groups/module_serial/fields/key_validation/value');
        $current_key = $this->getValue();
        
        $exceptions = array();
        //if ($key_validation){
            //TODO - verify key offline and online
            $exceptions[] = Mage::helper('rewardpoints')->__(base64_decode("U2VyaWFsIHVzZWQgaW4gaW52YWxpZCwgdGhlcmVmb3JlLCB5b3VyIGNvbmZpZ3VyYXRpb24gY2Fubm90IGJlIHNhdmVkLg=="));
        //}
        
        if (!empty($exceptions)) {
            //$ser_name_code = base64_decode("bW9kdWxlX3NlcmlhbA==");
            $ser_name_code = 'verser';
            $store_code = 'default';
            
            if ($current_store = Mage::app()->getRequest()->getParam('store')){
                $store_code = $current_store;
                $store = Mage::app()->getStore();
            } else {
                $websites = Mage::app()->getWebsites();
                //$store_code = $websites[1]->getDefaultStore()->getCode();
                $store_code = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStore()->getCode();
                $store = Mage::getModel('core/store')->load($store_code);
            }
            
            $url = parse_url($store->getBaseUrl());
            $domain = $url['host'];
            
            
            Mage::getConfig()->saveConfig('rewardpoints/'.$ser_name_code.'/ok', "0", $scope, $scope_id);
            
            //set next verif for all stores
            foreach (Mage::app()->getStores() as $store_list){
                if ($store_list->getCode() != $scope){
                    Mage::getConfig()->saveConfig('rewardpoints/'.$ser_name_code.'/ok', "0", "stores", $store_list->getId());
                }
            }
            Mage::app()->getStore()->resetConfig();
            
            $version = Mage::getConfig()->getModuleConfig("J2t_Rewardpoints")->version;
            $version_array = explode('.', $version);
            $module_branch_version = $version_array[0].'.'.$version_array[1];
            $module_key = $current_key;
            
            $url = "http://www.".base64_decode("ajJ0LWRlc2lnbi5uZXQ=")."/j2tmoduleintegrity/index/checkIntegrityNew/version/$module_branch_version/serial/$module_key/code/rewardpoints/domain/$domain";
            
            $curl = new Varien_Http_Adapter_Curl();
            $curl->setConfig(array(
                'timeout'   => 20
            ));

            $curl->write(Zend_Http_Client::GET, $url, '1.0');
            $data = $curl->read();
            
            $fs = false;
            if ($data === false || $curl->getErrno()) 
            {
                $exceptions[] = Mage::helper('rewardpoints')->__(base64_decode("Q1VSTCBlcnJvciAlcw=="), "(#{$curl->getErrno()}) / ".$curl->getError());
                $fs = true;
            } else {
                $exceptions[] = Mage::helper('rewardpoints')->__(base64_decode("Tm8gQ1VSTCBhY2Nlc3MgZXJyb3Jz"));
            }
            $return_curl = preg_split('/^\r?$/m', $data, 2);
            $return_curl = trim($return_curl[1]);
            $curl->close();
            if ($return_curl === "" && $return_curl !== "0" && $return_curl !== "1" && !$fs){
                $return_curl = 1;
            }
            if ($return_curl == 1){
                Mage::getConfig()->saveConfig('rewardpoints/'.$ser_name_code.'/ok', "1", $scope, $scope_id);
                Mage::app()->getStore()->resetConfig();
            } else {
                Mage::getConfig()->saveConfig('rewardpoints/'.$ser_name_code.'/ok', "0", $scope, $scope_id);
                Mage::app()->getStore()->resetConfig();
            }
            $ok = Mage::getStoreConfig('rewardpoints/'.$ser_name_code.'/ok', $scope_id);
            
            if (!$ok){
                throw new Exception( "\n" . implode("\n", $exceptions) );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpoints')->__(base64_decode('W1tZb3VyIHNlcmlhbCBpcyB2YWxpZCBhbmQgY29uZmlndXJhdGlvbiBjYW4gYmUgc2F2ZWQuXV0=')));
            }
        }
    }
}

