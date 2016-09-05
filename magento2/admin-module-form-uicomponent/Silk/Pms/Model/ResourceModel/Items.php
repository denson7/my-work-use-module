<?php
/**
 * Copyright © 2015 Silk. All rights reserved.
 */

namespace Silk\Pms\Model\ResourceModel;

class Items extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('silk_pms_items', 'id');
    }
}
