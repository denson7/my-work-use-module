<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2016/7/25
 * Time: 9:52
 */
class Silk_Xreview_Block_Adminhtml_Review_Image extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('review/images.phtml');
    }

    public function getImages()
    {
        if(Mage::registry('review_image')){
            return Mage::registry('review_image');
        }else{
            return null;
        }
    }
}