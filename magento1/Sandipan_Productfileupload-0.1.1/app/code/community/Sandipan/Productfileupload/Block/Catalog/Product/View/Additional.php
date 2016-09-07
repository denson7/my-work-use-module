<?php  
class Sandipan_Productfileupload_Block_Catalog_Product_View_Additional extends Mage_Core_Block_Template {
    public function _getModel()
    {
        return Mage::getModel('productfileupload/productfileupload');
    }
    public function _getProductId()
    {
        return $this->getRequest()->getParam('id');
    }

    public function getAdditionalFiles()
    {
		return $this->_getModel()->getFilesByAttr($this->_getProductId(), 1);
    }
}