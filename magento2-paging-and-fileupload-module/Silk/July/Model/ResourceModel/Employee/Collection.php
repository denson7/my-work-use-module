<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\July\Model\ResourceModel\Employee;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Silk\July\Model\Employee', 'Silk\July\Model\ResourceModel\Employee');
        //$this->_map['fields']['page_id'] = 'main_table.page_id';
    }
 
    
}
