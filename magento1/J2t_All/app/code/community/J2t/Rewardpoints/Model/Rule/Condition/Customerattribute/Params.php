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
class J2t_Rewardpoints_Model_Rule_Condition_Customerattribute_Params extends Mage_Rule_Model_Condition_Abstract
{
    /**
     * Retrieve attribute object
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */

    public function __construct()
    {
        parent::__construct();
        $this->setType('rewardpoints/rule_condition_customerattribute_params')
            ->setValue(null);
    }

    

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('rewardpoints');
	$attributes = array();

        $customer = Mage::getModel('customer/customer');
        foreach ($customer->getAttributes() as $attribute){
            if ($attribute->getBackendModel() == "" && $attribute->getFrontendLabel() != ""){
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }

        $this->setAttributeOption($attributes);        
        return $this;

    }

    
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '=='  => Mage::helper('rewardpoints')->__('is'),
            '!='  => Mage::helper('rewardpoints')->__('is not'),
            '>='  => Mage::helper('rewardpoints')->__('equals or greater than'),
            '<='  => Mage::helper('rewardpoints')->__('equals or less than'),
            '>'   => Mage::helper('rewardpoints')->__('greater than'),
            '<'   => Mage::helper('rewardpoints')->__('less than'),
        ));
        return $this;
    }




    /**
     * Retrieve Explicit Apply
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        switch ($this->getAttribute()) {
            case 'sku': case 'category_ids':
                return true;
        }
        $customer = Mage::getModel('customer/customer');
        foreach ($customer->getAttributes() as $attribute){
            if ($attribute->getBackendModel() == "" && $attribute->getFrontendLabel() != ""){
                if ($attribute->getAttributeCode() == $this->getAttribute()){
                    switch ($attribute->getBackendType()) {
                        case 'date':
                        case 'datetime':
                            return true;
                    }
                }
            }
        }
        return false;
    }

    

    /**
     * Retrieve value element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();
        $customer = Mage::getModel('customer/customer');
        foreach ($customer->getAttributes() as $attribute){
            if ($attribute->getBackendModel() == "" && $attribute->getFrontendLabel() != ""){
                if ($attribute->getAttributeCode() == $this->getAttribute()){
                    switch ($attribute->getBackendType()) {
                        case 'date':
                        case 'datetime':
                            $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                            break;
                    }
                }
            }
        }

        return $element;
    }


    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku': case 'category_ids':
                $url = 'adminhtml/promo_widget/chooser'
                    .'/attribute/'.$this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/'.$this->getJsFormObject();
                }
                break;
        }
        return $url!==false ? Mage::helper('adminhtml')->getUrl($url) : '';
    }


    public function asHtml()
    {
        if ($this->getAttribute()=='sku')
        {
                $html = $this->getTypeElement()->getHtml().
                        Mage::helper('rewardpoints')->__("%s %s",
                           $this->getAttributeElement()->getHtml(),
                           $this->getValueElement()->getHtml()
                   );
                   if ($this->getId()!='1') {
                           $html.= $this->getRemoveLinkHtml();
                   }
                return $html;
        }

        return parent::asHtml();
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal': case 'weight': case 'total_qty':
                return 'numeric';

            case 'shipping_method': case 'payment_method': case 'country_id': case 'region_id': case 'group_id':
                return 'select';
        }

        $customer = Mage::getModel('customer/customer');
        foreach ($customer->getAttributes() as $attribute){
            if ($attribute->getBackendModel() == "" && $attribute->getFrontendLabel() != ""){
                if ($attribute->getAttributeCode() == $this->getAttribute()){
                    switch ($attribute->getBackendType()) {
                        case 'date':
                        case 'datetime':
                            return 'date';
                        case 'varchar':
                            return 'string';
                        case 'int':
                            return 'numeric';
                        case 'static':
                            return 'select';
                    }
                }
            }
        }



        return 'string';
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'shipping_method':
            case 'payment_method':
            case 'country_id':
            case 'region_id':
            case 'group_id':
                return 'select';
        }

        $customer = Mage::getModel('customer/customer');
        foreach ($customer->getAttributes() as $attribute){
            if ($attribute->getBackendModel() == "" && $attribute->getFrontendLabel() != ""){
                if ($attribute->getAttributeCode() == $this->getAttribute()){
                    switch ($attribute->getBackendType()) {
                        case 'date':
                        case 'datetime':
                            return 'date';
                        case 'text':
                            return 'text';
                        case 'int':
                            return 'numeric';
                        case 'static':
                            //return 'select';
                            return 'text';
                    }
                }
            }
        }


        return 'text';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {

            $options = array();

            $customer = Mage::getModel('customer/customer');
            foreach ($customer->getAttributes() as $attribute){
                if ($attribute->getBackendModel() == "" && $attribute->getFrontendLabel() != ""){
                    if ($attribute->getAttributeCode() == $this->getAttribute()){
                        switch ($attribute->getBackendType()) {
                            case 'static':
                                /*$att = Mage::getModel('eav/entity_attribute');
                                $att->loadByCode($attribute->getEntityTypeId(), $attribute->getAttributeCode());
                                $options = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                    ->setStoreFilter()
                                    ->setAttributeFilter($att->getId())
                                    ->load()
                                    ->toOptionArray();
                            break;*/
                            default:
                                $options = array();
                        }
                    }
                }
            }

            if ($options == array()){
                switch ($this->getAttribute()) {
                    case 'confirmation':
                        $options = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();
                        break;
                    case 'country_id':
                        $options = Mage::getModel('adminhtml/system_config_source_country')
                            ->toOptionArray();
                        break;

                    case 'region_id':
                        $options = Mage::getModel('adminhtml/system_config_source_allregion')
                            ->toOptionArray();
                        break;

                    case 'group_id':
                        /*$options = Mage::getModel('adminhtml/system_config_source_allregion')
                            ->toOptionArray();*/
                        $options = Mage::getResourceModel('customer/group_collection')
                             ->load()->toOptionArray();
                        break;

                    default:
                        $options = array();
                }
            }            
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    public function validate(Varien_Object $object)
    {
	$customerId = $object->getQuote()->getCustomerId();
        if ($customerId){
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $datas = $customer->getData();
            return parent::validate($datas);
        }

        return false;
        
        

        //if ($object instanceof Mage_Sales_Model_Order && $order->getId())
        /*
        if ($datas = $object->getData()){
            return parent::validate($object);
        } else {
            return false;
        }*/

    }
}