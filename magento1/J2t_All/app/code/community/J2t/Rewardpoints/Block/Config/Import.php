<?php

/**
 * Magento
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

class J2t_Rewardpoints_Block_Config_Import extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('catalog/product'); 
        
        $img = '';
        switch ($element->getHtmlId()){
            case 'rewardpoints_design_small_inline_image':
                $img = '<img src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). '/j2t_image_small.png' .'" alt="" width="16" height="16" /> ';
                break;
            case 'rewardpoints_design_big_inline_image':
                $img = '<img src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). '/j2t_image_big.png' .'" alt="" width="16" height="16" /> ';
                break;
            default:
                $img = '';
        }
        
        
        $html = $img.'<input id="'.$element->getHtmlId().'" name="'.$element->getName()
             .'" value="'.$element->getEscapedValue().'" type="file" />'."\n";
        $html.= $this->getAfterElementHtml();
        return $html;
    }
    
}
