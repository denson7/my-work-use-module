<?php

$installer = $this;
$installer->startSetup();

$conn=$installer->getConnection();
/* @var $conn Varien_Db_Adapter_Pdo_Mysql */

$conn->addColumn($installer->getTable('sales/order'), 
        'cdr_order_comment',
        array(
        'nullable'  => true,
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => '64k',
        'comment'   => 'Order Comment'
        ));

$this->endSetup();