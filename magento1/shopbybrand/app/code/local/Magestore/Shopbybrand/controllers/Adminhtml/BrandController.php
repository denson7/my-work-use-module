<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Shopbybrand Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Adminhtml_BrandController extends Mage_Adminhtml_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Shopbybrand_Adminhtml_ShopbybrandController
     */
    protected function _initAction() {
        //Mage::helper('shopbybrand/brand')->updateBrandsFormCatalog();
        $this->loadLayout()
                ->_setActiveMenu('shopbybrand/shopbybrand')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Brands Manager'), Mage::helper('adminhtml')->__('Brand Manager')
        );
        $this->getLayout()->getBlock('head')->setTitle($this->__('Manage Brands / Shop by Brand / Magento Admin'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $this->_initAction()
                ->renderLayout();
    }
    
    public function importbrandAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $this->loadLayout()
                ->_addContent($this->getLayout()->createBlock('shopbybrand/adminhtml_brand_import'));
        $this->_title($this->__('Import brand'))
                ->_title($this->__('Import brand'));
        $this->renderLayout();
    }
    
    
   public function processImportAction() {
//       $this->loadLayout();
        if (!empty($_FILES['csv_brand']['tmp_name'])) {
                try {
                    $number = Mage::getResourceModel('shopbybrand/brand')->import($this->getRequest()->getParam('is_update'));
                         Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('shopbybrand')->__('You\'ve successfully imported ').$number['insert']. Mage::helper('shopbybrand')->__(' new item(s) and updated ').$number['update'].' '.Mage::helper('shopbybrand')->__('item(s). Please reindex brand URL key.'));
                }catch (Mage_Core_Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shopbybrand')->__('Invalid file upload attempt'));
                }
        }else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shopbybrand')->__('Invalid file upload attempt'));
        }
        Mage::getSingleton('index/indexer')
            ->getProcessByCode('url')
            ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        $this->_redirect('adminhtml/process/list');
//        $this->renderLayout();
    }
    /**
     * view and edit item action
     */
    public function editAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $brandId = $this->getRequest()->getParam('id');
        $store = $this->getRequest()->getParam('store');
        $model = Mage::getModel('shopbybrand/brand')->setStoreId($store)
                ->load($brandId);
        Mage::helper('shopbybrand/brand')->getCategoryIdsByBrand($model);
        if ($model->getId() || $brandId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('brand_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('shopbybrand/shopbybrand');

            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News')
            );
             if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}   
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('shopbybrand/adminhtml_brand_edit'))
                    ->_addLeft($this->getLayout()->createBlock('shopbybrand/adminhtml_brand_edit_tabs'));
            if($brandId){
                $this->getLayout()->getBlock('head')->setTitle($this->__('Edit Brands / Shop by Brand / Magento Admin'));
            }  else {
                $this->getLayout()->getBlock('head')->setTitle($this->__('Add Brands / Shop by Brand / Magento Admin'));
            }
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('shopbybrand')->__('Brand does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * save item action
     */
    public function saveAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        if ($data = $this->getRequest()->getPost()) {
            //image
            $store = $this->getRequest()->getParam('store', 0);
            if (isset($data['url_key'])) {
                $data['url_key'] = Mage::helper('shopbybrand')->refineUrlKey($data['url_key']);
                $urlRewrite = Mage::getModel('shopbybrand/brand')->loadByRequestPath($data['url_key'], $store);

                if ($urlRewrite->getId()) {
                    $urlRewriteIdPath = (version_compare(Mage::getVersion(), '1.13', '>='))?$urlRewrite->getIdentifier():$urlRewrite->getIdPath();
                    if (!$this->getRequest()->getParam('id')) {
                        Mage::getSingleton('adminhtml/session')->addError('Url key has existed. Please fill out a valid one.');
                        $this->_redirect('*/*/new', array('store' => $store));
                        return;
                    } elseif ($this->getRequest()->getParam('id') && $urlRewriteIdPath != 'brand/' . $this->getRequest()->getParam('id')) {
                        Mage::getSingleton('adminhtml/session')->addError('URL key has already existed. Please choose a different one.');
                        $this->_redirect('*/*/edit', array('store' => $store, 'id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                }
            }
            if (isset($data['image']['delete'])) {
                Mage::helper('shopbybrand')->deleteImageFile($data['name'], $data['old_image']);
                unset($data['old_image']);
            }
            $data['image'] = "";
            if (isset($_FILES['image']))
                $data['image'] = Mage::helper('shopbybrand')->refineImageName($_FILES['image']['name']);

            if (!$data['image'] && isset($data['old_image'])) {
                $data['image'] = $data['old_image'];
            }
            if (isset($data['thumbnail_image']['delete'])) {

                Mage::helper('shopbybrand')->deleteThumbnailImageFile($data['name'], $data['old_thumbnail_image']);
                unset($data['old_thumbnail_image']);
            }
            $data['thumbnail_image'] = "";

            if (isset($_FILES['thumbnail_image']))
                $data['thumbnail_image'] = Mage::helper('shopbybrand')->refineImageName($_FILES['thumbnail_image']['name']);


            if (!$data['thumbnail_image'] && isset($data['old_thumbnail_image'])) {
                $data['thumbnail_image'] = $data['old_thumbnail_image'];
            }
            /////////////////////////////////////////////////////////////////////////
                     
            $model = Mage::getModel('shopbybrand/brand');
            $model->load($this->getRequest()->getParam('id'))
                    ->addData($data);
            $oldProductsIds = $model->getData('product_ids');

            try {
                $productIds = array();
                if (isset($data['sproducts'])) {
                    if (is_string($data['sproducts'])) {
                        parse_str($data['sproducts'], $productIds);
                        $productIds = array_unique(array_keys($productIds));
                    }
                    $model->setData('product_ids', implode(',', $productIds));
                }
                
                
                if(isset($data['sproducts'])){
                    if(!isset($data['featuredproducts']))
                        $data['featuredproducts'] = array();
                    Mage::getModel('shopbybrand/brandproducts')->updateProductData($data['sproducts'], $data['featuredproducts']);
                }
                $model->setStoreId($store);
                
                /////////////////////////////////////////////////////////////////
                try {
                    $model->save();
                    $categoryIds = Mage::helper('shopbybrand/brand')->getCategoryIdsByBrand($model);
                    if ($categoryIds != $model->getCategoryIds()) {
                        $model->setCategoryIds($categoryIds)
                                ->save();
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }

                //upload image
                $image = $model->getImage();
                if (isset($_FILES['image'])) {
                    if (isset($_FILES['image']['name']) && $_FILES['image']['name'])
                        $image = Mage::helper('shopbybrand')->uploadBrandImage($model->getId(), $_FILES['image']);
                }
                $thumbnailImage = $model->getThumbnailImage();
                if (isset($_FILES['thumbnail_image'])) {
                    if (isset($_FILES['thumbnail_image']['name']) && $_FILES['thumbnail_image']['name'])
                        $thumbnailImage = Mage::helper('shopbybrand')->uploadThumbnailImage($model->getId(), $_FILES['thumbnail_image']);
                }
                if ($image != $model->getImage() || $thumbnailImage != $model->getThumbnailImage()) {
                    if ($image != $model->getImage())
                        $model->setImage($image);
                    if ($thumbnailImage != $model->getThumbnailImage())
                        $model->setThumbnailImage($thumbnailImage);
                    $model->save();
                }

                //////////////////////////////////////////////////////////////////////
                try {
                    $model->updateUrlKey();
                    
                    if ($model->getOptionId() == null) {
                        $optionId = Mage::getResourceModel('shopbybrand/brand')->addOption($model);
                        $model->setOptionId($optionId)->save();
                    }
                    if (isset($data['sproducts']))
                        Mage::helper('shopbybrand')->updateProductsBrand($productIds, $model, $store);
                }catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(
                            $e->getMessage()
                    );
                }
                //////////////////////////////////////////////////////////////////////
                
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('shopbybrand')->__('Brand was saved successfully.')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), 'store' => $store));
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
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('shopbybrand')->__('Unable to find brand to save')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        //$store = $this->getRequest()->getParam('store', 0);
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $stores = Mage::getModel('core/store')->getCollection()
                        ->addFieldToFilter('is_active', 1)
                        ->addFieldToFilter('store_id', array('neq' => 0))
                ;
                foreach ($stores as $store) {
                    $urlRewrite = Mage::getModel('shopbybrand/brand')->loadByIdPath('brand/' . $this->getRequest()->getParam('id'), $store->getId());
                    if ($urlRewrite->getId())
                        $urlRewrite->delete();
                }
                $model = Mage::getModel('shopbybrand/brand');
                $model->load($this->getRequest()->getParam('id'));
                Mage::getResourceModel('shopbybrand/brand')->removeOption($model);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Brand was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $shopbybrandIds = $this->getRequest()->getParam('shopbybrand');
        if (!is_array($shopbybrandIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select brand(s)'));
        } else {
            try {
                $stores = Mage::getModel('core/store')->getCollection()
                        ->addFieldToFilter('is_active', 1)
                        ->addFieldToFilter('store_id', array('neq' => 0))
                ;
                foreach ($shopbybrandIds as $shopbybrandId) {
                    foreach ($stores as $store) {
                        $urlRewrite = Mage::getModel('shopbybrand/brand')->loadByIdPath('brand/' . $shopbybrandId, $store->getId());
                        if ($urlRewrite->getId())
                            $urlRewrite->delete();
                    }
                    $shopbybrand = Mage::getModel('shopbybrand/brand')->load($shopbybrandId);
                    Mage::getResourceModel('shopbybrand/brand')->removeOption($shopbybrand);
                    $shopbybrand->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($shopbybrandIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass change status for item(s) action
     */
    public function massStatusAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $shopbybrandIds = $this->getRequest()->getParam('shopbybrand');
        /* add by Peter */
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        /* end add by Peter */
        if (!is_array($shopbybrandIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($shopbybrandIds as $shopbybrandId) {
                    Mage::getSingleton('shopbybrand/brand')
                            /* add by Peter */
                            ->setStoreId($storeId)
                            /* end add by Peter */
                            ->load($shopbybrandId)
                            ->setStatus($this->getRequest()->getParam('status'), $storeId)
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($shopbybrandIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        /* edit by Peter */
        $this->_redirect('*/*/', array('store' => $storeId));
        /* end edit by Peter */
    }

    /**
     * mass change is featured for item(s) action
     */
    public function massFeaturedAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $shopbybrandIds = $this->getRequest()->getParam('shopbybrand');
        /* add by Peter */
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        /* end add by Peter */
        if (!is_array($shopbybrandIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select brand(s)'));
        } else {
            try {
                foreach ($shopbybrandIds as $shopbybrandId) {
                    Mage::getSingleton('shopbybrand/brand')
                            /* add by Peter */
                            ->setStoreId($storeId)
                            /* end add by Peter */
                            ->load($shopbybrandId)
                            ->setIsFeatured($this->getRequest()->getParam('is_featured'), $storeId)
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d brand(s) were successfully updated', count($shopbybrandIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        /* edit by Peter    */
        $this->_redirect('*/*/', array('store' => $storeId));
        /* end edit by Peter */
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $fileName = 'shopbybrand.csv';
        $content = $this->getLayout()
                ->createBlock('shopbybrand/adminhtml_brand_export')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /*
     * export subcribers
     */

    public function exportCsvSubcribersAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $fileName = 'shopbybrand.csv';
        $content = $this->getLayout()
                ->createBlock('shopbybrand/adminhtml_brand_edit_tab_subcribers')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlSubcribersAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $fileName = 'shopbybrand.xml';
        $content = $this->getLayout()
                ->createBlock('shopbybrand/adminhtml_brand_edit_tab_subcribers')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $fileName = 'shopbybrand.xml';
        $content = $this->getLayout()
                ->createBlock('shopbybrand/adminhtml_brand_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('shopbybrand');
    }

    public function testAction() {
        Mage::helper('shopbybrand/brand')->updateBrandsFormCatalog();
    }

    public function productAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('shopbybrand.block.adminhtml.brand.edit.tab.products')
                ->setBrandProducts($this->getRequest()->getPost('brand_products', null));
        $this->renderLayout();
    }

    public function productGridAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('shopbybrand.block.adminhtml.brand.edit.tab.products')
                ->setBrandProducts($this->getRequest()->getPost('brand_products', null));
        $this->renderLayout();
    }

    public function subcriberAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function orderItemsGridAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('shopbybrand/adminhtml_brand_edit_tab_orderitems')->toHtml()
        );
    }

    public function ajaxBlockAction() {
        if ($this->_licenseKeyError() || !Mage::helper('magenotification')->checkLicenseKey('Manufacturer')) {
            return;
        }
        $output = '';
        $blockTab = $this->getRequest()->getParam('block');
        if (in_array($blockTab, array(
                    'adminhtml_brand_edit_tab_report_graph',
                ))) {
            $output = $this->getLayout()->createBlock("shopbybrand/$blockTab")->toHtml();
        }
        $this->getResponse()->setBody($output);
    }

    protected function _licenseKeyError() {
        $_helper = Mage::helper('magenotification');
        if ($_helper->checkLicenseKey('Manufacturer')) {
            $_licenseType = (int) $_helper->getDBLicenseType();
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
