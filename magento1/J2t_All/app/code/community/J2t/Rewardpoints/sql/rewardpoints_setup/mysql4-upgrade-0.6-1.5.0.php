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
 * @copyright  Copyright (c) 2011 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
?>
<?php

$installer = $this;
$installer->startSetup();

$installer->run("-- DROP TABLE IF EXISTS {$this->getTable('rewardpoints_flat_account')};
    CREATE TABLE IF NOT EXISTS {$this->getTable('rewardpoints_flat_account')} (
      `flat_account_id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `user_id` INTEGER(11) UNSIGNED NOT NULL,
      `store_id` INTEGER(11) UNSIGNED NULL DEFAULT NULL,
      `points_collected` INTEGER(11) NOT NULL DEFAULT '0',
      `points_used` INTEGER(11) NOT NULL DEFAULT '0',
      `points_waiting` INTEGER(11) NOT NULL DEFAULT '0',
      `points_current` INTEGER(11) NOT NULL DEFAULT '0',
      `points_lost` INTEGER(11) NOT NULL DEFAULT '0',
      `last_check` DATE NULL DEFAULT NULL,
      PRIMARY KEY (`flat_account_id`),
      KEY `FK_customer_rewardpoints` (`user_id`),
      UNIQUE KEY `IDX_REWARD_USER_STORE` (`user_id`,`store_id`),
      CONSTRAINT `FK_REWARDPOINTS_FLAT_CUSTOMER` FOREIGN KEY (`user_id`) 
        REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
$installer->endSetup();
