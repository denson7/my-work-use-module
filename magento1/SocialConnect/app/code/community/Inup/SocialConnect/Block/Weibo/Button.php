<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Block_Weibo_Button extends Mage_Core_Block_Template
{
    protected $client = null;
    protected $userInfo = null;

    protected function _construct()
    {
        parent::_construct();

        $this->client = Mage::getSingleton('inup_socialconnect/weibo_oauth_client');
        if (!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = Mage::registry('inup_socialconnect_weibo_userinfo');

        Mage::getSingleton('customer/session')
            ->setSocialConnectRedirect(Mage::helper('core/url')->getCurrentUrl());

        $this->setTemplate('inup/socialconnect/weibo/button.phtml');
    }

    protected function _getButtonUrl()
    {
        if (is_null($this->userInfo) || !$this->userInfo->hasData()) {
            return $this->client->createAuthUrl();
        } else {
            return $this->getUrl('socialconnect/weibo/disconnect');
        }
    }

    protected function _getButtonText()
    {
        if (is_null($this->userInfo) || !$this->userInfo->hasData()) {
            if (!($text = Mage::registry('inup_socialconnect_button_text'))) {
                $text = $this->__('Connect');
            }
        } else {
            $text = $this->__('Disconnect');
        }

        return $text;
    }

}