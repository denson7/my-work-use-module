<?php
/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_IndexController extends Mage_Core_Controller_Front_Action
{
    
    public function indexAction()
    {
	if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
	    $this->_redirect('customer/account/login');
	}
	if (Mage::getSingleton('customer/session')->isLoggedIn() && $this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session         = Mage::getSingleton('core/session');
            $emails           = $this->getRequest()->getPost('email'); //trim((string) $this->getRequest()->getPost('email'));
            $names            = $this->getRequest()->getPost('name'); //trim((string) $this->getRequest()->getPost('name'));

            
            $customerSession = Mage::getSingleton('customer/session');
            //$errors = array();
            try {
                foreach ($emails as $key_email => $email){
                    $name = trim((string) $names[$key_email]);
                    $email = trim((string) $email);
                    
                    ///////////////////////////////////////////
                    
                    $no_errors = true;
                    if (!Zend_Validate::is($email, 'EmailAddress')) {
                        //Mage::throwException($this->__('Please enter a valid email address.'));
                        //$errors[] = $this->__('Wrong email address (%s).', $email);
                        $session->addError($this->__('Wrong email address (%s).', $email));
                        $no_errors = false;
                    }
                    if ($name == ''){
                        //Mage::throwException($this->__('Please enter your friend name.'));
                        //$errors[] = $this->__('Friend name is required for (%s) on line %s.', $email, ($key_email+1));
                        $session->addError($this->__('Friend name is required for email: %s on line %s.', $email, ($key_email+1)));
                        $no_errors = false;
                    }
                    
                    if ($no_errors){
                        $referralModel = Mage::getModel('rewardpoints/referral');

                        $customer = Mage::getModel('customer/customer')
                                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                                        ->loadByEmail($email);

                        if ($referralModel->isSubscribed($email) || $customer->getEmail() == $email) {
                            //Mage::throwException($this->__('Email %s has been already submitted.', $email));
                            $session->addError($this->__('Email %s has been already submitted.', $email));
                        } else {
                            if ($referralModel->subscribe($customerSession->getCustomer(), $email, $name)) {
                                $session->addSuccess($this->__('Email %s was successfully invited.', $email));
                            } else {
                                $session->addError($this->__('There was a problem with the invitation email %s.', $email));
                            }
                        }
                    }
                    
                    ///////////////////////////////////////////
                }
                
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('%s', $e->getMessage()));
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the invitation.'));
            }
        }

        /*$handles = array('default');
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $handles[] = 'customer_account';
        }
        $this->loadLayout($handles);
        $this->renderLayout();*/

        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function rewardLoginAction()
    {
        Mage::getSingleton('customer/session')->setBeforeAuthUrl($this->_getRefererUrl());
        $this->_redirect('customer/account/login');
    }
    

    public function referralAction()
    {
        $this->indexAction();
    }

    public function pointsAction()
    {
        $this->indexAction();
    }


    public function goReferralAction(){
        
        $userId = (int) $this->getRequest()->getParam('referrer');
        if ($decrypt = $this->getRequest()->getParam('decript')){
            $userId = str_replace('j2t', '', base64_decode(trim(str_replace('-', '/', $decrypt))));
        }
        
        if ($userId){
            Mage::getSingleton('rewardpoints/session')->setReferralUser($userId);
        }
        //Mage::getSingleton('rewardpoints/session')->getReferralUser()
        //$url = Mage::getUrl();
        //$this->getResponse()->setRedirect($url);
        
        if ($url_redirection = Mage::getStoreConfig('rewardpoints/registration/referral_redirection', Mage::app()->getStore()->getId())){
            $this->_redirect($url_redirection);
        } else {
            /*$pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE);
            if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
                $this->_forward('defaultIndex');
            }*/
            $this->_redirect("/");
        }
        
    }

    public function removequotationAction(){
        Mage::getSingleton('rewardpoints/session')->setProductChecked(0);
        Mage::helper('rewardpoints/event')->setCreditPoints(0);
        $onestepcheckout = $this->getRequest()->getPost('is_onestepcheckout');
		$response['success'] = true;
		$response['message'] = '';
	Mage::helper('checkout/cart')->getCart()->getQuote()
                ->setRewardpointsQuantity(NULL)
                ->setRewardpointsDescription(NULL)
                ->setBaseRewardpoints(NULL)
                ->setRewardpoints(NULL)
                ->save();
    	if ($onestepcheckout){
			$this->processOneStepCheckoutElements($response);
		} else {
			$refererUrl = $this->_getRefererUrl();
			if (empty($refererUrl)) {
				$refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
			}
			$this->getResponse()->setRedirect($refererUrl);
		}
    }


    protected function processOneStepCheckoutElements($response)
	{
		// Add updated totals HTML to the output
		$html = $this->getLayout()
			->createBlock('onestepcheckout/summary')
			->setTemplate('onestepcheckout/summary.phtml')
			->toHtml();

		$html_reward = $this->getLayout()
			->createBlock('rewardpoints/coupon')
			->setTemplate('rewardpoints/reward_coupon_onestep.phtml')
			->toHtml();

		$response['summary'] = $html;
		$response['rewardcoupon'] = $html_reward;
		$this->getResponse()->setBody(Zend_Json::encode($response));
	}

    public function quotationAction(){
        $session = Mage::getSingleton('core/session');
        $points_value = $this->getRequest()->getPost('points_to_be_used');
        
	$onestepcheckout = $this->getRequest()->getPost('is_onestepcheckout');
		$response['success'] = true;
		$response['message'] = '';
	if (Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId())){
            if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId()) < $points_value){
                $points_max = (int)Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId());
                $session->addError($this->__('You tried to use %s loyalty points, but you can use a maximum of %s points per shopping cart.', ceil($points_value), $points_max));
                $points_value = $points_max;
		$response['error'] = true;
				$response['success'] = false;
				$response['message'] = $this->__('You tried to use %s loyalty points, but you can use a maximum of %s points per shopping cart.', ceil($points_value), $points_max);
            }
        }
        $quote_id = Mage::helper('checkout/cart')->getCart()->getQuote()->getId();

        Mage::getSingleton('rewardpoints/session')->setProductChecked(0);
        Mage::getSingleton('rewardpoints/session')->setShippingChecked(0);
        
        Mage::helper('rewardpoints/event')->setCreditPoints($points_value);
        
        Mage::helper('checkout/cart')->getCart()->getQuote()
                ->setRewardpointsQuantity($points_value)
                ->save();

	if ($onestepcheckout){
			$this->processOneStepCheckoutElements($response);
		} else {
		$refererUrl = $this->_getRefererUrl();
            if (empty($refererUrl)) {
                $refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
            }
            $this->getResponse()->setRedirect($refererUrl);	
	}
    }

    public function preDispatch()
    {
        
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $actions_array = array('referral', 'points');
        if (in_array($action, $actions_array)){
            $loginUrl = Mage::helper('customer')->getLoginUrl();

            if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            }
        }
    }
    
}
