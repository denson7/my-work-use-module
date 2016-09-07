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
class Magestore_Shopbybrand_Block_Adminhtml_Brand extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_brand';
        $this->_blockGroup = 'shopbybrand';
        $this->_headerText = Mage::helper('shopbybrand')->__('Brand Manager');
        $this->_addButtonLabel = Mage::helper('shopbybrand')->__('Add Brand');
        parent::__construct();
        $this->_addButton('import_brand',array(
		'label'		=> Mage::helper('shopbybrand')->__('Import Brands'),
		'onclick'	=> "setLocation('{$this->getUrl('*/*/importbrand')}')",
		'class'		=> 'add'
	),-1);
    }
}