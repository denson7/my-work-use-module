<?php
/**
 * Video Plugin for Magento
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Niveus
 * @package    Niveus_ProductVideo
 * @copyright  Copyright (c) 2013 Niveus Solutions (http://www.niveussolutions.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Niveus Solutions <support@niveussolutions.com>
 */

$installer = $this;
$installer->startSetup();

try 
{
      $installer->run("SELECT * FROM {$this->getTable('niveus_youtube_videos')}");
} 
catch (Exception $e) 
{
    $installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('niveus_youtube_videos')} (
  `video_id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) UNSIGNED NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `video_code` varchar(256) DEFAULT NULL,
  `video_title` text,
  PRIMARY KEY (`video_id`),
  KEY `NIVEUS_YOUTUBE_VIDEO_PRODUCT_ID_PRODUCT_ENTITY_ID` (`product_id`),
  KEY `NIVEUS_YOUTUBE_VIDEO_STORE_ID_STORE_ID` (`store_id`),
  CONSTRAINT `NIVEUS_YOUTUBE_VIDEO_PRODUCT_ID_PRODUCT_ENTITY_ID` FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog_product_entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `NIVEUS_YOUTUBE_VIDEO_STORE_ID_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
");

}

$installer->endSetup();
