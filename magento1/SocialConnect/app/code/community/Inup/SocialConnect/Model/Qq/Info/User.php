<?php

/**
 * Created by PhpStorm.
 * User: pgroot
 * Date: 16/8/11
 * Time: 19:33
 */
class Inup_SocialConnect_Model_Qq_Info_User extends Inup_SocialConnect_Model_Qq_Info
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

        if (!($socialconnectQid = $this->customer->getInupSocialconnectQid()) ||
            !($socialconnectQtoken = $this->customer->getInupSocialconnectQtoken())
        ) {
            return $this;
        }

        $this->setAccessToken($socialconnectQtoken);
        $this->_load();

        return $this;
    }

    protected function _onException($e)
    {
        parent::_onException($e);

        $helper = Mage::helper('inup_socialconnect/qq');

        $helper->disconnect($this->customer);
    }

}