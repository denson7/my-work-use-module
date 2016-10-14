<?php

class J2t_Rewardpoints_Adminhtml_Rewardpointsadmin_DashboardController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {   
        $this->_title($this->__('Rewardpoints Dashboard'));

        $this->loadLayout();
        //$this->_setActiveMenu('dashboard');
        //$this->_addBreadcrumb(Mage::helper('rewardpoints')->__('Rewardpoints Dashboard'), Mage::helper('rewardpoints')->__('Rewardpoints Dashboard'));
        $this->renderLayout();
    }

    
    public function ajaxBlockAction()
    {
        $output   = '';
        $blockTab = $this->getRequest()->getParam('block');
        if (in_array($blockTab, array('tab_gather', 'tab_spend', 'totals'))) {
            $output = $this->getLayout()->createBlock('rewardpoints/adminhtml_dashboard_' . $blockTab)->toHtml();
        }
        $this->getResponse()->setBody($output);
        return;
    }

    public function tunnelAction()
    {
        $httpClient = new Varien_Http_Client();
        $gaData = $this->getRequest()->getParam('ga');
        $gaHash = $this->getRequest()->getParam('h');
        if ($gaData && $gaHash) {
            $newHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
            if ($newHash == $gaHash) {
                if ($params = unserialize(base64_decode(urldecode($gaData)))) {
                    $response = $httpClient->setUri(Mage_Adminhtml_Block_Dashboard_Graph::API_URL)
                            ->setParameterGet($params)
                            ->setConfig(array('timeout' => 5))
                            ->request('GET');

                    $headers = $response->getHeaders();

                    $this->getResponse()
                        ->setHeader('Content-type', $headers['Content-type'])
                        ->setBody($response->getBody());
                }
            }
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('dashboard');
    }
}
