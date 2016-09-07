<?php

class Magestore_Shopbybrand_Adminhtml_SubscriberController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction(){
		$this->loadLayout()
			->_setActiveMenu('shopbybrand/shopbybrand')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Subscriber'));
		return $this;
	}
 
	public function indexAction(){
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return ;
        }
		$this->_initAction()
			->renderLayout();
	}
	/**
     * massUnsubscribe
     */
	  public function massUnsubscribeAction()
    {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return ;
        }
        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shopbybrand')->__('Please select subscriber(s)'));
        }
        else {
            try {
                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);
                    $subscriber->unsubscribe();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were updated', count($subscribersIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
	 /**
     * massDelete
     */
	 public function massDeleteAction()
    {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return ;
        }
        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shopbybrand')->__('Please select subscriber(s)'));
        }
        else {
            try {
                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);
                    $subscriber->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were deleted', count($subscribersIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
	/**
     * export grid item to CSV type
     */
    public function exportCsvAction()
    {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return ;
        }
        $fileName   = 'subscriberbrand.csv';
        $content    = $this->getLayout()
                           ->createBlock('shopbybrand/adminhtml_subscriber_grid')
                           ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction()
    {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return ;
        }
        $fileName   = 'subscriberbrand.xml';
        $content    = $this->getLayout()
                           ->createBlock('shopbybrand/adminhtml_subscriber_grid')
                           ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    protected function _licenseKeyError() {
        $_helper = Mage::helper('magenotification');
        if ($_helper->checkLicenseKey('Manufacturer')) {
            $_licenseType = (int)$_helper->getDBLicenseType();
            if ($_licenseType == 10 || $_licenseType == 7) {
                $versionLabel = ($_licenseType == 10) ? $this->__('trial') : $this->__('development');
                Mage::getSingleton('core/session')->addNotice($this->__('You are using a %s version of %s extension. It will be expired on %s.', $versionLabel, 'Shop by Brand', $_helper->getDBExpiredTime()));
            }
            return false;
        }
        $message = $_helper->getInvalidKeyNotice();
        $this->loadLayout();
        $contentBlock = $this->getLayout()->createBlock('core/text');
        $contentBlock->setText($message);
        $this->getLayout()->getBlock('root')->setChild('content', $contentBlock);
        $this->renderLayout();
        return true;
    }
}