<?php
namespace Silk\Test\Model\ResourceModel\News;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = \Silk\Test\Model\News::NEWS_ID;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Silk\Test\Model\News', 'Silk\Test\Model\ResourceModel\News');
    }


}