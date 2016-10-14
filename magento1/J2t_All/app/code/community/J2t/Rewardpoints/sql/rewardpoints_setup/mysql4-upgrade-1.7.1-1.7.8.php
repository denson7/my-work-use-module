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

if (version_compare(Mage::getVersion(), '1.4.0', '>=') && Mage::helper('core')->isModuleEnabled('J2t_Rewardproductvalue')){
    $installer->run("UPDATE {$this->getTable('rewardpoints_account')} SET   `rewardpoints_status` = (SELECT `status` FROM {$this->getTable('sales/order')} WHERE {$this->getTable('sales/order')}.quote_id = {$this->getTable('rewardpoints_account')}.quote_id), 
                                                                            `rewardpoints_state` = (SELECT `state` FROM {$this->getTable('sales/order')} WHERE {$this->getTable('sales/order')}.quote_id = {$this->getTable('rewardpoints_account')}.quote_id) 
                                                                            WHERE {$this->getTable('rewardpoints_account')}.order_id = '".J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REQUIRED."';");
}


$installer->endSetup();