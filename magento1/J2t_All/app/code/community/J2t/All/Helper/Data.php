<?php

class SimpleXMLExtended extends SimpleXMLElement
{
  public function addCData($cdata_text)
  {
    $node= dom_import_simplexml($this); 
    $no = $node->ownerDocument; 
    $node->appendChild($no->createCDATASection($cdata_text)); 
  } 
}

class  J2t_All_Helper_Data extends Mage_Core_Helper_Abstract {
    
    
    public function importXMLconfiguration($current, $xml, $scope, $scopeId, $website=null, $store=null){
        $store_id = ($store) ? Mage::getModel('core/store')->load($store)->getId() : '';
        $configuration = new SimpleXMLElement($xml);
        foreach($configuration as $key => $config){
            if ($key != "backup"){
                foreach ($config as $key_config => $value){
                    if (Mage::getStoreConfig("$current/$key/$key_config", $store_id) != $value){
                        $config_model = new Mage_Core_Model_Config();
                        $config_model->saveConfig("j2texpandtheme/$key/$key_config", $value, $scope, $scopeId);
                    }
                }
            }
        }
    }
    
    public function generateModuleXMLConfiguration($current, $website=null, $store=null){
        //$current = "j2texpandtheme";
        $configFields = Mage::getSingleton('adminhtml/config');
        $sections     = $configFields->getSections($current);
        $section      = $sections->$current;
        $hasChildren  = $configFields->hasChildren($section, $website, $store);
        
        $store_id = ($store) ? Mage::getModel('core/store')->load($store)->getId() : '';
        
        $xml = new SimpleXMLExtended('<root/>');
        $arr_configuration = array();
        foreach($section->groups as $groups){
            foreach($groups as $key_section => $sections){
                $arr_configuration[$key_section] = array();
                $subnode = $xml->addChild("$key_section");
                foreach ($sections->fields as $elements){
                    foreach($elements as $key_element => $field){
                        $arr_configuration[$key_section][$key_element] = Mage::getStoreConfig("$current/$key_section/$key_element", $store_id);
                        //$subnode->addChild("$key_element", Mage::getStoreConfig("$current/$key_section/$key_element", $store_id));
                        $subnode->addChild("$key_element")->addCData(Mage::getStoreConfig("$current/$key_section/$key_element", $store_id));
                    }
                }
            }
        }
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = TRUE;
        return $dom->saveXML();
        //return $xml->asXML();
    }
    
    
    public function getResizedUrl($imgName, $x, $y=NULL, $resizeFolder = "j2t_resized", $source_dir = ""){
        $imgNamePath = ($source_dir) ? $source_dir.DS.$imgName : $imgName;
        $imgPathFull = Mage::getBaseDir("media").DS.$imgNamePath;
        $widht = $x;
        $y ? $height = $y : $height = $x;
        $imageResizedPath=Mage::getBaseDir("media").DS.$resizeFolder.DS.$x.'-'.$imgName;
        if (!file_exists($imageResizedPath) && file_exists($imgPathFull)){
            $imageObj = new Varien_Image($imgPathFull);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepTransparency(true);
            $imageObj->resize($widht,$height);
            $imageObj->save($imageResizedPath);
        }
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$resizeFolder.'/'.$x.'-'.$imgName;
    }
    
    public function sanitizeString($string) {
        $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
        $string = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        $string = preg_replace(array('~[^0-9a-z_-]~i', '~[ ]+~'), ' ', $string);

        return trim($string, ' -');
    }
}
