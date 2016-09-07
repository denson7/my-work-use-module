<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2016/7/25
 * Time: 10:52
 */
class Silk_Xreview_Block_Adminhtml_Review_Image_Upload extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('review/image/upload.phtml');
    }

    protected function _prepareLayout() {
        $this->getLayout()->getBlock('head')->addJs('lib/flex.js');
        $this->getLayout()->getBlock('head')->addJs('mage/adminhtml/flexuploader.js');
        $this->getLayout()->getBlock('head')->addJs('lib/FABridge.js');

        // 添加Mage_Adminhtml_Block_Media_Uploader子块Block
        $this->setChild('uploader', $this->getLayout()->createBlock('adminhtml/media_uploader'));
        $this->getChild('uploader')->getConfig()
            // 文件上传处理action
            ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('xreview/adminhtml_xreview/upload'))
            ->setFileField('review_image')
            ->setFilters(array(
                'images' => array(
                    'label' => Mage::helper('adminhtml')->__('Images (.gif, .jpg, .png)'),
                    'files' => array('*.gif', '*.jpg','*.jpeg', '*.png')
                )
            ));
        return parent::_prepareLayout();
    }
}