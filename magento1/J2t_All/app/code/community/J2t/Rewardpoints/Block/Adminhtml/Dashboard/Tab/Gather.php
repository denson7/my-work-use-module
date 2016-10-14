<?php

class J2t_Rewardpoints_Block_Adminhtml_Dashboard_Tab_Gather extends J2t_Rewardpoints_Block_Adminhtml_Dashboard_Graph
{
    /**
     * Initialize object
     *
     * @return void
     */
    public function __construct()
    {
        $this->setHtmlId('gather_points');
        parent::__construct();
    }

    /**
     * Prepare chart data
     *
     * @return void
     */
    protected function _prepareData()
    {
        $this->setDataHelperName('rewardpoints/dashboard_stats');
        $this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
        $this->getDataHelper()->setParam('website', $this->getRequest()->getParam('website'));
        $this->getDataHelper()->setParam('group', $this->getRequest()->getParam('group'));
	
	$this->getDataHelper()->setParam(
            'period',
            $this->getRequest()->getParam('period')?$this->getRequest()->getParam('period'):'24h'
            );        

        $this->setDataRows('points_current');
        $this->_axisMaps = array(
            'x' => 'range',
            'y' => 'points_current'
        );

        parent::_prepareData();
    }
}
