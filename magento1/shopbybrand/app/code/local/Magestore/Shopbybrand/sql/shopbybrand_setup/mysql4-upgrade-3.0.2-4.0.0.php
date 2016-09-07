<?php
$installer = $this;
$installer->startSetup();
$installer->run(
    "
    ALTER TABLE {$this->getTable('brand')} ADD `position_brand` INT(11) NOT NULL AFTER `updated_time`;
    ALTER TABLE {$this->getTable('brand')} ADD `banner_url` varchar(200) NOT NULL default '' AFTER `position_brand`;
    DROP TABLE IF EXISTS {$this->getTable('brand_products')};
    CREATE TABLE  {$this->getTable('brand_products')} (
    `bp_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT( 11 ) NOT NULL,
    `is_featured` INT(1) NOT NULL DEFAULT '0',
    `position` INT( 11 ) NOT NULL
    ) ENGINE = INNODB;"
);
    Mage::getModel('shopbybrand/brandproducts')->convertData();
$installer->endSetup();