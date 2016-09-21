<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Model_Weibo_Oauth_Client
{
    const REDIRECT_URI_ROUTE = 'socialconnect/weibo/connect';
    const REQUEST_TOKEN_URI_ROUTE = 'socialconnect/weibo/request';

    const OAUTH_URI = 'https://api.weibo.com/oauth2/authorize';
    const OAUTH_TOKEN_URI = 'https://api.weibo.com/oauth2/access_token';
    const OAUTH_SERVICE_URI = 'https://api.weibo.com/2';

    const XML_PATH_ENABLED = 'customer/inup_socialconnect_weibo/enabled';
    const XML_PATH_CLIENT_ID = 'customer/inup_socialconnect_weibo/client_id';
    const XML_PATH_CLIENT_SECRET = 'customer/inup_socialconnect_weibo/client_secret';

    protected $clientId = null;
    protected $clientSecret = null;
    protected $redirectUri = null;
    protected $client = null;
    protected $token = null;

    protected $uid = null;

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
        $token = Mage::getSingleton('customer/session')->getInupSocialconnectWeiboAccessToken();
        if ($token) {
            return $token;
        }

        $token = $this->fetchAccessToken($code);
        Mage::getSingleton('customer/session')->setInupSocialconnectWeiboAccessToken($token);
        return $token;
    }

    public function fetchAccessToken($code)
    {
        $http = new Zend_Http_Client(self::OAUTH_TOKEN_URI);
        $http->setParameterPost('client_id', $this->clientId);
        $http->setParameterPost('client_secret', $this->clientSecret);
        $http->setParameterPost('redirect_uri', $this->redirectUri);
        $http->setParameterPost('grant_type', 'authorization_code');
        $http->setParameterPost('code', $code);
        $response = $http->request(Zend_Http_Client::POST)->getBody();

        $res = json_decode($response);
        if (!isset($res->access_token)) {
            throw new Exception(
                Mage::helper('inup_socialconnect')
                    ->__('Unable to retrieve request token.' . $res->error_description)
            );
        }
        $this->setAccessToken($res->access_token);
        $this->setId($res->uid);
        return $this->token;
    }

    public function setId($uid)
    {
        Mage::getSingleton('customer/session')->setInupSocialconnectWeiboId($uid);
        $this->uid = $uid;
    }

    public function getId()
    {
        $this->uid = Mage::getSingleton('customer/session')->getInupSocialconnectWeiboId();
        return $this->uid;
    }

    public function createAuthUrl()
    {
        return Mage::getUrl(self::REQUEST_TOKEN_URI_ROUTE);
    }

    public function redirectToAuthorize()
    {
        $url = vsprintf(self::OAUTH_URI . "?client_id=%s&response_type=code&redirect_uri=%s", [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri
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