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
    ) ");

$installer->endSetup();