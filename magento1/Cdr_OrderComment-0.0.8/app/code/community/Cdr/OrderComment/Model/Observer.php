<?php

class Cdr_OrderComment_Model_Observer
{

    /**
     * 
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer
     */
    public function saveOrderComment($observer)
    {
        if (Mage::getStoreConfigFlag('ordercomment/settings/active')) {
            $comment = trim(Mage::app()->getRequest()->getPost('cdr_ordercomment') ?: Mage::app()->getRequest()->getPost('customer_comment'));

            
            if (!empty($comment)) {
                $limit = (int) Mage::getStoreConfig('ordercomment/settings/limit');
                if (is_integer($limit) && $limit > 0 && strlen($comment) > $limit)
                    $comment = substr($comment, 0, $limit);

                $order = $observer->getEvent()->getOrder();
                /* @var $order Mage_Sales_Model_Order */

                $order->setCdrOrderComment($comment);
                $order->setCustomerComment($comment);
                $order->setCustomerNoteNotify(true);
                $order->setCustomerNote($comment);
            }
        }
        return $this;
    }

}
