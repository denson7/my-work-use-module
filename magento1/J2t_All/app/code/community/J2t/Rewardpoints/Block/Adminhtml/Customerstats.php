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
class J2t_Rewardpoints_Block_Adminhtml_Customerstats extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_stats_grid');
        $this->setDefaultSort('rewardpoints_account_id', 'desc');
        $this->setUseAjax(true);        
        $this->setEmptyText(Mage::helper('rewardpoints')->__('No Points Found'));
    }
    
    
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/rewardpointsadmin_customerstats', array('_current'=>true));
    }

    protected function _prepareCollection()
    {
        if (version_compare(Mage::getVersion(), '1.4.0.1', '>')){
            $collection = Mage::getResourceModel('rewardpoints/grid_collection')
                ->addFieldToSelect('rewardpoints_account_id')
                    ->addFieldToSelect('store_id')
                ->addFieldToSelect('order_id')
                ->addFieldToSelect('points_current')
                ->addFieldToSelect('points_spent')
                ->addFieldToSelect('date_start')
                ->addFieldToSelect('date_end')
                ->addFieldToSelect('rewardpoints_referral_id')
                ->addFieldToSelect('rewardpoints_description')
                ->addFieldToSelect('rewardpoints_linker')
				->addFieldToSelect('rewardpoints_firstorder')
                ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId());
        } else {
            //$collection = Mage::getResourceModel('rewardpoints/stats_collection')
            $collection = Mage::getResourceModel('rewardpoints/grid_collection')
                ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId());
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /*protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_grid_collection')
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('customer_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('grand_total')
            ->addFieldToSelect('order_currency_code')
            ->addFieldToSelect('store_id')
            ->addFieldToSelect('billing_name')
            ->addFieldToSelect('shipping_name')
            ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId())
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }*/

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('rewardpoints')->__('ID'),
            'align'     =>'right',
            'width'     => '100px',
            'index'     => 'rewardpoints_account_id',
            'sortable'  => false,
        ));

        
        $this->addColumn('order_id', array(
            'header'    => Mage::helper('rewardpoints')->__('Points type'),
            'align'     => 'left',
            'index'     => 'order_id',
            'type'    => 'action',
            'renderer' => new J2t_Rewardpoints_Block_Adminhtml_Renderer_Pointstype(),
            'filter'    => false,
            'sortable'  => false,
        ));
		$this->addColumn('rewardpoints_description', array(
			'header'    => Mage::helper('rewardpoints')->__('Description'),
			'align'     =>'right',
			'index'     => 'rewardpoints_description',
			'filter'    => false,
			'sortable'  => false,
			'column_css_class'=>'no-display',
			'header_css_class'=>'no-display',
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
            'filter'    => false,
            'sortable'  => false,
        ));
        $this->addColumn('points_spent', array(
            'header'    => Mage::helper('rewardpoints')->__('Spent points'),
            'align'     => 'right',
            'index'     => 'points_spent',
            'filter'    => false,
            'sortable'  => false,
        ));


        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('rewardpoints')->__('Stores'),
                'align'     => 'left',
                'index'     => 'store_id',
                /*'type'    => 'action',*/
                'renderer' => new J2t_Rewardpoints_Block_Adminhtml_Renderer_Store(),
                'sortable'  => false,
                'type'      => 'store',
            ));
        }
        
        $this->addColumn('date_start', array(
            'header'    => Mage::helper('rewardpoints')->__('From'),
            'align'     => 'right',
            'index'     => 'date_start',
            'type'      => 'date',
            'width'     => '50px',
            'filter'    => false,
            'sortable'  => false,
        ));


        $this->addColumn('date_end', array(
            'header'    => Mage::helper('rewardpoints')->__('Until'),
            'align'     => 'right',
            'index'     => 'date_end',
            'type'      => 'date',
            'width'     => '50px',
            'filter'    => false,
            'sortable'  => false,
        ));


        //rewardpoints_referral_id
        $this->addColumn('rewardpoints_referral_id', array(
            'header'    => Mage::helper('rewardpoints')->__('Referred customer'),
            'align'     => 'left',
            'index'     => 'rewardpoints_referral_id',
            'type'    => 'action',
            'renderer' => new J2t_Rewardpoints_Block_Adminhtml_Renderer_Referral(),
            'filter'    => false,
            'sortable'  => false,
        ));


        /*if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('customer')->__('Bought From'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view' => true
            ));
        }

        $this->addColumn('action', array(
            'header'    => ' ',
            'filter'    => false,
            'sortable'  => false,
            'width'     => '100px',
            'renderer'  => 'adminhtml/sales_reorder_renderer_action'
        ));*/

        return parent::_prepareColumns();
    }

    //public function getRowUrl($row)
    //{
    //    return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
    //}

    //public function getGridUrl()
    //{
    //    return $this->getUrl('*/*/orders', array('_current' => true));
    //}
}