<?php
class Company_Web_Block_Web extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getWeb()     
     { 
        if (!$this->hasData('web')) {
            $this->setData('web', Mage::registry('web'));
        }
        return $this->getData('web');
        
    }
}