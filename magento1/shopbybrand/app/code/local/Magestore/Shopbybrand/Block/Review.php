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
 * Shopbybrand Block
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Block_Review extends Mage_Core_Block_Template {

    /**
     * prepare block's layout
     *
     * @return Magestore_Shopbybrand_Block_Shopbybrand
     */
    protected $_reviewsCollection;

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getStoreId() {
        $storeId = Mage::app()->getStore()->getId();
        return $storeId;
    }

    public function getBrand() {
        if (!$this->hasData('current_brand')) {
            $brandId = $this->getRequest()->getParam('id');
            $storeId = $this->getStoreId();
            $brand = Mage::getModel('shopbybrand/brand')->setStoreId($storeId)
                    ->load($brandId);
            $this->setData('current_brand', $brand);
        }
        return $this->getData('current_brand');
    }

    public function getReviewsCollection() {
        if (null === $this->_reviewsCollection) {
            $resources = Mage::getSingleton('core/resource');
            $entityTable = $resources->getTableName('review/review_entity');
            $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter($this->getStoreId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addFieldToFilter('entity_pk_value',array('in'=>$this->getCollection()->getAllIDs()));
            $this->_reviewsCollection->getSelect()->join(array('rev'=>$entityTable), 'main_table.entity_id=rev.entity_id AND entity_code="product"')
                ->group('entity_pk_value');
            $this->_reviewsCollection->setDateOrder();
        }
        $store = Mage::app()->getStore()->getId();
        $maxReview = Mage::getStoreConfig('shopbybrand/optional/display_brand_maxreview', $store);
        if($maxReview)
            $this->_reviewsCollection->getSelect()->limit($maxReview);
        return $this->_reviewsCollection;
    }

}