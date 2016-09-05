<?php

$installer = $this;
$installer->startSetup();

$conn=$installer->getConnection();
/* @var $conn Varien_Db_Adapter_Pdo_Mysql */

$conn->addColumn($installer->getTable('sales/order_grid'), 
        'cdr_order_comment',
        array(
        'nullable'  => true,
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => '64k',
        'comment'   => 'Order Comment'
        ));

$select = $this->getConnection()->select();
$select->join(
    array('order'=>$this->getTable('sales/order')),
    $this->getConnection()->quoteInto(
        'order.entity_id = order_grid.entity_id'
    ),
    array('cdr_order_comment' => 'cdr_order_comment')
);
$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $this->getTable('sales/order_grid'))
    )
);

$this->endSetup();