<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Url rewrite Model
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Model_Mysql4_Subscriber extends Mage_Newsletter_Model_Mysql4_Subscriber
{
    public function _construct()
    {
        $this->_init('shopbybrand/subscriber', 'subscriber_id');
        $this->_subscriberLinkTable = $this->getTable('newsletter/queue_link');
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }
     
}