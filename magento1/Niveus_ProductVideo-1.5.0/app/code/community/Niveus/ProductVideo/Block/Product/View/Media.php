<?php
/**
 * Video Plugin for Magento
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Niveus
 * @package    Niveus_ProductVideo
 * @copyright  Copyright (c) 2013 Niveus Solutions (http://www.niveussolutions.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Niveus Solutions <support@niveussolutions.com>
 */

class Niveus_ProductVideo_Block_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{
    public function _toHtml()
    {
	if(Mage::getStoreConfig('niveus_productvideo/settings/enable'))
	{
		$html = parent::_toHtml();
		$html .= $this->getChildHtml('media_video');
		return $html;
	}
        else
           $html = parent::_toHtml();
           return $html;
    }
}
