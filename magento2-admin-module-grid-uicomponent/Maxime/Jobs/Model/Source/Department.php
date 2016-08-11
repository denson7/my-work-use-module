<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-4
 * Time: 下午5:30
 */
namespace Maxime\Jobs\Model\Source;

class Department implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Maxime\Jobs\Model\Department
     */
    protected $_department;

    /**
     * Constructor
     *
     * @param \Maxime\Jobs\Model\Department $department
     */
    public function __construct(\Maxime\Jobs\Model\Department $department)
    {
        $this->_department = $department;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $departmentCollection = $this->_department->getCollection()
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('name');
        foreach ($departmentCollection as $department) {
            $options[] = [
                'label' => $department->getName(),
                'value' => $department->getId(),
            ];
        }
        return $options;
    }
}