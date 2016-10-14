<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-2
 * Time: 下午2:58
 */
class Company_Web_Block_Adminhtml_Web_Grid_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $mediaurl=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $value = $row->getData($this->getColumn()->getIndex());
        return '<img src="'.$mediaurl.$value.'"  style="width:100%;"/>';
    }
}