<?php

class Magestore_Shopbybrand_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard//Mage_Core_Controller_Varien_Router_Abstract
{
	/**
	 * Initialize Controller Router
	*/
	public function initControllerRouters($observer)
	{
		$front = $observer->getEvent()->getFront();	
		$front->addRouter('shopbybrand', $this);
	}
	
	/**
	* Validate and Match shop view and modify request
	*/
	public function match(Zend_Controller_Request_Http $request)
	{
        $front = $this->getFront();
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
        $routerConfig = Mage::getStoreConfig('shopbybrand/general/router');
        $_end = Mage::getStoreConfig(Magestore_Shopbybrand_Helper_Data::XML_FRONTEND_LINK);
        $_path = urldecode(trim($request->getPathInfo(),'/'));
        
        if (strpos($_path,$_end)){
        	$_link_params = explode('/',str_replace($_end,'/',$_path),-1);
        }else{
        	$_link_params = explode('/',$_path.'/',-1);
        }
        $_count_params = count($_link_params);
        $found = false;
        if (isset($_link_params[0])){
            $router = $_link_params[0];
            if($router == $routerConfig){
                $request->setRouteName('shopbybrand')
                        ->setControllerModule('Magestore_Shopbybrand')
                        ->setModuleName('brand')
                        ;
                $module = 'shopbybrand';
                if(isset($_link_params[1]) && $_link_params[1])
                    $request->setControllerName($_link_params[1]);
                if(isset($_link_params[2]) && $_link_params[2])
                    $request->setActionName($_link_params[2]);
                // get controller name
                if ($request->getControllerName()) {
                    $controller = $request->getControllerName();
                } else {
                    if (!empty($_link_params[1])) {
                        $controller = $_link_params[1];
                    } else {
                        $controller = $front->getDefault('controller');
                        $request->setAlias(
                            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                            ltrim($request->getOriginalPathInfo(), '/')
                        );
                    }
                }
                
                // get action name
                if (empty($action)) {
                    if ($request->getActionName()) {
                        $action = $request->getActionName();
                    } else {
                        $action = !empty($_link_params[2]) ? $_link_params[2] : $front->getDefault('action');
                    }
                }

                //checking if this place should be secure
                $this->_checkShouldBeSecure($request, '/'.$module.'/'.$controller.'/'.$action);
                $controllerClassName = $this->_validateControllerClassName('Magestore_Shopbybrand', $controller);
                
                if (!$controllerClassName) {
                    return false;
                }
                $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $front->getResponse());
                
                if (!$controllerInstance->hasAction($action)) {
                    return false;
                }
                $found = true;
            }
        }
        if (!$found) {
            if ($this->_noRouteShouldBeApplied()) {
                $controller = 'index';
                $action = 'noroute';

                $controllerClassName = $this->_validateControllerClassName($realModule, $controller);
                if (!$controllerClassName) {
                    return false;
                }

                // instantiate controller class
                $controllerInstance = Mage::getControllerInstance($controllerClassName, $request,
                    $front->getResponse());

                if (!$controllerInstance->hasAction($action)) {
                    return false;
                }
            } else {
                return false;
            }
        }else{
            return true;
        }
        
	}
}