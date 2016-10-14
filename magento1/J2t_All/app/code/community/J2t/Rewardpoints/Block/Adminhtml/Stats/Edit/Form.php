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
class J2t_Rewardpoints_Block_Adminhtml_Stats_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $rule = Mage::getModel('rewardpoints/stats');
        $model = Mage::registry('stats_data');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('order_details', array('legend'=>Mage::helper('rewardpoints')->__('Order details')));
        
        $fieldset->addField('customer_id', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Customer ID'),
            'class'     => 'validate-greater-than-zero',
            'required'  => true,
            'name'      => 'customer_id',
        ));

        /*$fieldset->addField('store_id', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Store ID'),
            'class'     => 'validate-greater-than-zero',
            'required'  => true,
            'name'      => 'store_id',
        ));*/

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'store_id[]',
                'label'     => Mage::helper('rewardpoints')->__('Store'),
                'title'     => Mage::helper('rewardpoints')->__('Store'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_config_source_store')->toOptionArray(),
            ));
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'store_id[]',
                'value'     => Mage::app()->getStore(true)->getStoreId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getWebsiteId());
        }


        $fieldset->addField('target_type', 'select', array(
            'label'     => Mage::helper('rewardpoints')->__('Points are'),
            'name'      => 'target_type',
            'values'    => $rule->targetsToOptionArray(),
            'onchange'  => 'checkTarget()',
            'after_element_html' => '',
        ));

        $fieldset->addField('order_id', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Order ID'),
            'name'      => 'order_id',
        ));

        $fieldset = $form->addFieldset('points_action', array('legend'=>Mage::helper('rewardpoints')->__('Points')));

        $fieldset->addField('points_current', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Points'),
            'class'     => 'validate-greater-than-zero',
            'name'      => 'points_current',
        ));

        $fieldset->addField('points_spent', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Points Spent'),
            'class'     => 'validate-greater-than-zero',
            'name'      => 'points_spent',
        ));


        $fieldset->addField('rewardpoints_description', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Description'),
            'class'     => 'validate-length maximum-length-255',
            'name'      => 'rewardpoints_description',
        ));


        $fieldset->addField('date_start', 'date', array(
            'name'      => 'date_start',
            'title'     => Mage::helper('rewardpoints')->__('From Date'),
            'label'     => Mage::helper('rewardpoints')->__('From Date'),
            'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'required'  => true,
        ));

        $fieldset->addField('date_end', 'date', array(
            'name'      => 'date_end',
            'title'     => Mage::helper('rewardpoints')->__('To Date'),
            'label'     => Mage::helper('rewardpoints')->__('To Date'),
            'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'required'  => false,
        ));
        
        $fieldset->addField('rewardpoints_notification', 'checkbox', array(
            'label'     => Mage::helper('rewardpoints')->__('Send notification email'),
            'required'  => false,
            'name'      => 'rewardpoints_notification',
            'onclick'   => 'this.value = this.checked ? 1 : 0;',
        ));



        
        if ( Mage::getSingleton('adminhtml/session')->getStatsData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getStatsData());
            Mage::getSingleton('adminhtml/session')->setStatsData(null);
        } elseif ( Mage::registry('stats_data') ) {
            $form->setValues(Mage::registry('stats_data')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}