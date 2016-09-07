<?php

class Magestore_Shopbybrand_Block_Magenotification_Extensionkeys
    extends Magestore_Magenotification_Block_Config_Extensionkeys
{
    /**
     * render config form to html
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $modules = Mage::getConfig()->getNode('modules')->children();
        foreach ($modules as $moduleName => $moduleInfo) {
			if ($moduleName==='Mage_Adminhtml') {
                continue;
            }
            if ($moduleName==='Magestore_Magenotification') {
                continue;
            }
			if(strpos('a'.$moduleName,'Magestore') == 0){
				continue;
			}
			if((string)$moduleInfo->codePool != 'local'){
				continue;
			}
            // ignore Shop by Brand License (use Manufacturer)
            if ($moduleName === 'Magestore_Shopbybrand') {
                continue;
            }
            
            $module_alias = (string)$moduleInfo->aliasName ? (string)$moduleInfo->aliasName : $moduleName;
            $html .= $this->_getFieldHtml($element, $moduleName, $module_alias);
            
            $html .= $this->_getInfoHtml($element, $moduleName);
            $html .= $this->_getDividerHtml($element, $moduleName);
        }
        $html .= $this->_getFooterHtml($element);
        return $html;
    }
    
    protected function _getFieldHtml($fieldset, $moduleName, $module_alias)
    {
        $configData = $this->getConfigData();
        $path = 'magenotificationsecure/extension_keys/'.$moduleName;
        $data = isset($configData[$path]) ? $configData[$path] : '';
        $e = $this->_getDummyElement();
        $field = $fieldset->addField($moduleName, 'text', array(
            'name'          => 'groups[extension_keys][fields]['.$moduleName.'][value]',
            'label'         => $module_alias,
            'value'         => $data,
            'style'         => 'width:688px;',
            'inherit'       => isset($configData[$path]) ? false : true,
            'can_use_default_value' => $this->getForm()->canUseDefaultValue($e),
            'can_use_website_value' => $this->getForm()->canUseWebsiteValue($e),
        ))->setRenderer($this->_getFieldRenderer());
        return $field->toHtml();
    }
}
