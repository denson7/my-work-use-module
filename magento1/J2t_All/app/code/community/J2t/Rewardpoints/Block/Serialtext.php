<?php
/**
 * Magento
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
class J2t_Rewardpoints_Block_Serialtext extends Mage_Adminhtml_Block_System_Config_Form_Field {
    
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $extra = '';
        if ($current_store = Mage::app()->getRequest()->getParam('store')){
            $store = Mage::getModel('core/store')->load($current_store);
           
            $url = parse_url($store->getBaseUrl());
            $host = $url['host'];
            
            //$ok = Mage::getStoreConfig('j2tonecheckout/'.base64_decode("bW9kdWxlX3NlcmlhbA==").'/ok', $store->getId());
            /*$ok = Mage::getStoreConfig('rewardpoints/verser/ok', $store->getId());
            if ($ok === null){
                $extra = '<div style="color:red;">'.Mage::helper('rewardpoints')->__('Please insert / verify your serial for "%s" domain', $host).'</div>';
            } else if ($ok === "0"){
                $extra = '<div style="color:red;">'.Mage::helper('rewardpoints')->__('Serial is not valid for "%s" domain', $host).'</div>';
            } else if ($ok == "1"){
                $extra = '<div style="color:green;">'.Mage::helper('rewardpoints')->__('Serial is valid for "%s" domain', $host).'</div>';
            }*/
            $extra = '<div>'.Mage::helper('rewardpoints')->__("Serial defined for store domain '%s'", $host).'</div>';
        } else {
            $websites = Mage::app()->getWebsites();
            $code = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStore()->getCode();
            $store = Mage::getModel('core/store')->load($code);
            
            $url = parse_url($store->getBaseUrl());
            $host = $url['host'];
            $extra = '<div>'.Mage::helper('rewardpoints')->__("Serial defined for store domain '%s'", $host).'</div>';
        }
        
        //$checkbox_name = $element->getName();
        //$checkbox_name = str_replace("key", "key_validation", $checkbox_name);
        
        $html = $extra;//.'<input type="checkbox" name="'.$checkbox_name.'" id="revalidate" value="1" /> <label for="revalidate">'.Mage::helper('rewardpoints')->__('First time validation / Re-validate').'</label>';
        return parent::_getElementHtml($element).$html;
    }
}

