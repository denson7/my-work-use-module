<?php
$installer = $this;
$installer->startSetup();
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('productfileupload')};
CREATE TABLE {$this->getTable('productfileupload')} (
  `fid` int(10) unsigned NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL default '',
  `productid` int(11) NOT NULL default '0',
  `fileplace` smallint(6) NOT NULL default '1',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 