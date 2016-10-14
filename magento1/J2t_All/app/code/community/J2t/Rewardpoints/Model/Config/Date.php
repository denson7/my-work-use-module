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
 * @copyright  Copyright (c) 2015 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_Model_Config_Date extends Mage_Core_Model_Config_Data
{
    
    const ALL_WITH_BC = 4095;

    protected function _beforeSave()
    {
        if ($date = $this->getValue()){
            $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $dateObject = new Zend_Date($date, $format);
            $this->setValue($dateObject->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        }
        return $this;
    }
}
