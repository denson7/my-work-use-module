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

class J2t_Rewardpoints_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
	parent::__construct();
        $this->setType('rewardpoints/rule_condition_combine');
    }

    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();

        $conditions = array_merge_recursive($conditions, array(
            //array('value'=>'salesrule/rule_condition_product_found', 'label'=>Mage::helper('salesrule')->__('Product attribute combination')),
            //array('value'=>'salesrule/rule_condition_product_subselect', 'label'=>Mage::helper('salesrule')->__('Products subselection')),
			array('value'=>'rewardpoints/cartrule_condition_product_found', 'label'=>Mage::helper('salesrule')->__('Product attribute combination')),
			array('value'=>'rewardpoints/cartrule_condition_product_subselect', 'label'=>Mage::helper('salesrule')->__('Products subselection')),
            array('value'=>'rewardpoints/rule_condition_combine', 'label'=> Mage::helper('rewardpoints')->__('Conditions Combination'))
            )
                );
        $c_attributes = array(
            array('value'=>'rewardpoints/rule_condition_customeraddress_params|postcode', 'label'=>Mage::helper('rewardpoints')->__('User post code')),
            array('value'=>'rewardpoints/rule_condition_customeraddress_params|region_id', 'label'=>Mage::helper('rewardpoints')->__('User region')),
            array('value'=>'rewardpoints/rule_condition_customeraddress_params|country_id', 'label'=>Mage::helper('rewardpoints')->__('User country'))
        );

        
        /*$customer = Mage::getModel('customer/customer');
        $c2_attributes = array();
        foreach ($customer->getAttributes() as $attribute){
            //echo $attribute->getAttributeCode();
            //echo $attribute->getFrontendLabel();
            //backend_type
            if ($attribute->getBackendModel() == "" && $attribute->getFrontendLabel() != ""){
                $c2_attributes[] = array('value'=>'rewardpoints/rule_condition_customerattribute_params|'.$attribute->getAttributeCode(), 'label'=> $attribute->getFrontendLabel());
            }
            
        }*/

        $conditions = array_merge_recursive($conditions, array(
            array('label'=>Mage::helper('rewardpoints')->__('User location'), 'value'=>$c_attributes),
        ));

        /*$conditions = array_merge_recursive($conditions, array(
            array('label'=>Mage::helper('rewardpoints')->__('User Attributes'), 'value'=>$c2_attributes),
        ));*/


        //$addressCondition = Mage::getModel('salesrule/rule_condition_address');
        $addressCondition = Mage::getModel('rewardpoints/rule_condition_address_address'); 
	$addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();
        $cart_attributes = array();
        foreach ($addressAttributes as $code=>$label) {
            $cart_attributes[] = array('value'=>'rewardpoints/rule_condition_address_address|'.$code, 'label'=>$label); 
	    //$cart_attributes[] = array('value'=>'salesrule/rule_condition_address|'.$code, 'label'=>$label);
        }

        $conditions = array_merge_recursive($conditions, array(
            array('label'=>Mage::helper('salesrule')->__('Cart Attributes'), 'value'=>$cart_attributes),
        ));


        return $conditions;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml().
            Mage::helper('rewardpoints')->__("If %s of these order conditions are %s",
              $this->getAggregatorElement()->getHtml(),
			  $this->getValueElement()->getHtml()
           );
           if ($this->getId()!='1') {
               $html.= $this->getRemoveLinkHtml();
           }

        return $html;
    }
}
