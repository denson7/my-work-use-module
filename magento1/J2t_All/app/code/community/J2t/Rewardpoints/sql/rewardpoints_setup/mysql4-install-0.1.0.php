<?php
/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
?>
<?php
$installer = $this;
$installer->startSetup();
$installer->run("
        -- DROP TABLE IF EXISTS {$this->getTable('rewardpoints_account')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('rewardpoints_account')} (
                rewardpoints_account_id integer(10) unsigned NOT NULL auto_increment,
                customer_id integer(10) unsigned NOT NULL default '0',
                store_id TEXT NULL DEFAULT NULL,
                order_id varchar(60) NOT NULL default '0',
                points_current integer(10) unsigned NULL default '0',
                points_spent integer(10) unsigned NULL default '0',
                PRIMARY KEY (rewardpoints_account_id),
                KEY FK_sales_order_ENTITY_STORE (order_id),
                KEY customer_id (customer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reward points for an account';

        CREATE TABLE IF NOT EXISTS {$this->getTable('rewardpoints_rule')} (
            `rewardpoints_rule_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
            `rewardpoints_rule_name` VARCHAR( 255 ) NOT NULL ,
            `rewardpoints_rule_type` VARCHAR( 60 ) NOT NULL ,
            `rewardpoints_rule_test` TEXT NOT NULL ,
            `rewardpoints_rule_operator` VARCHAR( 50 ) NOT NULL ,
            `rewardpoints_rule_points` INT( 11 ) NOT NULL ,
            `rewardpoints_rule_extra` TINYINT( 1 ) NOT NULL ,
            `website_ids` TEXT NULL ,
            `rewardpoints_rule_start` DATE NULL DEFAULT NULL ,
            `rewardpoints_rule_end` DATE NULL DEFAULT NULL ,
            `rewardpoints_rule_activated` TINYINT( 1 ) NOT NULL,
            PRIMARY KEY ( `rewardpoints_rule_id` )
            )
");


    /*
     * ALTER TABLE `rewardpoints_account` ADD `rewardpoints_referral_id` INT( 11 ) NULL DEFAULT NULL
     */

	
	$installer->endSetup();

// OLD
/*
$installer = $this;
	$installer->startSetup();
	$installer->run("
		-- DROP TABLE IF EXISTS {$this->getTable('rewardpoints_account')};
		CREATE TABLE IF NOT EXISTS {$this->getTable('rewardpoints_account')} (
			rewardpoints_account_id integer(10) unsigned NOT NULL auto_increment,
			customer_id integer(10) unsigned NOT NULL default '0',
			store_id TEXT NULL DEFAULT NULL,
			order_id varchar(60) NOT NULL default '0',
			points_current integer(10) unsigned NULL default '0',
			points_spent integer(10) unsigned NULL default '0',
                        `date_start` DATE NULL default NULL,
                        `date_end` DATE NULL default NULL,
                        rewardpoints_referral_id int(11) DEFAULT NULL,
			PRIMARY KEY (rewardpoints_account_id),
			KEY FK_sales_order_ENTITY_STORE (order_id),
			KEY customer_id (customer_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Reward points for an account';

                -- DROP TABLE IF EXISTS {$this->getTable('rewardpoints_referral')};
                CREATE TABLE IF NOT EXISTS {$this->getTable('rewardpoints_referral')} (
                  `rewardpoints_referral_id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `rewardpoints_referral_parent_id` INTEGER(11) UNSIGNED NOT NULL,
                  `rewardpoints_referral_child_id` INTEGER(11) UNSIGNED DEFAULT NULL,
                  `rewardpoints_referral_email` VARCHAR(255) NOT NULL DEFAULT '',
                  `rewardpoints_referral_name` VARCHAR(255) NULL,
                  `rewardpoints_referral_status` TINYINT(1) DEFAULT '0',
                  PRIMARY KEY (`rewardpoints_referral_id`),
                  UNIQUE KEY `email` (`rewardpoints_referral_email`),
                  UNIQUE KEY `son_id` (`rewardpoints_referral_child_id`),
                  KEY `FK_customer_entity` (`rewardpoints_referral_parent_id`),
                  CONSTRAINT `rewardpoints_referral_parent_fk` FOREIGN KEY (`rewardpoints_referral_parent_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`),
                  CONSTRAINT `rewardpoints_referral_child_fk1` FOREIGN KEY (`rewardpoints_referral_child_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

                CREATE TABLE IF NOT EXISTS {$this->getTable('rewardpoints_rule')} (
                    `rewardpoints_rule_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
                    `rewardpoints_rule_name` VARCHAR( 255 ) NOT NULL ,
                    `rewardpoints_rule_type` VARCHAR( 60 ) NOT NULL ,
                    `rewardpoints_rule_test` TEXT NOT NULL ,
                    `rewardpoints_rule_operator` VARCHAR( 50 ) NOT NULL ,
                    `rewardpoints_rule_points` INT( 11 ) NOT NULL ,
                    `rewardpoints_rule_extra` TINYINT( 1 ) NOT NULL ,
                    `website_ids` TEXT NULL ,
                    `rewardpoints_rule_start` DATE NULL DEFAULT NULL ,
                    `rewardpoints_rule_end` DATE NULL DEFAULT NULL ,
                    `rewardpoints_rule_activated` TINYINT( 1 ) NOT NULL,
                    PRIMARY KEY ( `rewardpoints_rule_id` )
                    )
	");


   
	
	$installer->endSetup();
*/