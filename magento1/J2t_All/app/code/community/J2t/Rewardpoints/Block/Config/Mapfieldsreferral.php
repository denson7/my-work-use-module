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

class J2t_Rewardpoints_Block_Config_Mapfieldsreferral extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{   
    protected $_calculationTypeReferrer, $_calculationTypeReferred, $_date;
    public function __construct()
    {
        $this->addColumn('min_order_qty', array(
            'label' => Mage::helper('rewardpoints')->__('Min qty orders'),
            'style' => 'width:70px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('max_order_qty', array(
            'label' => Mage::helper('rewardpoints')->__('Max qty orders'),
            'style' => 'width:70px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('point_value_referrer', array(
            'label' => Mage::helper('rewardpoints')->__('Referrer Point/Ratio'),
            'style' => 'width:90px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('calculation_type_referrer', array(
            'label' => Mage::helper('rewardpoints')->__('Referrer Calc. Type'),
            'style' => 'width:70px',
            'class' => 'validate-zero-or-greater',
        ));
        
        $this->addColumn('point_value_referred', array(
            'label' => Mage::helper('rewardpoints')->__('Referred Point/Ratio'),
            'style' => 'width:90px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('calculation_type_referred', array(
            'label' => Mage::helper('rewardpoints')->__('Referred Calc. Type'),
            'style' => 'width:70px',
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
    
    
    protected function _getCalculationTypeReferrerRenderer()
    {
        if (!$this->_calculationTypeReferrer) {
            $this->_calculationTypeReferrer = $this->getLayout()
                    ->createBlock('rewardpoints/config_select')
                    ->setIsRenderToJsTemplate(true);  
            
        }
        return $this->_calculationTypeReferrer;
    }
    
    protected function _getCalculationTypeReferredRenderer()
    {
        if (!$this->_calculationTypeReferred) {
            $this->_calculationTypeReferred = $this->getLayout()
                    ->createBlock('rewardpoints/config_select')
                    ->setIsRenderToJsTemplate(true);  
            
        }
        return $this->_calculationTypeReferred;
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
        
        if ($columnName=="calculation_type_referrer") {
            /*$arr_select = array(
                array("label" => Mage::helper('rewardpoints')->__('No'), "value" => "0"),
                array("label" => Mage::helper('rewardpoints')->__('Yes'), "value" => "1")
            );*/
            
            $arr_select = Mage::getModel('rewardpoints/calculationtype')->toOptionArray();
            
            
            return $this->_getCalculationTypeReferrerRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setExtraParams('style="width:80px"')
                    ->setOptions($arr_select)
                    ->toHtml();
        }
		if ($columnName=="calculation_type_referred") {
            /*$arr_select = array(
                array("label" => Mage::helper('rewardpoints')->__('No'), "value" => "0"),
                array("label" => Mage::helper('rewardpoints')->__('Yes'), "value" => "1")
            );*/
            
            $arr_select = Mage::getModel('rewardpoints/calculationtype')->toOptionArray();
            
            
            return $this->_getCalculationTypeReferredRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setExtraParams('style="width:80px"')
                    ->setOptions($arr_select)
                    ->toHtml();
        }
        if ($columnName == "date_from" || $columnName == "date_end") {
            return $this->_getDateRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setImage(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif')
                    ->setFormat(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT))
                    ->setExtraParams('style="width:65px"')
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
        $row->setData(
            'option_extra_attr_' . $this->_getCalculationTypeReferrerRenderer()->calcOptionHash($row->getData('calculation_type_referrer')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getCalculationTypeReferredRenderer()->calcOptionHash($row->getData('calculation_type_referred')),
            'selected="selected"'
        );
    }
    
    
}