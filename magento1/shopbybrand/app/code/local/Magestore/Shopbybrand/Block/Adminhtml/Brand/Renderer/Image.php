<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Shopbybrand Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Block_Adminhtml_Brand_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $brandThumbnailImage = $row->getThumbnailImage();
        if($brandThumbnailImage){
            $path = 'brands' . DS . 'thumbnail'.DS.$row->getId();
            $img = Mage::helper('shopbybrand/image')->init($row, $path)->resizeThumbnail(100, 75);
            return '<img src='.$img.' />';
        }
        return '';
    }
}