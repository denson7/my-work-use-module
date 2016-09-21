<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_WeiboController extends Inup_SocialConnect_Controller_Abstract
{

    public function requestAction()
    {
        $client = Mage::getSingleton('inup_socialconnect/weibo_oauth_client');
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
        Mage::helper('inup_socialconnect/weibo')->disconnect($customer);

        Mage::getSingleton('core/session')
            ->addSuccess(
                $this->__('You have successfully disconnected your Weibo account from our store account.')
            );
    }

    protected function _connectCallback()
    {
        if (!($params = $this->getRequest()->getParams())

        ) {
            return $this;
        }

        if (!isset($params['code'])) {
            Mage::getSingleton('core/session')
                ->addNotice(
                    $this->__('Weibo Connect process aborted.')
                );
            return $this;
        }
        $client = Mage::getSingleton('inup_socialconnect/weibo_oauth_client');
        $token = $client->getAccessToken($params['code']);

        $info = Mage::getModel('inup_socialconnect/weibo_info')->load($client->getId());

        $customersByWeiboId = Mage::helper('inup_socialconnect/weibo')
            ->getCustomersByWeiboId($client->getId());

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            if ($customersByWeiboId->getSize()) {
                Mage::getSingleton('core/session')
                    ->addNotice(
                        $this->__('Your Weibo account is already connected to one of our store accounts.')
                    );

                return $this;
            }

            // Connect from account dashboard - attach
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            Mage::helper('inup_socialconnect/weibo')->connectByWeiboId(
                $customer,
                $client->getId(),
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Your Weibo account is now connected to your store account. You can now login using our Weibo Login button or using store account credentials you will receive to your email address.')
            );

            return $this;
        }

        if ($customersByWeiboId->getSize()) {
            // Existing connected user - login
            $customer = $customersByWeiboId->getFirstItem();

            Mage::helper('inup_socialconnect/weibo')->loginByCustomer($customer);

            Mage::getSingleton('core/session')
                ->addSuccess(
                    $this->__('You have successfully logged in using your Weibo account.')
                );

            return $this;
        }

        $customersByEmail = Mage::helper('inup_socialconnect/weibo')
            ->getCustomersByEmail($info->getEmail());

        if ($customersByEmail->getSize()) {
            // Email account already exists - attach, login
            $customer = $customersByEmail->getFirstItem();

            Mage::helper('inup_socialconnect/weibo')->connectByWeiboId(
                $customer,
                $client->getId(),
                $token
            );

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('We have discovered you already have an account at our store. Your Weibo account is now connected to your store account.')
            );

            return $this;
        }

        // New connection - create, attach, login
        $name = $info->getName();
        if (empty($name)) {
            throw new Exception(
                $this->__('Sorry, could not retrieve your Weibo name. Please try again.')
            );
        }

        Mage::helper('inup_socialconnect/weibo')->connectByCreatingAccount(
            $info->getEmail(),
            $info->getName(),
            $client->getId(),
            $token
        );

        Mage::getSingleton('core/session')->addSuccess(
            $this->__('Your Weibo account is now connected to your new user account at our store. Now you can login using our Weibo Login button.')
        );

        return $this;
    }

}