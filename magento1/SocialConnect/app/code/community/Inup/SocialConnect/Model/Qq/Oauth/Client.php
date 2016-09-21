<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/16
 * Time: 10:13
 */
class Inup_SocialConnect_Model_Qq_Oauth_Client
{
    const REDIRECT_URI_ROUTE = 'socialconnect/qq/connect';
    const REQUEST_TOKEN_URI_ROUTE = 'socialconnect/qq/request';

    const OAUTH_URI = 'https://graph.qq.com/oauth2.0/authorize';
    const OAUTH_TOKEN_URI = 'https://graph.qq.com/oauth2.0/token';
    const OAUTH_OPEN_ID_URI = 'https://graph.qq.com/oauth2.0/me';
    const OAUTH_SERVICE_URI = 'https://graph.qq.com';

    const XML_PATH_ENABLED = 'customer/inup_socialconnect_qq/enabled';
    const XML_PATH_CLIENT_ID = 'customer/inup_socialconnect_qq/client_id';
    const XML_PATH_CLIENT_SECRET = 'customer/inup_socialconnect_qq/client_secret';

    protected $clientId = null;
    protected $clientSecret = null;
    protected $redirectUri = null;
    protected $client = null;
    protected $token = null;

    protected $openid = null;

    public function __construct()
    {
        if (($this->isEnabled = $this->_isEnabled())) {
            $this->clientId = $this->_getClientId();
            $this->clientSecret = $this->_getClientSecret();
            $this->redirectUri = Mage::getModel('core/url')->sessionUrlVar(
                Mage::getUrl(self::REDIRECT_URI_ROUTE)
            );
            $this->client = $this;
        }
    }

    public function isEnabled()
    {
        return (bool)$this->isEnabled;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function setAccessToken($token)
    {
        $this->token = $token;
    }

    public function getAccessToken($code = null)
    {
        if ($this->token) {
            return $this->token;
        }
        $token = Mage::getSingleton('customer/session')->getInupSocialconnectQqAccessToken();
        if ($token) {
            return $token;
        }

        $token = $this->fetchAccessToken($code);
        Mage::getSingleton('customer/session')->setInupSocialconnectQqAccessToken($token);
        return $token;
    }

    public function fetchAccessToken($code)
    {
        $http = new Zend_Http_Client(self::OAUTH_TOKEN_URI);
        $http->setParameterGet('client_id', $this->clientId);
        $http->setParameterGet('client_secret', $this->clientSecret);
        $http->setParameterGet('redirect_uri', $this->redirectUri);
        $http->setParameterGet('grant_type', 'authorization_code');
        $http->setParameterGet('code', $code);
        $response = $http->request(Zend_Http_Client::GET)->getBody();

        parse_str($response, $res);
        if (!isset($res['access_token'])) {
            throw new Exception(
                Mage::helper('inup_socialconnect')
                    ->__('Unable to retrieve request token.')
            );
        }
        $this->setAccessToken($res['access_token']);
        return $this->token;
    }

    public function setOpenid($openid)
    {
        Mage::getSingleton('customer/session')->setInupSocialconnectQqOpenid($openid);
        $this->openid = $openid;
    }

    public function getOpenid()
    {
        $this->openid = Mage::getSingleton('customer/session')->getInupSocialconnectQqOpenid();
        return $this->openid;
    }

    public function getState()
    {
        return Mage::getSingleton('customer/session')->getQQState();
    }

    public function fetchOpenid($token)
    {
        $http = new Zend_Http_Client(self::OAUTH_OPEN_ID_URI);
        $http->setParameterGet('access_token', $token);
        $response = $http->request(Zend_Http_Client::GET)->getBody();
        if (preg_match("/\(([^()]+|(?R))*\)/", $response, $matches)) {
            $res = json_decode($matches[1]);
        }
        if (!isset($res->openid)) {
            throw new Exception(
                Mage::helper('inup_socialconnect')
                    ->__('Unable to retrieve openid.')
            );
        }
        $this->setOpenid($res->openid);
        return $res->openid;
    }

    public function createAuthUrl()
    {
        return Mage::getUrl(self::REQUEST_TOKEN_URI_ROUTE);
    }

    public function redirectToAuthorize()
    {
        $state = md5(time());
        Mage::getSingleton('customer/session')->setQQState($state);
        $url = vsprintf(self::OAUTH_URI . "?client_id=%s&response_type=code&redirect_uri=%s&state=%s", [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => $state
        ]);
        Mage::app()->getResponse()->setRedirect($url)->sendResponse();
    }

    public function api($endpoint, $method = 'GET', $params = array())
    {
        $url = self::OAUTH_SERVICE_URI . $endpoint;

        $response = $this->_httpRequest($url, strtoupper($method), $params);

        return $response;
    }

    protected function _httpRequest($url, $method = 'GET', $params = array())
    {
        $client = new Zend_Http_Client($url);

        switch ($method) {
            case 'GET':
                $client->setMethod(Zend_Http_Client::GET);
                $client->setParameterGet($params);
                break;
            case 'POST':
                $client->setMethod(Zend_Http_Client::POST);
                $client->setParameterPost($params);
                break;
            case 'DELETE':
                $client->setMethod(Zend_Http_Client::DELETE);
                break;
            default:
                throw new Exception(
                    Mage::helper('inup_socialconnect')
                        ->__('Required HTTP method is not supported.')
                );
        }

        $response = $client->request();

        Inup_SocialConnect_Helper_Data::log($response->getStatus() . ' - ' . $response->getBody());

        $decodedResponse = json_decode($response->getBody());

        if ($response->isError()) {
            $status = $response->getStatus();
            if (($status == 400 || $status == 401 || $status == 429)) {
                if (isset($decodedResponse->error->message)) {
                    $message = $decodedResponse->error->message;
                } else {
                    $message = Mage::helper('inup_socialconnect')
                        ->__('Unspecified OAuth error occurred.');
                }

                throw new Inup_SocialConnect_Model_Weibo_Oauth_Exception($message);
            } else {
                $message = sprintf(
                    Mage::helper('inup_socialconnect')
                        ->__('HTTP error %d occurred while issuing request.'),
                    $status
                );

                throw new Exception($message);
            }
        }

        return $decodedResponse;
    }

    protected function _isEnabled()
    {
        return $this->_getStoreConfig(self::XML_PATH_ENABLED);
    }

    protected function _getClientId()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_ID);
    }

    protected function _getClientSecret()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_SECRET);
    }

    protected function _getStoreConfig($xmlPath)
    {
        return Mage::getStoreConfig($xmlPath, Mage::app()->getStore()->getId());
    }

}