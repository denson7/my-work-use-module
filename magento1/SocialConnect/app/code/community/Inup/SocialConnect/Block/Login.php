<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Block_Login extends Mage_Core_Block_Template
{
    protected $clientWeibo = null;
    protected $clientQq = null;
    protected $clientWechat = null;

    protected $numEnabled = 0;
    protected $numDescShown = 0;
    protected $numButtShown = 0;

    protected function _construct()
    {
        parent::_construct();

        $this->clientWeibo = Mage::getSingleton('inup_socialconnect/weibo_oauth_client');
        $this->clientQq = Mage::getSingleton('inup_socialconnect/qq_oauth_client');
        $this->clientWechat = Mage::getSingleton('inup_socialconnect/wechat_oauth_client');

        if (!$this->_weiboEnabled() &&
            !$this->_qqEnabled() &&
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

        Mage::register('inup_socialconnect_button_text', $this->__('Login'), true);

        $this->setTemplate('inup/socialconnect/login.phtml');
    }

    protected function _getColSet()
    {
        return 'col' . $this->numEnabled . '-set';
    }

    protected function _getDescCol()
    {
        return 'col-' . ++$this->numDescShown;
    }

    protected function _getButtCol()
    {
        return 'col-' . ++$this->numButtShown;
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