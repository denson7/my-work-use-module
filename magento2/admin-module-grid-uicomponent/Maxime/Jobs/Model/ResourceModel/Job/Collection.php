<?php
namespace Maxime\Jobs\Model\ResourceModel\Job;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = \Maxime\Jobs\Model\Job::JOB_ID;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Maxime\Jobs\Model\Job', 'Maxime\Jobs\Model\ResourceModel\Job');
    }

    public function addStatusFilter($job, $department){
        $this->addFieldToSelect('*')
            ->addFieldToFilter('status', $job->getEnableStatus())
            ->join(
                array('department' => $department->getResource()->getMainTable()),
                'main_table.department_id = department.'.$department->getIdFieldName(),
                array('department_name' => 'name')
            );

        return $this;
    }
}