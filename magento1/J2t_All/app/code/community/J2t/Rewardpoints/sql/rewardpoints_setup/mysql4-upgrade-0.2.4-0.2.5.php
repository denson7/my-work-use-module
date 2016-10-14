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
$installer->run("ALTER TABLE {$this->getTable('rewardpoints_referral')} ADD COLUMN `rewardpoints_referral_name` VARCHAR( 255 ) NULL AFTER `rewardpoints_referral_email`;");
$installer->endSetup();

//OLD
/*
$connection = Mage::getSingleton('core/resource')
                 ->getConnection('rewardpoints_read');
$select = $connection->select()
                    ->from('information_schema.COLUMNS')
                    ->where("COLUMN_NAME='rewardpoints_referral_name' AND TABLE_NAME='{$this->getTable('rewardpoints_referral')}'");
$data = $connection->fetchRow($select);
if(!isset($data['COLUMN_NAME'])){
    $installer = $this;
    
    $installer->startSetup();

    $installer->run("
    ALTER TABLE {$this->getTable('rewardpoints_referral')} ADD COLUMN `rewardpoints_referral_name` VARCHAR( 255 ) NULL AFTER `rewardpoints_referral_email`;
    ");

    $installer->endSetup();
}

*/


