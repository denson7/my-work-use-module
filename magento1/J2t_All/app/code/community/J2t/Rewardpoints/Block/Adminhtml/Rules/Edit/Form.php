<?php
/**
 * J2T RewardsPoint2
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
class J2t_Rewardpoints_Block_Adminhtml_Rules_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $rule = Mage::getModel('rewardpoints/rules');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('rule_details', array('legend'=>Mage::helper('rewardpoints')->__('Rule details')));

        $fieldset->addField('rewardpoints_rule_name', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Rule Name'),
            'class'     => 'input-text',
            'required'  => true,
            'name'      => 'rewardpoints_rule_name',
            'required'  => true,
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('rewardpoints_rule_start', 'date', array(
            
            'name'   => 'rewardpoints_rule_start',
            'label'  => Mage::helper('catalogrule')->__('From Date'),
            'title'  => Mage::helper('catalogrule')->__('From Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso,
            'required'  => true


        ));

        $fieldset->addField('rewardpoints_rule_end', 'date', array(
            'name'      => 'rewardpoints_rule_end',
            'title'     => Mage::helper('rewardpoints')->__('To Date'),
            'label'     => Mage::helper('rewardpoints')->__('To Date'),
            'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'required'  => true,
        ));

        $fieldset->addField('rewardpoints_rule_type', 'select', array(
            'label'     => Mage::helper('rewardpoints')->__('Type of rule'),
            'name'      => 'rewardpoints_rule_type',
            'values'    => $rule->targetsToOptionArray(),
            'onchange'  => 'checkTarget()',
            'after_element_html' => '',
            'required'  => true,
        ));

        $fieldset = $form->addFieldset('rule_action', array('legend'=>Mage::helper('rewardpoints')->__('Rule condition')));

        $fieldset->addField('rewardpoints_rule_operator', 'select', array(
            'label'     => Mage::helper('rewardpoints')->__('Operator'),
            'name'      => 'rewardpoints_rule_operator',
            'values'    => $rule->operatorToOptionArray(),
            'onchange'  => 'changeOperator()',
            'after_element_html' => '',
            'required'  => true,
        ));

        $fieldset->addField('rewardpoints_rule_test', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Test value'),
            'class'     => 'input-text',
            'required'  => true,
            'name'      => 'rewardpoints_rule_test',
            'required'  => true,
        ));
    
    
        $fieldset->addField('rewardpoints_rule_points', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Points to be earned'),
            'class'     => 'validate-greater-than-zero',
            'required'  => true,
            'name'      => 'rewardpoints_rule_points',
            'required'  => true,
        ));


        $fieldset->addField('rewardpoints_rule_extra', 'select', array(
            'label'     => Mage::helper('rewardpoints')->__('Overload existing configuration'),
            'name'      => 'rewardpoints_rule_extra',
            'values'    => $rule->activatedToOptionArray(),
            'after_element_html' => '',
            'required'  => true,
        ));





        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name'      => 'website_ids[]',
                'label'     => Mage::helper('catalogrule')->__('Websites'),
                'title'     => Mage::helper('catalogrule')->__('Websites'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray(),
            ));
        }
        else {
            $fieldset->addField('website_ids', 'hidden', array(
                'name'      => 'website_ids[]',
                'value'     => Mage::app()->getStore(true)->getWebsiteId()
            ));
            //$model->setWebsiteIds(Mage::app()->getStore(true)->getWebsiteId());
        }




        $fieldset->addField('rewardpoints_rule_activated', 'select', array(
            'label'     => Mage::helper('rewardpoints')->__('Status'),
            'name'      => 'rewardpoints_rule_activated',
            'values'    => $rule->activatedToOptionArray(),
            'after_element_html' => '',
            'required'  => true,
        ));



        if ( Mage::getSingleton('adminhtml/session')->getRulesData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getRulesData());
            Mage::getSingleton('adminhtml/session')->setRulesData(null);
        } elseif ( Mage::registry('pointrules_data') ) {
            $form->setValues(Mage::registry('pointrules_data')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}