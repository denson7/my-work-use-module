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
class J2t_Rewardpoints_Model_Config_Serialized_Arrayevents extends Mage_Adminhtml_Model_System_Config_Backend_Serialized
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
            if (isset($val['class_name']) && isset($val['model_id']) && isset($val['point_value']) && isset($val['use_end'])
                    && isset($val['process_once']) && isset($val['max_point']) && isset($val['duration']) && isset($val['description']) && isset($val['verifications'])){
                
                if (trim($val['class_name']) != ""){
                    $arr[$key] = array('class_name' => $val['class_name'], 'model_id' => (int)$val['model_id'], 'point_value' => (int)$val['point_value'],
                        'process_once' => (int)$val['process_once'], 'max_point' => (int)$val['max_point'], 'duration' => (int)$val['duration'], 
                        'use_end' => (int)$val['use_end'], 'description' => $val['description'], 'verifications' => $val['verifications']);
                } else {
                    Mage::getSingleton('adminhtml/session')->addError('Event data issues. Please verify Customer Point Event values.');
                }
                //$arr[] = $val;
            }
        }
        
        $this->setValue($arr);
        //$this->setValue($value);
        parent::_beforeSave();
    }
}
