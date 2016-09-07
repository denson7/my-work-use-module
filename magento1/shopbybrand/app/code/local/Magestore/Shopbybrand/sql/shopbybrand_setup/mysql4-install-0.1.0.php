<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create shopbybrand table
 */
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('brand')};
DROP TABLE IF EXISTS {$this->getTable('brand_store_value')};
DROP TABLE IF EXISTS {$this->getTable('brand_subscriber')};
    
CREATE TABLE {$this->getTable('brand')} (
  `brand_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url_key` varchar(255) NOT NULL default '',
  `image` varchar(255) NULL,
  `thumbnail_image` varchar(255) NULL,
  `short_description` text NOT NULL default '',
  `description` text NOT NULL default '',
  `page_title` varchar(255) NOT NULL default '',
  `option_id` int(11) NULL,
  `category_ids` text NOT NULL default '',
  `product_ids` text NOT NULL default '',
  `order` int(11) NULL,
  `meta_keywords` varchar(255) NOT NULL default '',
  `meta_description` varchar(255) NOT NULL default '',
  `is_featured` tinyint(1) NOT NULL default '0',
  `created_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `status` smallint(6) NOT NULL default '1',
  PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('brand_store_value')}(
    `value_id` int(11) unsigned NOT NULL auto_increment,
    `brand_id` int(11) unsigned NOT NULL,
    `store_id`  smallint(5) unsigned NOT NULL,
    `attribute_code` varchar(255) NOT NULL default '',
    `value` text NOT NULL,
    UNIQUE(`brand_id`, `store_id`, `attribute_code`),
    INDEX(`brand_id`),
    INDEX(`store_id`),
    FOREIGN KEY (`brand_id`) REFERENCES {$this->getTable('brand')} (`brand_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (`value_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('brand_subscriber')}(
    `brand_subscriber_id` int(11) unsigned NOT NULL auto_increment,
    `brand_id` int(11) unsigned NOT NULL,
    `subscriber_id` int(10) unsigned NOT NULL,
    UNIQUE(`brand_id`, `subscriber_id`),
    INDEX(`brand_id`),
    INDEX(`subscriber_id`),
    FOREIGN KEY (`brand_id`) REFERENCES {$this->getTable('brand')} (`brand_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`subscriber_id`) REFERENCES {$this->getTable('newsletter_subscriber')} (`subscriber_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (`brand_subscriber_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
if ($installer->tableExists($installer->getTable('manufacturer'))){
    Mage::getResourceModel('shopbybrand/brand')->convertData();
}else{
    Mage::helper('shopbybrand/brand')->updateBrandsFormCatalog();
}
$installer->endSetup();

