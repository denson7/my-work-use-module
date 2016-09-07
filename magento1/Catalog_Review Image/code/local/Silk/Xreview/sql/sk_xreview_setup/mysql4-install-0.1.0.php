<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2016/7/22
 * Time: 14:54
 */
$installer = $this;

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS `review_image`;
    CREATE TABLE `review_image`(
      `img_id` INT unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT ,
      `review_id` INT unsigned NOT NULL ,
      `image` VARCHAR (200) NOT NULL ,
      `status_id` INT unsigned DEFAULT 1,
      `store_id`  INT unsigned NOT NULL ,
      `customer_id` INT unsigned
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();