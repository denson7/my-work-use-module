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

class J2t_Rewardpoints_Block_Config_Export extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('catalog/product'); 


        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button');

        $params = array(
            'website' => $buttonBlock->getRequest()->getParam('website')
        );

        $data = array(
            'label'     => Mage::helper('rewardpoints')->__('Export CSV'),
            'onclick'   => 'setLocation(\''.$this->getUrl("adminhtml/rewardpointsadmin_config/exportTablepoints", $params) . 'rewardpoints.csv\' )',
            'class'     => '',
        );

        $html = $buttonBlock->setData($data)->toHtml();


        /*$html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData($data)
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Run Now !')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();*/

        return $html;
    }

    /*extends Varien_Data_Form_Element_Abstract
{
    public function getElementHtml()
    {
        $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock('adminhtml/widget_button');

        $params = array(
            'website' => $buttonBlock->getRequest()->getParam('website')
        );

        $data = array(
            'label'     => Mage::helper('rewardpoints')->__('Export CSV'),*/
        //    'onclick'   => 'setLocation(\''.Mage::helper('j2tsmsgateway')->getUrl("*/*/exportTablerates", $params) . '/tablephones.csv\' )',
        /*    'class'     => '',
        );

        $html = $buttonBlock->setData($data)->toHtml();

        return $html;
    }*/
}
