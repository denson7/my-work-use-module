<?php

class J2t_Rewardpoints_Model_Config_Email_Identity
{
    protected $_options = null;
    public function toOptionArray()
    {
        if (is_null($this->_options)) {
            $this->_options = array();
            $config = Mage::getSingleton('adminhtml/config')->getSection('trans_email')->groups->children();
            
            $this->_options[0] = array(
                    'value' => 'user-email-address',
                    'label' => Mage::helper('rewardpoints')->__("Referrer's Email Address")
                );
            
            foreach ($config as $node) {
                $nodeName   = $node->getName();
                $label      = (string) $node->label;
                $sortOrder  = (int) $node->sort_order;
                $sortOrder  = ($sortOrder) ? $sortOrder : ($sortOrder + 1);
                $this->_options[$sortOrder] = array(
                    'value' => preg_replace('#^ident_(.*)$#', '$1', $nodeName),
                    'label' => Mage::helper('adminhtml')->__($label)
                );
            }
            ksort($this->_options);
        }

        return $this->_options;
    }
}
