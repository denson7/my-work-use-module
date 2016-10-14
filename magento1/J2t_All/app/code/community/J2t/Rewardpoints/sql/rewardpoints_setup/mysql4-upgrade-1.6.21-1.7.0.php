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

$installer->addAttribute('quote_item', 'rewardpoints_gathered', array('type'=>'decimal', 'visible' => false, 'default' => 0));
$installer->addAttribute('order_item', 'rewardpoints_gathered', array('type'=>'decimal', 'visible' => false, 'default' => 0));

$installer->addAttribute('quote_item', 'rewardpoints_used', array('type'=>'decimal', 'visible' => false, 'default' => 0));
$installer->addAttribute('order_item', 'rewardpoints_used', array('type'=>'decimal', 'visible' => false, 'default' => 0));

$installer->addAttribute('quote', 'rewardpoints_gathered', array('type'=>'int', 'visible' => false, 'default' => 0));
$installer->addAttribute('order', 'rewardpoints_gathered', array('type'=>'int', 'visible' => false, 'default' => 0));

/************* invoice datas ******************/
$installer->addAttribute('invoice', 'rewardpoints_gathered', array('type'=>'decimal', 'default' => 0));
$installer->addAttribute('invoice', 'rewardpoints_description', array('type'=>'varchar'));
$installer->addAttribute('invoice', 'rewardpoints_quantity', array('type'=>'decimal'));
$installer->addAttribute('invoice', 'base_rewardpoints', array('type'=>'decimal'));
$installer->addAttribute('invoice', 'rewardpoints', array('type'=>'decimal'));
$installer->addAttribute('invoice', 'rewardpoints_referrer', array('type'=>'int', 'default' => 0));
/************* /invoice datas *****************/


$installer->run("ALTER TABLE {$this->getTable('rewardpoints_account')} ADD COLUMN `rewardpoints_status` VARCHAR( 255 ) NULL;");
$installer->run("ALTER TABLE {$this->getTable('rewardpoints_account')} ADD COLUMN `rewardpoints_state` VARCHAR( 255 ) NULL AFTER `rewardpoints_status`;");
$installer->run("ALTER TABLE {$this->getTable('rewardpoints_account')} ADD COLUMN `date_order` DATETIME NULL AFTER `rewardpoints_state`;");
$installer->run("ALTER TABLE {$this->getTable('rewardpoints_account')} ADD COLUMN `date_insertion` DATETIME NULL AFTER `date_order`;");
$installer->run("ALTER TABLE {$this->getTable('rewardpoints_account')} ADD COLUMN `period` DATE NULL AFTER `date_insertion`;");
$installer->run("ALTER TABLE {$this->getTable('rewardpoints_account')} ADD COLUMN `object_name` VARCHAR( 255 ) NULL AFTER `period`;");

$installer->run("ALTER TABLE {$this->getTable('rewardpoints_account')} ADD INDEX `rewardpoints_status` (`rewardpoints_status`);");
$installer->run("ALTER TABLE {$this->getTable('rewardpoints_account')} ADD INDEX `rewardpoints_state` (`rewardpoints_state`);");
$installer->run("ALTER TABLE {$this->getTable('rewardpoints_account')} ADD INDEX `object_name` (`object_name`);");

if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
    $installer->run("UPDATE {$this->getTable('rewardpoints_account')} SET   `rewardpoints_status` = (SELECT `status` FROM {$this->getTable('sales/order')} WHERE {$this->getTable('sales/order')}.increment_id = {$this->getTable('rewardpoints_account')}.order_id), 
                                                                            `rewardpoints_state` = (SELECT `state` FROM {$this->getTable('sales/order')} WHERE {$this->getTable('sales/order')}.increment_id = {$this->getTable('rewardpoints_account')}.order_id),
                                                                            `date_order` = (SELECT {$this->getTable('sales/order')}.`created_at` FROM {$this->getTable('sales/order')} WHERE {$this->getTable('sales/order')}.increment_id = {$this->getTable('rewardpoints_account')}.order_id),
                                                                            `date_insertion` = (SELECT {$this->getTable('sales/order')}.`created_at` FROM {$this->getTable('sales/order')} WHERE {$this->getTable('sales/order')}.increment_id = {$this->getTable('rewardpoints_account')}.order_id),
                                                                            `period` = (SELECT {$this->getTable('sales/order')}.`created_at` FROM {$this->getTable('sales/order')} WHERE {$this->getTable('sales/order')}.increment_id = {$this->getTable('rewardpoints_account')}.order_id);");
} else {
    $table_sales_order = $this->getTable('sales/order').'_varchar';
    
    $states = array(
        Mage_Sales_Model_Order::STATE_NEW, 
        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, 
        Mage_Sales_Model_Order::STATE_PROCESSING, 
        Mage_Sales_Model_Order::STATE_COMPLETE, 
        Mage_Sales_Model_Order::STATE_CLOSED, 
        Mage_Sales_Model_Order::STATE_CANCELED, 
        Mage_Sales_Model_Order::STATE_HOLDED
        );
    $installer->run("UPDATE {$this->getTable('rewardpoints_account')} SET   `rewardpoints_state` = (   SELECT order_state.value 
                                                                                                        FROM $table_sales_order as order_state 
                                                                                                        WHERE order_state.entity_id IN ( 
                                                                                                            SELECT orders.entity_id FROM {$this->getTable('sales/order')} as orders 
                                                                                                            WHERE orders.increment_id = {$this->getTable('rewardpoints_account')}.order_id
                                                                                                        )
                                                                                                        WHERE order_state.value in ('".implode("','", $states)."') ORDER BY value_id DESC
                                                                                                        LIMIT 1
                                                                                                     ),
                                                                            `date_order` = (SELECT {$this->getTable('sales/order')}.`created_at` FROM {$this->getTable('sales/order')} WHERE {$this->getTable('sales/order')}.increment_id = {$this->getTable('rewardpoints_account')}.order_id),
                                                                            `date_insertion` = (SELECT {$this->getTable('sales/order')}.`created_at` FROM {$this->getTable('sales/order')} WHERE {$this->getTable('sales/order')}.increment_id = {$this->getTable('rewardpoints_account')}.order_id),
                                                                            `period` = (SELECT {$this->getTable('sales/order')}.`created_at` FROM {$this->getTable('sales/order')} WHERE {$this->getTable('sales/order')}.increment_id = {$this->getTable('rewardpoints_account')}.order_id);");
}


$installer->run("ALTER TABLE {$this->getTable('rewardpoints_flat_account')} ADD COLUMN `notification_date` DATETIME NULL AFTER `points_lost`;");
$installer->run("ALTER TABLE {$this->getTable('rewardpoints_flat_account')} ADD COLUMN `notification_qty` integer(10) unsigned NOT NULL default '0' AFTER `points_lost`;");


// /end get all invoices and update rewardpoints & rewardpoints_gathered (from rewardpoints_account table)


//Mage_Customer_Model_Entity_Setup
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
$setup->addAttribute('customer', 'rewardpoints_referrer', array(
    'label'        => 'Reward Points Referrer ID',
    'input'         => 'text',
    'type'          => 'int',
    'visible'      => 1,
    'required'     => 0,
    'user_defined' => 1,
));

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'rewardpoints_referrer',
    '999'  //sort_order
);

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'rewardpoints_referrer');
$oAttribute->setData('used_in_forms', array('adminhtml_customer'));
$oAttribute->save();

//$setup->endSetup();


$installer->endSetup();