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

class J2t_Rewardpoints_Block_Adminhtml_Catalogpointrules_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('catalogpointrules_data');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/rewardpointsadmin_catalogpointrules/newConditionHtml/form/rule_conditions_fieldset'));
        
        // /admin/rewardpointsadmin_catalogpointrules/index/key/cd0e8183e2736a9eb7602055f3c1036e/
        // /admin/rewardpointsadmin_catalogpointrules/
        // /admin/rewardpoints_admin/adminhtml_catalogpointrules/newConditionHtml/form/rule_conditions_fieldset/key/ddca22158e369a25490f42c5368eadf4

        

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