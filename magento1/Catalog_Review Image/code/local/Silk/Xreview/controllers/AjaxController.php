<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2016/7/25
 * Time: 16:20
 */
class Silk_Xreview_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $imgId = $this->getRequest()->getParam('imgId');
        $html = null;
        if($imgId){
            $block = $this->getLayout()->getBlock('review.ajax.image');
            $block->setImageId($imgId);
        }
        $this->renderLayout();
    }
}