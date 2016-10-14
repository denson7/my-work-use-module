<?php
/**
 * @category    Silk Manzoor
 * @package     Silk_Autocomplete
 * @version     1.0
 */
class Silk_Autocomplete_ProductController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve all products from current store as JSON
     */
    public function jsonAction()
    {
        $cacheId = 'silk_autocomplete_' . Mage::app()->getStore()->getId();
        if (false === ($data = Mage::app()->loadCache($cacheId))) {
            $collection = Mage::getModel('catalog/product')->getCollection();

            Mage::dispatchEvent('silk_autocomplete_product_collection_init', array('collection' => $collection));

            $data = json_encode($collection->getData());

            $lifetime = Mage::helper('silk_autocomplete')->getCacheLifetime();
            Mage::app()->saveCache($data, $cacheId, array('block_html'), $lifetime);
        }

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody($data);
    }
}
