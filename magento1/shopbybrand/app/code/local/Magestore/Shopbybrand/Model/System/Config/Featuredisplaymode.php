<?php

class Magestore_Shopbybrand_Model_System_Config_Featuredisplaymode
{
    public function toOptionArray()
    {
        $options = array(
					array('value'=>1,'label'=> Mage::helper('shopbybrand')->__('Normal')),
					array('value'=>2,'label'=> Mage::helper('shopbybrand')->__('Slide')),
				);
		
		return $options;
    }
}
