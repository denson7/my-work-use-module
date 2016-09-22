<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-9-22
 * Time: 下午2:18
 */
class Silk_CustomLinkedProducts_Block_Adminhtml_Catalog_Product_Edit_Tab
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function canShowTab()
    {
        return (($this->getRequest()->getActionName() === 'new') && (!$this->getRequest()->getParam('set')))
            ? false
            : true;
    }

    public function getTabLabel()
    {
        return $this->__('Custom Linked Products');
    }

    public function getTabTitle()
    {
        return $this->__('Custom Linked Products');
    }

    public function isHidden()
    {
        return false;
    }

    public function getTabUrl()
    {
        return $this->getUrl('*/*/custom', array('_current' => true));
    }

    public function getTabClass()
    {
        return 'ajax';
    }

}