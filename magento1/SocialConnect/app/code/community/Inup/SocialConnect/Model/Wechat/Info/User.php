<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Model_Wechat_Info_User extends Inup_SocialConnect_Model_Wechat_Info
{

    protected $customer = null;

    public function load($id = null)
    {
        if (is_null($id) && Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->customer = Mage::getSingleton('customer/session')->getCustomer();
        } else if (is_int($id)) {
            $this->customer = Mage::getModel('customer/customer')->load($id);
        }

        if (!$this->customer->getId()) {
            return $this;
        }

        if (!($socialconnectCid = $this->customer->getInupSocialconnectCid()) ||
            !($socialconnectCtoken = $this->customer->getInupSocialconnectCtoken())
        ) {
            return $this;
        }

        $this->setAccessToken($socialconnectCtoken);
        $this->_load();

        return $this;
    }

    protected function _onException($e)
    {
        parent::_onException($e);

        $helper = Mage::helper('inup_socialconnect/wechat');

        $helper->disconnect($this->customer);
    }

}