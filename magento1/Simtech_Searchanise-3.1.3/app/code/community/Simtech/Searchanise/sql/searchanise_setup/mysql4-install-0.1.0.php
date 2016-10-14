<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

$installer = $this;

$installer->startSetup();

$installer->run(
    " 
    DROP TABLE IF EXISTS {$this->getTable('searchanise_config')};
    CREATE TABLE IF NOT EXISTS {$this->getTable('searchanise_config')} (
        `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Config Id',
        `scope` varchar(8) NOT NULL DEFAULT 'default' COMMENT 'Config Scope',
        `scope_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Config Scope Id',
        `path` varchar(255) NOT NULL DEFAULT 'general' COMMENT 'Config Path',
        `value` text COMMENT 'Config Value',
        PRIMARY KEY (`config_id`),
        UNIQUE KEY `UNQ_CORE_CONFIG_DATA_SCOPE_SCOPE_ID_PATH` (`scope`,`scope_id`,`path`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Config Data Searchanise';

    DROP TABLE IF EXISTS {$this->getTable('searchanise_queue')};
    CREATE TABLE IF NOT EXISTS {$this->getTable('searchanise_queue')} (
        `queue_id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `data` text NOT NULL,
        `action` varchar(32) NOT NULL DEFAULT '',
        `store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store Id',
        `started` int(11) NOT NULL DEFAULT '0',
        `error_count` int(11) NOT NULL DEFAULT '0',
        `status` enum('pending','processing') DEFAULT 'pending',
        PRIMARY KEY (`queue_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Queue tasks for Searchanise';
    "
);

$installer->endSetup();