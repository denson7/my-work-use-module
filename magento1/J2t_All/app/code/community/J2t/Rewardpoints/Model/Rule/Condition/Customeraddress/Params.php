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
class J2t_Rewardpoints_Model_Rule_Condition_Customeraddress_Params extends Mage_Rule_Model_Condition_Abstract
{
    /**
     * Retrieve attribute object
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */

    public function __construct()
    {
        parent::__construct();
        $this->setType('rewardpoints/rule_condition_customeraddress_params')
            ->setValue(null);
    }

    

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('rewardpoints');
	$attributes = array(
            'postcode' => Mage::helper('rewardpoints')->__('Zip/Postal Code'),
            'region_id' => Mage::helper('rewardpoints')->__('Region'),
            'country_id' => Mage::helper('rewardpoints')->__('Country'),
        );

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

            case 'shipping_method': case 'payment_method': case 'country_id': case 'region_id':
                return 'select';
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
                return 'select';
        }

        

        return 'text';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {

            $options = array();

            

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
	/*$customerId = $object->getQuote()->getCustomerId();
        if ($customerId){
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $address = $customer->getPrimaryBillingAddress();
        } else {
            return false;
        }*/
        //Mage_Checkout_Model_Cart
        //if ($object instanceof Mage_Sales_Model_Order && $order->getId())

        //print_r(Mage::helper('checkout/cart')->getCart());

        $customerId = $object->getQuote()->getCustomerId();
        if ($customerId){
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if ($address = $object->getPrimaryBillingAddress()){
                return parent::validate($address);
            }
        }
        
        return false;
        /*if ($address = $object->getPrimaryBillingAddress()){
            return parent::validate($address);
        } else {
            return false;
        }*/
    }
}