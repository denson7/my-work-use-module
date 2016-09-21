<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/16
 * Time: 10:13
 */
class Inup_SocialConnect_Model_Qq_Info extends Varien_Object
{
    protected $params = [];

    protected $openid = null;
    protected $email = null;
    protected $name = null;

    protected $client = null;

    public function _construct()
    {
        parent::_construct();

        $this->client = Mage::getSingleton('inup_socialconnect/qq_oauth_client');
        if (!($this->client->isEnabled())) {
            return $this;
        }
        $this->params['access_token'] = $this->client->getAccessToken();
        $this->params['oauth_consumer_key'] = $this->client->getClientId();
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient(Inup_SocialConnect_Model_Qq_Oauth_Client $client)
    {
        $this->client = $client;
    }

    public function setAccessToken($token)
    {
        $this->client->setAccessToken($token);
    }

    /**
     * Get Weibo client's access token
     *
     * @return stdClass
     */
    public function getAccessToken()
    {
        return $this->client->getAccessToken();
    }

    public function load($openid = null)
    {
        $this->params['openid'] = $openid == null ? $this->client->getOpenid() : $openid;
        $info = $this->_load();

        $this->setOpenid($this->params['openid']);
        $this->setEmail(sprintf('%s@open.qq.com', $this->params['openid']));
        $this->setName($info->nickname);

        return $this;
    }

    public function setOpenid($openid)
    {
        $this->openid = $openid;
    }

    public function getOpenid()
    {
        return $this->openid;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    protected function _load()
    {
        try {
            $response = $this->client->api(
                '/user/get_user_info',
                'GET',
                $this->params
            );

            return $response;

        } catch (Inup_SocialConnect_Qq_Oauth_Exception $e) {
            $this->_onException($e);
        } catch (Exception $e) {
            $this->_onException($e);
        }
    }

    protected function _onException($e)
    {
        if ($e instanceof Inup_SocialConnect_Qq_Oauth_Exception) {
            Mage::getSingleton('core/session')->addNotice($e->getMessage());
        } else {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

}