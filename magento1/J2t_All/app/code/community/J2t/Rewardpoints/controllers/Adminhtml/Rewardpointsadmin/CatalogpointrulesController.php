<?php

class J2t_Rewardpoints_Adminhtml_Rewardpointsadmin_CatalogpointrulesController extends Mage_Adminhtml_Controller_Action {
    
	protected function _isAllowed()
    {
        return true;
    }
	
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('rewardpoints/catalogpointrules')
                ->_addBreadcrumb(Mage::helper('rewardpoints')->__('Point Rules'), Mage::helper('rewardpoints')->__('Catalog point Rules'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('rewardpoints/adminhtml_catalogpointrules'))
                ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }


    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('rewardpoints/catalogpointrules');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpoints')->__('Rule was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('Unable to find a page to delete'));
        $this->_redirect('*/*/');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('rewardpoints/catalogpointrules');

        if ($id) {
            $model->load($id);
            if (! $model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('This rule no longer exists'));
                $this->_redirect('*/*');
                return;
            }
        }

        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);

        //$data = $this->_filterDates($data, array('from_date', 'to_date'));

        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('catalogpointrules_conditions_fieldset');

        $model->setData('segments_cut', explode(';', $model->getSegmentsCut()));
        $model->setData('segments_paste', explode(';', $model->getSegmentsPaste()));

        Mage::register('catalogpointrules_data', $model);

        $this->loadLayout();
        $this->_setActiveMenu('rewardpoints');

        $block = $this->getLayout()->createBlock('rewardpoints/adminhtml_catalogpointrules_edit')
            ->setData('action', $this->getUrl('*/rewardpoints_catalogpointrules/save'));


        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->setCanLoadRulesJs(true);

        $this
            ->_addContent($block)
            ->_addLeft($this->getLayout()->createBlock('rewardpoints/adminhtml_catalogpointrules_edit_tabs'))
            ->renderLayout();
    }

    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {

            try {
                $model = Mage::getModel('rewardpoints/catalogpointrules');


                $data = $this->getRequest()->getPost();
                //$data = $this->_filterDates($data, array('from_date', 'to_date'));

                if (method_exists('Mage_Core_Controller_Varien_Action','_filterDates')){
                    $data = $this->_filterDates($data, array('from_date', 'to_date'));
                } else {
                    if ($data['from_date'] != null){
                        $date = Mage::app()->getLocale()->date($data['from_date'], Zend_Date::DATE_SHORT, null, false);
                        $time = $date->getTimestamp();
                        $data['from_date'] = Mage::getModel('core/date')->gmtDate(null, $time);
                    }
                    if ($data['to_date'] != null){
                        $date = Mage::app()->getLocale()->date($data['to_date'], Zend_Date::DATE_SHORT, null, false);
                        $time = $date->getTimestamp();
                        $data['to_date'] = Mage::getModel('core/date')->gmtDate(null, $time);
                    }
                }



                //$data = $this->_filterDates($data, array('from_date', 'to_date'));
                
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('rewardpoints')->__('Wrong rule specified.'));
                    }
                }

                //$validateResult = $model->validateData(new Varien_Object($data));
                if (method_exists('Mage_Rule_Model_Rule','validateData')){
                    $validateResult = $model->validateData(new Varien_Object($data));
                } else {
                    $validateResult = $model->validateVarienData(new Varien_Object($data));
                }
                
                if ($validateResult !== true) {
                    foreach($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->_redirect('*/*/edit', array('id'=>$model->getId()));
                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
                if (isset($data['frontend_label'])){
                    $data['labels'] = serialize($data['frontend_label']);
                }
                if (isset($data['frontend_labelsummary'])){
                    $data['labels_summary'] = serialize($data['frontend_labelsummary']);
                }
                
                
                $model->loadPost($data);

                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
                
                $model->save();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpoints')->__('Rule was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }

        $this->_redirect('*/*/');
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('rewardpoints/catalogpointrules'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }


        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
