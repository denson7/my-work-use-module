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
class J2t_Rewardpoints_Block_Adminhtml_Stats_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('statsGrid');
      $this->setDefaultSort('user_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      /*$collection = Mage::getModel('rewardpoints/stats')->getCollection()
              ->addValidPoints(Mage::app()->getStore()->getId())
              ->addClientEntries()
              ->showCustomerInfo();*/
      
      //$collection = Mage::getModel('rewardpoints/flatstats')->getCollection()->addClientEntries()->showCustomerInfo();
      $collection = Mage::getResourceModel('rewardpoints/flatstats_collection');
      
      //$collection->addFilterToMap('store_id', 'main_table.store_id');
      $collection->addClientEntries();
      $collection->showCustomerInfo();
      
      $this->setCollection($collection);
      /*echo $collection->getSelect()->__toString();
      die;*/
      parent::_prepareCollection();
      return $this;      
  }

  protected function _prepareColumns()
  {
      
      $this->addColumn('id', array(
            'header'    => Mage::helper('rewardpoints')->__('ID'),
            'width'     => '50px',
            'index'     => 'flat_account_id',          
            'filter_index' =>'main_table.flat_account_id',
            'type'  => 'number',
        ));


      $this->addColumn('customer_firstname', array(
          'header'    => Mage::helper('rewardpoints')->__('Customer First Name'),
          'align'     => 'right',
          'index'     => 'customer_firstname',          
          'filter_index' =>'customer_firstname_table.value',          
      ));
      
      $this->addColumn('customer_lastname', array(
          'header'    => Mage::helper('rewardpoints')->__('Customer Last Name'),
          'align'     => 'right',
          'index'     => 'customer_lastname',          
          'filter_index' =>'customer_lastname_table.value',
      ));
      
      //if (!Mage::app()->isSingleStoreMode()){
          $this->addColumn('store_id', array(
            'header'    => Mage::helper('rewardpoints')->__('Stores'),
            'index'     => 'current_store_id',
            'type'      => 'store',
            'store_view' => true,
            'sortable'   => false,
            'filter_index' =>'main_table.store_id',
        ));
      //}
      
      $this->addColumn('email', array(
          'header'    => Mage::helper('rewardpoints')->__('Customer email'),
          'align'     => 'left',
          'index'     => 'email',
          'filter_index' =>'cust.email',
      ));
      
      
      $this->addColumn('points_collected', array(
          'header'    => Mage::helper('rewardpoints')->__('Accumulated points'),
          'align'     => 'right',
          'index'     => 'points_collected',
          'filter'    => false,
          'width'     => '50px',
      ));
      $this->addColumn('points_used', array(
          'header'    => Mage::helper('rewardpoints')->__('Spent points'),
          'align'     => 'right',
          'index'     => 'points_used',
          'filter'    => false,
          'width'     => '50px',
          //'sortable'    => false,
      ));
      
      $this->addColumn('points_current', array(
          'header'    => Mage::helper('rewardpoints')->__('Available points'),
          'align'     => 'right',
          'index'     => 'points_current',
          //'filter'    => false,
          'width'     => '50px',
          'type'      => 'number',
          'filter_index' =>'main_table.points_current',
          //'sortable'    => false,
      ));
      
      $this->addColumn('points_lost', array(
          'header'    => Mage::helper('rewardpoints')->__('Lost'),
          'align'     => 'right',
          'index'     => 'points_lost',
          'filter'    => false,
          'width'     => '50px',
          //'sortable'    => false,
      ));
      
      /*$this->addColumn('points_waiting', array(
          'header'    => Mage::helper('rewardpoints')->__('Waiting for validation'),
          'align'     => 'right',
          'index'     => 'points_waiting',
          'filter'    => false,
          'width'     => '50px',
          //'sortable'    => false,
      ));*/
      
      $this->addColumn('last_check', array(
            'header'            => Mage::helper('sales')->__('Calculation date'),
            'index'             => 'last_check',
            'filter_index'      => 'main_table.last_check',
            'width'             => '50px',
            'type'              => 'date',
            'align'             => 'center',
            'default'           => $this->__('N/A'),
            'html_decorators'   => array('nobr')
        ));
      
      

      $this->addExportType('*/*/exportCsv', Mage::helper('rewardpoints')->__('CSV'));
      $this->addExportType('*/*/exportXml', Mage::helper('rewardpoints')->__('XML'));
      
      return parent::_prepareColumns();
  }
  
  
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('current_customer_id');
        $this->getMassactionBlock()->setFormFieldName('user_ids');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('rewardpoints')->__('Recalculate points&nbsp;&nbsp;'),
             'url'      => $this->getUrl('*/*/recalculatePoints'),
             'confirm'  => Mage::helper('rewardpoints')->__('Are you sure? This action may take some time!')
        ));

        return $this;
    }
  
}

