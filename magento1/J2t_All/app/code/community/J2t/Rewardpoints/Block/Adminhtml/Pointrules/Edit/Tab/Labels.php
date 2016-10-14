<?php
/**
 * Rewardpoints
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
 * @package    Rewardpoints
 * @copyright  Copyright (c) 2014 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_Block_Adminhtml_Pointrules_Edit_Tab_Labels extends Mage_Adminhtml_Block_Widget
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('rewardpoints/labels.phtml');
    }
    
    public function getStores()
    {
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(false)
                ->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }
    
    public function getLabelValues($type = 'default')
    {
        $current_rule = Mage::registry('pointrules_data');
        $return_value = array();
        
        foreach ($this->getStores() as $_store){
            $return_value[$_store->getId()] = "";
        }
        
        if ($current_rule->getLabels() && $type == 'default'){
            $rule_labels = unserialize($current_rule->getLabels());
            foreach ($rule_labels as $key_l => $text_l) {
                $return_value[$key_l] = $text_l;
            }
        } else if ($current_rule->getLabelsSummary() && $type == 'labels_summary'){
            $rule_labels = unserialize($current_rule->getLabelsSummary());
            foreach ($rule_labels as $key_l => $text_l) {
                $return_value[$key_l] = $text_l;
            }
        }
        return $return_value;
    }
    
}
