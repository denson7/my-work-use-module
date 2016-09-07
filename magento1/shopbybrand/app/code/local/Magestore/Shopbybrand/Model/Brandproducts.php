<?php

class Magestore_Shopbybrand_Model_Brandproducts extends Mage_Core_Model_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('shopbybrand/brandproducts');
    }
    public function convertData() {
        $products = Mage::getModel('catalog/product')
                ->getCollection();
        foreach ($products as $pro) {
            $this->setProductId($pro->getId())
                    ->setPosition(0)
                    ->setId(null)
                    ->save();
        }
    }
//    public function updatePosition($productIds,$StrPositions){
//        $positionArray = array();
//        $position = array();
//        /**
//         * chuyen position tu string sang array
//         */
//        parse_str($StrPositions, $positionArray);
//        foreach($positionArray as $key => $value){
//            parse_str(base64_decode($value),$position);
//            $positionArray[$key] = $position['position'];
//        }
//        $collection = $this->getCollection()->addFieldToFilter('product_id', array('in' => $productIds));
//        foreach ($collection as $item){
//            $posi = (is_numeric($positionArray[$item->getProductId()]))?$positionArray[$item->getProductId()]:0;
//            $item->setPosition($posi)->save();
//            unset($positionArray[$item->getProductId()]);
//        }
//        foreach ($positionArray as $key => $value){
//            $value = is_numeric($value)?$value:0;
//            $this->setProductId($key)
//                    ->setPosition($value)
//                    ->setId(NULL)
//                    ->save();
//        }
//    }
    
    public function updateProductData($Data, $featuredProduct){
        $positionArray = array();
        parse_str($Data, $positionArray);
        $dataArray = array();
        foreach ($positionArray as $key => $value) {
            $product = array();
            parse_str(base64_decode($value),$product);
            $dataArray[$key] = $product;
        }
        foreach ($dataArray as $key => $value) {
            $dataArray[$key]['is_featured'] = 0;
        }
        foreach ($featuredProduct as $value) {
            $dataArray[$value]['is_featured']=1;
        }
        $productIds = array_unique(array_keys($dataArray));
        $collection = $this->getCollection()->addFieldToFilter('product_id', array('in' => $productIds));
        foreach ($collection as $item){
            if(!isset($dataArray[$item->getProductId()]['position']))
                $dataArray[$item->getProductId()]['position'] = '';
            $posi = (is_numeric($dataArray[$item->getProductId()]['position'])&&$dataArray[$item->getProductId()]['position']>=0)?$dataArray[$item->getProductId()]['position']:0;
            $item   ->setPosition($posi)
                    ->setIsFeatured($dataArray[$item->getProductId()]['is_featured'])
                    ->save();
            unset($dataArray[$item->getProductId()]);
        }
        foreach ($dataArray as $key => $value){
            $position = (is_numeric($value['position'])&&$value['position']>=0)?$value['position']:0;
            $this   ->setProductId($key)
                    ->setPosition($position)
                    ->setIsFeatured($value['is_featured'])
                    ->setId(NULL)
                    ->save();
        }
    }

    public function setZeroPositionByProductId($productId){
        $collection = $this->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->getFirstItem()
                ->setPosition(0)
                ->save();
    }

    /**
     * @param $brandId
     * @return array
     * lay ra mot mang cac positions
     */
    public function getPositionsArray(){
        $brandProducts = $this->getCollection();
        $ArrayPositions = array();
        foreach($brandProducts->getItems() as $product ){
            $ArrayPositions[$product->getProductId()] = $product->getPosition();
        }
        return $ArrayPositions;
    }
    
    public function getFeaturedArray(){
        $brandProducts = $this->getCollection();
        $ArrayFeatured = array();
        foreach($brandProducts->getItems() as $product ){
            $ArrayFeatured[$product->getProductId()] = $product->getIsFeatured();
        }
        return $ArrayFeatured;
    }
}