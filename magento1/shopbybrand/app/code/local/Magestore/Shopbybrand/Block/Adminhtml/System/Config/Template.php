<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Brand Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Block_Adminhtml_System_Config_Template extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
		$this->setElement($element);
		return $this->_toHtml();
	}
    
    public function getValue($store = 0){
        $value = Mage::getStoreConfig('shopbybrand/template/brand_listing', $store);
        return $value;
    }
    
    protected function _toHtml(){
        $store = $this->getRequest()->getParam('store',0);
        $storeId = Mage::app()->getStore($store)->getId();
        $value = $this->getValue($storeId);
        $defaultValue  = $this->getValue(0);
        $templates = Mage::getSingleton('shopbybrand/system_config_source_listingtype')->toOptionArray();
        $options = '';
        foreach($templates as $template){
            if($template['value'] == $value){
                $options .= '<option value="'.$template['value'].'" selected >'.$template['label'].'</option>'; 
            }else{
                $options .= '<option value="'.$template['value'].'" >'.$template['label'].'</option>'; 
            }
        }
        $disabled = '';
        if($value =='default')
            $display = 'display:none;';
        else
            $display = '';
        if($storeId != 0 && $value == $defaultValue) $disabled = 'disabled';
        $html = '
            <select onchange="loadImage(this)" '.$disabled.' id="shopbybrand_template_brand_listing" name="groups[template][fields][brand_listing][value]" class=" select">
                '.$options.'
            </select>
            <input type="hidden" id="skin-url" value="'.$this->getSkinUrl('images').'"/>
            <br/>
            <br/>
            <img style="'.$display.'" id="shopbybrand-template-image" width="600" src="'.$this->getSkinUrl('images').'/'.$value.'.jpg" />
            <script type="text/javascript">
                function loadImage(el){
                    var value = el.value;
                    if(value == "default"){
                        $("shopbybrand-template-image").style.display = "none";
                    }else{
                        $("shopbybrand-template-image").style.display = "";
                        $("shopbybrand-template-image").src = $("skin-url").value+"/"+value+".jpg";
                    }
                }
            </script>
        ';
        return $html;
    }

        /**
	 * Constructor for block 
	 * 
	 */
	public function __construct(){
		parent::__construct();		
	}
}