<?php
namespace Silk\Test\Model;

use \Magento\Framework\Model\AbstractModel;

class News extends AbstractModel
{
    const NEWS_ID = 'entity_id'; // We define the id fieldname

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'test';

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'news';

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = self::NEWS_ID;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Silk\Test\Model\ResourceModel\News');
    }

    public function getEnableStatus() {
        return 1;
    }

    public function getDisableStatus() {
        return 0;
    }

    public function getAvailableStatuses() {
        return [$this->getDisableStatus() => __('Disabled'), $this->getEnableStatus() => __('Enabled')];
    }
}