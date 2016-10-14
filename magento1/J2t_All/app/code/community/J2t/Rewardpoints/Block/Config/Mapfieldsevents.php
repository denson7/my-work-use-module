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

class J2t_Rewardpoints_Block_Config_Mapfieldsevents extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{   
    protected $_processOnceRenderer;
    protected $_modelIdRenderer;
    public function __construct()
    {
        //List of event defined by: observed magento class name, point value, type of process, maximum point allocation, duration between two point allocation.
        
        
        $this->addColumn('class_name', array(
            'label' => Mage::helper('rewardpoints')->__('Class Name'),
            'style' => 'width:180px',
            //'class' => 'validate-zero-or-greater',
        ));
        
        $this->addColumn('model_id', array(
            'label' => Mage::helper('rewardpoints')->__('Id Verification'),
            'style' => 'width:90px',
            'class' => 'validate-zero-or-greater',
        ));
        
        $this->addColumn('point_value', array(
            'label' => Mage::helper('rewardpoints')->__('Point Value'),
            'style' => 'width:30px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('process_once', array(
            'label' => Mage::helper('rewardpoints')->__('Process Once'),
            'style' => 'width:30px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('use_end', array(
            'label' => Mage::helper('rewardpoints')->__('Validity duration'),
            'style' => 'width:30px',
            'class' => 'validate-zero-or-greater',
        ));
        
        $this->addColumn('max_point', array(
            'label' => Mage::helper('rewardpoints')->__('Max Points'),
            'style' => 'width:50px',
            'class' => 'validate-zero-or-greater',
        ));
        
        $this->addColumn('duration', array(
            'label' => Mage::helper('rewardpoints')->__('Duration'),
            'style' => 'width:30px',
            'class' => 'validate-zero-or-greater',
        ));
        
        $this->addColumn('description', array(
            'label' => Mage::helper('rewardpoints')->__('Description'),
            'style' => 'width:90px',
        ));
        
        $this->addColumn('verifications', array(
            'label' => Mage::helper('rewardpoints')->__('Verifications'),
            'style' => 'width:90px',
        ));
        
        /*$this->addColumn('template', array(
            'label' => Mage::helper('rewardpoints')->__('Email template'),
            'style' => 'width:90px',
        ));*/
        //adminhtml/system_config_source_email_template
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('rewardpoints')->__('Add value');
        parent::__construct();
    }
    
    
    protected function _getModelIdRenderer()
    {
        //_modelIdRenderer
        if (!$this->_modelIdRenderer) {
            $this->_modelIdRenderer = $this->getLayout()
                    ->createBlock('rewardpoints/config_select')
                    ->setIsRenderToJsTemplate(true);            
        }
        return $this->_modelIdRenderer;
    }
    
    protected function _getProcessOnceRenderer() 
    {
        if (!$this->_processOnceRenderer) {
            $this->_processOnceRenderer = $this->getLayout()
                    ->createBlock('rewardpoints/config_select')
                    ->setIsRenderToJsTemplate(true);            
        }
        return $this->_processOnceRenderer;
    }
    
    
    protected function _renderCellTemplate($columnName) 
    {
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        if ($columnName=="process_once") {
            $arr_select = array(
                array("label" => Mage::helper('rewardpoints')->__('No'), "value" => "0"),
                array("label" => Mage::helper('rewardpoints')->__('Yes'), "value" => "1")
            );
            
            
            return $this->_getProcessOnceRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setExtraParams('style="width:60px"')
                    //->setOptions($this->getElement()->getValues())
                    ->setOptions($arr_select)
                    ->toHtml();
        }
        
        if ($columnName=="model_id") {
            $arr_select = array(
                array("label" => Mage::helper('rewardpoints')->__('No'), "value" => "0"),
                array("label" => Mage::helper('rewardpoints')->__('Yes'), "value" => "1")
            );
            
            
            return $this->_getModelIdRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setExtraParams('style="width:60px"')
                    //->setOptions($this->getElement()->getValues())
                    ->setOptions($arr_select)
                    ->toHtml();
        }
        

        return parent::_renderCellTemplate($columnName);
    }
    
    protected function _prepareArrayRow(Varien_Object $row) 
    {
        $row->setData(
            'option_extra_attr_' . $this->_getProcessOnceRenderer()->calcOptionHash($row->getData('process_once')),
            'selected="selected"'
        );
        
        $row->setData(
            'option_extra_attr_' . $this->_getModelIdRenderer()->calcOptionHash($row->getData('model_id')),
            'selected="selected"'
        );
    }
    
    
}