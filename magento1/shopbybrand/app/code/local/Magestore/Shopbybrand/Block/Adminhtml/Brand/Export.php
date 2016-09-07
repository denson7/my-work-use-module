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
 * Shopbybrand Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Block_Adminhtml_Brand_Export extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('brandExport');
        $this->setDefaultSort('brand_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_Shopbybrand_Block_Adminhtml_Shopbybrand_Grid
     */
    protected function _prepareCollection()
    {
        $storeId = $this->getRequest()->getParam('store',0);
        $collection = Mage::getModel('shopbybrand/brand')->getCollection()->setStoreId($storeId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Magestore_Shopbybrand_Block_Adminhtml_Shopbybrand_Grid
     */
    protected function _prepareColumns()
    {

        $this->addColumn('name', array(
            'header'    => Mage::helper('shopbybrand')->__('Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));
        
        /* add by Peter */
        $this->addColumn('position_brand', array(
            'header'    => Mage::helper('shopbybrand')->__('Sort Order'),
            'align'     =>'left',
            'width'     => '30px',
            'index'     => 'position_brand',
        ));
        /* end add by Peter */
        
        $this->addColumn('url_key', array(
            'header'    => Mage::helper('shopbybrand')->__('URL Key'),
            'width'     => '250px',
            'index'     => 'url_key',
        ));
        $this->addColumn('page_title', array(
            'header'    => Mage::helper('shopbybrand')->__('Page Title'),
            'width'     => '250px',
            'index'     => 'page_title',
        ));
        
        
        $this->addColumn('is_featured', array(
            'header'    => Mage::helper('shopbybrand')->__('Is Featured'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_featured',
            'type'        => 'options',
            'options'     => array(
                1 => '1',
                0 => '0',
            ),
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('shopbybrand')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'        => 'options',
            'options'     => array(
                1 => '1',
                2 => '2',
            ),
        ));
        
        $this->addColumn('short_description', array(
            'header'    => Mage::helper('shopbybrand')->__('Short Description'),
            'width'     => '250px',
            'index'     => 'short_description',
        ));
        $this->addColumn('description', array(
            'header'    => Mage::helper('shopbybrand')->__('Description'),
            'width'     => '250px',
            'index'     => 'description',
        ));
        $this->addColumn('meta_keywords', array(
            'header'    => Mage::helper('shopbybrand')->__('Meta Keywords'),
            'width'     => '250px',
            'index'     => 'meta_keywords',
        ));
        $this->addColumn('meta_description', array(
            'header'    => Mage::helper('shopbybrand')->__('Meta Description'),
            'width'     => '250px',
            'index'     => 'meta_description',
        ));
        
        return parent::_prepareColumns();
    }
    
    /**
     * prepare mass action for this grid
     *
     * @return Magestore_Shopbybrand_Block_Adminhtml_Shopbybrand_Grid
     */
    
    
    /**
     * get url for each row in grid
     *
     * @return string
     */
    
}