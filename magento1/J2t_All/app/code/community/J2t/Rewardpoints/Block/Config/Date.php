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

class J2t_Rewardpoints_Block_Config_Date extends Mage_Core_Block_Html_Date 
{
    
    public function _old_toHtml()
    {
        $return_value = trim(preg_replace('/\s+/', ' ',parent::_toHtml()));
        
        $return_value = trim(preg_replace('~[[:cntrl:]]~', ' ', $return_value));
        $return_value = trim(str_replace('//<![CDATA[', ' ',$return_value));
        $return_value = trim(str_replace('//]]>', ' ',$return_value));
        $return_value = trim(str_replace('</script>', '<\/script>',$return_value));
        $return_value = trim(str_replace('text/javascript', 'text\/javascript',$return_value));
        $return_value = trim(str_replace('/%', '\/%',$return_value));
        return $return_value;
    }
    
    protected function _toHtml()
    {
        $displayFormat = Varien_Date::convertZendToStrFtime($this->getFormat(), true, (bool)$this->getTime());
        $html  = '<div style="white-space: nowrap;"><input type="text" name="' . $this->getName() . '" id="' . $this->getId() . '" ';
        //$html .= 'value="' . $this->escapeHtml($this->getValue()) . '" class="' . $this->getClass() . '" ' . $this->getExtraParams() . '/> ';
        $html .= 'value="#{' . $this->getTitle() . '}" class="' . $this->getClass() . '" ' . $this->getExtraParams() . '/> ';
        
        $html .= '<img src="' . $this->getImage() . '" alt="' . $this->helper('core')->__('Select Date') . '" class="v-middle j2t-buttons-date-admin" ';
        $html .= 'title="' . $this->helper('core')->__('Select Date') . '" />';
        //$html .= '<script type="text/javascript">refreshJ2tDateAdminButons(\\\''.$displayFormat.'\\\');<\/script></div>';
        if (version_compare(Mage::getVersion(), '1.9.0', '>=')){
	    $html .= '<script type="text/javascript">refreshJ2tDateAdminButons(\''.$displayFormat.'\');<\/script></div>';
	} else {
	    $html .= '<script type="text/javascript">refreshJ2tDateAdminButons(\\\''.$displayFormat.'\\\');<\/script></div>';
	}
	return $html;
    }
}
