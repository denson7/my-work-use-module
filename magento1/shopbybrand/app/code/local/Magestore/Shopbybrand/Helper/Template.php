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
 * Template Helper
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Helper_Template extends Mage_Core_Helper_Abstract
{
    public function getSwitchedTemplate($type, $file){
		$templateConfig = Mage::getStoreConfig('shopbybrand/template/'.$type);
		$templateDir = 'templates';
		$filename = substr($file,strrpos($file,DS)+1);
		$offsetdir = str_replace('shopbybrand'.DS,'',substr($file,strrpos($file,'shopbybrand'.DS)));
		$basedir = substr($file,0,strrpos($file,'shopbybrand'.DS)).'shopbybrand';
		$switchedfile = $basedir.DS.$templateDir.DS.$templateConfig.DS.$offsetdir;
		return $switchedfile;
    }
}