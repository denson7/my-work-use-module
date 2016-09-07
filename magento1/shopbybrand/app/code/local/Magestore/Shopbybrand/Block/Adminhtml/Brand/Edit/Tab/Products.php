<?php

class Magestore_Shopbybrand_Block_Adminhtml_Brand_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid
{
    
     public function __construct()
    {
        parent::__construct();
        $this->setId('productsgrid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getBrand()->getId()) {
            $this->setDefaultFilter(array('in_products'=>1));
        }
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                /*edit by Cuong*/
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds))
                        /**
                     * join them truong position trong bang brand_position
                     */
                    ->getSelect()
                    ->joinLeft(
                        array('brand_products'=>Mage::getModel('core/resource')->getTableName('brand_products')),
                        "e.entity_id = brand_products.product_id",
                        array(
                            'position' => 'brand_products.position',
//                            'is_featured' => 'brand_products.is_featured'
                        )
                    );
                /*endedit by Cuong*/
            } else {
                if($productIds) {
                    /*edit by Cuong*/
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds))
                        /**
                     * join them truong position trong bang brand_position
                     */
                    ->getSelect()
                    ->joinLeft(
                        array('brand_products'=>'brand_products'),
                        "e.entity_id = brand_products.product_id",
                        array(
                            'position' => 'brand_products.position',
//                            'is_featured' => 'brand_products.is_featured'
                        )
                    );
                    /*endedit by Cuong*/
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    public function getProductTypeIds(){
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attributeCode);
        $applyTo = $attributeModel->getData('apply_to');
        $types = Mage::getSingleton('catalog/product_type')->getOptionArray();
		if(is_null($applyTo)){
			return $types;
		}
		$productTypes = explode(',', $applyTo);
        $newTypes = array();
        foreach($productTypes as $type){
            if(key_exists($type, $types)){
                $newTypes[$type] = $types[$type];
            }
        }
        return $newTypes;
    }
    
    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
     protected function _prepareCollection(){
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        $types = array_keys($this->getProductTypeIds());
        if(count($types)){
            $collection->addFieldToFilter('type_id', array('in'=>$types));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
	
        
    }
    /*end edit by cuong*/
    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
       $this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'in_products',
            'values'            => $this->_getSelectedProducts(),
            'align'             => 'center',	
            'index'             => 'entity_id'
        ));
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('shopbybrand')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('shopbybrand')->__('Name'),
            'index'     => 'name'
        ));
        /*edit by cuong*/
        $productTypes = $this->getProductTypeIds();
        $this->addColumn('type', array(
            'header'    => Mage::helper('shopbybrand')->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => $productTypes,
        ));
        /*end edit by cuong*/
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('shopbybrand')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('product_status', array(
            'header'    => Mage::helper('shopbybrand')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('shopbybrand')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('shopbybrand')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('shopbybrand')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));
        /* add by cuong*/
        $this->addColumn('is_featured', array(
            'header_css_class'  => 'a-center',
            'width'             => "30",
            'align'             => 'center',
            'header'            => Mage::helper('shopbybrand')->__('Featured'),
            'type'              => 'checkbox',
            'field_name'        => 'featuredproducts[]',
            'values'            => $this->_getFeaturedProducts(),
            'index'             => 'entity_id',
            'filter'            => false,
        ));
        /* end add by cuong*/
        $this->addColumn('position', array(
            'header'            => Mage::helper('shopbybrand')->__('Sort Order'),
            'name'              => 'position',    
            'index'             => 'position',
            'width'             => '80px',
            'width'             => 100,
            'editable'          => true,
            'filter'            => false,
       ));

        return parent::_prepareColumns();
    }
    
    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/productGrid', array('_current' => true,'id'=>$this->getRequest()->getParam('id')));
    }

    /**
     * Retrieve selected related products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getBrandProducts();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedProducts());
        }
        return $products;
    }
    /*add by cuong*/
    protected function _getFeaturedProducts()
    {
        $brand = $this->getBrand();
        $productIds = explode(',', $brand->getData('product_ids'));
        $brandProductCollection = Mage::getModel('shopbybrand/brandproducts')->getCollection()
                ->addFieldToFilter('product_id', array('in' => $productIds))
                ->addFieldToFilter('is_featured', 1);
        $featuredProductIds = array();
        foreach ($brandProductCollection as $item){
            array_push($featuredProductIds,$item->getProductId());
        }
        return $featuredProductIds;
    }
    /*end add by cuong*/

    /**
     * Retrieve related products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $products = array();
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        /*edit by Cuong*/
        $PositionsArray = Mage::getModel('shopbybrand/brandproducts')->getPositionsArray();
//        $FeaturedArray = Mage::getModel('shopbybrand/brandproducts')->getFeaturedArray();
        $brand = $this->getBrand();
        $productIds = $brand->getProductIds();
        
        $collection = Mage::getModel('catalog/product')            
			->getCollection()            
			->addAttributeToSelect('*')
			//->addAttributeToFilter('manufacturer', array('notnull'=>true))
		;        
		$types = array_keys($this->getProductTypeIds());
		if(count($types)){    
			$collection->addFieldToFilter('type_id', array('in'=>$types));
        }
		$productIds = array_intersect($productIds, $collection->getAllIds());
                
        foreach ($productIds as $productId){
            $products[$productId] = array(
//                'is_featured'     => $FeaturedArray[$productId],
                'position'        => $PositionsArray[$productId],
                                          );
        }
        /*end edit by cuong*/
        return $products;
    }
       
    public function getBrand(){
        $brandId=$this->getRequest()->getParam('id');
        $brand = Mage::getModel('shopbybrand/brand')->load($brandId);
        return $brand;
    }
}
