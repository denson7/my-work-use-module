<?php

class J2t_Rewardpoints_Block_Adminhtml_Referralrules_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('referralrules_data');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('rewardpoints')->__('General Information')));

        if ($model->getId()) {
        	$fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
            ));
        }
		
    	$fieldset->addField('title', 'text', array(
            'name' => 'title',
            'label' => Mage::helper('rewardpoints')->__('Rule Title'),
            'title' => Mage::helper('rewardpoints')->__('Rule Title'),
            'required' => true,
        ));

        $fieldset->addField('rule_type', 'select', array(
            'label'     => Mage::helper('rewardpoints')->__('Type of rule'),
            'name'      => 'rule_type',
            'values'    => $model->ruletypesToOptionArray(),
            'after_element_html' => '',
            'required'  => true,
        ));

    	$fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('rewardpoints')->__('Status'),
            'title'     => Mage::helper('rewardpoints')->__('Status'),
            'name'      => 'status',
            'options'    => array(
                '1' => Mage::helper('rewardpoints')->__('Active'),
                '0' => Mage::helper('rewardpoints')->__('Inactive'),
            ),
        ));


        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name'      => 'website_ids[]',
                'label'     => Mage::helper('rewardpoints')->__('Websites'),
                'title'     => Mage::helper('rewardpoints')->__('Websites'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray(),
            ));
        }
        else {
            $fieldset->addField('website_ids', 'hidden', array(
                'name'      => 'website_ids[]',
                'value'     => Mage::app()->getStore(true)->getWebsiteId()
            ));
            $model->setWebsiteIds(Mage::app()->getStore(true)->getWebsiteId());
        }

        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('catalogrule')->__('NOT LOGGED IN')));
        }

        $fieldset->addField('customer_group_ids', 'multiselect', array(
            'name'      => 'customer_group_ids[]',
            'label'     => Mage::helper('rewardpoints')->__('Customer Groups'),
            'title'     => Mage::helper('rewardpoints')->__('Customer Groups'),
            'required'  => true,
            'values'    => $customerGroups,
        ));



        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name'   => 'from_date',
            'label'  => Mage::helper('rewardpoints')->__('From Date'),
            'title'  => Mage::helper('rewardpoints')->__('From Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));
        $fieldset->addField('to_date', 'date', array(
            'name'   => 'to_date',
            'label'  => Mage::helper('rewardpoints')->__('To Date'),
            'title'  => Mage::helper('rewardpoints')->__('To Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => Mage::helper('rewardpoints')->__('Priority'),
        ));



        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
