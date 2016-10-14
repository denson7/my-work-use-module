<?php

class J2t_Rewardpoints_Block_Adminhtml_Catalogpointrules_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('rule_id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('rewardpoints/catalogpointrules')
            ->getResourceCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $model = Mage::getModel('rewardpoints/catalogpointrules');
        $this->addColumn('rule_id', array(
            'header'    => Mage::helper('rewardpoints')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'rule_id',
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('rewardpoints')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ));

        $this->addColumn('action_type', array(
            'header'    => Mage::helper('rewardpoints')->__('Action type'),
            'align'     =>'left',
            'index'     => 'action_type',
            'type'      => 'options',
            'options'   => $model->ruleActionTypesToArray(),
        ));
        
        $this->addColumn('status', array(
            'header'    => Mage::helper('rewardpoints')->__('Status'),
            'align'     =>'left',
            'width'     => '100px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));
        
        /*$this->addColumn('sort_order', array(
            'header'    => Mage::helper('rewardpoints')->__('Priority'),
            'align'     =>'left',
            'index'     => 'sort_order',
            'width'     => '60px',
        ));*/

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getRuleId()));
    }

}
