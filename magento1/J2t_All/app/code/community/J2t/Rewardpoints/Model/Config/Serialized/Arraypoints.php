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
class J2t_Rewardpoints_Model_Config_Serialized_Arraypoints extends Mage_Adminhtml_Model_System_Config_Backend_Serialized
{
    /**
     * Unset array element with '__empty' key
     *
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        
        if (is_array($value)) {
            unset($value['__empty']);
        }
        
        $arr = array();
        foreach($value as $key => $val){
            if (isset($val['min_cart_value']) && isset($val['max_cart_value']) && isset($val['point_value']) && isset($val['group_id'])
                    && isset($val['date_from']) && isset($val['date_end'])){
                
                if (is_float($val['point_value']) && (int)$val['point_value'] != 0){
                    Mage::getSingleton('adminhtml/session')->addError('Custom point values issue. Point value must be an integer.');
                }
                
                $fromDate = $toDate = null;
                $data = $this->_filterDates($val, array('date_from', 'date_end'));
                
                $fromDate = $data['date_from'];
                $toDate = $data['date_end'];
                if ($fromDate && $toDate) {
                    $fromDate = new Zend_Date($fromDate, Varien_Date::DATE_INTERNAL_FORMAT);
                    $toDate = new Zend_Date($toDate, Varien_Date::DATE_INTERNAL_FORMAT);

                    if ($fromDate->compare($toDate) === 1) {
                        Mage::getSingleton('adminhtml/session')->addError('End Date must be greater than Start Date.');
                    }
                }
                
                $arr[$key] = $data;
            }
        }
        
        $this->setValue($arr);
        //$this->setValue($value);
        parent::_beforeSave();
    }
    
    protected function _filterDates($array, $dateFields)
    {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }
}
