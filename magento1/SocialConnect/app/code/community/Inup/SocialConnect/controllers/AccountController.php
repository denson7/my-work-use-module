<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_AccountController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return $this;
        }

        /*
         * Avoid situations where before_auth_url redirects when doing connect
         * and disconnect from account dashboard. Authenticate.
         */
        if (!Mage::getSingleton('customer/session')
            ->unsBeforeAuthUrl()
            ->unsAfterAuthUrl()
            ->authenticate($this)
        ) {
            $this->setFlag('', 'no-dispatch', true);
        }

    }

    public function weiboAction()
    {
        if (!($userInfo = Mage::getSingleton('customer/session')
                ->getInupSocialconnectWeiboUserinfo()) || !$userInfo->hasData()
        ) {

            $userInfo = Mage::getSingleton('inup_socialconnect/weibo_info_user')
                ->load();

            Mage::getSingleton('customer/session')
                ->setInupSocialconnectWeiboUserinfo($userInfo);
        }

        Mage::register('inup_socialconnect_weibo_userinfo', $userInfo);

        $this->loadLayout();
        $this->renderLayout();
    }

    public function qqAction()
    {
        $userInfo = Mage::getSingleton('inup_socialconnect/qq_info_user')
            ->load();

        Mage::register('inup_socialconnect_qq_userinfo', $userInfo);

        $this->loadLayout();
        $this->renderLayout();
    }

}