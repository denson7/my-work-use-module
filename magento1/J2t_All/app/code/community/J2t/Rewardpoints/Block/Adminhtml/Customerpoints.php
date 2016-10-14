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
class J2t_Rewardpoints_Block_Adminhtml_Customerpoints extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('rewardpoints/clientpoints.phtml');
    }

    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_rewardpoints');
        $customer = Mage::registry('current_customer');
        

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('rewardpoints')->__('Update Reward Points')));

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
            'required'  => false,
            /*'class'     => 'validate-date',*/
        ));

        $fieldset->addField('date_end', 'date', array(
            'name'      => 'date_end',
            'title'     => Mage::helper('rewardpoints')->__('To Date'),
            'label'     => Mage::helper('rewardpoints')->__('To Date'),
            'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'required'  => false,
            /*'class'     => 'validate-date',*/
        ));
        
        $fieldset->addField('rewardpoints_notification', 'checkbox', array(
            'label'     => Mage::helper('rewardpoints')->__('Send notification email'),
            'required'  => false,
            'name'      => 'rewardpoints_notification',
            'onclick'   => 'this.value = this.checked ? 1 : 0;',
        ));
        

        $this->setForm($form);
        return $this;
    }

    public function getCurrentBalance($store_id = null){        
        $customer = Mage::registry('current_customer');
        if ($store_id == null)
            $store_id = $customer->getStore()->getId();
        $points_received = 0;
        if ($customer->getId()){
            $points_received = Mage::getModel('rewardpoints/stats')->getPointsCurrent($customer->getId(), $store_id);
        }
        
        return $points_received;
    }
    
    public function isStoreScope() {
        return Mage::getStoreConfig('rewardpoints/default/store_scope');
    }
    
    public function getAllStores() {
        $allStores = Mage::app()->getStores();
        return $allStores;
        /*foreach ($allStores as $_eachStoreId => $val)
        {
            $_storeCode = Mage::app()->getStore($_eachStoreId)->getCode();
            $_storeName = Mage::app()->getStore($_eachStoreId)->getName();
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            echo $_storeId;
            echo $_storeCode;
            echo $_storeName;
        }*/
    }
 
    protected function _prepareLayout()
    {
        $this->setChild('grid',
            $this->getLayout()->createBlock('rewardpoints/adminhtml_customerstats','rewardpoints.customer_stats_grid')
        );
        return parent::_prepareLayout();
    }

}
