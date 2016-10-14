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
class J2t_Rewardpoints_Block_Adminhtml_Rules_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('rulesGrid');
      $this->setDefaultSort('rewardpoints_rule_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {

      $collection = Mage::getResourceModel('rewardpoints/rules_collection');
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

      $this->addColumn('id', array(
          'header'    => Mage::helper('rewardpoints')->__('id'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'rewardpoints_rule_id',
      ));

      $this->addColumn('rewardpoints_rule_name', array(
          'header'    => Mage::helper('rewardpoints')->__('Name'),
          'align'     =>'right',
          /*'width'     => '50px',*/
          'index'     => 'rewardpoints_rule_name',
      ));

      
      $this->addColumn('rewardpoints_rule_activated', array(
            'header'    => Mage::helper('rewardpoints')->__('Status'),
            'index'     => 'rewardpoints_rule_activated',
            'width'     => '50px',
            'type'      => 'options',
            'options'   => array('1' => Mage::helper('adminhtml')->__('Active'), '0' => Mage::helper('adminhtml')->__('Inactive')),
        ));



      $this->addColumn('admin_action',
            array(
                'header'    =>  Mage::helper('rewardpoints')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('rewardpoints')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        ));


  
      return parent::_prepareColumns();
  }


  protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rewardpoints_rule_id');
        $this->getMassactionBlock()->setFormFieldName('rewardpoints_rule_ids');

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

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }



}
