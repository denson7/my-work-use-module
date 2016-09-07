<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Brand Value Model
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Model_Brandvalue extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('shopbybrand/brandvalue');
    }
    
    public function loadAttributeValue($brandId, $storeId, $attributeCode){
        $attributeValue = $this->getCollection()
    		->addFieldToFilter('brand_id', $brandId)
    		->addFieldToFilter('store_id', $storeId)
    		->addFieldToFilter('attribute_code',$attributeCode)
    		->getFirstItem();
		$this->setData('brand_id', $brandId)
			->setData('store_id',$storeId)
			->setData('attribute_code',$attributeCode);
    	if ($attributeValue)
    		$this->addData($attributeValue->getData())
    			->setId($attributeValue->getId());
		return $this;
    }
}