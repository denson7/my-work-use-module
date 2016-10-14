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
class J2t_Rewardpoints_Adminhtml_Rewardpointsadmin_StatsController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
    {
        return true;
    }

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('rewardpoints/stats')
                ->_addBreadcrumb(Mage::helper('rewardpoints')->__('Statistics'), Mage::helper('rewardpoints')->__('Statistics'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('rewardpoints/adminhtml_stats'))
            ->renderLayout();
    }


    public function editAction() {
            $id     = $this->getRequest()->getParam('id');
            $model  = Mage::getModel('rewardpoints/stats')->load($id);
            if ($model->getId() || $id == 0) {
                    $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

                    Mage::register('stats_data', $model);

                    $this->loadLayout();
                    $this->_setActiveMenu('rewardpoints/stats');

                    $this->_addBreadcrumb(Mage::helper('rewardpoints')->__('Manage Points'), Mage::helper('adminhtml')->__('Manage Points'));
                    $this->_addBreadcrumb(Mage::helper('rewardpoints')->__('Point Configuration'), Mage::helper('adminhtml')->__('Point Configuration'));

                    $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

                    $this->_addContent($this->getLayout()->createBlock('rewardpoints/adminhtml_stats_edit'));

                    $this->renderLayout();
            } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('No points'));
                    $this->_redirect('*/*/');
            }
    }

    public function newAction() {
            $this->_forward('edit');
    }
    
    public function allcustomerpointsAction(){
        $collection = Mage::getResourceModel('customer/customer_collection');
        $collection->load();
        if ($collection->getSize()){
            foreach($collection as $customer){
                $allStores = Mage::app()->getStores();
                foreach ($allStores as $_eachStoreId => $val) {
                    //Mage::helper('rewardpoints')->processRecordFlatAction($customer->getId(), Mage::app()->getStore($_eachStoreId)->getId());
                    $model = Mage::getModel('rewardpoints/flatstats');
                    $model->processRecordFlat($customer->getId(), Mage::app()->getStore($_eachStoreId)->getId());
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('rewardpoints')->__(
                    'Total of %d customers were successfully processed.', $collection->getSize()
                )
            );
        }
        $this->_redirect('*/*/index');
    }
    
    public function recalculatePointsAction(){
        $customerIds = $this->getRequest()->getParam('user_ids');
        $customerIds = array_unique($customerIds);
        if(!is_array($customerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer'));
        } else {
            try {
                foreach ($customerIds as $customerId) {
                    if ( $flat_stat = Mage::getModel('rewardpoints/flatstats')->load($customerId) ){
                        $allStores = Mage::app()->getStores();
                        foreach ($allStores as $_eachStoreId => $val) {
                            //Mage::helper('rewardpoints')->processRecordFlatAction($flat_stat->getUserId(), Mage::app()->getStore($_eachStoreId)->getId());
                            $model = Mage::getModel('rewardpoints/flatstats');
                            $model->processRecordFlat($flat_stat->getUserId(), Mage::app()->getStore($_eachStoreId)->getId());
                        }
                    }
                    
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpoints')->__(
                        'Total of %d lines were successfully processed.', count($customerIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function checkpointsAction() {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('rewardpoints/stats')->load($id);

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
                $model->setData($data);
        }

        Mage::register('stats_data', $model);

        $this->loadLayout();
        $this->_setActiveMenu('rewardpoints/stats');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('rewardpoints/adminhtml_stats_checkpoints'));
        $this->renderLayout();
    }

    public function savecheckAction(){
        if ($data = $this->getRequest()->getPost()) {
            //$from = Mage::app()->getLocale()->date($this->getRequest()->getParam('from'), Zend_Date::DATE_SHORT, null, false);
            //$to = Mage::app()->getLocale()->date($this->getRequest()->getParam('ends'), Zend_Date::DATE_SHORT, null, false);

            $date = Mage::app()->getLocale()->date($data['from'], Zend_Date::DATE_SHORT, null, false);
            $time = $date->getTimestamp();
            $from = Mage::getModel('core/date')->gmtDate(null, $time);


            $date = Mage::app()->getLocale()->date($data['ends'], Zend_Date::DATE_SHORT, null, false);
            $time = $date->getTimestamp();
            $to = Mage::getModel('core/date')->gmtDate(null, $time);


            $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', Mage::app()->getStore()->getId());
            $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses', Mage::app()->getStore()->getId());


            //$order_states = array("processing","complete");
            $orders = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')

                ->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to))
                ->joinAttribute('status', 'order/status', 'entity_id', null, 'left');

            $orders_array =array();
            $orders_array[] = '0';
            //$attribute_restriction = Mage::getStoreConfig('rewardpoints/default/process_restriction', Mage::app()->getStore()->getId());
            foreach ($orders as $order){

                $order = Mage::getModel('sales/order')->load($order->getId());

                $reward_model = Mage::getModel('rewardpoints/stats');
                $test_points = $reward_model->checkProcessedOrder($order->getCustomerId(), $order->getIncrementId(), true);
                if (!$test_points->getId()){
                    $orders_array[] = "'".$order->getIncrementId()."'";
                    $rewardPoints = Mage::helper('rewardpoints/data')->getPointsOnOrder($order, null, null, true, $order->getStoreId());

                    if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $order->getStoreId())){
                        if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $order->getStoreId()) < $rewardPoints){
                            $rewardPoints = Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $order->getStoreId());
                        }
                    }
                    $customerId = $order->getCustomerId();

                    //record points for item into db
                    if ($rewardPoints > 0){
                        Mage::getModel('rewardpoints/observer')->recordPoints($rewardPoints, $customerId, $order->getIncrementId());
                    }
                    
                    Mage::getModel('rewardpoints/observer')->sales_order_success_referral($order);
                    
                    if ($customerId && $order->getStoreId()){
                        $model = Mage::getModel('rewardpoints/flatstats');
                        $model->processRecordFlat($customerId, $order->getStoreId());
                    }
                    
                }
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpoints')->__('Full check was proceed on %s orders', (sizeof($orders_array)-1) ));
            Mage::getSingleton('adminhtml/session')->setFormData(false);


            $this->_redirect('*/*/');
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('Unable to proccess the full order checking'));
        $this->_redirect('*/*/');
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            if (isset($data['date_start'])){
                $date = Mage::app()->getLocale()->date($data['date_start'], Zend_Date::DATE_SHORT, null, false);
                $time = $date->getTimestamp();
                $data['date_start'] = Mage::getModel('core/date')->gmtDate(null, $time);
            }
            if (isset($data['date_end'])){
                if ($data['date_end'] != ""){
                    $date = Mage::app()->getLocale()->date($data['date_end'], Zend_Date::DATE_SHORT, null, false);
                    $time = $date->getTimestamp();
                    $data['date_end'] = Mage::getModel('core/date')->gmtDate(null, $time);
                }
                else {
                    unset($data['date_end']);
                }
            }

            /*if (!empty($data)) {
                $model->setData($data);
            }*/

            if (!empty($data)) {
                $data['store_id'] = implode(',',$data['store_id']);

                $model = Mage::getModel('rewardpoints/stats');
                $model->setData($data)
                        ->setId($this->getRequest()->getParam('id'));
            }

            try {
                $model->save();
                
                $points = 0;
                if (trim($data['points_current'])){
                    $points = $data['points_current'];
                }
                if (trim($data['points_spent'])){
                    $points = - $data['points_spent'];
                }
                
                $customer_id = $data['customer_id'];
                $customer = Mage::getModel('customer/customer')->load($customer_id);
                $description = $data['rewardpoints_description'];
                if ($description == ""){
                    $description = Mage::helper('rewardpoints')->__('Store input');
                }

                if (!empty($data['rewardpoints_notification']) && $customer->getId()){
                    $model->sendAdminNotification($customer, $customer->getStoreId(), $points, $description);
                }
                
                
                $arr_store_id = explode(",",$data['store_id']);
                foreach ($arr_store_id as $curr_store_id){
                    if (($customerId = $data['customer_id']) && $curr_store_id){
                        $model = Mage::getModel('rewardpoints/flatstats');
                        $model->processRecordFlat($customerId, $curr_store_id);
                    }
                }
                
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpoints')->__('Points were successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                        return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('Unable to find points to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
            if( $this->getRequest()->getParam('id') > 0 ) {
                    try {
                            $model = Mage::getModel('rewardpoints/stats');

                            $model->setId($this->getRequest()->getParam('id'))
                                    ->delete();

                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpoints')->__('Points were successfully deleted'));
                            $this->_redirect('*/*/');
                    } catch (Exception $e) {
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    }
            }
            $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $ruleIds = $this->getRequest()->getParam('stats');
        if(!is_array($ruleIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select points'));
        } else {
            try {
                foreach ($ruleIds as $ruleId) {
                    $rule = Mage::getModel('rewardpoints/points')->load($ruleId);
                    $rule->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpoints')->__(
                        'Total of %d points were successfully deleted', count($ruleIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'rewardpoints.csv';
        $content    = $this->getLayout()->createBlock('rewardpoints/adminhtml_stats_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'rewardpoints.xml';
        $content    = $this->getLayout()->createBlock('rewardpoints/adminhtml_stats_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }


}
