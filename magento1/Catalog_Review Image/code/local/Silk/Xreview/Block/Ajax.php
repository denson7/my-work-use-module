<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2016/7/25
 * Time: 16:23
 */
class Silk_Xreview_Block_Ajax extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('xreview/ajax.phtml');
    }

    /**
     * 得到页面传递的图片Id
     * @return mixed
     */
    public function getImgId()
    {
        return $this->getImageId();
    }

    /**
     * 获取评论id
     * @return mixed
     */
    public function  getViewId()
    {
        $images = Mage::getModel('xreview/image')->load($this->getImgId());
        return $images->getReviewId();
    }

    /**
     * 评论下的所有图片
     * @return mixed
     */
    public function getImages()
    {
        $collection = Mage::getModel('xreview/image')->getCollection()
            ->addFieldToFilter('store_id',Mage::app()->getStore()->getId())
            ->addFieldToFilter('review_id',$this->getViewId())
            ->addFieldtoFilter('status_id',Mage_Review_Model_Review::STATUS_APPROVED);
        return $collection;
    }

    /**
     * 通过img的id获取对应的评论model
     * @return Mage_Review_Model_Review
     */
    public function getReview()
    {
        $reviewId = $this->getViewId();
        $review  = Mage::getModel('review/review')->load($reviewId);
        return $review;
    }

    /**
     * 得到图片的url
     * @return string
     */
    public function getImageUrlById()
    {
        $images = Mage::getModel('xreview/image')->load($this->getImgId());
        return Mage::getBaseUrl('media').'review/'.$images->getImage();
    }
}