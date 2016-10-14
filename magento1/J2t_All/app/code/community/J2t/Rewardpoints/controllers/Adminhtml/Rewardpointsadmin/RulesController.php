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
class J2t_Rewardpoints_Adminhtml_Rewardpointsadmin_RulesController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
    {
        return true;
    }
	protected function _initAction() {
            $this->loadLayout()
                    ->_setActiveMenu('rewardpoints/rules')
                    ->_addBreadcrumb(Mage::helper('rewardpoints')->__('Rules'), Mage::helper('rewardpoints')->__('Rules'));

            return $this;
	}

	public function indexAction() {
            $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('rewardpoints/adminhtml_rules'))
                ->renderLayout();
	}

        public function editAction() {
		$id     = $this->getRequest()->getParam('id');
                $model  = Mage::getModel('rewardpoints/rules')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

                        
			Mage::register('rules_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('rewardpoints/rules');



			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('rewardpoints/adminhtml_rules_edit'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('No rules'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction() {
		$this->_forward('edit');
	}


	public function saveAction() {
            if ($data = $this->getRequest()->getPost()) {
                $model = Mage::getModel('rewardpoints/rules');

                $data['website_ids'] = implode(',',$data['website_ids']);


                $model->setData($data)
                        ->setId($this->getRequest()->getParam('id'));

                $date = Mage::app()->getLocale()->date($data['rewardpoints_rule_start'], Zend_Date::DATE_SHORT, null, false);
                $time = $date->getTimestamp();
                $model->setRewardpointsRuleStart(Mage::getModel('core/date')->gmtDate(null, $time));

                $date = Mage::app()->getLocale()->date($data['rewardpoints_rule_end'], Zend_Date::DATE_SHORT, null, false);
                $time = $date->getTimestamp();
                //$data['rewardpoints_rule_end'] = Mage::getModel('core/date')->gmtDate(null, $time);
                $model->setRewardpointsRuleEnd(Mage::getModel('core/date')->gmtDate(null, $time));
                
                try {
                    $model->save();
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpoints')->__('Rule were successfully saved'));
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
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('Unable to save'));
            $this->_redirect('*/*/');
	}

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
                    try {
                            $model = Mage::getModel('rewardpoints/rules');

                            $model->setId($this->getRequest()->getParam('id'))
                                    ->delete();

                            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpoints')->__('Rule were successfully deleted'));
                            $this->_redirect('*/*/');
                    } catch (Exception $e) {
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    }
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $ruleIds = $this->getRequest()->getParam('rewardpoints_rule_ids');
        if(!is_array($ruleIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select rules'));
        } else {
            try {
                foreach ($ruleIds as $ruleId) {
                    $rule = Mage::getModel('rewardpoints/rules')->load($ruleId);
                    $rule->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpoints')->__(
                        'Total of %d rules were successfully deleted', count($ruleIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }


}
