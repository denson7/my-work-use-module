<?php
namespace Maxime\Jobs\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Department post mysql resource
 */
class Job extends AbstractDb
{

//    public function getEnableStatus() {
//        return 1;
//    }
//
//    public function getDisableStatus() {
//        return 0;
//    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        // Table Name and Primary Key column
        $this->_init('maxime_job', 'entity_id');
    }

}