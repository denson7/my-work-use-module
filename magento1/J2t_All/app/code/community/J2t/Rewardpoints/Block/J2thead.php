<?php
/**
 * Magento
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

class J2t_Rewardpoints_Block_J2thead extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /*if (Mage::getStoreConfig('rewardpoints/registration/referral_addthis', Mage::app()->getStore()->getId()) && Mage::getStoreConfig('rewardpoints/registration/referral_addthis_account', Mage::app()->getStore()->getId()) != ""){
            $block = $this->getLayout()->createBlock('Mage_Core_Block_Text');
            $block->setText('<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username='.Mage::getStoreConfig('rewardpoints/registration/referral_addthis_account', Mage::app()->getStore()->getId()).'"></script>');
            $this->getLayout()->getBlock('head')->append($block);
        }*/

    }

}
