<?php

class Cdr_OrderComment_Block_Checkout_Onepage_Review_Commentfield extends Mage_Core_Block_Template
{

    public function getMaxCommentSize()
    {
        if ($this->limitTextArea()) {
            return Mage::getStoreConfig('ordercomment/settings/limit');
        }
        return '';
    }

    public function limitTextArea()
    {
        $limit = Mage::getStoreConfig('ordercomment/settings/limit');
        return (is_numeric($limit) && $limit > 0);
    }

    public function isActive()
    {
        return Mage::getStoreConfigFlag('ordercomment/settings/active');
    }

    public function getCommentHeader()
    {
        return Mage::getStoreConfig('ordercomment/settings/commentheader');
    }

}
