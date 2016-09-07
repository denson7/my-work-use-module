<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2016/7/22
 * Time: 14:20
 */
require_once Mage::getModuleDir('controllers', 'Mage_Review').DS.'ProductController.php';
class Silk_Xreview_ProductController extends Mage_Review_ProductController
{
    protected $images = null;
    /**
     * Submit new review action
     *
     */
    public function postAction()
    {
        if (!$this->_validateFormKey()) {
            // returns to the product item page
            $this->_redirectReferer();
            return;
        }
        /**
         * 如果评论中上传了图片
         * 评论图片上传(多图片上传)
         */
        //Mage::log($_FILES['images'],null,'file.log');

        if(count($_FILES['images']['name']) && $_FILES['images']){
            for ($i = 1; $i <= count($_FILES['images']['name']); $i++) {
                if($_FILES['images']['name'][$i-1]){
                    $_FILES['images'.$i] = array(
                        'name'      =>$_FILES['images']['name'][$i-1],
                        'type'      =>$_FILES['images']['type'][$i-1],
                        'tmp_name'  =>$_FILES['images']['tmp_name'][$i-1],
                        'error'     =>$_FILES['images']['error'][$i-1],
                        'size'      =>$_FILES['images']['size'][$i-1]
                    );
                    try {
                        $uploader = new Varien_File_Uploader('images'.$i);
                        $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir('media') . DS . 'review' . DS;
                        if (!file_exists($path)) {
                            mkdir($path, 777, true);
                        }
                        $type = substr($_FILES['images']['type'][$i-1],strpos($_FILES['images']['type'][$i-1],'/')+1);
                        $name = rand().'.'.$type;
                        $uploader->save($path, $name);
                        $this->images[] = $name;
                    }catch(Exception $e) {
                        Mage::getSingleton('core/session')->addError($this->__($e->getMessage()));
                        $this->_redirectReferer();
                        return;
                    }
                }else{
                    continue;
                }
            }
        }
        //Mage::log($this->images,null,'image.log');

        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }

        if (($product = $this->_initProduct()) && !empty($data)) {
            $session    = Mage::getSingleton('core/session');
            /* @var $session Mage_Core_Model_Session */
            $review     = Mage::getModel('review/review')->setData($data);

            /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setStores(array(Mage::app()->getStore()->getId()))
                        ->save();

                    /**如果上传了图片，则给对应的review保存图片*/
                    if($this->images){
                        foreach($this->images as $image){
                            Mage::getModel('xreview/image')
                                ->setReviewId($review->getId())
                                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                                ->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
                                ->setStoreId(Mage::app()->getStore()->getId())
                                ->setImage($image)
                                ->save();
                        }
                    }

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                            ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $session->addSuccess($this->__('Your review has been accepted for moderation.'));
                }
                catch (Exception $e) {
                    $session->setFormData($data);
                    $session->addError($this->__('Unable to post the review.'));
                }
            }
            else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                }
                else {
                    $session->addError($this->__('Unable to post the review.'));
                }
            }
        }

        if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
            $this->_redirectUrl($redirectUrl);
            return;
        }
        $this->_redirectReferer();
    }
}