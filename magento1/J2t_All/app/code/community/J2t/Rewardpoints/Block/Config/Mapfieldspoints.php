<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class J2t_Rewardpoints_Block_Config_Mapfieldspoints extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{   
    protected $_customGroup, $_date;
    public function __construct()
    {
        $this->addColumn('min_cart_value', array(
            'label' => Mage::helper('rewardpoints')->__('Min Cart'),
            'style' => 'width:90px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('max_cart_value', array(
            'label' => Mage::helper('rewardpoints')->__('Max Cart'),
            'style' => 'width:40px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('point_value', array(
            'label' => Mage::helper('rewardpoints')->__('Value'),
            'style' => 'width:40px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('group_id', array(
            'label' => Mage::helper('rewardpoints')->__('Customer Group'),
            'style' => 'width:50px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('date_from', array(
            'label' => Mage::helper('rewardpoints')->__('From'),
            'style' => 'width:50px',
        ));
        
        $this->addColumn('date_end', array(
            'label' => Mage::helper('rewardpoints')->__('Until'),
            'style' => 'width:50px',
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('rewardpoints')->__('Add value');
        parent::__construct();
    }
    
    
    protected function _getCustomerGroupRenderer()
    {
        if (!$this->_customGroup) {
            $this->_customGroup = $this->getLayout()
                    ->createBlock('rewardpoints/config_select')
                    ->setIsRenderToJsTemplate(true);  
            
        }
        return $this->_customGroup;
    }
    
    protected function _getDateRenderer()
    {
        
        if (!$this->_date) {
            $this->_date = $this->getLayout()
                    ->createBlock('rewardpoints/config_date')
                    ->setIsRenderToJsTemplate(true);
        }
        return $this->_date;
    }
    
    
    protected function _renderCellTemplate($columnName) 
    {
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        
        if ($columnName=="group_id") {
            $collection = Mage::getResourceModel('customer/group_collection')
                ->load();
            $arr_select = $collection->toOptionArray();
            
             return $this->_getCustomerGroupRenderer($columnName)
                    ->setName($inputName.'[]')
                    ->setTitle($columnName)
                    ->setExtraParams('style="width:90px"')
                    ->setIsMultiSelect(1)
                    ->setOptions($arr_select)
                    ->setClass("j2t_customdelivery_countries")
                    ->setExtraParams('multiple="multiple" size="5"')
                    ->toHtml();
            
            
            /*return $this->_getCustomerGroupRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setIsMultiSelect(1)
                    ->setExtraParams('style="width:180px" multiple="multiple" size="5"')
                    ->setOptions($arr_select)
                    ->toHtml();*/
        }
        if ($columnName == "date_from" || $columnName == "date_end") {
            return $this->_getDateRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setImage(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif')
                    ->setFormat(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT))
                    ->setExtraParams('style="width:110px"')
                    //->setOptions($arr_select)
                    ->toHtml();
        }
        
        return parent::_renderCellTemplate($columnName);
    }
    
    protected function _prepareArrayRow(Varien_Object $row) 
    {
        $date_from  = ($row->getData('date_from')) ? Mage::helper('core')->formatDate($row->getData('date_from') , Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, false) : "";
        $date_end   = ($row->getData('date_end')) ? Mage::helper('core')->formatDate($row->getData('date_end') , Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, false) : "";
        $row->setData(
            'date_from',
            $date_from 
        );
        $row->setData(
            'date_end',
            $date_end 
        );
        if (is_array($row->getData('group_id')) && sizeof($row->getData('group_id'))){
            foreach($row->getData('group_id') as $value){
                $row->setData(
                    'option_extra_attr_' . $this->_getCustomerGroupRenderer('group_id')->calcOptionHash($value),
                    'selected="selected"'
                );
            }
        }
    }
    
    
}