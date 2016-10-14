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
 * @copyright  Copyright (c) 2014 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class J2t_Rewardpoints_Model_Total_Pdf extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    
    public function getTotalsForDisplay()
    {
        $store = $this->getOrder()->getStore();
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        
        $totals = array();
        if (Mage::getStoreConfig('rewardpoints/order_invoice/show_on_pdf_invoice')){
            $totals = array(array(
                'amount'    => ceil($this->getAmount()),
                'label'     => Mage::helper('rewardpoints')->__($this->getTitle()) . ':',
                'font_size' => $fontSize
            ));
        }
        return $totals;
    }
}