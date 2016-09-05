<?php
namespace Silk\July\Block\Adminhtml\Form;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Silk\July\Model\EmployeeFactory
     */
    protected $_employeeFactory;

    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Silk\July\Model\EmployeeFactory $employeeFactory
     * @param \Silk\July\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Silk\July\Model\EmployeeFactory $employeeFactory,
        \Silk\July\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_employeeFactory = $employeeFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('e_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_employeeFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'e_id',
            [
                'header' => __('Employee ID'),
                'type' => 'number',
                'index' => 'e_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'name'=>'e_id'
            ]
        );
        $this->addColumn(
            'e_name',
            [
                'header' => __('Employee Name'),
                'index' => 'e_name',
                'class' => 'xxx',
                'name'=>'e_name'
            ]
        );

        $this->addColumn(
            'e_address',
            [
                'header' => __('Employee Address'),
                'index' => 'e_address',
                'class' => 'xxx',
                'name'=>'e_address'
            ]
        );

        $this->addColumn(
            'e_profile_picture',
            [
                'header' => __('Employee Profile Picture'),
                'index' => 'e_profile_picture',
                'class' => 'xxx',
                'name'=>'e_profile_picture',
                'renderer'  => '\Silk\July\Block\Adminhtml\Form\Grid\Renderer\Image'
            ]
        );

        $this->addColumn(
            'is_active',
            [
                'header' => __('Active'),
                'index' => 'is_active',
                'type' => 'options',
                'name'=>'is_active',
                'options' => $this->_status->getOptionArray()
            ]
        );


        $this->addColumn(
            'edit',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit'
                        ],
                        'field' => 'e_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
        //添加导出功能.需要在控制器中新增exportCsv,exportXml,exportExcel这三个方法,可以参考Magestore_BannerSlider
        //$this->addExportType('*/*/exportCsv', __('CSV'));
        //$this->addExportType('*/*/exportXml', __('XML'));
        //$this->addExportType('*/*/exportExcel', __('Excel'));
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        
        $this->setMassactionIdField('e_id');
        //$this->getMassactionBlock()->setTemplate('Silk_July::form/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('silk_emp');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('july/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('july/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('july/*/grid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'july/*/edit',
            ['e_id' => $row->getId()]
        );
    }
}