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

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
/**
 * add attributes rewardpoints_description, rewardpoints, base_rewardpoints
 */

/*
$installer->addAttribute('quote_address', 'rewardpoints_description', array('type'=>'varchar'));
$installer->addAttribute('quote_address', 'rewardpoints', array('type'=>'decimal'));
$installer->addAttribute('quote_address', 'base_rewardpoints', array('type'=>'decimal'));
*/

$installer->addAttribute('quote', 'rewardpoints_description', array('type'=>'varchar'));
$installer->addAttribute('quote', 'rewardpoints_quantity', array('type'=>'decimal'));
$installer->addAttribute('quote', 'base_rewardpoints', array('type'=>'decimal'));
$installer->addAttribute('quote', 'rewardpoints', array('type'=>'decimal'));

$installer->addAttribute('order', 'rewardpoints_description', array('type'=>'varchar'));
$installer->addAttribute('order', 'rewardpoints_quantity', array('type'=>'decimal'));
$installer->addAttribute('order', 'base_rewardpoints', array('type'=>'decimal'));
$installer->addAttribute('order', 'rewardpoints', array('type'=>'decimal'));

$installer->endSetup();
