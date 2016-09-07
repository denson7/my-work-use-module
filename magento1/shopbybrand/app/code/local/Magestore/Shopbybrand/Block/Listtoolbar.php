<?php
class Magestore_Shopbybrand_Block_Listtoolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
   public function __construct(){
       parent::_construct();
       $this->_availableOrder = array(
           'brand_position'  => $this->__('Recommended'),
           'name'      => $this->__('Name'),
           'price'     => $this->__('Price'),
       );
   }
    public function getAvailableLimit(){
        $store = Mage::app()->getStore()->getId();
        $number = Mage::getStoreConfig('shopbybrand/brand_detail/brand_products_per_row', $store);
        return array($number*2=>$number*2,
                    $number*4=>$number*4,
                    $number*8=>$number*8,
                    'all'=>$this->__('All'));
    }
}