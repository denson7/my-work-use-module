<?php
namespace Maxime\Jobs\Model;

use \Magento\Framework\Model\AbstractModel;

class Department extends AbstractModel
{
    const DEPARTMENT_ID = 'entity_id'; // We define the id fieldname

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'jobs'; // parent value is 'core_abstract'

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'department'; // parent value is 'object'

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = self::DEPARTMENT_ID; // parent value is 'id'

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Maxime\Jobs\Model\ResourceModel\Department');
    }

}