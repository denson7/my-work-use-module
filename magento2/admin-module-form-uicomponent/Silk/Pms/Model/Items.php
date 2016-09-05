<?php
/**
 * Copyright © 2015 Silk. All rights reserved.
 */

namespace Silk\Pms\Model;

class Items extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Silk\Pms\Model\ResourceModel\Items');
    }
}
