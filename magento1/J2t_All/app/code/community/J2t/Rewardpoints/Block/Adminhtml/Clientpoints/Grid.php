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
class J2t_Rewardpoints_Block_Adminhtml_Clientpoints_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('clientpointsGrid');
      $this->setDefaultSort('customer_id ');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
     
    $collection = Mage::getResourceModel('rewardpoints/rewardpoints_collection');
    
    $this->setCollection($collection);
    parent::_prepareCollection();

    if (!Mage::app()->isSingleStoreMode()) {
        $this->getCollection()->addStoreData();
    } 

    return $this;
  }

  
  protected function _prepareColumns()
  {
      $model = Mage::getModel('rewardpoints/stats');

      $this->addColumn('id', array(
          'header'    => Mage::helper('rewardpoints')->__('id'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'rewardpoints_account_id',
          'type'  => 'number',
      ));

      $this->addColumn('client_id', array(
          'header'    => Mage::helper('rewardpoints')->__('Client ID'),
          'align'     =>'right',
          'index'     => 'customer_id',
          'width'     => '50px',
          'type'  => 'number',
      ));
	  
	  
	  $this->addColumn('rewardpoints_firstorder', array(
		'header' => Mage::helper('rewardpoints')->__('First Order'),
		'index' => 'rewardpoints_firstorder',
		'type'  => 'options',
			'options' => array(
				0 => 'No',
				1 => 'Yes',
			),
		'column_css_class'=>'no-display',
		'header_css_class'=>'no-display',
		));

      $this->addColumn('email', array(
          'header'    => Mage::helper('rewardpoints')->__('Email'),
          'align'     =>'right',
          'index'     => 'email',
      ));

      $this->addColumn('order_id', array(
          'header'    => Mage::helper('rewardpoints')->__('Order ID'),
          'align'     =>'right',
          'index'     => 'order_id',
          'renderer'  => new J2t_Rewardpoints_Block_Adminhtml_Renderer_Order(),
      ));
	  
	  $this->addColumn('rewardpoints_description', array(
          'header'    => Mage::helper('rewardpoints')->__('Description'),
          'align'     =>'right',
          'index'     => 'rewardpoints_description',
      ));


      /*$this->addColumn('order_id_corres', array(
            'header'    => Mage::helper('rewardpoints')->__('Type of points'),
            'index'     => 'order_id',
            'width'     => '150px',
            'type'      => 'options',
            'options'   => Mage::getModel("rewardpoints/stats")->getPointsTypeToArray(),
        ));*/
      
      $this->addColumn('order_id_corres', array(
            'header'    => Mage::helper('rewardpoints')->__('Points type'),
            'align'     => 'left',
            'index'     => 'order_id',
            'type'    => 'action',
            'renderer' => new J2t_Rewardpoints_Block_Adminhtml_Renderer_Pointstype(),
            'filter'    => false,
            'sortable'  => false,
        ));
      
      
      /*$this->addColumn('rewardpoints_description', array(
          'header'    => Mage::helper('rewardpoints')->__('Description'),
          'align'     => 'right',
          'index'     => 'rewardpoints_description',
          'width'     => '50px',
          'filter'    => false,
      ));*/
      
      if (Mage::getConfig()->getModuleConfig('J2t_Rewardsocial')->is('active', 'true')){
          $this->addColumn('rewardpoints_linker', array(
            'header'    => Mage::helper('rewardpoints')->__('Relation'),
            'align'     => 'right',
            'index'     => 'rewardpoints_linker',
            'width'     => '150px',
            'renderer' => new J2t_Rewardpoints_Block_Adminhtml_Renderer_Pointslink(),
            'filter'    => false,
            'sortable'  => false,
        ));
      }
        
      $this->addColumn('points_current', array(
          'header'    => Mage::helper('rewardpoints')->__('Accumulated points'),
          'align'     => 'right',
          'index'     => 'points_current',
          'width'     => '50px',
          'filter'    => false,
      ));
      $this->addColumn('points_spent', array(
          'header'    => Mage::helper('rewardpoints')->__('Spent points'),
          'align'     => 'right',
          'index'     => 'points_spent',
          'width'     => '50px',
          'filter'    => false,
      ));
      
      
      /*$this->addColumn('date_start', array(
          'header'    => Mage::helper('rewardpoints')->__('From'),
          'align'     => 'right',
          'index'     => 'date_start',
          'type'      => 'date',
          'width'     => '50px',
          'filter'    => false,
      ));
      
      
      $this->addColumn('date_end', array(
          'header'    => Mage::helper('rewardpoints')->__('Until'),
          'align'     => 'right',
          'index'     => 'date_end',
          'type'      => 'date',
          'width'     => '50px',
          'filter'    => false,
      ));*/

      

      if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('rewardpoints')->__('Stores'),
                'index'     => 'stores',
                'type'      => 'store',
                'store_view' => false,
                'sortable'   => false,
            ));
        }

      
      $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
      $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));

      return parent::_prepareColumns();
  }

  

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rewardpoints_account_id');
        $this->getMassactionBlock()->setFormFieldName('rewardpoints_account_ids');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('rewardpoints')->__('Delete&nbsp;&nbsp;'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('rewardpoints')->__('Are you sure?')
        ));

        return $this;
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }



}