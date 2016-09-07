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
 * Shopbybrand Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Block_Adminhtml_Brand_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'shopbybrand';
        $this->_controller = 'adminhtml_brand';
        
        $this->_updateButton('save', 'label', Mage::helper('shopbybrand')->__('Save Brand'));
        $this->_updateButton('delete', 'label', Mage::helper('shopbybrand')->__('Delete Brand'));
        
        $this->_addButton('saveandcontinue', array(
            'label'        => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'    => 'saveAndContinueEdit()',
            'class'        => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('shopbybrand_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'shopbybrand_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'shopbybrand_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
        ";
    }
    /* Edit by Son*/
    public function _prepareLayout()
        {
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
        $block->setCanLoadTinyMce(true);
        //$block->setCanLoadExtJs(true);
        }
        return parent::_prepareLayout();

        }
        /* Edit by Son*/
    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('brand_data')
            && Mage::registry('brand_data')->getId()
        ) {
            return Mage::helper('shopbybrand')->__("Edit Brand '%s'",
                $this->htmlEscape(Mage::registry('brand_data')->getName())
            );
        }
        return Mage::helper('shopbybrand')->__('Add Brand');
    }
}