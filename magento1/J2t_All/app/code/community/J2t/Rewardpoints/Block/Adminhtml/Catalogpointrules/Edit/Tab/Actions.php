<?php
/**
 * Rewardpoints
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
 * @package    Rewardpoints
 * @copyright  Copyright (c) 2014 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_Block_Adminhtml_Catalogpointrules_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('catalogpointrules_data');
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
            'note'  => Mage::helper('rewardpoints')->__('Only integer values accepted.'),
            'required' => true,
        ));
        
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
