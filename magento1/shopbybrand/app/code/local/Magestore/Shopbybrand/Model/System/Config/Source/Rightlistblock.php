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
 * Listing type Model
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Model_System_Config_Source_Rightlistblock
{
     public function toOptionArray(){
         $redirectUrl = Mage::app()->getStore($storeId)->getUrl('brand/index/getrightblock', array('_secure'=>true));
         $listblock = json_decode(file_get_contents($redirectUrl));
         $array = array();
         if(count($listblock))
            foreach ($listblock as $block){
                $array[] = array('value'=>$block, 'label'=>$block);
            }
         return $array;
    }
}