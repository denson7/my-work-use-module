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
 * @copyright  Copyright (c) 2015 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class J2t_Rewardpoints_Model_Cartrule_Condition_Product_Subselect extends Mage_SalesRule_Model_Rule_Condition_Product_Subselect
{
    public function __construct()
    {
		parent::__construct();
        $this->setType('rewardpoints/cartrule_condition_product_subselect')
            ->setValue(null);
    }
	
	public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $attr = $this->getAttribute();
        $total = 0;
		
		if (get_class($object) == 'Mage_Sales_Model_Quote'){
			$quote = $object;
		} else {
			$quote = $object->getQuote();
		}
		
        foreach ($quote->getAllVisibleItems() as $item) {
            if (Mage_SalesRule_Model_Rule_Condition_Product_Combine::validate($item)) {
                $total += $item->getData($attr);
            }
        }
        return $this->validateAttribute($total);
    }
}
