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
class J2t_Rewardpoints_Model_Config_Serialized_Array extends Mage_Adminhtml_Model_System_Config_Backend_Serialized
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
            /*echo "$key = $value
                    ";*/
            if (isset($val['min_value']) && isset($val['max_value']) && isset($val['duration'])){
                $arr[$key] = $val;
            }
        }
        
        if (!$this->_checkOverlap($arr)){
            Mage::getSingleton('adminhtml/session')->addError('Overlap issues. Please verify Customer Point Notifications values.');
        }
        
        //$this->setValue($value);
        $this->setValue($arr);
        
        parent::_beforeSave();
    }
    
    protected function _checkOverlap($array){
        foreach ($array as $key => $value){
            //array_walk($value, $this->_checkOverlapInArray());
            foreach ($array as $key_2 => $value_2){
                if ($key != $key_2){
                    if (($value['min_value'] >= $value_2['min_value']) && ($value['min_value'] <= $value_2['max_value'])) {
                        return false;
                    } else if (($value['max_value'] >= $value_2['min_value']) && ($value['max_value'] <= $value_2['max_value'])){
                        return false;
                    }
                }
                if ($value['min_value'] >= $value['max_value']){
                    return false;
                }
                if ($value['min_value'] <= 0 || $value['max_value'] <= 0){
                    return false;
                }
            }
        }
        return true;
    }
}
