<?php

class Inchoo_Productfilter_Block_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
	
	const CONFIG_ENABLED = 'attribute_filter_section/settings_group/use_attribute_filter';
	const CONFIG_ATTRIBUTE_CODE = 'attribute_filter_section/settings_group/filter_attribute';

    protected function _prepareCollection()
    {
    	if(Mage::getStoreConfig(self::CONFIG_ENABLED)) {
    		$store = $this->_getStore();
    		$collection = Mage::getModel('catalog/product')->getCollection()
    		->addAttributeToSelect('sku')
    		->addAttributeToSelect('name')
    		->addAttributeToSelect('attribute_set_id')
    		->addAttributeToSelect('type_id')
    		->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');

    		if ($store->getId()) {
    			//$collection->setStoreId($store->getId());
    			$adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
    			$collection->addStoreFilter($store);
    			$collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore);
    			$collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
    			$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
    			$collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());
    			$collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
    		}
    		else {
    			$collection->addAttributeToSelect('price');
    			$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
    			$collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
    		}
    		
    		//this extension specific
    		$attributeCode = Mage::getStoreConfig(self::CONFIG_ATTRIBUTE_CODE);
    		$collection->joinAttribute($attributeCode, 'catalog_product/'.$attributeCode, 'entity_id', null, 'left');
    		$collection->addAttributeToSelect($attributeCode);

    		$this->setCollection($collection);

    		Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    		$this->getCollection()->addWebsiteNamesToResult();
    		//this extension specific end
    	} else {
    		parent::_prepareCollection();
    	}
    	return $this;

    }
    

    protected function _prepareColumns()
    {  	
    	if(Mage::getStoreConfig(self::CONFIG_ENABLED)) {
    		$this->addColumn('entity_id',
    		array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
    		));
    		$this->addColumn('name',
    		array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
    		));

    		$store = $this->_getStore();
    		if ($store->getId()) {
    			$this->addColumn('custom_name',
    			array(
                    'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
    			));
    		}

    		$this->addColumn('type',
    		array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
    		));


    		// this extension specific

    		$attributeCodeConfig = Mage::getStoreConfig(self::CONFIG_ATTRIBUTE_CODE);

    		$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', $attributeCodeConfig);

    		$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
    		$attributeData = $attribute->getData();
    		$frontEndLabel = $attributeData['frontend_label'];

    		$attributeOptions = $attribute->getSource()->getAllOptions();
    		$b = new Mage_Catalog_Model_Resource_Eav_Attribute();
    		$attributeOptions2 = array();
    		foreach ($attributeOptions as $value) {
    			if(!empty($value['value'])) {
    				$attributeOptions2[$value['value']] = $value['label'];
    			}
    				
    		}
    		

    		if(count($attributeOptions2) > 0) {
    			$this->addColumn($attributeCodeConfig,
    				array(
                		'header'=> Mage::helper('catalog')->__($frontEndLabel),
                		'width' => '80px',
                		'index' => $attributeCodeConfig,
                		'type'  => 'options',
                		'options' => $attributeOptions2,

    			));
    		} else {
    			$this->addColumn($attributeCodeConfig,
    				array(
                		'header'=> Mage::helper('catalog')->__($frontEndLabel),
                		'width' => '80px',
                		'index' => $attributeCodeConfig,

    			));
    		}
    		

    		// this extension specific end


    		$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
    		->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
    		->load()
    		->toOptionHash();


    		$this->addColumn('set_name',
    		array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
    		));
    		$this->addColumn('sku',
    		array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
    		));

    		$store = $this->_getStore();
    		$this->addColumn('price',
    		array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
    		));

    		$this->addColumn('qty',
    		array(
                'header'=> Mage::helper('catalog')->__('Qty'),
                'width' => '100px',
                'type'  => 'number',
                'index' => 'qty',
    		));

    		$this->addColumn('visibility',
    		array(
                'header'=> Mage::helper('catalog')->__('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
    		));

    		$this->addColumn('status',
    		array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
    		));

    		if (!Mage::app()->isSingleStoreMode()) {
    			$this->addColumn('websites',
    			array(
                    'header'=> Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
    			));
    		}

    		$this->addColumn('action',
    		array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
    		array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
    		),
                        'field'   => 'id'
                        )
                        ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                        ));

        $this->addRssList('rss/catalog/notifystock', Mage::helper('catalog')->__('Notify Low Stock RSS'));
        Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    	} else {
    		parent::_prepareColumns();
    	}
        
        return $this;
    }
}