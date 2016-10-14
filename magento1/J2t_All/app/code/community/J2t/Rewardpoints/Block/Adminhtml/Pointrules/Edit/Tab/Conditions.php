<?php


class J2t_Rewardpoints_Block_Adminhtml_Pointrules_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('pointrules_data');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/rewardpointsadmin_pointrules/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('rewardpoints')->__('Conditions'))
        )->setRenderer($renderer);

    	$fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('rewardpoints')->__('Conditions'),
            'title' => Mage::helper('rewardpoints')->__('Conditions'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}