<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Shopbybrand Resource Collection Model
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Model_Mysql4_Brand_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_storeId = null;
    protected $_addedTable = array();
    protected $_isGroupSql = false;
    protected $_storeField = array(
        'name',
        'is_featured',
        'page_title',
        'meta_keywords',
        'meta_description',
        'short_description',
        'description',
        'status',
//        'position_brand',
    );
    
    public function getStoreId(){
        return $this->_storeId;
    }
    
    public function setStoreId($storeId,$array = null){
        $this->_storeId = $storeId;
        if($this->_storeId){
            $storeField = (isset($array)&&count($array))?$array:$this->_storeField;
            foreach ($storeField as $value) {
                $brandValue = Mage::getModel('shopbybrand/brandvalue')->getCollection()
                    ->addFieldToFilter('store_id',$storeId)
                    ->addFieldToFilter('attribute_code',$value)
                    ->getSelect()
                    ->assemble();
                $this->getSelect()
                    ->joinLeft(
                        array(
                            'brand_value_'.$value => new Zend_Db_Expr("($brandValue)")), 
                            'main_table.brand_id = brand_value_'.$value.'.brand_id',
                            array($value => 'IF(brand_value_'.$value.'.value IS NULL,main_table.'.$value.',brand_value_'.$value.'.value)'));
            }
        }
        return $this;
    } 
    protected function _before3Load()
    {
        $storeId = $this->getStoreId();
        if($storeId){
            $brandValueName = Mage::getModel('shopbybrand/brandvalue')->getCollection()
                    ->addFieldToFilter('store_id',$storeId)
                    ->addFieldToFilter('attribute_code','name')
                    ->getSelect()
                    ->assemble();
            $this->getSelect()
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->joinLeft(
                        array('brand_value_name'=>new Zend_Db_Expr("($brandValueName)")), 
                        'main_table.brand_id = brand_value_name.brand_id', 
                        array(
                            'name'=>'IF(brand_value_name.value IS NULL,main_table.name,brand_value_name.value)',
                        )
                    )
                    ->columns('*')
                ;
        }
        return $this;
    }


    public function setIsGroupCountSql($value) {
        $this->_isGroupSql = $value;
        return $this;
    }

    public function getSelectCountSql() {
        if ($this->_isGroupSql) {
            $this->_renderFilters();
            $countSelect = clone $this->getSelect();
            $countSelect->reset(Zend_Db_Select::ORDER);
            $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
                $countSelect->reset(Zend_Db_Select::GROUP);
                $countSelect->distinct(true);
                $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
                $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
            } else {
                $countSelect->columns('COUNT(*)');
            }
            return $countSelect;
        }
        return parent::getSelectCountSql();
    }
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('shopbybrand/brand');
    }
    protected function _afterLoad(){
    	return $this;
    }
    
    public function addFieldToFilter($field, $condition=null) {
        $attributes = array(
            'name',
            'is_featured',
            'page_title',
            'meta_keywords',
            'meta_description',
            'short_description',
            'description',
            'status'
        );
        $storeId = $this->getStoreId();
        if (in_array($field, $attributes) && $storeId) {
            if (!in_array('brand_'.$field, $this->_addedTable)) {
                $this->getSelect()
                    ->joinLeft(array('brand_'.$field => $this->getTable('shopbybrand/brandvalue')),
                        "main_table.brand_id = brand_$field.brand_id" .
                        " AND brand_$field.store_id = $storeId" .
                        " AND brand_$field.attribute_code = '$field'",
                        array()
                    );
                $this->_addedTable[] = 'brand_'.$field;
            }
            return parent::addFieldToFilter("IF(brand_$field.value IS NULL, main_table.$field, brand_$field.value)", $condition);
        }
        if ($field == 'store_id') {
            $field = 'main_table.store_id';
        }
        $field = $this->_getMappedField($field);
        if (strpos($field, 'SUM') === false && strpos($field, 'COUNT') === false) {
            $this->_select->where($this->_getConditionSql($field, $condition), null,null);// Varien_Db_Select::TYPE_CONDITION);
        } else {
            $this->_select->having($this->_getConditionSql($field, $condition), null,null);// Varien_Db_Select::TYPE_CONDITION);
        }
        return $this;
    }
    
    public function getAllCategories(){
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns('main_table.category_ids');
        $idsSelect->resetJoinLeft();
        return $this->getConnection()->fetchCol($idsSelect);
    }
    public function getAllField($name){
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns('main_table.'.$name);
        $idsSelect->resetJoinLeft();
        return $this->getConnection()->fetchCol($idsSelect);
    }
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false) {
        $dateEnd = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);

        switch ($range) {
            case '24h':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subDay(1);
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;

            case '1m':
                $dateStart->setDay(1);
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateStart;
                $dateEnd = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(',', '1,1');
                $startMonth = isset($startMonthDay[0]) ? (int) $startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int) $startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                if ($range == '2y') {
                    $dateStart->subYear(1);
                }
                $dateEnd->setDay(1);
                $dateEnd->addMonth(1);
                $dateEnd->subDay(1);
                break;
        }
        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');

        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
        }
    }
    protected function _getRangeExpressionForAttribute($range, $attribute) {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), $expression);
    }

    protected function _getRangeExpression($range) {
        switch ($range) {
            case '24h':
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d %H:00\')';
                break;
            case '7d':
            case '1m':
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d\')';
                break;
            case 'custom':
                $rangeCustom=Mage::helper('shopbybrand')->getSubtractTime();
                if($rangeCustom=='y'){
                    $expression = 'DATE_FORMAT({{attribute}}, \'%Y\')';
                } elseif ($rangeCustom=='m') {
                    $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m\')';
                }elseif ($rangeCustom=='d') {
                    $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d\')';
                }else {
                    $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d %H:00\')';
                }
                break;
            case '1y':
            case '2y':
            
            default:
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m\')';
                break;
        }

        return $expression;
    }
    public function prepareReportBrandSales($range, $customStart, $customEnd) {
        $brandId=Mage::app()->getRequest()->getParam('id');
        $this->addFieldToFilter('brand_id',$brandId);
        if (version_compare(Mage::getVersion(), '1.4.1.1', '>=')) {
            $sfog = Mage::getModel('core/resource')->getTableName('sales_flat_order_grid');
        } else {
            $sfog = Mage::getModel('core/resource')->getTableName('sales_order');
        }
        $this->getSelect()
                ->join(array('sfoi' => Mage::getModel('core/resource')->getTableName('sales_flat_order_item')), 'FIND_IN_SET(sfoi.product_id, main_table.product_ids)', array('qty_ordered', 'base_row_total',))
                ->join(array('sfog'=>$sfog),'sfoi.order_id = sfog.entity_id AND sfog.status = "complete"');
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $this->getSelect()
                ->columns(array(
                    'brand_qty_ordered' => 'SUM(IF( qty_ordered > 0, qty_ordered, 0 ))',
                    'brand_base_row_total' => 'SUM(IF( base_row_total > 0, base_row_total, 0 ))/'.Mage::registry('rate_chart_money'),
                ));
        $dateRange = $this->getDateRange($range, $customStart, $customEnd);
        $this->getSelect()->columns(array('range' => $this->_getRangeExpressionForAttribute($range, 'sfoi.created_at')))
                ->order('range', Zend_Db_Select::SQL_ASC)
                ->group('range');
        $this->addFieldToFilter('sfoi.created_at', $dateRange);
        return $this;
    }
    
    public function getCategoryIdsFromProducts($table){
         $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns($table.'.category_id');
        $idsSelect->resetJoinLeft();
        return $this->getConnection()->fetchCol($idsSelect);
    }
}