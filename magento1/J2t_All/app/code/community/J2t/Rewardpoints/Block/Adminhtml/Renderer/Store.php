<?php
/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class J2t_Rewardpoints_Block_Adminhtml_Renderer_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $stores = explode(',', $row->getData($this->getColumn()->getIndex()));
        $store_name = array();
        if ($stores != array()){
            foreach ($stores as $store_id){
                //getStoreName
                $store_name[] = Mage::getSingleton('adminhtml/system_store')->getStoreName($store_id);
            }
        }

        return implode(', ', $store_name);
        //$data = MyHelperClass::calculateSpecialDate($row->getData($this->getColumn()->getIndex()));
        //return $data;
    }
}

