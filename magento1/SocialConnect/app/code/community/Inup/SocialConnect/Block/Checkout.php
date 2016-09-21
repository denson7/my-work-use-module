<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Block_Checkout extends Mage_Core_Block_Template
{
    protected $clientWeibo = null;
    protected $clientQq = null;
    protected $clientWechat = null;

    protected $numEnabled = 0;
    protected $numShown = 0;

    protected function _construct()
    {
        parent::_construct();

        $this->clientWeibo = Mage::getSingleton('inup_socialconnect/weibo_oauth_client');
        $this->clientQq = Mage::getSingleton('inup_socialconnect/qq_oauth_client');
        $this->clientWechat = Mage::getSingleton('inup_socialconnect/wechat_oauth_client');

        if (!$this->_weiboEnabled() &&
            !$this->_qqEnabled()&&
            !$this->_wechatEnabled()
        ) {
            return;
        }

        if ($this->_weiboEnabled()) {
            $this->numEnabled++;
        }

        if ($this->_qqEnabled()) {
            $this->numEnabled++;
        }
        if ($this->_wechatEnabled()) {
            $this->numEnabled++;
        }

        Mage::register('inup_socialconnect_button_text', $this->__('Continue'), true);

        $this->setTemplate('inup/socialconnect/checkout.phtml');
    }

    protected function _getColSet()
    {
        return 'col' . $this->numEnabled . '-set';
    }

    protected function _getCol()
    {
        return 'col-' . ++$this->numShown;
    }

    protected function _weiboEnabled()
    {
        return $this->clientWeibo->isEnabled();
    }

    protected function _qqEnabled()
    {
        return $this->clientQq->isEnabled();
    }
    protected function _wechatEnabled() {
        return $this->clientWechat->isEnabled();
    }

}
