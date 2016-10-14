<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/
class Simtech_Searchanise_Model_System_Config_Source_Searchanise_Sync_Modes
{
    /**
     * Retrieve option values array
     *
     * @return array
    */
    public function toOptionArray()
    {
        return array(
            array('value' => 'realtime', 'label' => Mage::helper('searchanise')->__('When catalog updates')),
            array('value' => 'periodic', 'label' => Mage::helper('searchanise')->__('Periodically via cron')),
            array('value' => 'manual', 'label' => Mage::helper('searchanise')->__('Manually')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'realtime' => Mage::helper('searchanise')->__('When catalog updates'),
            'periodic' => Mage::helper('searchanise')->__('Periodically via cron'),
            'manual' => Mage::helper('searchanise')->__('Manually'),
        );
    }
}
