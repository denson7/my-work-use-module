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
class J2t_Rewardpoints_Block_Adminhtml_Referrals_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('referralsGrid');
      $this->setDefaultSort('rewardpoints_referral_id ');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {

      $collection = Mage::getResourceModel('rewardpoints/referral_collection');
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

      $this->addColumn('id', array(
          'header'    => Mage::helper('rewardpoints')->__('id'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'rewardpoints_referral_id',
      ));

      $this->addColumn('email', array(
          'header'    => Mage::helper('rewardpoints')->__('Parent email'),
          'align'     =>'right',
          'index'     => 'email',
      ));

      $this->addColumn('rewardpoints_referral_email', array(
          'header'    => Mage::helper('rewardpoints')->__('Referred email'),
          'align'     =>'right',
          'index'     => 'rewardpoints_referral_email',
      ));

      $this->addColumn('rewardpoints_referral_name', array(
          'header'    => Mage::helper('rewardpoints')->__('Referred Name'),
          'align'     =>'right',          
          'index'     => 'rewardpoints_referral_name',
      ));


      

      $this->addColumn('rewardpoints_referral_status', array(
            'header'    => Mage::helper('rewardpoints')->__('Status'),
            'index'     => 'rewardpoints_referral_status',
            'width'     => '150px',
            'type'      => 'options',
            'options'   => array('1' => Mage::helper('adminhtml')->__('Has ordered'), '0' => Mage::helper('adminhtml')->__('Waiting for order')),
        ));



      return parent::_prepareColumns();
  }


  protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rewardpoints_referral_id');
        $this->getMassactionBlock()->setFormFieldName('rewardpoints_referral_ids');

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