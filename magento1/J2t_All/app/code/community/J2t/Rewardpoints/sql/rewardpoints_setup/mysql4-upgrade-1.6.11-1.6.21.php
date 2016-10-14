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
$installer=new  Mage_Customer_Model_Entity_Setup ('core_setup');
 
$installer->startSetup();
 
$installer->addAttribute('customer', 'rewardpoints_accumulated', array(
    'type'         => 'int',
    'label'        => 'Accumulated Points',
    'visible'      => false,
    'required'     => false,
 ));

$installer->addAttribute('customer', 'rewardpoints_available', array(
    'type'         => 'int',
    'label'        => 'Available Points',
    'visible'      => false,
    'required'     => false,
 ));

$installer->addAttribute('customer', 'rewardpoints_spent', array(
    'type'         => 'int',
    'label'        => 'Spent Points',
    'visible'      => false,
    'required'     => false,
 ));

$installer->addAttribute('customer', 'rewardpoints_lost', array(
    'type'         => 'int',
    'label'        => 'Lost Points',
    'visible'      => false,
    'required'     => false,
 ));

$installer->addAttribute('customer', 'rewardpoints_waiting', array(
    'type'         => 'int',
    'label'        => 'Waiting Points',
    'visible'      => false,
    'required'     => false,
 ));

 
$installer->endSetup();