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
DROP TABLE IF EXISTS {$this->getTable('rewardpoints/pointrules')};
CREATE TABLE IF NOT EXISTS {$this->getTable('rewardpoints/pointrules')} (
  `rule_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(166) NOT NULL,
  `status` int(11) NOT NULL,
  `website_ids` varchar( 255 ) NULL,
  `customer_group_ids` varchar( 255 ) NULL,
  `action_type` int(11) NULL,
  `conditions_serialized` mediumtext NOT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL,
  `rule_type` TINYINT( 2 ) NULL DEFAULT NULL,
  PRIMARY KEY  (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('rewardpoints/catalogpointrules')};
CREATE TABLE IF NOT EXISTS {$this->getTable('rewardpoints/catalogpointrules')} (
  `rule_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(166) NOT NULL,
  `status` int(11) NOT NULL,
  `website_ids` varchar( 255 ) NULL,
  `customer_group_ids` varchar( 255 ) NULL,
  `action_type` int(11) NULL,
  `conditions_serialized` mediumtext NOT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL,
  `rule_type` TINYINT( 2 ) NULL DEFAULT NULL,
  PRIMARY KEY  (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


");



/*
 * ALTER TABLE `rewardpoints_pointrules` ADD `website_ids` VARCHAR( 255 ) NULL AFTER `status` ,
ADD `customer_group_ids` VARCHAR( 255 ) NULL AFTER `website_ids` ,
ADD `action_type` INT NULL AFTER `customer_group_ids`
 *
 * $subscribers = Mage::getResourceSingleton('newsletter/subscriber_collection')
        ->showCustomerInfo(true)
        ->getData();

foreach ($subscribers as $subscriber)
{
    try
    {
        Mage::getModel('j2tnewsletter/subscriptions')
        ->setEmail($subscriber['subscriber_email'])
		->setSegmentsCodes(J2t_Newsletter_Helper_Data::AN_SEGMENTS_ALL)
        ->save();
    }
    catch(Exception $e){continue;}
}*/

$installer->endSetup();