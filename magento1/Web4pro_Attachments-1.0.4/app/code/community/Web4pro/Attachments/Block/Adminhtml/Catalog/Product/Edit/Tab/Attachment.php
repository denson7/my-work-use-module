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
 * Attachment tab on product edit form
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Block_Adminhtml_Catalog_Product_Edit_Tab_Attachment extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid params
     *
     * @access public
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */

    public function __construct()
    {
        parent::__construct();
        $this->setId('attachment_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
        if ($this->getProduct()->getId()) {
            $this->setDefaultFilter(array('in_attachments'=>1));
        }
    }

    /**
     * prepare the attachment collection
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Adminhtml_Catalog_Product_Edit_Tab_Attachment
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('web4pro_attachments/attachment_collection');
        if ($this->getProduct()->getId()) {
            $constraint = 'related.product_id='.$this->getProduct()->getId();
        } else {
            $constraint = 'related.product_id=0';
        }
        $collection->getSelect()->joinLeft(
            array('related' => $collection->getTable('web4pro_attachments/attachment_product')),
            'related.attachment_id=main_table.entity_id AND '.$constraint,
            array('position')
        );
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * prepare mass action grid
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Adminhtml_Catalog_Product_Edit_Tab_Attachment
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
     * @return Web4pro_Attachments_Block_Adminhtml_Catalog_Product_Edit_Tab_Attachment
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_attachments',
            array(
                'header_css_class'  => 'a-center',
                'type'  => 'checkbox',
                'name'  => 'in_attachments',
                'values'=> $this->_getSelectedAttachments(),
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
            'title',
            array(
                'header' => Mage::helper('web4pro_attachments')->__('Title'),
                'align'  => 'left',
                'index'  => 'title',
                'renderer' => 'web4pro_attachments/adminhtml_helper_column_renderer_relation',
                'params' => array(
                    'id' => 'getId'
                ),
                'base_link' => 'adminhtml/attachments_attachment/edit',
            )
        );
        $this->addColumn(
            'position',
            array(
                'header'         => Mage::helper('web4pro_attachments')->__('Position'),
                'name'           => 'position',
                'width'          => 60,
                'type'           => 'number',
                'validate_class' => 'validate-number',
                'index'          => 'position',
                'editable'       => true,
            )
        );
        return parent::_prepareColumns();
    }

    /**
     * Retrieve selected attachments
     *
     * @access protected
     * @return array
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _getSelectedAttachments()
    {
        $attachments = $this->getProductAttachments();
        if (!is_array($attachments)) {
            $attachments = array_keys($this->getSelectedAttachments());
        }
        return $attachments;
    }

    /**
     * Retrieve selected attachments
     *
     * @access protected
     * @return array
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getSelectedAttachments()
    {
        $attachments = array();
        //used helper here in order not to override the product model
        $selected = Mage::helper('web4pro_attachments/product')->getSelectedAttachments(Mage::registry('current_product'));
        if (!is_array($selected)) {
            $selected = array();
        }
        foreach ($selected as $attachment) {
            $attachments[$attachment->getId()] = array('position' => $attachment->getPosition());
        }
        return $attachments;
    }

    /**
     * get row url
     *
     * @access public
     * @param Web4pro_Attachments_Model_Attachment
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
            '*/*/attachmentsGrid',
            array(
                'id'=>$this->getProduct()->getId()
            )
        );
    }

    /**
     * get the current product
     *
     * @access public
     * @return Mage_Catalog_Model_Product
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Add filter
     *
     * @access protected
     * @param object $column
     * @return Web4pro_Attachments_Block_Adminhtml_Catalog_Product_Edit_Tab_Attachment
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_attachments') {
            $attachmentIds = $this->_getSelectedAttachments();
            if (empty($attachmentIds)) {
                $attachmentIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$attachmentIds));
            } else {
                if ($attachmentIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$attachmentIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
}
