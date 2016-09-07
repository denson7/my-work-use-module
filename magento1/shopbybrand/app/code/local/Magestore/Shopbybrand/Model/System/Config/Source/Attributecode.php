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
class Magestore_Shopbybrand_Model_System_Config_Source_Attributecode
{
     public function toOptionArray(){
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->addIsFilterableFilter()
            ->addFieldToFilter('main_table.frontend_input', array('eq' => 'select'))
                ;
        $array=array();
        $array[] = array('value'=>'', 'label'=>'');
        foreach ($collection as $value) {
            $array[]=array('value'=>$value->getAttributeCode(), 'label'=>$value->getFrontendLabel());
        }
        return $array;
    }
}