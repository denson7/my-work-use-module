<?php

class Magestore_Shopbybrand_Block_Sidebar extends Mage_Core_Block_Template {
    
//    protected function _construct()
//    {
//        $this->addData(array(
//            'cache_lifetime' => 3600,
//            'cache_tags'        => array('brand_slider')
//        ));
//    }

    protected $_brandCollection = null;
    /* add by Peter */
    public function getBrandSort(){
        return Mage::getSingleton('shopbybrand/brand')->getBrandsData();
//        return Mage::getModel('shopbybrand/brand')->getCollection()
//                ->setStoreId(Mage::app()->getStore()->getId())
//                ->setOrder('position_brand','ASC')
//                ->setOrder('name','ASC')
//                ->addFieldToFilter('status',array('eq'=>1));
    }
    /* end add by Peter */
     public function getBrandsByBegin() {
        if ($input = $this->getInputSearch()) {

            $store = Mage::app()->getStore()->getId();
            $extended_search = Mage::getStoreConfig('shopbybrand/general/brand_extended_search', $store);
            $shopbybrands = Mage::getModel('shopbybrand/brand')->getCollection();
            $shopbybrands->setStoreId($this->getStoreId());
            $shopbybrands->addFieldToFilter('name', array('like' => '%' . $input . '%'));
            if ($extended_search) {
                $allIds1 = $shopbybrands->getAllIDs();
                $allIds2 = Mage::getModel('shopbybrand/brand')
                        ->getCollection()
                        ->setStoreId($this->getStoreId())
                        ->addFieldToFilter('description', array('like' => '%' . $input . '%'))
                        ->getAllIDs();
                $allIds = array_merge($allIds1, $allIds2);
                $allIds = array_unique($allIds);
                $shopbybrands = Mage::getModel('shopbybrand/brand')
                        ->getCollection()
                        ->setStoreId($this->getStoreId())
                        ->addFieldToFilter('brand_id', array('in' => $allIds));
            }
            return $shopbybrands;
        }
        if ($top = $this->getRequest()->getParam("top")) {
            if ($top == 'most_subscribers') {
                
            } elseif ($top == 'sales') {
                
            }
        }
        $begin = $this->getRequest()->getParam("begin");
        $shopbybrands = Mage::helper("shopbybrand")->getBrandsByBegin($begin);

        return $shopbybrands;
    }
    public function getMaximumSidebar(){
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/sidebar/maximum_item_sidebar', $store);
        return $display;
    }
//    public function getBrandUrl($brand) {
//        $url = $this->getUrl($brand->getUrlKey(), array());
//        return $url;
//    }
    public function getBrandUrl($url_key) {
        $url = $this->getUrl($url_key, array());
        return $url;
    }
    public function getDisplayModule(){
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/general/enable', $store);
        return $display;
    }
    /*add by Peter */
    public function getDisplaySidebar(){
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/sidebar/brand_sidebar', $store);
        return $display;
    }
    public function getOptionDisplay(){
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/sidebar/option_display', $store);
        return $display;
    }
    /* end add by Peter */
}
