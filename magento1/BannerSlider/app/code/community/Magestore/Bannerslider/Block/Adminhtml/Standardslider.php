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
 * @category 	Magestore
 * @package 	Magestore_Bannerslider
 * @copyright 	Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license 	http://www.magestore.com/license-agreement.html
 */

 /**
 * Bannerslider Adminhtml Block
 * 
 * @category 	Magestore
 * @package 	Magestore_Bannerslider
 * @author  	Magestore Developer
 */
class Magestore_Bannerslider_Block_Adminhtml_Standardslider extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
           // zend_debug::dump($this);die();
		$this->_controller = 'adminhtml_standardslider';
		$this->_blockGroup = 'bannerslider';
		$this->_headerText = Mage::helper('bannerslider')->__('Preview Slider Styles ');
		parent::__construct();
                $this->removeButton('add');
	}
}