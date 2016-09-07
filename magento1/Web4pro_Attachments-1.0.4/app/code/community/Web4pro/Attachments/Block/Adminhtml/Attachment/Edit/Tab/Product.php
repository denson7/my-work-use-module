<?php
/**
 * WEB4PRO - Creating profitable online stores
 * 
 * @author WEB4PRO <srepin@corp.web4pro.com.ua>
 * @category  WEB4PRO
 * @package   Web4pro_Attachments
 * @copyright Copyright (c) 2015 WEB4PRO (http://www.web4pro.net)
 * @license   http://www.web4pro.net/license.txt
 */
/**
 * Attachment - product relation edit block
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Block_Adminhtml_Attachment_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid params
     *
     * @access protected
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        if ($this->getAttachment()->getId()) {
            $this->setDefaultFilter(array('in_products'=>1));
        }
    }

    /**
     * prepare the product collection
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Adminhtml_Attachment_Edit_Tab_Product
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect('price');
        $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
        $collection->joinAttribute('product_name', 'catalog_product/name', 'entity_id', null, 'left', $adminStore);
        if ($this->getAttachment()->getId()) {
            $constraint = '{{table}}.attachment_id='.$this->getAttachment()->getId();
        } else {
            $constraint = '{{table}}.attachment_id=0';
        }
        $collection->joinField(
            'position',
            'web4pro_attachments/attachment_product',
            'position',
            'product_id=entity_id',
            $constraint,
            'left'
        );
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * prepare mass action grid
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Adminhtml_Attachment_Edit_Tab_Product
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * prepare the grid columns
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Adminhtml_Attachment_Edit_Tab_Product
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            array(
                'header_css_class'  => 'a-center',
                'type'  => 'checkbox',
                'name'  => 'in_products',
                'values'=> $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'entity_id'
            )
        );
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('catalog')->__('ID'),
                'width'         => '1',
                'align'  => 'left',
                'index'  => 'entity_id',
            )
        );
        $this->addColumn(
            'product_name',
            array(
                'header'    => Mage::helper('catalog')->__('Name'),
                'align'     => 'left',
                'index'     => 'product_name',
                'renderer'  => 'web4pro_attachments/adminhtml_helper_column_renderer_relation',
                'params'    => array(
                    'id'    => 'getId'
                ),
                'base_link' => 'adminhtml/catalog_product/edit',
            )
        );
        $this->addColumn(
            'sku',
            array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'align'  => 'left',
                'index'  => 'sku',
            )
        );
        $this->addColumn(
            'price',
            array(
                'header'        => Mage::helper('catalog')->__('Price'),
                'type'          => 'currency',
                'width'         => '1',
                'currency_code' => (string)Mage::getStoreConfig(
                    Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE
                ),
                'index'         => 'price'
            )
        );
        $this->addColumn(
            'position',
            array(
                'header'         => Mage::helper('catalog')->__('Position'),
                'name'           => 'position',
                'width'          => 60,
                'type'           => 'number',
                'validate_class' => 'validate-number',
                'index'          => 'position',
                'editable'       => true,
            )
        );
    }

    /**
     * Retrieve selected products
     *
     * @access protected
     * @return array
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getAttachmentProducts();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedProducts());
        }
        return $products;
    }

    /**
     * Retrieve selected products
     *
     * @access protected
     * @return array
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getSelectedProducts()
    {
        $products = array();
        $selected = Mage::registry('current_attachment')->getSelectedProducts();
        if (!is_array($selected)) {
            $selected = array();
        }
        foreach ($selected as $product) {
            $products[$product->getId()] = array('position' => $product->getPosition());
        }
        return $products;
    }

    /**
     * get row url
     *
     * @access public
     * @param Web4pro_Attachments_Model_Product
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getRowUrl($item)
    {
        return '#';
    }

    /**
     * get grid url
     *
     * @access public
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/productsGrid',
            array(
                'id' => $this->getAttachment()->getId()
            )
        );
    }

    /**
     * get the current attachment
     *
     * @access public
     * @return Web4pro_Attachments_Model_Attachment
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getAttachment()
    {
        return Mage::registry('current_attachment');
    }

    /**
     * Add filter
     *
     * @access protected
     * @param object $column
     * @return Web4pro_Attachments_Block_Adminhtml_Attachment_Edit_Tab_Product
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
}
