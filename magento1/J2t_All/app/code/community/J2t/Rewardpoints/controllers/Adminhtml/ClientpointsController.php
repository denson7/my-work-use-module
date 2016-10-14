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
class J2t_Rewardpoints_Adminhtml_ClientpointsController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
    {
        return true;
    }
	
	protected function _initAction() {
            $this->loadLayout()
                    ->_setActiveMenu('rewardpoints/clientpoints')
                    ->_addBreadcrumb(Mage::helper('rewardpoints')->__('Client points'), Mage::helper('rewardpoints')->__('Client points'));

            return $this;
	}

	public function indexAction() {
            $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('rewardpoints/adminhtml_clientpoints'))
                ->renderLayout();
	}

        public function editAction() {
		$id     = $this->getRequest()->getParam('id');

                $model  = Mage::getModel('rewardpoints/stats')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('stats_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('rewardpoints/stats');



			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('rewardpoints/adminhtml_clientpoints_edit'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('No points'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction() {
		$this->_forward('edit');
	}

        

	public function saveAction() {
            if ($data = $this->getRequest()->getPost()) {
                $model = Mage::getModel('rewardpoints/stats');
                $model->setData($data)
                        ->setId($this->getRequest()->getParam('id'));

                try {
                    $model->save();
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

        
        
    protected function refreshFlat($customer_id, $store_id)
    {
        //NEW VERSION 1.6.21 - this has been deactivated because flatstats has been automated
        /*if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            Mage::getModel('rewardpoints/flatstats')->processRecordFlat($customer_id, $store_id);
        }*/
    }
    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('rewardpoints/stats');
                $model->load($this->getRequest()->getParam('id'));
                $store_ids = $model->getStoreId();
                $customer_id = $model->getCustomerId();

                $model->delete();

                if ($store_ids){
                    $store_arr = explode(',', $store_ids);
                    foreach($store_arr as $store_id){
                        $this->refreshFlat($customer_id, $store_id);
                    }
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpoints')->__('Points were successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function deleteAllFromGridAction() {
        //remove all points
        $points = Mage::getModel('rewardpoints/stats')->getCollection();
        if ($points->count()){
            foreach($points as $point){
                $point->delete();
            }
        }
        $flatpoints = Mage::getModel('rewardpoints/flatstats')->getCollection();
        if ($flatpoints->count()){
            foreach($flatpoints as $point){
                $point->delete();
            }
        }
        if (Mage::getConfig()->getModuleConfig('J2t_Rewardshare')->is('active', 'true')){
            $pointshare = Mage::getModel('j2trewardshare/share')->getCollection();
            if ($pointshare->count()){
                foreach($pointshare as $point){
                    $point->delete();
                }
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('rewardpoints')->__(
                'Points flushed for all customers.'
            )
        );
        $this->_redirectReferer();
    }
    
    public function deleteFromGridAction() {
        $customer_ids = $this->getRequest()->getParam('customer');
        if(!is_array($customer_ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('Please select items.'));
        } else {
            try {
                foreach ($customer_ids as $customer_id) {
                    $points = Mage::getModel('rewardpoints/stats')->loadByCustomerId($customer_id);
                    if ($points->count()){
                        foreach($points as $point){
                            $point->delete();
                        }
                    }
                    $flatpoints = Mage::getModel('rewardpoints/flatstats')->loadByCustomerId($customer_id);
                    if ($flatpoints->count()){
                        foreach($flatpoints as $point){
                            $point->delete();
                        }
                    }
                    if (Mage::getConfig()->getModuleConfig('J2t_Rewardshare')->is('active', 'true')){
                        $collection = Mage::getModel('j2trewardshare/share')->getCollection();
                        $pointshare = $collection->addFieldToFilter('customer_id', $customer_id);
                        if ($pointshare->count()){
                            foreach($pointshare as $point){
                                $point->delete();
                            }
                        }
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpoints')->__(
                        'Point flushed for %d customer(s).', count($customer_ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirectReferer();
    }

    public function massDeleteAction() {
        $ruleIds = $this->getRequest()->getParam('rewardpoints_account_ids');

        if(!is_array($ruleIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select points'));
        } else {
            try {
                foreach ($ruleIds as $ruleId) {
                    $rule = Mage::getModel('rewardpoints/stats')->load($ruleId);
                    $store_ids = $rule->getStoreId();
                    $customer_id = $rule->getCustomerId();
                    $rule->delete();
                    
                    if ($store_ids){
                        $store_arr = explode(',', $store_ids);
                        foreach($store_arr as $store_id){
                            $this->refreshFlat($customer_id, $store_id);
                        }
                    }
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
        $fileName   = 'j2t_rewardpoints.csv';
        $content    = $this->getLayout()->createBlock('rewardpoints/adminhtml_clientpoints_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'j2t_rewardpoints.xml';
        $content    = $this->getLayout()->createBlock('rewardpoints/adminhtml_clientpoints_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }


}
