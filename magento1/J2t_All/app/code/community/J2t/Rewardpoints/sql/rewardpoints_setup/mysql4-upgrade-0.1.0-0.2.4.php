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
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

//ALTER TABLE {$this->getTable('rewardpoints_account')} ADD COLUMN `rewardpoints_referral_id` INT( 11 ) NULL AFTER `points_spent`;

$installer->run("

    -- DROP TABLE IF EXISTS {$this->getTable('rewardpoints_referral')};
    CREATE TABLE IF NOT EXISTS {$this->getTable('rewardpoints_referral')} (
      `rewardpoints_referral_id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `rewardpoints_referral_parent_id` INTEGER(11) UNSIGNED NOT NULL,
      `rewardpoints_referral_child_id` INTEGER(11) UNSIGNED DEFAULT NULL,
      `rewardpoints_referral_email` VARCHAR(255) NOT NULL DEFAULT '',
      `rewardpoints_referral_status` TINYINT(1) DEFAULT '0',
      PRIMARY KEY (`rewardpoints_referral_id`),
      UNIQUE KEY `email` (`rewardpoints_referral_email`),
      UNIQUE KEY `son_id` (`rewardpoints_referral_child_id`),
      KEY `FK_customer_entity` (`rewardpoints_referral_parent_id`),
      CONSTRAINT `rewardpoints_referral_parent_fk` FOREIGN KEY (`rewardpoints_referral_parent_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `rewardpoints_referral_child_fk1` FOREIGN KEY (`rewardpoints_referral_child_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

//OLD
/*
$sqlAdd = '';
$connection = Mage::getSingleton('core/resource')
                 ->getConnection('rewardpoints_read');
$select = $connection->select()
                    ->from('information_schema.COLUMNS')
                    ->where("COLUMN_NAME='rewardpoints_referral_id' AND TABLE_NAME='{$this->getTable('rewardpoints_account')}'");
$data = $connection->fetchRow($select);
if(!isset($data['COLUMN_NAME'])){
    $sql_add = "ALTER TABLE {$this->getTable('rewardpoints_account')} ADD COLUMN `rewardpoints_referral_id` INT( 11 ) NULL AFTER `points_spent`;";
}

$installer->run("$sqlAdd

-- DROP TABLE IF EXISTS {$this->getTable('rewardpoints_referral')};
                CREATE TABLE IF NOT EXISTS {$this->getTable('rewardpoints_referral')} (
                  `rewardpoints_referral_id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `rewardpoints_referral_parent_id` INTEGER(11) UNSIGNED NOT NULL,
                  `rewardpoints_referral_child_id` INTEGER(11) UNSIGNED DEFAULT NULL,
                  `rewardpoints_referral_email` VARCHAR(255) NOT NULL DEFAULT '',
                  `rewardpoints_referral_status` TINYINT(1) DEFAULT '0',

                  PRIMARY KEY (`rewardpoints_referral_id`),
                  UNIQUE KEY `email` (`rewardpoints_referral_email`),
                  UNIQUE KEY `son_id` (`rewardpoints_referral_child_id`),
                  KEY `FK_customer_entity` (`rewardpoints_referral_parent_id`),
                  CONSTRAINT `rewardpoints_referral_parent_fk` FOREIGN KEY (`rewardpoints_referral_parent_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                  CONSTRAINT `rewardpoints_referral_child_fk1` FOREIGN KEY (`rewardpoints_referral_child_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
*/