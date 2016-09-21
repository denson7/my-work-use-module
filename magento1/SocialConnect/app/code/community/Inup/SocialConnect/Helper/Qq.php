<?php

/**
 * Created by PhpStorm.
 * User: pgroot
 * Date: 16/8/11
 * Time: 19:32
 */
class Inup_SocialConnect_Helper_Qq extends Mage_Core_Helper_Abstract
{

    public function disconnect(Mage_Customer_Model_Customer $customer)
    {
        Mage::getSingleton('customer/session')
            ->unsInupSocialconnectQqUserinfo();

        $pictureFilename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . DS
            . 'inup'
            . DS
            . 'socialconnect'
            . DS
            . 'qq'
            . DS
            . $customer->getInupSocialconnectQid();

        if (file_exists($pictureFilename)) {
            @unlink($pictureFilename);
        }

        $customer->setInupSocialconnectQid(null)
            ->setInupSocialconnectQtoken(null)
            ->save();
    }

    public function connectByQqId(
        Mage_Customer_Model_Customer $customer,
        $openid,
        $token)
    {
        $customer->setInupSocialconnectQid($openid)
            ->setInupSocialconnectQtoken($token)
            ->save();

        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
    }

    public function connectByCreatingAccount(
        $email,
        $name,
        $openid,
        $token)
    {
        $customer = Mage::getModel('customer/customer');

        $name = explode(' ', $name, 2);

        if (count($name) > 1) {
            $firstName = $name[0];
            $lastName = $name[1];
        } else {
            $firstName = mb_substr($name[0], 0, 1);
            $lastName = mb_substr($name[0], 1);
        }

        $customer->setEmail($email)
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setInupSocialconnectQid($openid)
            ->setInupSocialconnectQtoken($token)
            ->setPassword($customer->generatePassword(10))
            ->save();

        $customer->setConfirmation(null);
        $customer->save();

        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

    }

    public function loginByCustomer(Mage_Customer_Model_Customer $customer)
    {
        if ($customer->getConfirmation()) {
            $customer->setConfirmation(null);
            $customer->save();
        }

        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
    }

    public function getCustomersByQqId($openid)
    {
        $customer = Mage::getModel('customer/customer');

        $collection = $customer->getCollection()
            ->addAttributeToSelect('inup_socialconnect_qtoken')
            ->addAttributeToFilter('inup_socialconnect_qid', $openid)
            ->setPageSize(1);

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                Mage::app()->getWebsite()->getId()
            );
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id',
                array('neq' => Mage::getSingleton('customer/session')->getCustomerId())
            );
        }

        return $collection;
    }

    public function getCustomersByEmail($email)
    {
        $customer = Mage::getModel('customer/customer');

        $collection = $customer->getCollection()
            ->addFieldToFilter('email', $email)
            ->setPageSize(1);

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                Mage::app()->getWebsite()->getId()
            );
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id',
                array('neq' => Mage::getSingleton('customer/session')->getCustomerId())
            );
        }

        return $collection;
    }

    public function getProperDimensionsPictureUrl($weiboId, $pictureUrl)
    {
        $pictureUrl = str_replace('_normal', '', $pictureUrl);

        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . 'inup'
            . '/'
            . 'socialconnect'
            . '/'
            . 'weibo'
            . '/'
            . $weiboId;

        $filename = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA)
            . DS
            . 'inup'
            . DS
            . 'socialconnect'
            . DS
            . 'weibo'
            . DS
            . $weiboId;

        $directory = dirname($filename);

        if (!file_exists($directory) || !is_dir($directory)) {
            if (!@mkdir($directory, 0777, true))
                return null;
        }

        if (!file_exists($filename) ||
            (file_exists($filename) && (time() - filemtime($filename) >= 3600))
        ) {
            $client = new Zend_Http_Client($pictureUrl);
            $client->setStream();
            $response = $client->request('GET');
            stream_copy_to_stream($response->getStream(), fopen($filename, 'w'));

            $imageObj = new Varien_Image($filename);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepFrame(false);
            $imageObj->resize(150, 150);
            $imageObj->save($filename);
        }

        return $url;
    }

}