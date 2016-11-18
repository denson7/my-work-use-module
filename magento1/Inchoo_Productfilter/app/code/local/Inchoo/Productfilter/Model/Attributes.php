<?php

class Inchoo_Productfilter_Model_Attributes
{

    public function toOptionArray()
    {
    	$ignoreAttributes = array('sku', 'name', 'attribute_set_id', 'type_id', 'qty', 'price', 'status', 'visibility');

    	$collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter();

        $result = array();
    	foreach ($collection as $model) {
    		if(in_array($model->getAttributeCode(), $ignoreAttributes)) {
    			continue;
    		}
    		$productCollection = Mage::getModel('catalog/product')->getCollection();
    		$productCollection->addAttributeToSelect(array($model->getAttributeCode()));
    		$productCollection->addAttributeToFilter($model->getAttributeCode(), array('gt' => 0));

    		if(count($productCollection->getData()) > 0) {
    			$result[] = array('value' => $model->getAttributeCode(), 'label'=>$model->getFrontendLabel());
    		}

        }

       return $result;

    }
}
