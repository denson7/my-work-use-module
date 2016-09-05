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

 


 
class Niveus_ProductVideo_Block_Rewrite_AdminhtmlCatalogProductEditTabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs 
{
	protected function _prepareLayout() 
    {$product = $this->getProduct();

    if (!($setId = $product->getAttributeSetId())) {
 $return = parent::_prepareLayout();
      
   
    }
    if ($setId) {
      $return = parent::_prepareLayout();
		 
		 $this->addTab('niveus_youtube_videos', array(
            'label'     => Mage::helper('productvideo')->__('Videos'),
            'url'       => $this->getUrl('productvideo_admin/adminhtml_videos', array('_current' => true)),
            'class'     => 'ajax',
            'after'     => 'custom_option',
        ));
		
		return $return;
    }
    return false;
		
	}
}
