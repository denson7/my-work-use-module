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
class Magestore_Shopbybrand_Block_Adminhtml_Brand_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('brandGrid');
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
        $this->addColumn('brand_id', array(
            'header'    => Mage::helper('shopbybrand')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'brand_id',
            'filter_index'=>'main_table.brand_id'
        ));

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
        
        $this->addColumn('thumnail_image', array(
            'header'    => Mage::helper('shopbybrand')->__('Logo'),
            'width'     => '150px',
            'index'     => 'thumnail_image',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'shopbybrand/adminhtml_brand_renderer_image'
        ));
        
        $this->addColumn('is_featured', array(
            'header'    => Mage::helper('shopbybrand')->__('Featured'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_featured',
            'type'        => 'options',
            'options'     => array(
                1 => 'Yes',
                0 => 'No',
            ),
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('shopbybrand')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'        => 'options',
            'options'     => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));

        $this->addColumn('action',
            array(
                'header'    =>    Mage::helper('shopbybrand')->__('Action'),
                'width'        => '100',
                'type'        => 'action',
                'getter'    => 'getId',
                'actions'    => array(
                    array(
                        'caption'    => Mage::helper('shopbybrand')->__('Edit'),
                        'url'        => array('base'=> '*/*/edit/store/'.$this->getRequest()->getParam('store')),
                        'field'        => 'id'
                    )),
                'filter'    => false,
                'sortable'    => false,
                'index'        => 'stores',
                'is_system'    => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('shopbybrand')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('shopbybrand')->__('XML'));

        return parent::_prepareColumns();
    }
    
    /**
     * prepare mass action for this grid
     *
     * @return Magestore_Shopbybrand_Block_Adminhtml_Shopbybrand_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('shopbybrand_id');
        $this->getMassactionBlock()->setFormFieldName('shopbybrand');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'        => Mage::helper('shopbybrand')->__('Delete'),
            'url'        => $this->getUrl('*/*/massDelete'),
            'confirm'    => Mage::helper('shopbybrand')->__('Are you sure?')
        ));
        $yesno = array(
            1 => "Yes",
            0 => "No"
        );
        $this->getMassactionBlock()->addItem('featured', array(
            'label'=> Mage::helper('shopbybrand')->__('Change Featured'),
            'url'    => $this->getUrl('*/*/massFeatured', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'    => 'is_featured',
                    'type'    => 'select',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('shopbybrand')->__('Featured'),
                    'values'=> $yesno
                ))
        ));
         
        $statuses = Mage::getSingleton('shopbybrand/status')->getOptionArray();
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('shopbybrand')->__('Change Status'),
            'url'    => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name'    => 'status',
                    'type'    => 'select',
                    'class'    => 'required-entry',
                    'label'    => Mage::helper('shopbybrand')->__('Status'),
                    'values'=> $statuses
                ))
        ));
        return $this;
    }
    
    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        $store = $this->getRequest()->getParam('store');
        return $this->getUrl('*/*/edit', array('id' => $row->getId(), 'store'=>$store));
    }
}