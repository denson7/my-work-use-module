<?php

class Magestore_Shopbybrand_Block_Adminhtml_Report_Statistic_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
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
    protected function _prepareCollection() {
        $store = $this->getRequest()->getParam('store', 0);
        $collection = Mage::getModel('shopbybrand/brand')->getCollection()->setStoreId($store);
        if (version_compare(Mage::getVersion(), '1.4.1.1', '>=')) {
            $sfog = Mage::getModel('core/resource')->getTableName('sales_flat_order_grid');
        } else {
            $sfog = Mage::getModel('core/resource')->getTableName('sales_order');
        }
        $collection->getSelect()
                ->join(array('sfoi' => Mage::getModel('core/resource')->getTableName('sales_flat_order_item')), 'FIND_IN_SET(sfoi.product_id, main_table.product_ids)', array('qty_ordered', 'base_row_total',))
                ->join(array('sfog' => $sfog), 'sfoi.order_id = sfog.entity_id AND sfog.status = "complete"',array(""));
        if ($store)
            $collection->addFieldToFilter('sfoi.store_id', $store);
        $collection->getSelect()
                ->group('main_table.brand_id')
                ->columns(array(
                    'brand_qty_ordered' => 'SUM(IF( qty_ordered > 0, qty_ordered, 0 ))',
                    'brand_base_row_total' => 'SUM(IF( base_row_total > 0, base_row_total, 0 ))'
                ));

        $filter = Mage::app()->getRequest()->getParam('filter');
        $date = Mage::helper('adminhtml')->prepareFilterString($filter);
        $array=array(
          'date' => true  
        );
        if(isset($date['report_from']))
            $array['from'] = $date['report_from'];
        if(isset($date['report_to']))
            $array['to'] = $date['report_to'];
        
        if (isset($date['report_from']) || isset($date['report_to'])) {
            $collection->addFieldtoFilter('sfoi.created_at',$array);
        }
        $collection->setIsGroupCountSql(TRUE);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_Shopbybrand_Block_Adminhtml_Shopbybrand_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('brand_id', array(
            'header' => Mage::helper('shopbybrand')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'brand_id',
            'filter_index' => 'main_table.brand_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('shopbybrand')->__('Brand Name'),
            'align' => 'left',
            'index' => 'name',
            'filter_index' => 'main_table.name',
        ));

        $this->addColumn('brand_qty_ordered', array(
            'header' => Mage::helper('shopbybrand')->__('Number of Items Sold'),
            'type' => 'number',
            'index' => 'brand_qty_ordered',
            'filter_index' => 'SUM(IF( qty_ordered > 0, qty_ordered, 0 ))',
        ));
        $this->addColumn('brand_base_row_total', array(
            'header' => Mage::helper('shopbybrand')->__('Total Sales ($)'),
            'index' => 'brand_base_row_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
            'filter_index' => 'SUM(IF( base_row_total > 0, base_row_total, 0 ))'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('shopbybrand')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));
        $period='';
        if ($this->getFilter('report_from'))
            $period = "custom";
        $this->addColumn('action', array(
            'header' => Mage::helper('shopbybrand')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('shopbybrand')->__('View sales chart'),
                    'url' => array('base' => 'brandadmin/adminhtml_brand/edit/store/' . $this->getRequest()->getParam('store') . '/reportSales/1/from/' . str_replace("/", "_", $this->getFilter('report_from')) . '/to/' . str_replace("/", "_", $this->getFilter('report_to')) . '/period/' . $period),
                    'field' => 'id'
            )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('shopbybrand')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('shopbybrand')->__('XML'));
        $this->addExportType('*/*/exportSalesExcel', Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        $store = $this->getRequest()->getParam('store');
        $period='';
        if ($this->getFilter('report_from'))
            $period = 'custom';
        return $this->getUrl('brandadmin/adminhtml_brand/edit', array(
                    'id' => $row->getId(),
                    'store' => $store,
                    'reportSales' => 1,
                    'period' => $period,
                    'from' => str_replace("/", "_", $this->getFilter('report_from')),
                    'to' => str_replace("/", "_", $this->getFilter('report_to'))));
    }

    public function getFilter($nameFilter) {
        $filter = Mage::app()->getRequest()->getParam('filter');
        $date = Mage::helper('adminhtml')->prepareFilterString($filter);
        return isset($date[$nameFilter]) ? $date[$nameFilter] : null;
    }

}
