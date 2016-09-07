<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2016/7/26
 * Time: 13:10
 */
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Catalog/Product/ReviewController.php';
class Silk_Xreview_Adminhtml_Catalog_Product_ReviewController extends  Mage_Adminhtml_Catalog_Product_ReviewController
{
    CONST REVIEW_STORE = 1;
    public function postAction()
    {
        $productId  = $this->getRequest()->getParam('product_id', false);
        $session    = Mage::getSingleton('adminhtml/session');

        if ($data = $this->getRequest()->getPost()) {
            if (Mage::app()->isSingleStoreMode()) {
                $data['stores'] = array(Mage::app()->getStore(true)->getId());
            } else  if (isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }

            $review = Mage::getModel('review/review')->setData($data);

            $product = Mage::getModel('catalog/product')
                ->load($productId);

            try {
                $review->setEntityId(1) // product
                ->setEntityPkValue($productId)
                    ->setStoreId($product->getStoreId())
                    ->setStatusId($data['status_id'])
                    ->setStoreId(self::REVIEW_STORE)
                    ->setCustomerId(null)//null is for administrator only
                    ->save();
                /**
                 * 如果添加了图片，保存图片
                 */
                $images = $this->getRequest()->getParam('images');
                if($images){
                    foreach($images as $image){
                        Mage::getModel('xreview/image')->setReviewId($review->getId())
                            ->setCustomerId(null)
                            ->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
                            ->setStoreId(self::REVIEW_STORE)
                            ->setImage(substr($image,1))
                            ->save();
                    }
                }
                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId=>$optionId) {
                    Mage::getModel('rating/rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->addOptionVote($optionId, $productId);
                }

                $review->aggregate();

                $session->addSuccess(Mage::helper('catalog')->__('The review has been saved.'));
                if( $this->getRequest()->getParam('ret') == 'pending' ) {
                    $this->getResponse()->setRedirect($this->getUrl('*/*/pending'));
                } else {
                    $this->getResponse()->setRedirect($this->getUrl('*/*/'));
                }

                return;
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while saving review.'));
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
        return;
    }
}