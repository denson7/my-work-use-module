<?php

class Magestore_Shopbybrand_Helper_Dashboard extends Mage_Adminhtml_Helper_Dashboard_Data
{
	/**
     * Prepare array with periods for dashboard graphs
     *
     * @return array
     */
    public function getDatePeriods()
    {
        return array(
            'custom'=>$this->__('Custom'),
            '24h' => $this->__('Last 24 Hours'),
            '7d'  => $this->__('Last 7 Days'),
            '1m'  => $this->__('Current Month'),
            '1y'  => $this->__('YTD'),
            '2y'  => $this->__('2YTD')
        );
    }
}