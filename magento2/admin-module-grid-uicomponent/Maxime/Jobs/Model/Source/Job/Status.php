<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-4
 * Time: 下午5:25
 */
namespace Maxime\Jobs\Model\Source\Job;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Maxime\Jobs\Model\Job
     */
    protected $_job;

    /**
     * Constructor
     *
     * @param \Maxime\Jobs\Model\Job $job
     */
    public function __construct(\Maxime\Jobs\Model\Job $job)
    {
        $this->_job = $job;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->_job->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
