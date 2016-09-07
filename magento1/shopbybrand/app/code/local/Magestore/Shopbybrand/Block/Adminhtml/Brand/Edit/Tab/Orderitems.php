<?php

class Magestore_Shopbybrand_Block_Adminhtml_Brand_Edit_Tab_Orderitems extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('order_items_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass() {
        return 'sales/order_item_collection';
    }

    protected function _prepareCollection() {
        $brand = Mage::getModel('shopbybrand/brand')->load($this->getRequest()->getParam('id'));
        $productIDs = explode(',', $brand->getData('product_ids'));
        $collection = Mage::getResourceModel($this->_getCollectionClass())
                ->addFieldToFilter('product_id', array('in' => $productIDs));
        if (version_compare(Mage::getVersion(), '1.4.1.1', '>=')) {
            $sfog = Mage::getModel('core/resource')->getTableName('sales_flat_order_grid');
            $collection->getSelect()
                    ->join(array('sfog'=>$sfog),'main_table.order_id = sfog.entity_id AND sfog.status = "complete"',array('billing_name','shipping_name'));
        } else {
            $sfog = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToSelect('*')
                    ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
                    ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
                    ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                    ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                    ->addExpressionAttributeToSelect('billing_name', 'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})', array('billing_firstname', 'billing_lastname'))
                    ->addExpressionAttributeToSelect('shipping_name', 'CONCAT({{shipping_firstname}},  IFNULL(CONCAT(\' \', {{shipping_lastname}}), \'\'))', array('shipping_firstname', 'shipping_lastname'))
                    ->getSelect()
                    ->assemble();
            $collection->getSelect()
                ->joinleft(array('sfog'=> new Zend_Db_Expr("($sfog)")),'main_table.order_id = sfog.entity_id AND sfog.status = "complete"',array('billing_name','shipping_name'));
        }
        

        $store = Mage::app()->getRequest()->getParam('store');
        if ($store) {
            $collection->addFieldtoFilter('main_table.store_id', $store);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('item_id', array(
            'header' => Mage::helper('sales')->__('Item ID'),
            'width' => '10px',
            'type' => 'text',
            'index' => 'item_id',
        ));

        $this->addColumn('item_name', array(
            'header' => Mage::helper('sales')->__('Product Name'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'name',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('sales')->__('Purchased from Store'),
                'index' => 'store_id',
                'type' => 'store',
                'filter_index' => 'main_table.store_id',
                'store_view' => true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchase Date'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('qty_ordered', array(
            'header' => Mage::helper('sales')->__('Qty'),
            'width' => '80px',
            'type' => 'number',
            'index' => 'qty_ordered',
        ));

        $this->addColumn('base_row_total', array(
            'header' => Mage::helper('sales')->__('Row Total (Base)'),
            'index' => 'base_row_total',
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE)
        ));

        $this->addExportType('*/*/exportCsvOrderItems', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcelOrderItems', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/orderItemsGrid', array('_current' => true));
    }

}
