<?php
/**
 * @category    silk Manzoor
 * @package     silk_Autocomplete
 * @version     1.0
 * @copyright   Copyright (c) 2015 (http://www.silk.me/)
 */
class Silk_Autocomplete_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('silk_autocomplete/general/enable', $store);
    }

    public function getLimit($store = null)
    {
        return (int) Mage::getStoreConfig('silk_autocomplete/general/limit', $store);
    }

    public function getMinLength($store = null)
    {
        return (int) Mage::getStoreConfig('silk_autocomplete/general/min_length', $store);
    }

    public function getCacheLifetime($store = null)
    {
        return (int) Mage::getStoreConfig('silk_autocomplete/general/cache_lifetime', $store);
    }

    public function getUseLocalStorage($store = null)
    {
        return Mage::getStoreConfigFlag('silk_autocomplete/general/use_local_storage', $store);
    }

    public function getJsPriceFormat()
    {
        return Mage::app()->getLocale()->getJsPriceFormat();
    }

    public function getBaseUrl()
    {
        return Mage::app()->getStore()->getBaseUrl();
    }

    public function getBaseUrlMedia()
    {
        return Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl();
    }
}