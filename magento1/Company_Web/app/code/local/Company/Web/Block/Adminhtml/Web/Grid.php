<?php

class Company_Web_Block_Adminhtml_Web_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('webGrid');
      $this->setDefaultSort('web_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('web/web')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('web_id', array(
          'header'    => Mage::helper('web')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'web_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('web')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));


      $this->addColumn(
          'filename',
          array(
              'header' => Mage::helper('web')->__('Image'),
              'align'  => 'left',
              'width' => '100px',
              'index'  => 'filename',
              'renderer' =>'Company_Web_Block_Adminhtml_Web_Grid_Renderer_Image',
          )
      );

      $this->addColumn('content', array(
			'header'    => Mage::helper('web')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('web')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('web')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('web')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('web')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('web')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('web_id');
        $this->getMassactionBlock()->setFormFieldName('web');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('web')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('web')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('web/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('web')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('web')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}


/**

 */