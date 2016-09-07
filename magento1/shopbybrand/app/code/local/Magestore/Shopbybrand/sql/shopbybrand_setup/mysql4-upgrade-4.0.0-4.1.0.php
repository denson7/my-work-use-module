<?php
$installer = $this;
$installer->startSetup();

$attributeCode = Mage::getStoreConfig('shopbybrand/general/attribute_code');
$array = Mage::getModel('shopbybrand/system_config_source_attributecode')->toOptionArray();
foreach($array as $value){
    if(!strcasecmp($attributeCode,$value['value'])){
        $inchooSwitch = Mage::getModel('core/config')->saveConfig('shopbybrand/general/attribute_code', $value['value'], 'default', 0);
        break;
    }
}
$installer->endSetup();