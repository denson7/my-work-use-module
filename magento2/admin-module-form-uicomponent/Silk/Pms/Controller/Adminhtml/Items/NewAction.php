<?php
/**
 * Copyright © 2015 Silk. All rights reserved.
 */

namespace Silk\Pms\Controller\Adminhtml\Items;

class NewAction extends \Silk\Pms\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
