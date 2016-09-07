<?php
/**
 * Video Plugin for Magento
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Niveus
 * @package    Niveus_ProductVideo
 * @copyright  Copyright (c) 2013 Niveus Solutions (http://www.niveussolutions.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Niveus Solutions <support@niveussolutions.com>
 */

class Niveus_ProductVideo_Model_Observer
{	
	public function catalog_product_save_after($observer)
        {
            $product = $observer->getProduct();

            $videos = Mage::getModel('productvideo/videos')
            ->getCollection()
 	    ->addFieldToFilter('product_id', $product->getId())
            ->addFieldToFilter('store_id', $product->getStoreId());

            foreach ($videos as $video) 
            {
                 $video->delete();
            }


             // Video 1
 
             $video1 =  $this->_getRequest()->getPost('video1');
             $model = Mage::getModel('productvideo/videos');

             try
             {

                if(!is_null($video1) && isset($video1) && $video1!='')
                 {
                  $model->setProductId($product->getId());
        	  $model->setStoreId($product->getStoreId());
                  $model->setVideoCode($video1);
        	  $model->save();
                 }
             }
             catch (Exception $e)
             {
        	  Mage::logException($e);
             }

        		

             // Video 2

             $video2 =  $this->_getRequest()->getPost('video2');
             $model = Mage::getModel('productvideo/videos');
             try
             {

                 if(!is_null($video2) && isset($video2) && $video2!='') 
                 {
                  $model->setProductId($product->getId());
        	  $model->setStoreId($product->getStoreId());
                  $model->setVideoCode($video2);
        	  $model->save();
                }
             }
             catch (Exception $e)
             {
        	  Mage::logException($e);
             }
              
             // Video 3

             $video3 =  $this->_getRequest()->getPost('video3');
             $model = Mage::getModel('productvideo/videos');
             try
             {
                if(!is_null($video3) && isset($video3) && $video3!='') 
                 {
                  $model->setProductId($product->getId());
        	  $model->setStoreId($product->getStoreId());
                  $model->setVideoCode($video3);
        	  $model->save();
                 }
             }
             catch (Exception $e)
             {
        	  Mage::logException($e);
             }
        
       }

       protected function _getRequest()
       {
          return Mage::app()->getRequest();
       }
     
}
