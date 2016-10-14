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

$installer->addAttribute('quote', 'rewardpoints_cart_rule_text', array('type'=>'varchar', 'visible' => false));
$installer->addAttribute('order', 'rewardpoints_cart_rule_text', array('type'=>'varchar', 'visible' => false));

$installer->addAttribute('quote_item', 'rewardpoints_catalog_rule_text', array('type'=>'varchar', 'visible' => false));
$installer->addAttribute('order_item', 'rewardpoints_catalog_rule_text', array('type'=>'varchar', 'visible' => false));

/************* invoice datas ******************/
$installer->addAttribute('invoice', 'rewardpoints_cart_rule_text', array('type'=>'varchar'));
/************* /invoice datas *****************/

$installer->run("
        ALTER TABLE `{$this->getTable('rewardpoints/pointrules')}` ADD COLUMN `labels` TEXT NULL AFTER `to_date`;
        ALTER TABLE `{$this->getTable('rewardpoints/pointrules')}` ADD COLUMN `labels_summary` TEXT NULL AFTER `labels`;
        ");
        
        
$installer->run("
        ALTER TABLE `{$this->getTable('rewardpoints/catalogpointrules')}` ADD COLUMN `labels` TEXT NULL AFTER `to_date`;
        ALTER TABLE `{$this->getTable('rewardpoints/catalogpointrules')}` ADD COLUMN `labels_summary` TEXT NULL AFTER `labels`;
        ");
        
$installer->addAttribute('quote_item', 'rewardpoints_gathered_float', array('type'=>'decimal', 'visible' => false, 'default' => 0));
$installer->addAttribute('order_item', 'rewardpoints_gathered_float', array('type'=>'decimal', 'visible' => false, 'default' => 0));

$installer->endSetup();