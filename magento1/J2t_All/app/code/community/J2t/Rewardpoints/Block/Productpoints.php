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
class J2t_Rewardpoints_Block_Productpoints extends Mage_Catalog_Block_Product_Abstract
{
    public function getConfigurableProducts($product){
        $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);
    }

    
    public function getTierPricesJson($product, $return_array = false)
    {
        //TODO : configurable product tierprice
        //$prices  = $product->getFormatedTierPrice();
        $prices  = $product->getTierPrice();
        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['price_qty'] = $price['price_qty']*1;
                $price['savePercent'] = ceil(100 - $price['price']);
                $price['saveAmount'] = $product->getPrice() - $price['website_price'];
                $price['saveAmountCurrency'] = Mage::helper('core')->currency($price['saveAmount'], false, false);
                $price['savePoints'] = Mage::helper('rewardpoints/data')->convertProductMoneyToPoints($price['saveAmount']);
                
                
                //Mage::helper('rewardpoints/data')->getProductPoints($_product, true)
                $tierprice_incl_tax = Mage::helper('tax')->getPrice($product, $price['website_price'], true);
                $tierprice_excl_tax = Mage::helper('tax')->getPrice($product, $price['website_price']);
                //getProductPoints($product, $noCeil = false, $from_list = false, $money_points = false, $tierprice_incl_tax = null, $tierprice_excl_tax = null)
                $price['productTierPoints'] = Mage::helper('rewardpoints/data')->getProductPoints($product, true, false, false, $tierprice_incl_tax, $tierprice_excl_tax);
                
                /*if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', Mage::app()->getStore()->getId())){
                    $price['productTierPoints'] = Mage::helper('rewardpoints/data')->convertProductMoneyToPoints(Mage::helper('tax')->getPrice($product, $price['website_price']));
                } else {
                    $price['productTierPoints'] = Mage::helper('rewardpoints/data')->convertProductMoneyToPoints(Mage::helper('tax')->getPrice($product, $price['website_price'], true));
                }*/
                //$price['formated_price'] = Mage::app()->getStore()->formatPrice(Mage::app()->getStore()->convertPrice(Mage::helper('tax')->getPrice($product, $price['website_price'])));
                //$price['formated_price_incl_tax'] = Mage::app()->getStore()->formatPrice(Mage::app()->getStore()->convertPrice(Mage::helper('tax')->getPrice($product, $price['website_price'], true)));
                $res[] = $price;
            }
        }
        
        if ($return_array){
            return $res;
        } else if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            return Mage::helper('core')->jsonEncode($res);
        } else {
            return Zend_Json::encode($res);
        }
    }
    

    public function getOptions($product)
    {
        $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
        $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
        $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
            $product->getTypeInstance(true)->getOptionsIds($product),
            $product
        );
        return $optionCollection->appendSelections($selectionCollection, false, false);
    }


    public function formatOptionPrice($price, $product)
    {
        $priceTax = Mage::helper('tax')->getPrice($product, $price);
        $priceIncTax = Mage::helper('tax')->getPrice($product, $price, true);

        return $priceIncTax;
    }

    public function getJsGrouped($product){
        $config = array();
        
        
        $_associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
        $_hasAssociatedProducts = count($_associatedProducts) > 0;
        if ($_hasAssociatedProducts){
            foreach ($_associatedProducts as $_item){
                $priceValue = Mage::helper('rewardpoints/data')->convertProductMoneyToPoints($_item->getFinalPrice());
                $config[$_item->getId()] = $priceValue;
            }
        }
        
        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            return Mage::helper('core')->jsonEncode($config);
        } else {
            return Zend_Json::encode($config);
        }
    }
    
    public function getJsDownloadable($product)
    {
        $config = array();
        //$coreHelper = Mage::helper('core');
        
        $links = $product->getTypeInstance(true)->getLinks($product);

        foreach ($links as $link) {
            //$config[$link->getId()] = $coreHelper->currency($link->getPrice(), false, false);
            $priceValue = Mage::helper('rewardpoints/data')->convertProductMoneyToPoints(($link->getPrice()));
            $config[$link->getId()] = $priceValue;
        }
        
        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            return Mage::helper('core')->jsonEncode($config);
        } else {
            return Zend_Json::encode($config);
        }
    }
    

    public function getJsOptions($product)
    {
        $config = array();

        foreach ($product->getOptions() as $option) {
            $priceValue = 0;
            if ($option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                $_tmpPriceValues = array();
                foreach ($option->getValues() as $value) {
                    $tmp_price = Mage::helper('rewardpoints/data')->convertProductMoneyToPoints((Mage::helper('core')->currency($value->getPrice(true), false, false)));
                    $_tmpPriceValues[$value->getId()] = $tmp_price;
                }
                $priceValue = $_tmpPriceValues;
            } else {
                $priceValue = Mage::helper('core')->currency($option->getPrice(true), false, false);
                $priceValue = Mage::helper('rewardpoints/data')->convertProductMoneyToPoints(($priceValue));
            }
            $config[$option->getId()] = $priceValue;
        }

        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            return Mage::helper('core')->jsonEncode($config);
        } else {
            return Zend_Json::encode($config);
        }
    }



    public function getJsBundlePoints($product)
    {
        Mage::app()->getLocale()->getJsPriceFormat();
        $store = Mage::app()->getStore();
        $optionsArray = $this->getOptions($product);
        $selected = array();

        $pts_array = array();
		$isPriceFixedType = ($product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED);

        foreach ($optionsArray as $_option) {
            if (!$_option->getSelections()) {
                continue;
            }
            
            $selectionCount = count($_option->getSelections());
			$bundlePriceModel = Mage::getModel('bundle/product_price');
			
            foreach ($_option->getSelections() as $_selection) {
                $_qty = !($_selection->getSelectionQty()*1)?'1':$_selection->getSelectionQty()*1;
                $price_tmp = $product->getPriceModel()->getSelectionPreFinalPrice($product, $_selection, 1);
                $subprice = $this->formatOptionPrice($price_tmp, $product);

                /*if (!Mage::helper('rewardpoints/data')->isCustomProductPoints($_selection)){
                    $pts = Mage::helper('rewardpoints/data')->convertProductMoneyToPoints(($subprice));
                } else {
                    $pts = Mage::helper('rewardpoints/data')->getProductPoints($_selection);
                }
                //BUNDLE FIX PRICE FIX
                $extra_points = Mage::getModel('rewardpoints/catalogpointrules')->getCatalogRulePointsGathered($product, $pts, Mage::app()->getStore()->getId(), 1, null, true);
		*/
               	//BUNDLE FIX PRICE FIX
                //$item = $isPriceFixedType ? $product : $_selection;
                $item = $_selection;
                //$extra_points = Mage::getModel('rewardpoints/catalogpointrules')->getCatalogRulePointsGathered($product, $pts, Mage::app()->getStore()->getId(), 1, null, true);
                //getProductPoints($product, $noCeil = false, $from_list = false, $money_points = false, $tierprice_incl_tax = null, $tierprice_excl_tax = null)
                //$tierprice_incl_tax = Mage::helper('tax')->getPrice($product, $subprice, true);
                //$tierprice_excl_tax = Mage::helper('tax')->getPrice($product, $subprice);
				
				$itemPrice = $bundlePriceModel->getSelectionFinalTotalPrice($product, $item,
                    $product->getQty(), $item->getQty(), false, false
                );
				
				$taxHelper = Mage::helper('tax');
				$tierprice_incl_tax  = $taxHelper->getPrice($item, $itemPrice, true,
                    null, null, null, null, null, false);
				$tierprice_excl_tax = $taxHelper->getPrice($item, $itemPrice, false,
                    null, null, null, null, null, false);
				
				if ($product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                    $tierprice_incl_tax = $taxHelper->getPrice($product, $itemPrice, true,
                        null, null, null, null, null, false);
                    $tierprice_excl_tax = $taxHelper->getPrice($product, $itemPrice, false,
                        null, null, null, null, null, false);
                }
				
                //$current_point = ceil(Mage::helper('rewardpoints')->getProductPoints($item, false, true, false, (float)$tierprice_incl_tax, (float)$tierprice_excl_tax)); 
				$current_point = Mage::helper('rewardpoints')->getProductPoints($item, false, true, false, (float)$tierprice_incl_tax, (float)$tierprice_excl_tax); 
                
				$selection = array (
                    //'points' => $pts + $extra_points,
                    'points' => $current_point, 
					'subprice' => $subprice,
                    'optionId' => $_option->getId(),
					'priceInclTax' => $tierprice_incl_tax,
					'priceExclTax' => $tierprice_excl_tax,
                );
                $responseObject = new Varien_Object();
                $args = array('response_object'=>$responseObject, 'selection'=>$_selection);
                Mage::dispatchEvent('bundle_product_view_config', $args);
                if (is_array($responseObject->getAdditionalOptions())) {
                    foreach ($responseObject->getAdditionalOptions() as $o=>$v) {
                        $selection[$o] = $v;
                    }
                }

                $pts_array[$_selection->getSelectionId()] = $selection;

                if (($_selection->getIsDefault() || ($selectionCount == 1 && $_option->getRequired())) && $_selection->isSalable()) {
                    $selected[$_option->getId()][] = $_selection->getSelectionId();
                }
            }
            
        }

        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            return Mage::helper('core')->jsonEncode($pts_array);
        } else {
            return Zend_Json::encode($pts_array);
        }

    }



    public function getJsPoints($_product) {
        $attributes = array();
        $attribute_credit = array();

        if ($_product->isConfigurable()){
            $allProducts = $_product->getTypeInstance(true)
                            ->getUsedProducts(null, $_product);
            $allowAttributes = $_product->getTypeInstance(true)
                        ->getConfigurableAttributes($_product);
            
            $tierPrices = $this->getTierPricesJson($_product, true);
            foreach ($allProducts as $product) {
                $product = Mage::getModel('catalog/product')->load($product->getId());
		if ($product->isSaleable()) {
                    $attr_values = array();
                    foreach ($allowAttributes as $attribute) {
                        $productAttribute = $attribute->getProductAttribute();
                        $attributeId = $productAttribute->getId();
                        $attributeValue = $product->getData($productAttribute->getAttributeCode());
                        $attr_values[] = $attributeValue;
                    }
                    $return_val[implode("|",$attr_values)] = Mage::helper('rewardpoints/data')->getProductPoints($product, true, false);
                    //TIER PRICE MODIFICATION
                    if ($tierPrices != array()){
                        $tmpTierPrice = array();
                        foreach ($tierPrices as $tierPrice){
                            $_finalPriceInclTax = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
                            $_weeeTaxAmount = Mage::helper('weee')->getAmount($product);
                            $price_exc_tax = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), false) - $tierPrice['saveAmount'];
                            $price_inc_tax = $_finalPriceInclTax+$_weeeTaxAmount - $tierPrice['saveAmount'];
                            $tmpTierPrice[] = array ("price_qty" => $tierPrice['price_qty'], "productTierPoints" => Mage::helper('rewardpoints/data')->getProductPoints($product, true, false, false, $price_inc_tax, $price_exc_tax));
                            //[$tierPrice['price_qty']] = Mage::helper('rewardpoints/data')->getProductPoints($product, true, false, false, $price_inc_tax, $price_exc_tax);
                        }
                        $return_val[implode("|",$attr_values).'|tierPrice'] = $tmpTierPrice;
                    }
                }
            }
            
            if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
                return Mage::helper('core')->jsonEncode($return_val);
            } else {
                return Zend_Json::encode($return_val);
            }
            
            // end of modifications
            
            
            foreach ($allProducts as $product) {
                if ($product->isSaleable()) {
                    foreach ($allowAttributes as $attribute) {
                        $productAttribute = $attribute->getProductAttribute();
                        $attributeId = $productAttribute->getId();
                        $attributeValue = $product->getData($productAttribute->getAttributeCode());
                        
                        if (!isset($options[$productAttribute->getId()])) {
                            $options[$productAttribute->getId()] = array();
                        }

                        if (!isset($options[$productAttribute->getId()][$attributeValue])) {
                            $options[$productAttribute->getId()][$attributeValue] = array();
                        }
                        
                        
                        $attribute_credit[$attributeValue] = Mage::helper('rewardpoints/data')->getProductPoints($product, false, false);
                        
                        $prices = $attribute->getPrices();
                        if (is_array($prices)) {
                            $attr_list = array();
                            foreach ($prices as $value) {
                                if(!isset($options[$attributeId][$value['value_index']])) {
                                    continue;
                                }
                                
                                $price = $value['pricing_value'];
                                $isPercent = $value['is_percent'];
                                if ($isPercent && !empty($price)) {
                                    $price = $_product->getFinalPrice()*$price/100;
                                }
                                
                                if (!isset($attribute_credit[$attributeValue])){
                                    $attribute_credit[$attributeValue] = array();
                                }
                                $attr_list[] = $value['value_index'];
                                
                                //$tierprice_json = $this->getTierPricesJson($_product);
                                
                                //$attribute_credit[$attributeValue][$value['value_index']] = Mage::helper('rewardpoints/data')->getProductPoints($product, false, false);
                                
                                //$attribute_credit[$value['value_index']] = Mage::helper('rewardpoints/data')->convertProductMoneyToPoints(($price + $_product->getFinalPrice()));
                            }
                            //$attribute_credit[$attributeValue][implode('_',$attr_list)] = Mage::helper('rewardpoints/data')->getProductPoints($product, false, false);
                        }
                    }
                }
            }
        }
        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            return Mage::helper('core')->jsonEncode($attribute_credit);
        } else {
            return Zend_Json::encode($attribute_credit);
        }
    }
}
