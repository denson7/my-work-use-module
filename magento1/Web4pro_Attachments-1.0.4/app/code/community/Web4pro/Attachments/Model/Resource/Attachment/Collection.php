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
 * Attachment collection resource model
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Model_Resource_Attachment_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected $_joinedFields = array();

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('web4pro_attachments/attachment');
    }

    /**
     * get attachments as array
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _toOptionArray($valueField='entity_id', $labelField='title', $additional=array())
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    /**
     * get options hash
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @return array
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _toOptionHash($valueField='entity_id', $labelField='title')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * add the product filter to collection
     *
     * @access public
     * @param mixed (Mage_Catalog_Model_Product|int) $product
     * @return Web4pro_Attachments_Model_Resource_Attachment_Collection
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function addProductFilter($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product = $product->getId();
        }
        if (!isset($this->_joinedFields['product'])) {
            $this->getSelect()->join(
                array('related_product' => $this->getTable('web4pro_attachments/attachment_product')),
                'related_product.attachment_id = main_table.entity_id',
                array('position')
            );
            $this->getSelect()->where('related_product.product_id = ?', $product);
            $this->_joinedFields['product'] = true;
        }
        return $this;
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @access public
     * @return Varien_Db_Select
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }
}
