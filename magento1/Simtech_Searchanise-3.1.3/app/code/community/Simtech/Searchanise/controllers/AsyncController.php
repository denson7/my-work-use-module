<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/
class Simtech_Searchanise_AsyncController extends Mage_Core_Controller_Front_Action
{
    protected $_notUseHttpRequestText = null;
    protected $_flShowStatusAsync = null;
    
    public function getNotUseHttpRequestText()
    {
        if (is_null($this->_notUseHttpRequestText)) {
            $this->_notUseHttpRequestText = $this->getRequest()->getParam(Simtech_Searchanise_Helper_ApiSe::NOT_USE_HTTP_REQUEST);
        }
        
        return $this->_notUseHttpRequestText;
    }
    
    public function checkNotUseHttpRequest()
    {
        return ($this->getNotUseHttpRequestText() == Simtech_Searchanise_Helper_ApiSe::NOT_USE_HTTP_REQUEST_KEY) ? true : false;
    }

    protected function getFlShowStatusAsync()
    {
        if (is_null($this->_flShowStatusAsync)) {
            $this->_flShowStatusAsync = $this->getRequest()->getParam(Simtech_Searchanise_Helper_ApiSe::FL_SHOW_STATUS_ASYNC);
        }
        
        return $this->_flShowStatusAsync;
    }

    protected function checkShowSatusAsync()
    {
        return ($this->getFlShowStatusAsync() == Simtech_Searchanise_Helper_ApiSe::FL_SHOW_STATUS_ASYNC_KEY) ? true : false;
    }

    /**
     * Dispatch event before action
     *
     * @return void
    */
    public function preDispatch()
    {
        // Do not start standart session
        $this->setFlag('', self::FLAG_NO_START_SESSION, 1); 
        $this->setFlag('', self::FLAG_NO_CHECK_INSTALLATION, 1);
        $this->setFlag('', self::FLAG_NO_COOKIES_REDIRECT, 0);
        $this->setFlag('', self::FLAG_NO_PRE_DISPATCH, 1);

        // Need for delete the "PDOExceptionPDOException" error
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, 1); 

        parent::preDispatch();

        return $this;
    }

    /*
     * async
    */
    public function indexAction()
    {
        if (Mage::helper('searchanise/ApiSe')->getStatusModule() == 'Y') {
            $checkKey = Mage::helper('searchanise')->checkPrivateKey();
            
            // not need because it checked in the "Async.php" block
            // if (Mage::helper('searchanise/ApiSe')->checkStartAsync()) {
            if (true) {
                $check = true;
                // code if need use httprequest
                // $check = $this->checkNotUseHttpRequest();
                // Mage::app('admin')->setUseSessionInUrl(false);
                // Mage::app('customer')->setUseSessionInUrl(false); // need check: sometimes not work properly (the async script will not start)
                // end code

                if ($check) {
                    @ignore_user_abort(true);
                    @set_time_limit(0);
                    if ($checkKey && $this->getRequest()->getParam('display_errors') === 'Y') {
                        @error_reporting(E_ALL | E_STRICT);
                        @ini_set('display_errors', 1);
                        @ini_set('display_startup_errors', 1);
                    } else {
                        @error_reporting(0);
                        @ini_set('display_errors', 0);
                        @ini_set('display_startup_errors', 0);
                    }
                    $flIgnoreProcessing = false;
                    if ($checkKey && $this->getRequest()->getParam('ignore_processing') === 'Y') {
                        $flIgnoreProcessing = true;
                    }

                    $result = Mage::helper('searchanise/ApiSe')->async($flIgnoreProcessing);

                    if ($this->checkShowSatusAsync()) {
                        echo 'Searchanise status sync: ';
                        echo $result;
                    }
                    
                    die();
                    
                } else {
                    @ignore_user_abort(false);
                    @set_time_limit(Mage::helper('searchanise/ApiSe')->getAjaxAsyncTimeout());
                    $asyncUrl = Mage::helper('searchanise/ApiSe')->getAsyncUrl(false, 0, false);

                    Mage::helper('searchanise/ApiSe')->httpRequest(
                        Zend_Http_Client::GET,
                        $asyncUrl,
                        array(
                            Simtech_Searchanise_Helper_ApiSe::NOT_USE_HTTP_REQUEST => Simtech_Searchanise_Helper_ApiSe::NOT_USE_HTTP_REQUEST_KEY,
                        ),
                        array(),
                        array(),
                        Mage::helper('searchanise/ApiSe')->getAjaxAsyncTimeout(),
                        2 // maxredirects
                    );
                }
            }
        }
        
        return $this;
    }
}