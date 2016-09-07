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
class Magestore_Shopbybrand_Model_System_Config_Source_Branddetailblockposition
{
     public function toOptionArray(){
        return array(
            array('value'=>'1', 'label'=>'1'),
            array('value'=>'2', 'label'=>'2'),
            array('value'=>'3', 'label'=>'3'),
            array('value'=>'4', 'label'=>'4'),
            array('value'=>'5', 'label'=>'5'),
            array('value'=>'6', 'label'=>'6')
        );
    }
}