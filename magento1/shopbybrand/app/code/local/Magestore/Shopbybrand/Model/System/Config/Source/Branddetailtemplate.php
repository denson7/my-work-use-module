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
class Magestore_Shopbybrand_Model_System_Config_Source_Branddetailtemplate
{
     public function toOptionArray(){
        return array(
            array('value'=>'page/1column.phtml', 'label'=>'1 Column'),
            array('value'=>'page/2columns-left.phtml', 'label'=>'2 Columns Left'),
            array('value'=>'page/2columns-right.phtml', 'label'=>'2 Columns Right'),
            array('value'=>'page/3columns.phtml', 'label'=>'3 Columns')
        );
    }
}