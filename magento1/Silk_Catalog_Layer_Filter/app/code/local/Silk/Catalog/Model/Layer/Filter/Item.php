<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-10-20
 * Time: 下午1:49
 */
class Silk_Catalog_Model_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{
    /**
     * @return bool
     */
    public function isSelected()
    {
        $selected = Mage::getSingleton('core/app')->getRequest()->getParam($this->getFilter()->getRequestVar());

        if ($selected == $this->getValue()) {
            return true;
        }
        else {
            return false;
        }
    }
}