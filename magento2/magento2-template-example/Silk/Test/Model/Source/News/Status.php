<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-4
 * Time: 下午5:25
 */
namespace Silk\Test\Model\Source\News;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Silk\Test\Model\News
     */
    protected $_news;

    /**
     * Constructor
     *
     * @param \Silk\Test\Model\News $news
     */
    public function __construct(\Silk\Test\Model\News $news)
    {
        $this->_news = $news;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_news->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
