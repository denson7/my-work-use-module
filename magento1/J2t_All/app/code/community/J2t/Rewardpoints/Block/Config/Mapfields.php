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

class J2t_Rewardpoints_Block_Config_Mapfields extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    
    protected $_templateRenderer;
    protected $_senderRenderer;

    public function __construct()
    {
        $this->addColumn('min_value', array(
            'label' => Mage::helper('rewardpoints')->__('Min value'),
            'style' => 'width:90px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('max_value', array(
            'label' => Mage::helper('rewardpoints')->__('Max value'),
            'style' => 'width:90px',
            'class' => 'validate-zero-or-greater',
        ));
        $this->addColumn('duration', array(
            'label' => Mage::helper('rewardpoints')->__('Duration (in days)'),
            'style' => 'width:90px',
            'class' => 'validate-zero-or-greater',
        ));
        
        $this->addColumn('sender', array(
            'label' => Mage::helper('rewardpoints')->__('Email sender'),
            'style' => 'width:90px',
        ));
        
        //adminhtml/system_config_source_email_template
        $this->addColumn('template', array(
            'label' => Mage::helper('rewardpoints')->__('Email template'),
            'style' => 'width:90px',
        ));
        //adminhtml/system_config_source_email_template
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('rewardpoints')->__('Add value');
        parent::__construct();
    }
    
    protected function _getSenderRenderer() 
    {
        if (!$this->_senderRenderer) {
            $this->_senderRenderer = $this->getLayout()
                    ->createBlock('rewardpoints/config_select')
                    ->setIsRenderToJsTemplate(true);            
        }
        return $this->_senderRenderer;
    }
    
    
    protected function _getTemplateRenderer() 
    {
        if (!$this->_templateRenderer) {
            $this->_templateRenderer = $this->getLayout()
                    ->createBlock('rewardpoints/config_select')
                    ->setIsRenderToJsTemplate(true);            
        }
        return $this->_templateRenderer;
    }
    
    protected function _renderCellTemplate($columnName) 
    {
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        if ($columnName=="template") {
            $collection = Mage::getResourceModel('core/email_template_collection')
                ->load();
            $arr_select = $collection->toOptionArray();
            
            array_unshift(
                $arr_select,
                array(
                    'label' => Mage::helper('rewardpoints')->__('Default'),
                    'value' => ''
                )
            );
            
            
            return $this->_getTemplateRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setExtraParams('style="width:260px"')
                    //->setOptions($this->getElement()->getValues())
                    ->setOptions($arr_select)
                    ->toHtml();
        } else if ($columnName=="sender") {
            $arr_select = array();
            $config = Mage::getSingleton('adminhtml/config')->getSection('trans_email')->groups->children();
            foreach ($config as $node) {
                $nodeName   = $node->getName();
                $label      = (string) $node->label;
                $sortOrder  = (int) $node->sort_order;
                $arr_select[$sortOrder] = array(
                    'value' => preg_replace('#^ident_(.*)$#', '$1', $nodeName),
                    'label' => Mage::helper('adminhtml')->__($label)
                );
            }
            ksort($arr_select);
            
            /*array_unshift(
                $arr_select,
                array(
                    'label' => Mage::helper('rewardpoints')->__('Default'),
                    'value' => ''
                )
            );*/
            
            return $this->_getSenderRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setExtraParams('style="width:260px"')
                    ->setOptions($arr_select)
                    ->toHtml();
        }

        return parent::_renderCellTemplate($columnName);
    }
    
    protected function _prepareArrayRow(Varien_Object $row) 
    {
        $row->setData(
            'option_extra_attr_' . $this->_getSenderRenderer()->calcOptionHash($row->getData('sender')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getTemplateRenderer()->calcOptionHash($row->getData('template')),
            'selected="selected"'
        );
    }
    
    
}