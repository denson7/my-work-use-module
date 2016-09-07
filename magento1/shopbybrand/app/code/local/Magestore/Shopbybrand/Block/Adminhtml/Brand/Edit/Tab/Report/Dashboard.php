<?php

class Magestore_Shopbybrand_Block_Adminhtml_Brand_Edit_Tab_Report_Dashboard extends Mage_Adminhtml_Block_Template
{	
	public function __construct(){
		parent::__construct();
		$this->setTemplate('shopbybrand/report/dashboard.phtml');
	}
	
	protected function _prepareLayout(){
        $this->setChild('report-graph',$this->getLayout()->createBlock('shopbybrand/adminhtml_brand_edit_tab_report_graph'));
		parent::_prepareLayout();
	}
    public function getDataLifetime(){
        $brand=Mage::getModel('shopbybrand/brand')->load($this->getRequest()->getParam('id'));
        $productIDs = explode(',', $brand->getData('product_ids'));
        $collection = Mage::getResourceModel('sales/order_item_collection')
                ->addFieldToFilter('product_id',array('in'=>$productIDs));
        if (version_compare(Mage::getVersion(), '1.4.1.1', '>=')) {
            $sfog = Mage::getModel('core/resource')->getTableName('sales_flat_order_grid');
        } else {
            $sfog = Mage::getModel('core/resource')->getTableName('sales_order');
        }
        $collection->getSelect()
                    ->join(array('sfog'=>$sfog),'main_table.order_id = sfog.entity_id AND sfog.status = "complete"');
        $store = Mage::app()->getRequest()->getParam('store');
        if ($store) {
            $collection->addFieldtoFilter('main_table.store_id', $store);
        }
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array(
            'totals_money' => 'SUM(main_table.base_row_total)',
            'totals_qty' => 'SUM(main_table.qty_ordered)',
        ));
        if($collection->getSize())
        return $collection->getFirstItem()->getData();
        return array(
            'totals_money'=>0,
            'totals_qty'=>0
        );
    }
    public function getDateFormat() {
        return $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);//FORMAT_TYPE_MEDIUM
    }
	public function getLocale() {
        if (!$this->_locale) {
            $this->_locale = Mage::app()->getLocale();
        }
        return $this->_locale;
    }
    public function getReportFrom(){
        return str_replace("_", "/", $this->getRequest()->getParam('from'));
    }
    public function getReportTo(){
        return str_replace("_", "/", $this->getRequest()->getParam('to'));
    }
}