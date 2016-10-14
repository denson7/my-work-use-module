<?php

class J2t_Rewardpoints_Block_Adminhtml_Referralrules_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('referralrules_data');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('action_fieldset', array('legend'=>Mage::helper('rewardpoints')->__('Actions')));

        //$segments_remove = array(array('label' => 'No change', 'value' => J2t_Rewardpoints_Helper_Data::RULES_NO_CHANGE));
        //$segments_remove = array_merge($segments_remove, Mage::getModel('j2tnewsletter/segmentrules')->getSegmentList());

        //action_type

        $fieldset->addField('action_type', 'select', array(
            'label'     => Mage::helper('rewardpoints')->__('Type of action'),
            'name'      => 'action_type',
            'values'    => $model->ruleActionTypesToOptionArray(),
            'after_element_html' => '',
            'required'  => true,
        ));

        $fieldset->addField('points', 'text', array(
            'name' => 'points',
            /*'class'     => 'validate-greater-than-zero',*/
            'class' => 'validate-number',
            'label' => Mage::helper('rewardpoints')->__('Value'),
            'title' => Mage::helper('rewardpoints')->__('Value'),
            'required' => true,
        ));
        
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
