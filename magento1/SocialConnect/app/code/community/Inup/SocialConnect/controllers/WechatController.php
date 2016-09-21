<?php
/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/16
 * Time: 13:03
 */
class Inup_SocialConnect_WechatController extends Inup_SocialConnect_Controller_Abstract
{

    public function requestAction()
    {
        $client = Mage::getSingleton('inup_socialconnect/wechat_oauth_client');
        if (!($client->isEnabled())) {
            $this->norouteAction();
        }

        try {
            $client->redirectToAuthorize();
        } catch (Exception $e) {
            $referer = Mage::getSingleton('core/session')
                ->getSocialConnectRedirect();

            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::logException($e);

            if (!empty($referer)) {
                $this->_redirectUrl($referer);
            } else {
                $this->norouteAction();
            }
        }
    }

    protected function _disconnectCallback(Mage_Customer_Model_Customer $customer)
    {
        Mage::helper('inup_socialconnect/wechat')->disconnect($customer);

        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your Wechat account from our store account.')
            );
    }

    protected function _connectCallback()
    {
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');
        if (empty($code)) {
            Mage::getSingleton('core/session')
                ->addNotice(
                    $this->__('Wechat Connect process aborted.')
                );
            return $this;
        }

        $savedState = Mage::getSingleton('customer/session')->getWechatAuthState();
        if (!$state || $state != $savedState) {
            Mage::getSingleton('core/session')
                ->addNotice(
                    $this->__('Wechat Connect process aborted.')
                );
            return $this;
        }
        
        $client = Mage::getSingleton('inup_socialconnect/wechat_oauth_client');
        $token = $client->getAccessToken($code);

        $info = Mage::getModel('inup_socialconnect/wechat_info')->load($client->getOpenid());

        $customersByWechatId = Mage::helper('inup_socialconnect/wechat')
            ->getCustomersByWechatId($info->getOpenid());

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            if ($customersByWechatId->getSize()) {
                Mage::getSingleton('core/session')
                    ->addNotice(
                        $this->__('Your Wechat account is already connected to one of our store accounts.')
                    );

                return $this;
            }

            // Connect from account dashboard - attach
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            Mage::helper('inup_socialconnect/wechat')->connectByWechatId(
                $customer,
                $info->getOpenid(),
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your Wechat account is now connected to your store account. You can now login using our Wechat Login button or using store account credentials you will receive to your email address.')
            );

            return $this;
        }

        if ($customersByWechatId->getSize()) {
            // Existing connected user - login
            $customer = $customersByWechatId->getFirstItem();

            Mage::helper('inup_socialconnect/wechat')->loginByCustomer($customer);

            Mage::getSingleton('core/session')
                ->addSuccess(
                    $this->__('You have successfully logged in using your Wechat account.')
                );

            return $this;
        }

        $customersByEmail = Mage::helper('inup_socialconnect/wechat')
            ->getCustomersByEmail($info->getEmail());

        if ($customersByEmail->getSize()) {
            // Email account already exists - attach, login
            $customer = $customersByEmail->getFirstItem();

            Mage::helper('inup_socialconnect/wechat')->connectByWechatId(
                $customer,
                $info->getOpenid(),
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('We have discovered you already have an account at our store. Your Wechat account is now connected to your store account.')
            );

            return $this;
        }

        // New connection - create, attach, login
        $name = $info->getName();
        if (empty($name)) {
            throw new Exception(
                $this->__('Sorry, could not retrieve your Wechat name. Please try again.')
            );
        }

        Mage::helper('inup_socialconnect/wechat')->connectByCreatingAccount(
            $info->getEmail(),
            $info->getName(),
            $info->getOpenid(),
            $token
        );

        Mage::getSingleton('core/session')->addSuccess(
            $this->__('Your Wechat account is now connected to your new user account at our store. Now you can login using our Wechat Login button.')
        );

        return $this;
    }

}