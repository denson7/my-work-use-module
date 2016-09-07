<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2016/7/22
 * Time: 17:41
 */
class Silk_Xreview_Block_Product_View_List extends Mage_Review_Block_Product_View_List
{
    public function getImages($viewId)
    {
        $collection = Mage::getModel('xreview/image')->getCollection()
                ->addFieldToFilter('store_id',Mage::app()->getStore()->getId())
                ->addFieldToFilter('review_id',$viewId)
                ->addFieldtoFilter('status_id',Mage_Review_Model_Review::STATUS_APPROVED);
        return $collection;
    }
}