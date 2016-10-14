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
 * @copyright  Copyright (c) 2015 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_Model_Observer extends Mage_Core_Model_Abstract {

    const XML_PATH_NOTIFICATION_NOTIFICATION_DAYS = 'rewardpoints/notifications/notification_days';
    const XML_PATH_EXPIRY_NOTIFICATION_ACTIVE = 'rewardpoints/notifications/expiry_notification_active';
    const XML_PATH_NOTIFICATION_POINTS_NOTIFICATIONS = 'rewardpoints/status_notification/points_notifications';
    const XML_PATH_EVENTS_EVENT_LIST = 'rewardpoints/events/event_list';
    const XML_PATH_POINTS_DURATION = 'rewardpoints/default/points_duration';
    const XML_PATH_CRON_REMOVE = 'rewardpoints/default/cron_remove';
    const XML_PATH_SHOW_DASHBOARD = 'rewardpoints/admin_dashboard/show_dashboard';
    const XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SHOW = 'rewardpoints/design/small_inline_image_show';
    const XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SIZE = 'rewardpoints/design/small_inline_image_size';
    const XML_PATH_REFERRAL_GUEST_ALLOW = 'rewardpoints/registration/referral_guestallow';
    const XML_PATH_FORCE_STORE = 'rewardpoints/default/force_store';
    const XML_PATH_FIRST_ORDER_POINT = 'rewardpoints/first_order/point_qty';
    const XML_PATH_FIRST_ORDER_MIN = 'rewardpoints/first_order/min_order';
    const XML_PATH_FIRST_ORDER_CUSTOMER_ID = 'rewardpoints/first_order/from_customer_id';

    /* public function checkConfig($observer){

      } */


    /* protected function refreshPointStatistics () {
      //load all points having starting date or ending date today
      $collection = Mage::getModel('rewardpoints/stats')->getCollection();
      $collection->addStartEndDays();
      $collection->groupByCustomer();
      } */

    public function fixAdminCustomerStoreId(Varien_Event_Observer $observer) {
        if (Mage::getStoreConfig(self::XML_PATH_FORCE_STORE)) {
            $customer = $observer->getEvent()->getCustomer();
            $request = $observer->getEvent()->getRequest();
            $storeId = $customer->getSendemailStoreId();
            if ($customer->getWebsiteId() && !$storeId) {
                $storeIds = Mage::app()->getWebsite($customer->getWebsiteId())->getStoreIds();
                reset($storeIds);
                $storeId = current($storeIds);
            }
            if (!$customer->getStoreId() && $storeId) {
                $customer->setStoreId($storeId);
            }
        }
    }

    public function modifyGuestCheckoutAvailability(Varien_Event_Observer $observer) {
        $result = $observer->getEvent()->getResult();
        $quote = $observer->getEvent()->getQuote();
        if (Mage::getStoreConfig(self::XML_PATH_REFERRAL_GUEST_ALLOW, $quote->getStoreId()) && Mage::getSingleton('rewardpoints/session')->getReferralUser()) {
            $result->setIsAllowed(false);
        }
    }

    public function saveProductForm(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        $request = $observer->getEvent()->getRequest();
        $productData = $request->getPost('product');
        if (isset($productData['reward_points_groups'])) {
            //print_r($productData['reward_points_groups']);
            //die;
            $reward_points_groups = '';
            $reward_points_groups_array = array();
            if (is_array($productData['reward_points_groups']) && sizeof($productData['reward_points_groups'])) {
                foreach ($productData['reward_points_groups'] as $value) {
                    if ($value['delete'] != 1 && $value['point'] > 0 && $value['group_id'] > 0) {
                        $reward_points_groups_array[] = array("point" => $value['point'], "group_id" => $value['group_id']);
                    }
                }
                if (sizeof($reward_points_groups_array)) {
                    $reward_points_groups = Mage::helper('core')->jsonEncode($reward_points_groups_array);
                }
                //$reward_points_groups = Mage::helper('core')->jsonEncode($productData['reward_points_groups']);

                $productData['reward_points_groups'] = $reward_points_groups;
                $request->setPost('product', $productData);
                $product->setRewardPointsGroups($reward_points_groups);
            } else {
                $product->setRewardPointsGroups(NULL);
            }
        }
    }

    public function modifyProductForm(Varien_Event_Observer $observer) {
        $block = $observer->getEvent()->getBlock();
        $form = $block->getForm();

        if (is_object($form)) {
            $list_price = $form->getElement('reward_points_groups');
            if ($list_price) {
                $list_price->setRenderer(
                        $block->getLayout()->createBlock('rewardpoints/adminhtml_listpointgroup')
                );
            }
        }
    }

    public function addMassAction($observer) {
        $block = $observer->getEvent()->getBlock();
        if (get_class($block) == 'Mage_Adminhtml_Block_Widget_Grid_Massaction' && $block->getRequest()->getControllerName() == 'customer') {
            $block->addItem('rewardpoints', array(
                'label' => 'Flush Customer Points',
                'url' => $block->getUrl('rewardpoints/adminhtml_clientpoints/deleteFromGrid'),
                'confirm' => Mage::helper('rewardpoints')->__('Are you sure that you want to remove customer points?'),
            ));
        }
        if (get_class($block) == 'Mage_Adminhtml_Block_Customer' && $block->getRequest()->getControllerName() == 'customer') {
            $message = Mage::helper('rewardpoints')->__('Are you sure that you want to flush customer points?');

            $block->addButton('rewardpoints_flush_all', array(
                'label' => Mage::helper('rewardpoints')->__('Flush All Customer Points'),
                'class' => 'delete',
                'onclick' => 'confirmSetLocation(\'' . $message . '\', \'' . $block->getUrl('rewardpoints/adminhtml_clientpoints/deleteAllFromGrid') . '\')',
            ));
        }
    }

    public function adminConfiguration(Varien_Event_Observer $observer) {
        if (is_object(Mage::app()) && is_object(Mage::app()->getRequest()) &&
                Mage::app()->getRequest()->getParam('section') == 'rewardpoints' &&
                is_object($observer->getEvent()) && $observer->getEvent()->getData('config_data') &&
                ($groups = $observer->getEvent()->getData('config_data')->getData('groups')) &&
                isset($groups['module_serial']) && isset($groups['module_serial']['fields']) &&
                isset($groups['module_serial']['fields']['key']) && isset($groups['module_serial']['fields']['key']['inherit']) &&
                $groups['module_serial']['fields']['key']['inherit'] == 1
        ) {
            $website = Mage::app()->getRequest()->getParam('website');
            if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) { // store level
                $store_id = Mage::getModel('core/store')->load($code)->getId();
            } elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) { // website level
                $website_id = Mage::getModel('core/website')->load($code)->getId();
                $store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
            } else { // default level
                $store_id = 0;
            }

            $module_key = Mage::getStoreConfig('rewardpoints/module_serial/key', $store_id);

            $exceptions = array();
            $exceptions[] = Mage::helper('rewardpoints')->__(base64_decode("U2VyaWFsIHVzZWQgaW4gaW52YWxpZCwgdGhlcmVmb3JlLCB5b3VyIGNvbmZpZ3VyYXRpb24gY2Fubm90IGJlIHNhdmVkLg=="));

            $version = Mage::getConfig()->getModuleConfig("J2t_Rewardpoints")->version;
            $version_array = explode('.', $version);
            $module_branch_version = $version_array[0] . '.' . $version_array[1];

            $ser_name_code = 'verser';
            $store_code = 'default';

            if ($current_store = Mage::app()->getRequest()->getParam('store')) {
                $store_code = $current_store;
                $store = Mage::app()->getStore();
            } else {
                $store = Mage::getModel('core/store')->load($store_id);
            }

            $url = parse_url($store->getBaseUrl());
            $domain = $url['host'];


            $url = "http://www." . base64_decode("ajJ0LWRlc2lnbi5uZXQ=") . "/j2tmoduleintegrity/index/checkIntegrityNew/version/$module_branch_version/serial/$module_key/code/rewardpoints/domain/$domain";
            $curl = new Varien_Http_Adapter_Curl();
            $curl->setConfig(array(
                'timeout' => 20
            ));

            $curl->write(Zend_Http_Client::GET, $url, '1.0');
            $data = $curl->read();

            $fs = false;
            if ($data === false || $curl->getErrno()) {
                $exceptions[] = Mage::helper('rewardpoints')->__(base64_decode("Q1VSTCBlcnJvciAlcw=="), "(#{$curl->getErrno()}) / " . $curl->getError());
                $fs = true;
            } else {
                $exceptions[] = Mage::helper('rewardpoints')->__(base64_decode("Tm8gQ1VSTCBhY2Nlc3MgZXJyb3Jz"));
            }
            $return_curl = preg_split('/^\r?$/m', $data, 2);
            $return_curl = trim($return_curl[1]);
            $curl->close();

            if ($return_curl === "" && $return_curl !== "0" && $return_curl !== "1" && !$fs) {
                $return_curl = 1;
            } elseif ($return_curl != "1") {

                $return_curl = 0;
            }

            if (!$return_curl) {
                throw new Exception("\n" . implode("\n", $exceptions));
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpoints')->__(base64_decode('W1tZb3VyIHNlcmlhbCBpcyB2YWxpZCBhbmQgY29uZmlndXJhdGlvbiBjYW4gYmUgc2F2ZWQuXV0=')));
            }
        }
    }

    public function verifyCustomerLinks(Varien_Event_Observer $observer) {
        if (Mage::helper('rewardpoints')->isModuleActive()) {
            $update = $observer->getEvent()->getLayout()->getUpdate();
            $update->addHandle('customer_new_handle');
        }
    }

    public function setPointsOnTotals(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();
        if ($block->getTemplate() == 'amrules/checkout/discount.phtml' && Mage::getConfig()->getModuleConfig('Amasty_Rules')->is('active', 'true') && Mage::getStoreConfig('amrules/general/breakdown') && Mage::helper('rewardpoints')->isModuleActive()) {
            $block->setTemplate('rewardpoints/total/default.phtml');
        }
    }

    public function modifyDashboardTab($observer) {
        if (Mage::getStoreConfig(self::XML_PATH_SHOW_DASHBOARD)) {
            $block = $observer->getBlock();
            if (!isset($block))
                return;

            
            if (is_object(Mage::app()) && is_object(Mage::app()->getFrontController()) && is_object(Mage::app()->getFrontController()->getAction()) &&
                    Mage::app()->getFrontController()->getAction()->getFullActionName() === 'adminhtml_dashboard_index') {
                if ($block->getNameInLayout() === 'dashboard') {
                    /* $block->setChild('totals',
                      //$this->getLayout()->createBlock('adminhtml/dashboard_totals')
                      $block->getLayout()->createBlock('rewardpoints/adminhtml_dashboard_totals')
                      ); */
                    $diagrams = $block->getChild('diagrams');
                    
                    if (is_object($diagrams) && method_exists($diagrams, 'addTab')) {
                        $diagrams->addTab('gather', array(
                            'label' => Mage::helper('rewardpoints')->__('Gathered Points'),
                            'content' => $block->getLayout()->createBlock('rewardpoints/adminhtml_dashboard_tab_gather')->toHtml(),
                        ));

                        $diagrams->addTab('spend', array(
                            'label' => Mage::helper('rewardpoints')->__('Points Used'),
                            'content' => $block->getLayout()->createBlock('rewardpoints/adminhtml_dashboard_tab_spend')->toHtml(),
                        ));
                    }
                }
            }
        }
    }

    public function rewardOnDashboard(Varien_Event_Observer $observer) {
        $output = '';
        $blockTab = Mage::app()->getRequest()->getParam('block');
        //if (in_array($blockTab, array('tab_gather', 'tab_spend', 'totals'))) {
        //echo '>> '.$blockTab.' <<';
        //echo '<br />'; 
        if (in_array($blockTab, array('tab_gather', 'tab_spend'))) {

            $magento_block = Mage::getSingleton('core/layout');
            $output = $magento_block->createBlock('rewardpoints/adminhtml_dashboard_' . $blockTab)->toHtml();
            //$this->getResponse()->setBody($output);
            echo $output;
            die;
        }
    }

    public function appendAdminBlocks(Varien_Event_Observer $observer) {
        if (Mage::getStoreConfig(self::XML_PATH_SHOW_DASHBOARD)) {
            $block = $observer->getBlock();

            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $fileName = $block->getTemplateFile();
                $thisClass = get_class($block);

                if ($block->getType() == 'adminhtml/dashboard_totals') {

                    $html = $transport->getHtml();
                    $magento_block = Mage::getSingleton('core/layout');
                    $productsHtml = $magento_block->createBlock('rewardpoints/adminhtml_dashboard_totals');
                    $extraHtml = $productsHtml->toHtml();
                    $script_element = '<script type="text/javascript">$("dashboard_diagram_totals").insert($("rewardpoints_dashboard_diagram_totals").descendants()[0]); $("rewardpoints_dashboard_diagram_totals").remove()</script>';


                    $transport->setHtml($html . $extraHtml . $script_element);
                }
            } else {
                if ($block->getType() == 'adminhtml/dashboard_totals') {
                    $magento_block = Mage::getSingleton('core/layout');
                    $productsHtml = $magento_block->createBlock('rewardpoints/adminhtml_dashboard_totals');
                    $extraHtml = $productsHtml->toHtml();
                    echo $extraHtml;
                }
            }
        }
    }

    public function addRewardDetailsFront(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();

        $array_ctrl = array("sales_order_view", "sales_order_invoice");
        $array_blockname = array("order_items", "content");

        $active_top_message = Mage::getStoreConfig('rewardpoints/top_cart_messages/show_information', Mage::app()->getStore()->getId());
        $block_default_top_message = Mage::getStoreConfig('rewardpoints/top_cart_messages/block_default', Mage::app()->getStore()->getId());
        $top_message_duration = (int) Mage::getStoreConfig('rewardpoints/top_cart_messages/message_duration', Mage::app()->getStore()->getId());


        $review_points = (int) Mage::getStoreConfig('rewardpoints/other_points/review_points', Mage::app()->getStore()->getId());
        $review_points_show = Mage::getStoreConfig('rewardpoints/other_points/show_review_points', Mage::app()->getStore()->getId());

        $tag_points = (int) Mage::getStoreConfig('rewardpoints/other_points/tag_points', Mage::app()->getStore()->getId());
        $tag_points_show = Mage::getStoreConfig('rewardpoints/other_points/show_tag_points', Mage::app()->getStore()->getId());

        $poll_points = (int) Mage::getStoreConfig('rewardpoints/other_points/poll_points', Mage::app()->getStore()->getId());
        $poll_points_show = Mage::getStoreConfig('rewardpoints/other_points/show_poll_points', Mage::app()->getStore()->getId());

        $newsletter_points = (int) Mage::getStoreConfig('rewardpoints/other_points/newsletter_points', Mage::app()->getStore()->getId());
        $newsletter_points_show = Mage::getStoreConfig('rewardpoints/other_points/show_newsletter_points', Mage::app()->getStore()->getId());

        $registration_points = (int) Mage::getStoreConfig('rewardpoints/registration/registration_points', Mage::app()->getStore()->getId());
        $show_registration_points = Mage::getStoreConfig('rewardpoints/registration/show_registration_points', Mage::app()->getStore()->getId());

        if (is_object(Mage::app()) && is_object(Mage::app()->getFrontController()) && is_object(Mage::app()->getFrontController()->getAction()) && Mage::helper('rewardpoints')->isModuleActive()) {
            $extraHtml = '';
            $referral_side_area = Mage::getStoreConfig('rewardpoints/registration/referral_side', Mage::app()->getStore()->getId());

            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $html = $transport->getHtml();
            }

            if (($block->getNameInLayout() == 'left' || $block->getBlockAlias() == 'left') && $referral_side_area == 'left') {
                $magento_block = Mage::getSingleton('core/layout');
                $productsHtml = $magento_block->createBlock('rewardpoints/referral');
                $productsHtml->setTemplate('rewardpoints/side.phtml');
                $productsHtml->setNameInLayout("referral_side_left");
                $extraHtml = $productsHtml->toHtml();
            } else if (($block->getNameInLayout() == 'right' || $block->getBlockAlias() == 'right') && $referral_side_area == 'right') {
                $magento_block = Mage::getSingleton('core/layout');
                $productsHtml = $magento_block->createBlock('rewardpoints/referral');
                $productsHtml->setTemplate('rewardpoints/side.phtml');
                $productsHtml->setNameInLayout("referral_side_left");

                $extraHtml = $productsHtml->toHtml();
            }
            if (version_compare(Mage::getVersion(), '1.5.0', '>=') && $extraHtml) {
                $transport->setHtml($extraHtml . $html);
            } else if ($extraHtml) {
                echo $extraHtml;
            }
        }
        if (is_object(Mage::app()) && is_object(Mage::app()->getFrontController()) && is_object(Mage::app()->getFrontController()->getAction()) &&
                (Mage::app()->getFrontController()->getAction()->getFullActionName() == 'customer_account_index' && ($block->getNameInLayout() == 'customer_account_dashboard_top' || $block->getBlockAlias() == 'customer_account_dashboard_top')) && Mage::helper('rewardpoints')->isModuleActive()) {

            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $html = $transport->getHtml();
            }

            $magento_block = Mage::getSingleton('core/layout');
            $productsHtml = $magento_block->createBlock('rewardpoints/dashboard');
            $productsHtml->setTemplate('rewardpoints/dashboard_points.phtml');
            $productsHtml->setNameInLayout("customer_account_points");
            $extraHtml = $productsHtml->toHtml();


            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport->setHtml($html . $extraHtml);
            } else {
                echo $extraHtml;
            }
        }

        //one step checkout form injection
        if (is_object(Mage::app()) && is_object(Mage::app()->getFrontController()) && is_object(Mage::app()->getFrontController()->getAction()) &&
                (Mage::app()->getFrontController()->getAction()->getFullActionName() == 'onestepcheckout_index_index' && ($block->getNameInLayout() == 'choose-payment-method' || $block->getBlockAlias() == 'choose-payment-method')) && Mage::helper('rewardpoints')->isModuleActive()) {

            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $html = $transport->getHtml();
            }


            $magento_block = Mage::getSingleton('core/layout');
            $productsHtml = $magento_block->createBlock('rewardpoints/coupon');
            $productsHtml->setTemplate('rewardpoints/reward_coupon_onestep_js.phtml');
            $productsHtml->setNameInLayout("coupon_points_onestepcheckout_js");
            $extraJsHtml = $productsHtml->toHtml();

            $magento_block = Mage::getSingleton('core/layout');
            $productsHtml = $magento_block->createBlock('rewardpoints/coupon');
            $productsHtml->setTemplate('rewardpoints/reward_coupon_onestep.phtml');
            $productsHtml->setNameInLayout("coupon_points_onestepcheckout");
            $extraHtml = $productsHtml->toHtml();


            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport->setHtml($html . $extraJsHtml . $extraHtml);
            } else {
                echo $extraJsHtml . $extraHtml;
            }
        }


        if (is_object(Mage::app()) && is_object(Mage::app()->getFrontController()) && is_object(Mage::app()->getFrontController()->getAction()) &&
                (Mage::app()->getFrontController()->getAction()->getFullActionName() == 'customer_account_create' && $block->getNameInLayout() == 'customer.form.register.fields.before' && (($newsletter_points && $newsletter_points_show) || ($registration_points && $show_registration_points))) && Mage::helper('rewardpoints')->isModuleActive()) {
            $img = '';
            $extra_message = '';
            if (Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SHOW, Mage::app()->getStore()->getId()) && ($img_size = Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SIZE, Mage::app()->getStore()->getId()))) {
                $img = '<img src="' . Mage::helper('rewardpoints')->getResizedUrl('j2t_image_small.png', $img_size, $img_size) . '" alt="' . Mage::helper('rewardpoints')->__("Reward points") . '" width="' . $img_size . '" height="' . $img_size . '" /> ';
            }

            if ($registration_points && $show_registration_points) {
                $extra_message .= '<div class="j2t-rewards-registration-points">' . $img . Mage::helper('rewardpoints')->__('Collect %s points when you create a new account.', $registration_points) . '</div>';
            }

            if ($newsletter_points && $newsletter_points_show) {
                $extra_message .= '<div class="j2t-rewards-newsletter-points">' . $img . Mage::helper('rewardpoints')->__('Sign up to our newsletter and collect %s extra points.', $newsletter_points) . '</div>';
            }

            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $html = $transport->getHtml();
                $transport->setHtml($extra_message . $html);
            } else {
                echo $extra_message;
            }
        }

        if ($block->getType() == 'poll/activePoll' && $poll_points_show && $poll_points && Mage::helper('rewardpoints')->isModuleActive()) {
            $img = '';
            if (Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SHOW, Mage::app()->getStore()->getId()) && ($img_size = Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SIZE, Mage::app()->getStore()->getId()))) {
                $img = '<img src="' . Mage::helper('rewardpoints')->getResizedUrl('j2t_image_small.png', $img_size, $img_size) . '" alt="' . Mage::helper('rewardpoints')->__("Reward points") . '" width="' . $img_size . '" height="' . $img_size . '" /> ';
            }
            $extra_message = '<span class="j2t-rewards-poll-points">' . $img . Mage::helper('rewardpoints')->__('Participate in a poll and collect %s points.', $poll_points) . '</span>';
            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $html = $transport->getHtml();
                $transport->setHtml($extra_message . $html);
            } else {
                echo $extra_message;
            }
        }

        if ($block->getNameInLayout() == 'product.tag.list.list.before' && $tag_points_show && $tag_points && Mage::helper('rewardpoints')->isModuleActive()) {
            $img = '';
            if (Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SHOW, Mage::app()->getStore()->getId()) && ($img_size = Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SIZE, Mage::app()->getStore()->getId()))) {
                $img = '<img src="' . Mage::helper('rewardpoints')->getResizedUrl('j2t_image_small.png', $img_size, $img_size) . '" alt="' . Mage::helper('rewardpoints')->__("Reward points") . '" width="' . $img_size . '" height="' . $img_size . '" /> ';
            }
            $extra_message = '<span class="j2t-rewards-tag-points">' . $img . Mage::helper('rewardpoints')->__('Tag a product and collect %s points.', $tag_points) . '</span>';
            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $html = $transport->getHtml();
                $transport->setHtml($html . $extra_message);
            } else {
                echo $extra_message;
            }
        }


        if (is_object(Mage::app()) && is_object(Mage::app()->getFrontController()) && is_object(Mage::app()->getFrontController()->getAction()) &&
                ($block->getType() == 'review/helper' || $block->getType() == 'review/product_view_list') && $review_points && $review_points_show && Mage::app()->getFrontController()->getAction()->getFullActionName() == 'catalog_product_view' && Mage::helper('rewardpoints')->isModuleActive()) {
            $img = '';
            if (Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SHOW, Mage::app()->getStore()->getId()) && ($img_size = Mage::getStoreConfig(self::XML_PATH_DESIGN_SMALL_INLINE_IMAGE_SIZE, Mage::app()->getStore()->getId()))) {
                $img = '<img src="' . Mage::helper('rewardpoints')->getResizedUrl('j2t_image_small.png', $img_size, $img_size) . '" alt="' . Mage::helper('rewardpoints')->__("Reward points") . '" width="' . $img_size . '" height="' . $img_size . '" /> ';
            }
            if ($block->getType() == 'review/product_view_list' && !count($block->getReviewsCollection()->getItems())) {
                return false;
            }
            $extra_message = '<span class="j2t-rewards-review-points">' . $img . Mage::helper('rewardpoints')->__('Review and collect %s points.', $review_points) . '</span>';
            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $html = $transport->getHtml();
                //$transport->setHtml($html.$extra_message);
                if ($block->getType() == 'review/product_view_list') {
                    $transport->setHtml($extra_message . $html);
                } else {
                    $transport->setHtml($html . $extra_message);
                }
            } else {
                echo $extra_message;
            }
        }

        if (is_object(Mage::app()) && is_object(Mage::app()->getFrontController()) && is_object(Mage::app()->getFrontController()->getAction()) &&
                $active_top_message && ($block->getNameInLayout() == $block_default_top_message || $block->getBlockAlias() == $block_default_top_message) && Mage::app()->getFrontController()->getAction()->getFullActionName() == "checkout_cart_index" && Mage::helper('rewardpoints')->isModuleActive()) {

            $quote = Mage::getSingleton('checkout/session')->getQuote();
            if (is_object($quote) && $quote->getId()) {
                $collection = Mage::getModel('rewardpoints/pointrules')->getCollection();
                $storeId = Mage::app()->getStore()->getId();
                $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
                $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                $rules = $collection->setValidationFilter($websiteId, $customerGroupId);

                $messages = array();
                foreach ($rules as $rule) {
                    if (!$rule->getStatus())
                        continue;
                    $rule_validate = Mage::getModel('rewardpoints/pointrules')->load($rule->getRuleId());
                    if (!$rule_validate->validate($quote) &&
                            (
                            $rule_validate->getActionType() == J2t_Rewardpoints_Model_Pointrules::RULE_ACTION_TYPE_MULTIPLY || $rule_validate->getActionType() == J2t_Rewardpoints_Model_Pointrules::RULE_ACTION_TYPE_DIVIDE || $rule_validate->getActionType() == J2t_Rewardpoints_Model_Pointrules::RULE_ACTION_TYPE_ADD
                            )
                    ) {
                        if (($labels = $rule_validate->getLabels()) && $labels_array = unserialize($rule_validate->getLabels())) {
                            if (isset($labels_array[$storeId]) && trim($labels_array[$storeId]) != "") {
                                $messages[] = $labels_array[$storeId];
                            }
                        }
                    }
                }

                if (sizeof($messages)) {
                    //$extra_message = '<ul class="messages rewardpoints-msgs"><li class="success-msg"><ul><li><span id="j2t-rewardpoints-top-content">'.$messages[0].'</span></li></ul></li></ul>';
                    $extra_message = '<ul class="messages rewardpoints-msgs"><li class="success-msg"><ul><li>';
                    foreach ($messages as $key => $message) {
                        $extra_mess_style = '';
                        if ($key > 0) {
                            $extra_mess_style = 'style="display: none"';
                        }
                        $extra_message .= '<span class="j2trewardpoints-top-slide" id="j2t-rewardpoints-top-content-' . $key . '" ' . $extra_mess_style . '>' . $message . '</span>';
                    }
                    $extra_message .= '</li></ul></li></ul>';

                    if (sizeof($messages) > 1) {

                        if (version_compare(Mage::getVersion(), '1.4.0', '>=')) {
                            $json_js_array = Mage::helper('core')->jsonEncode($messages);
                        } else {
                            $json_js_array = Zend_Json::encode($messages);
                        }
                        $extra_message .= '<script type="text/javascript">rewards_start_slideshow(0, ' . (sizeof($messages) - 1) . ', ' . $top_message_duration . ');
   
                        function rewards_start_slideshow(start_frame, end_frame, delay) {
                            setTimeout(rewards_switch_slides(start_frame,start_frame,end_frame, delay), delay);
                        }

                        function rewards_switch_slides(frame, start_frame, end_frame, delay) {
                            return (function() {
                                Effect.Fade(\'j2t-rewardpoints-top-content-\' + frame);
                                if (frame == end_frame) { frame = start_frame; } else { frame = frame + 1; }
                                setTimeout(function (){
                                    Effect.Appear(\'j2t-rewardpoints-top-content-\' + frame);
                                }, 850);
                                setTimeout(rewards_switch_slides(frame, start_frame, end_frame, delay), delay + ' . $top_message_duration . ');
                            })
                        }</script>';
                    }
                    if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                        $transport = $observer->getTransport();
                        $html = $transport->getHtml();
                        $transport->setHtml($html . $extra_message);
                    } else {
                        echo $extra_message;
                    }
                }
            }
        }

        if (is_object(Mage::app()->getFrontController()) && is_object(Mage::app()->getFrontController()->getAction()) && (
                (Mage::app()->getFrontController()->getAction()->getFullActionName() == 'sales_order_view' && ( $block->getNameInLayout() == "order_items" || $block->getBlockAlias() == "order_items") ) || (Mage::app()->getFrontController()->getAction()->getFullActionName() == 'sales_order_invoice' && ( $block->getNameInLayout() == "invoice_comments" || $block->getBlockAlias() == "invoice_comments") ) || (Mage::app()->getFrontController()->getAction()->getFullActionName() == 'sales_order_printInvoice' && ( $block->getNameInLayout() == "content" || $block->getBlockAlias() == "content") )
                ) && Mage::helper('rewardpoints')->isModuleActive()) {

            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $html = $transport->getHtml();
            }
            $magento_block = Mage::getSingleton('core/layout');
            if (is_object(Mage::app()) && is_object(Mage::app()->getFrontController()) && is_object(Mage::app()->getFrontController()->getAction()) &&
                    Mage::app()->getFrontController()->getAction()->getFullActionName() == "sales_order_printInvoice") {
                $productsHtml = $magento_block->createBlock('rewardpoints/details_rewardinvoice');
            } else {
                $productsHtml = $magento_block->createBlock('rewardpoints/details_reward');
            }

            $productsHtml->setTemplate('rewardpoints/details.phtml');
            $productsHtml->setNameInLayout("reward_details");
            $extraHtml = $productsHtml->toHtml();
            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport->setHtml($html . $extraHtml);
            } else {
                echo $extraHtml;
            }
        }
    }

    public function addRewardDetailsAdmin(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();

        //echo $block->getNameInLayout()." ";

        $array_ctrl = array("sales_order_invoice", "sales_order");
        $array_blockname = array("order_totals", "invoice_totals");
        if ($block->getTemplate() != 'sales/order/view/tab/info.phtml' && (
                in_array($block->getNameInLayout(), $array_blockname) || in_array($block->getBlockAlias(), $array_blockname)
                ) && in_array(Mage::app()->getRequest()->getControllerName(), $array_ctrl)
        /* && Mage::app()->getRequest()->getActionName() != 'email' */) {

            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $html = $transport->getHtml();
            }
            $magento_block = Mage::getSingleton('core/layout');
            if (Mage::app()->getRequest()->getControllerName() == "sales_order_invoice") {
                $productsHtml = $magento_block->createBlock('rewardpoints/adminhtml_details_rewardinvoice');
            } else {
                $productsHtml = $magento_block->createBlock('rewardpoints/adminhtml_details_reward');
            }

            $productsHtml->setTemplate('rewardpoints/details.phtml');
            $productsHtml->setNameInLayout("reward_details");
            $extraHtml = $productsHtml->toHtml();
            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport->setHtml($html . $extraHtml);
            } else {
                echo $extraHtml;
            }
        }
    }

    public function addRewardFormAdmin(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();

        if (($block->getNameInLayout() == 'coupons' || $block->getBlockAlias() == 'coupons') && (Mage::app()->getRequest()->getControllerName() == "sales_order_create" || Mage::app()->getRequest()->getControllerName() == "sales_order_edit")) {
            //<block type="rewardpoints/adminhtml_createorder_reward" template="rewardpoints/form.phtml" name="reward_coupons" />

            $show = true;
            if ($block->getQuote() && $block->getQuote()->getStoreId() && !Mage::helper('rewardpoints')->isModuleActive($block->getQuote()->getStoreId())) {
                $show = false;
            }


            if (version_compare(Mage::getVersion(), '1.5.0', '>=') && $show) {
                $transport = $observer->getTransport();
                $fileName = $block->getTemplateFile();
                $thisClass = get_class($block);

                $html = $transport->getHtml();
                $magento_block = Mage::getSingleton('core/layout');
                $productsHtml = $magento_block->createBlock('rewardpoints/adminhtml_createorder_reward');
                $productsHtml->setTemplate('rewardpoints/form.phtml');
                $productsHtml->setNameInLayout("reward_coupons");
                $extraHtml = $productsHtml->toHtml();
                $transport->setHtml($extraHtml . $html);
            } elseif ($show) {
                $magento_block = Mage::getSingleton('core/layout');
                $productsHtml = $magento_block->createBlock('rewardpoints/adminhtml_createorder_reward');
                $productsHtml->setTemplate('rewardpoints/form.phtml');
                $productsHtml->setNameInLayout("reward_coupons");
                $extraHtml = $productsHtml->toHtml();
                echo $extraHtml;
            }
        }
    }

    protected function customerPoints($quote) {
        $store_id = $quote->getStoreId();
        if ($quote->getCustomerId()) {
            $customerId = $quote->getCustomerId();
        } else {
            return 0;
        }
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)) {
            $reward_model = Mage::getModel('rewardpoints/flatstats');
            $customer_points = $reward_model->collectPointsCurrent($customerId, $store_id);
        } else {
            $reward_model = Mage::getModel('rewardpoints/stats');
            $customer_points = $reward_model->getPointsCurrent($customerId, $store_id);
        }
        return $customer_points;
    }

    protected function _pointsOnAdminOrder($order_model, $quote, $data) {
        if (isset($data['rewardpoints']['qty']) && is_object($order_model) && is_object($quote) && $quote->getId()) {
            if (is_numeric($data['rewardpoints']['qty'])) {
                //$this->applyPoints($data['rewardpoints']['qty']);
                $points = $data['rewardpoints']['qty'];
                $user_points = $this->customerPoints($quote);
                $points = ($user_points < $points) ? $user_points : $points;

                if ($points > 0) {
                    Mage::helper('rewardpoints/event')->setCreditPoints($points);
                    $quote->setRewardpointsQuantity($points);
                    //->save();
                } else {
                    Mage::getSingleton('rewardpoints/session')->setProductChecked(0);
                    Mage::helper('rewardpoints/event')->setCreditPoints(0);
                    $quote
                            ->setRewardpointsQuantity(NULL)
                            ->setRewardpointsDescription(NULL)
                            ->setBaseRewardpoints(NULL)
                            ->setRewardpoints(NULL);
                }
                $order_model->setRecollect(true);
            }
        }
    }

    public function addRewardPointsAdminP16(Varien_Event_Observer $observer) {
        if (version_compare(Mage::getVersion(), '1.7.0', '<')) {
            $controller_action = $observer->getControllerAction();

            $request = $controller_action->getRequest();
            $order_model = Mage::getSingleton('adminhtml/sales_order_create');

            $data = $request->getPost('order');
            $quote = $order_model->getQuote();
            $this->_pointsOnAdminOrder($order_model, $quote, $data);
        }
    }

    public function addRewardPointsAdmin(Varien_Event_Observer $observer) {
        $request = $observer->getRequestModel();
        $order_model = $observer->getOrderCreateModel();

        $data = $request->getPost('order');
        $quote = $order_model->getQuote();
        $this->_pointsOnAdminOrder($order_model, $quote, $data);
    }

    public function setPointsOnProductPages(Varien_Event_Observer $observer) {
        //$this->appendAdminBlocks($observer);
        /* @var $block Mage_Core_Block_Abstract */

        if (!Mage::helper('rewardpoints')->isModuleActive()) {
            return true;
        }

        $block = $observer->getBlock();

        $show_info = Mage::getStoreConfig('rewardpoints/product_page/show_information', Mage::app()->getStore()->getId());
        $show_list_info = Mage::getStoreConfig('rewardpoints/product_page/show_list_points', Mage::app()->getStore()->getId());

        $show_duplicate = Mage::getStoreConfig('rewardpoints/product_page/duplicate_text_product_page', Mage::app()->getStore()->getId());
        $block_default = Mage::getStoreConfig('rewardpoints/product_page/block_default', Mage::app()->getStore()->getId());
        $block_default = (trim($block_default) != "") ? trim($block_default) : 'product.info.addtocart';
        $block_extra = Mage::getStoreConfig('rewardpoints/product_page/block_extra', Mage::app()->getStore()->getId());
        $block_extra = (trim($block_extra) != "") ? trim($block_extra) : 'product.info.configurable';

        $block_default_array = explode("|", $block_default);
        $block_extra_array = explode("|", $block_extra);

        $arr_product_types = array("Mage_Catalog", "Mage_Bundle", "OrganicInternet_SimpleConfigurableProducts_Catalog", "FireGento_GermanSetup");

        if ($show_info) {
            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {
                $transport = $observer->getTransport();
                $fileName = $block->getTemplateFile();
                $thisClass = get_class($block);
                //echo $block->getType();

                if ($block->getType() == 'catalog/product_price' || strpos($block->getType(), 'product_price') !== false) {
                    if (in_array($block->getModuleName(), $arr_product_types) && (
                            (
                            Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalog' && Mage::app()->getFrontController()->getRequest()->getControllerName() == 'category'
                            ) ||
                            (
                            Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalogsearch' && (Mage::app()->getFrontController()->getRequest()->getControllerName() == 'result' || 'advanced')
                            )
                            ) && $show_list_info) {
                        //if (in_array($block->getModuleName(), $arr_product_types) && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalog'
                        //        && Mage::app()->getFrontController()->getRequest()->getControllerName() == 'category' && $show_list_info){
                        //echo $block->getTemplate();
                        //print_r($block->getProduct()->getEntityId());
                        if ($_product = $block->getProduct()) {
                            $extraHtml = Mage::helper('rewardpoints/data')->getProductPointsText($_product, false, true);
                            //$html = $transport->getHtml();
                            //$transport->setHtml($html.$extraHtml);
                            if (!$block->getProduct()->getShownPoints()) {
                                $html = $transport->getHtml();
                                $transport->setHtml($html . $extraHtml);
                                $block->getProduct()->setShownPoints(true);
                            }
                        }
                    }
                }
                if (
                        ($block->getNameInLayout() == $block_default || $block->getBlockAlias() == $block_default) || (in_array($block->getNameInLayout(), $block_default_array) || in_array($block->getBlockAlias(), $block_default_array))
                ) {
                    if (Mage::registry('current_product') && is_object(Mage::registry('current_product')) && Mage::registry('current_product')->getId()) {
                        Mage::registry('current_product')->setPointDetails(NULL);
                        Mage::registry('current_product')->setPointDetails(NULL);
                    }

                    $html = $transport->getHtml();
                    $magento_block = Mage::getSingleton('core/layout');
                    $productsHtml = $magento_block->createBlock('rewardpoints/productpoints');
                    $productsHtml->setTemplate('rewardpoints/addtocart.phtml');
                    $extraHtml = $productsHtml->toHtml();

                    $transport->setHtml($extraHtml . $html);
                } else if (
                        (($block->getNameInLayout() == $block_extra || $block->getBlockAlias() == $block_extra) || (in_array($block->getNameInLayout(), $block_extra_array) || in_array($block->getBlockAlias(), $block_extra_array))
                        ) && $show_duplicate) {
                    $html = $transport->getHtml();
                    $extraHtml = '<div class="j2t-points-clone" id="j2t-points-clone" style="display:none;"></div>';
                    $transport->setHtml($html . $extraHtml);
                }
            } else {

                if ($block->getType() == 'catalog/product_price' || strpos($block->getType(), 'product_price') !== false) {
                    if (in_array($block->getModuleName(), $arr_product_types) && (
                            (
                            Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalog' && Mage::app()->getFrontController()->getRequest()->getControllerName() == 'category'
                            ) ||
                            (
                            Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalogsearch' && (Mage::app()->getFrontController()->getRequest()->getControllerName() == 'result' || 'advanced')
                            )
                            ) && $show_list_info) {
                        //if (in_array($block->getModuleName(), $arr_product_types) && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'catalog'
                        //        && Mage::app()->getFrontController()->getRequest()->getControllerName() == 'category' && $show_list_info){
                        if ($_product = $block->getProduct()) {
                            $extraHtml = Mage::helper('rewardpoints/data')->getProductPointsText($_product, false, true);
                            echo $extraHtml;
                        }
                    }
                }

                if (
                        ($block->getNameInLayout() == $block_default || $block->getBlockAlias() == $block_default) || (in_array($block->getNameInLayout(), $block_default_array) || in_array($block->getBlockAlias(), $block_default_array))
                ) {
                    //if($block->getNameInLayout() == $block_default || $block->getBlockAlias() == $block_default){
                    $magento_block = Mage::getSingleton('core/layout');
                    $productsHtml = $magento_block->createBlock('rewardpoints/productpoints');
                    $productsHtml->setTemplate('rewardpoints/addtocart.phtml');
                    $extraHtml = $productsHtml->toHtml();
                    echo $extraHtml;
                } else if (
                        (($block->getNameInLayout() == $block_extra || $block->getBlockAlias() == $block_duplicate) || (in_array($block->getNameInLayout(), $block_extra_array) || in_array($block->getBlockAlias(), $block_extra_array))
                        ) && $show_duplicate) {


                    //if(($block->getNameInLayout() == $block_extra || $block->getBlockAlias() == $block_extra) && $show_duplicate){
                    echo '<div class="j2t-points-clone" id="j2t-points-clone" style="display:none;"></div>';
                }
            }
        }
    }

    public function processBeforeSave($observer) {
        $object = $observer->getEvent()->getObject();
        if ($object instanceof Mage_Customer_Model_Customer) {
            if (Mage::getSingleton('rewardpoints/session')->getReferralUser() == $object->getId()) {
                Mage::getSingleton('rewardpoints/session')->setReferralUser(null);
                $object->setRewardpointsReferrer(null);
            }
            if (Mage::getSingleton('rewardpoints/session')->getReferralUser() && Mage::helper('rewardpoints')->isModuleActive($object->getStoreId(), $object->getGroupId())) {
                $userId = Mage::getSingleton('rewardpoints/session')->getReferralUser();
                if (!$object->getRewardpointsReferrer() && ($email = $object->getEmail())) {
                    $object->setRewardpointsReferrer($userId);
                }
            }
        }
        if ($object instanceof J2t_Rewardpoints_Model_Pointrules || $object instanceof J2t_Rewardpoints_Model_Catalogpointrules) {
            if (is_array($object->getWebsiteIds())) {
                $object->setWebsiteIds(implode(',', $object->getWebsiteIds()));
            }
            if (is_array($object->getCustomerGroupIds())) {
                $object->setCustomerGroupIds(implode(',', $object->getCustomerGroupIds()));
            }
        }
        //verify required points
        if (Mage::getConfig()->getModuleConfig('J2t_Rewardproductvalue')->is('active', 'true') && ($object instanceof Mage_Sales_Model_Order)) {
            if (($customer_id = $object->getCustomerId()) && ($store_id = $object->getStoreId()) && (Mage::helper('rewardpoints')->isModuleActive($object->getStoreId(), $object->getCustomerGroupId()))) {
                
                $reward_model = Mage::getModel('rewardpoints/stats');
                $reward_object = $reward_model->loadCustomerOrderQuote($customer_id, J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REQUIRED, $object->getQuoteId());
                if ($reward_object->getId() && $reward_object->getRewardpointsStatus() != $object->getStatus() && $reward_object->getRewardpointsState() != $object->getState()) {
                    $pointsModel = Mage::getModel('rewardpoints/stats')->load($reward_object->getId());
                    $pointsModel->setData("rewardpoints_state", $object->getState());
                    $pointsModel->setData("rewardpoints_status", $object->getStatus());
                    $pointsModel->save();
                }
            }
        }
    }

    //J2T Check referral
    public function checkReferral($observer) {
        $event = $observer->getEvent();
        $invoice = $event->getInvoice();
        $order = $invoice->getOrder();

        //load referral by referral customer id
        $referralModel = Mage::getModel('rewardpoints/referral');
        $referralModel->loadByChildId($order->getCustomerId());

        if ($referral_id = $referralModel->getRewardpointsReferralId()) {
            //load points by referral_id
            $pointsModel = Mage::getModel('rewardpoints/stats');
            $pointsModel->loadByReferralId($referral_id, $order->getCustomerId());

            if (($order_id = $pointsModel->getOrderId()) && Mage::helper('rewardpoints')->isModuleActive($order->getStoreId(), $order->getCustomerGroupId())) {
                $rewardPointsReferralMinOrder = Mage::getStoreConfig('rewardpoints/registration/referral_min_order', $order->getStoreId());

                if (!$order->getBaseSubtotalInclTax()) {
                    $order->setBaseSubtotalInclTax($order->getBaseSubtotal() + $order->getBaseTaxAmount());
                }

                $base_subtotal = $order->getBaseSubtotalInclTax();
                if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $order->getStoreId())) {
                    $base_subtotal = $base_subtotal - $order->getBaseTaxAmount();
                }
                if ($order_id != $order->getIncrementId() && ($rewardPointsReferralMinOrder == 0 || $rewardPointsReferralMinOrder <= $base_subtotal)) {
                    //check if order has correct status
                    if ($loadedOrder = Mage::getModel('sales/order')->loadByIncrementId($order_id)) {
                        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', $loadedOrder->getStoreId());
                        $order_states = explode(",", $statuses);

                        $statuses_used = Mage::getStoreConfig('rewardpoints/default/valid_used_statuses', $loadedOrder->getStoreId());
                        $order_states_used = explode(",", $statuses_used);

                        $status_state = Mage::getStoreConfig('rewardpoints/default/status_used', $loadedOrder->getStoreId());

                        //1. Parent points        
                        $rewardPoints = Mage::getStoreConfig('rewardpoints/registration/referral_points', $loadedOrder->getStoreId());
                        $referralPointMethod = Mage::getStoreConfig('rewardpoints/registration/referral_points_method', $loadedOrder->getStoreId());
                        if ($referralPointMethod != J2t_Rewardpoints_Model_Calculationtype::STATIC_VALUE) {
                            //$rewardPoints = $this->referralPointsEntry($order, $rewardPoints);
                            $referralPointMethod = Mage::getStoreConfig('rewardpoints/registration/referral_points_method', $order->getStoreId());
                            $rewardPoints = Mage::helper('rewardpoints')->referralPointsEntry($order, $referralPointMethod, $rewardPoints);

                            //$pointsModel->setPointsCurrent($rewardPoints);
                            $pointsModel->setData("points_current", $rewardPoints);
                        }

                        if (!in_array($loadedOrder->getStatus(), $order_states) && $status_state == 'status') {
                            //modify order_id to current order id (from invoice)
                            //if (in_array($order->getStatus(),$order_states)){
                            $pointsModel->setOrderId($order->getIncrementId());
                            $pointsModel->save();
                            //}
                        } else if (!in_array($loadedOrder->getState(), $order_states) && $status_state == 'state') {
                            //modify order_id to current order id (from invoice)
                            //if (in_array($order->getState(),$order_states)){
                            $pointsModel->setOrderId($order->getIncrementId());
                            $pointsModel->save();
                            //}
                        }


                        //2. Child points
                        $childPointsModel = Mage::getModel('rewardpoints/stats');
                        $childPointsModel->loadByChildReferralId($referral_id, $order->getCustomerId());

                        $rewardChildPoints = Mage::getStoreConfig('rewardpoints/registration/referral_child_points', $loadedOrder->getStoreId());
                        $referralChildPointMethod = Mage::getStoreConfig('rewardpoints/registration/referral_child_points_method', $loadedOrder->getStoreId());
                        if ($referralChildPointMethod != J2t_Rewardpoints_Model_Calculationtype::STATIC_VALUE) {
                            $rewardChildPoints = $this->referralChildPointsEntry($order, $rewardChildPoints);
                            //$childPointsModel->setPointsCurrent($rewardChildPoints);
                            $childPointsModel->setData("points_current", $rewardChildPoints);
                        }
                        if (!in_array($loadedOrder->getStatus(), $order_states) && $status_state == 'status') {
                            $childPointsModel->setOrderId($order->getIncrementId());
                            $childPointsModel->save();
                        } else if (!in_array($loadedOrder->getState(), $order_states) && $status_state == 'state') {
                            $childPointsModel->setOrderId($order->getIncrementId());
                            $childPointsModel->save();
                        }
                    }
                }
            }
        }
    }

    protected function recalculateEndingPoints() {
        $allStores = Mage::app()->getStores();
        $already_checked = array();
        foreach ($allStores as $_eachStoreId => $val) {
            $store_id = Mage::app()->getStore($_eachStoreId)->getId();
            $points = Mage::getModel('rewardpoints/stats')
                    ->getResourceCollection()
                    ->addFinishFilter(0)
                    ->addValidPoints($store_id, true, true);
            //echo $points->getSelect()->__toString();
            //die;
            if ($points->getSize()) {
                foreach ($points as $current_point) {
                    $customer_id = $current_point->getCustomerId();

                    if (!in_array($customer_id, $already_checked)) {
                        $already_checked[] = $customer_id;
                        //refresh points for this customer
                        foreach ($allStores as $_eachStoreId_in => $val_in) {
                            $model = Mage::getModel('rewardpoints/flatstats');
                            $model->processRecordFlat($customer_id, Mage::app()->getStore($_eachStoreId_in)->getId(), false, true);
                        }
                    }
                }
            }
        }
    }

    public function aggregateRewardpointsData() {
        //remove all points related to non-valid orders
        if (Mage::getStoreConfig(self::XML_PATH_CRON_REMOVE)) {
            $status_field = Mage::getStoreConfig('rewardpoints/default/status_used');
            $collection = Mage::getModel('rewardpoints/stats')->getCollection();
            if (version_compare(Mage::getVersion(), '1.4.0', '>=')) {
                $collection->getSelect()->where("main_table.rewardpoints_$status_field = ?", Mage_Sales_Model_Order::STATE_CANCELED);
            } else {
                $collection->getSelect()->where("main_table.rewardpoints_state = ?", Mage_Sales_Model_Order::STATE_CANCELED);
            }
            $loaded_collection = $collection->load();

            if ($loaded_collection->count()) {
                foreach ($loaded_collection as $reward_line) {
                    //Mage::getModel('rewardpoints/stats')->load($reward_line->getId())->delete();
                    $reward_line->delete();
                }
            }
        }

        $this->recalculateEndingPoints();
        //1. Get all points per customer
        //1.1 Browse all store ids : $store_id
        $this->processCustomerNotifications();
        //$this->recalculateEndingPoints();
    }

    public function processCustomerNotifications() {
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $_eachStoreId => $val) {
            $store_id = Mage::app()->getStore($_eachStoreId)->getId();
            $active = Mage::getStoreConfig(self::XML_PATH_EXPIRY_NOTIFICATION_ACTIVE, $store_id);

            if ($active) {
                // POINT VALIDITY EXPIRATION VERIFICATION
                /* $duration = Mage::getStoreConfig(self::XML_PATH_POINTS_DURATION, $_eachStoreId);
                  if ($duration){ */
                $days = Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_NOTIFICATION_DAYS, $store_id);

                $points = Mage::getModel('rewardpoints/stats')
                        ->getResourceCollection()
                        ->addFinishFilter($days)
                        ->addValidPoints($store_id);

                //echo $points->getSelect()->__toString();
                //die;

                if ($points->getSize()) {
                    foreach ($points as $current_point) {
                        $customer_id = $current_point->getCustomerId();
                        $customer = Mage::getModel('customer/customer')->load($customer_id);
                        if ($customer->getId() && ($customer->getStoreId() == $store_id || $customer->getStoreId() == "")) {
                            $points = $current_point->getNbCredit();
                            if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)) {
                                $points_received = Mage::getModel('rewardpoints/flatstats')->collectPointsCurrent($customer_id, $store_id);
                            } else {
                                $points_received = Mage::getModel('rewardpoints/stats')->getPointsCurrent($customer_id, $store_id);
                            }

                            //2. check if total points >= points available
                            if ($points_received >= $points) {
                                //3. send notification email
                                //$customer = Mage::getModel('customer/customer')->load($customer_id);
                                $customer_store_id = ($customer->getStoreId()) ? $customer->getStoreId() : $store_id;
                                Mage::getModel('rewardpoints/stats')->sendNotification($customer, $customer_store_id, $points, $days);
                            }
                        }
                    }
                }
                //}
                // CUSTOMER NOTIFICATIONS
                $this->customerPointsNotifications($store_id);
            }
        }
    }

    public function customerPointsNotifications($store_id) {

        $notifications = Mage::getStoreConfig(self::XML_PATH_NOTIFICATION_POINTS_NOTIFICATIONS, $store_id);
        $notifications_array = unserialize($notifications);

        $collection = Mage::getResourceModel('core/email_template_collection')
                ->load();
        $arr_select = $collection->toOptionArray();


        if (sizeof($notifications_array)) {
            foreach ($notifications_array as $notification) {
                if (isset($notification['min_value']) && isset($notification['max_value']) && isset($notification['duration'])) {
                    if ($notification['min_value'] < $notification['max_value'] && $notification['duration'] > 0) {

                        $template = $notification['template'];
                        $sender = $notification['sender'];
                        $points = Mage::getModel('rewardpoints/flatstats')
                                ->getResourceCollection();
                        $points->addStoreId($store_id);
                        $points->addPointsRange((int) $notification['min_value'], (int) $notification['max_value']);
                        $points->addCheckNotificationDate((int) $notification['duration']);
                        //echo $points->getSelect()->__toString();
                        //die;
                        if ($points->getSize()) {
                            foreach ($points as $customer_point) {

                                $customer_id = $customer_point->getUserId();
                                $points_current = $customer_point->getData('points_current');
                                $customer = Mage::getModel('customer/customer')->load($customer_id);
                                if ($customer->getId() && ($customer->getStoreId() == $store_id || $customer->getStoreId() == "")) {

                                    //email template verification
                                    $email_template = null;
                                    if ($template != "") {
                                        foreach ($arr_select as $trans_email) {
                                            if ($trans_email['value'] == $template) {
                                                $email_template = $template;
                                            }
                                        }
                                    }

                                    Mage::getModel('rewardpoints/flatstats')->sendCustomerNotification($customer, $store_id, $points_current, $customer_point, $sender, $email_template);
                                    //SET notification_date to today and increase notification_qty
                                    $model = Mage::getModel('rewardpoints/flatstats')->load($customer_point->getId());
                                    $model->setData('notification_qty', $model->getNotificationQty() + 1);
                                    $model->setData('notification_date', $model->getResource()->formatDate(time()));

                                    $model->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function pointsRefresh($observer) {
        $userId = Mage::getSingleton('rewardpoints/session')->getReferralUser();
        Mage::getSingleton('rewardpoints/session')->unsetAll();
        Mage::getSingleton('rewardpoints/session')->setReferralUser($userId);
    }

    public function pointsRefreshLogout($observer) {
        $userId = Mage::getSingleton('rewardpoints/session')->getReferralUser();
        Mage::getSingleton('rewardpoints/session')->unsetAll();
    }

    /* public function processBeforeSave($observer){
      $object = $observer->getEvent()->getObject();
      if ($object instanceof Mage_Customer_Model_Customer && Mage::helper('rewardpoints')->isModuleActive()) {
      $referrer = Mage::getModel('customer/customer')
      ->setWebsiteId($object->getStoreId())
      ->loadByEmail($object->getEmail());
      if ($referrer->getId()){
      Mage::getSingleton('rewardpoints/session')->setReferralUser($referrer->getRewardpointsReferralParentId());
      }
      }
      } */

    public function recordPointsUponRegistration($observer) {
        $customerId = $observer->getEvent()->getCustomer()->getEntityId();

        if (Mage::helper('rewardpoints')->isModuleActive()) {

            //check referral parent
            $referrer = Mage::getModel('rewardpoints/referral')->loadByEmail($observer->getEvent()->getCustomer()->getEmail());
            if ($referrer->getId()) {
                Mage::getSingleton('rewardpoints/session')->setReferralUser($referrer->getRewardpointsReferralParentId());
            }

            if (Mage::getStoreConfig('rewardpoints/registration/registration_points', Mage::app()->getStore()->getId()) > 0) {
                //check if points already earned
                $points = Mage::getStoreConfig('rewardpoints/registration/registration_points', Mage::app()->getStore()->getId());
                $min_customer_id = Mage::getStoreConfig('rewardpoints/registration/registration_points_customer_id', Mage::app()->getStore()->getId());
                if ((int) $min_customer_id < $customerId) {
                    $this->recordPoints($points, $customerId, J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REGISTRATION, null, false, false, false, Mage::app()->getStore()->getId());
                }
            }

            if (Mage::getSingleton('rewardpoints/session')->getReferralUser() == $customerId) {
                Mage::getSingleton('rewardpoints/session')->setReferralUser(null);
            }

            //$customer = Mage::getModel('customer/customer')->load($customerId);
            $customer = $observer->getEvent()->getCustomer();
            $referrer_id = Mage::getSingleton('rewardpoints/session')->getReferralUser();
            $referrer_id = ($referrer_id) ? $referrer_id : $customer->getRewardpointsReferrer();

            if ($customer->getId() != $referrer_id && $customer->getId() && $referrer_id && ($points = Mage::getStoreConfig('rewardpoints/registration/referrer_registration_points', Mage::app()->getStore()->getId()))) {
                $this->recordPoints($points, $referrer_id, J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REFERRAL_REGISTRATION, null, false, $customerId, false, Mage::app()->getStore()->getId());
            }
            if ($customer->getId() && $referrer_id && ($points = Mage::getStoreConfig('rewardpoints/registration/referred_registration_points', Mage::app()->getStore()->getId()))) {
                $this->recordPoints($points, $customerId, J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REFERRAL_REGISTRATION, null, false, $referrer_id, false, Mage::app()->getStore()->getId());
            }
        }
    }

    public function recordPointsAdminEvent($observer) {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        $request = $event->getRequest();


        if ($data = $request->getPost()) {
            if (isset($data['points_current']) || isset($data['points_spent'])) {
                if ($data['points_current'] > 0 || $data['points_spent'] > 0) {
                    $model = Mage::getModel('rewardpoints/stats');
                    if (trim($data['date_start'])) {
                        $date = Mage::app()->getLocale()->date($data['date_start'], Zend_Date::DATE_SHORT, null, false);
                        $time = $date->getTimestamp();
                        $model->setDateStart(Mage::getModel('core/date')->gmtDate(null, $time));
                    } else {
                        $model->setDateStart(Mage::getModel('core/date')->gmtDate(null, Mage::getModel('core/date')->timestamp(time())));
                    }
                    if (trim($data['date_end'])) {
                        if ($data['date_end'] != "") {
                            $date = Mage::app()->getLocale()->date($data['date_end'], Zend_Date::DATE_SHORT, null, false);
                            $time = $date->getTimestamp();
                            $model->setDateEnd(Mage::getModel('core/date')->gmtDate(null, $time));
                        }
                    }
                    $points = 0;
                    if (trim($data['points_current'])) {
                        $model->setPointsCurrent($data['points_current']);
                        $points = $data['points_current'];
                    }
                    if (trim($data['points_spent'])) {
                        $model->setPointsSpent($data['points_spent']);
                        $points = - $data['points_spent'];
                    }
                    if (trim($data['rewardpoints_description'])) {
                        $model->setRewardpointsDescription($data['rewardpoints_description']);
                    }

                    $store_ids = array();
                    if ($store_id = $customer->getStore()->getId()) {
                        $model->setStoreId($store_id);
                    } else {
                        $allStores = Mage::app()->getStores();
                        foreach ($allStores as $_eachStoreId => $val) {
                            $store_ids[] = Mage::app()->getStore($_eachStoreId)->getId();
                        }
                        $model->setStoreId(implode(",", $store_ids));
                    }

                    $model->setCustomerId($customer->getId());
                    $model->setOrderId(J2t_Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN);
                    $model->save();

                    $description = $data['rewardpoints_description'];
                    if ($description == "") {
                        $description = Mage::helper('rewardpoints')->__('Store input');
                    }

                    if (!empty($data['rewardpoints_notification'])) {
                        $model->sendAdminNotification($customer, $customer->getStoreId(), $points, $description);
                    }

                    //flatstats record
                    //NEW VERSION 1.6.21 - this has been deactivated because flatstats has been automated
                    /* if ($store_id = $customer->getStore()->getId()){
                      Mage::getModel('rewardpoints/flatstats')->processRecordFlat($customer->getId(), $store_id);
                      } else {
                      $allStores = Mage::app()->getStores();
                      foreach ($allStores as $_eachStoreId => $val) {
                      $this->processRecordFlatAction($customer->getId(), Mage::app()->getStore($_eachStoreId)->getId());
                      }
                      } */
                }
            }
        }
    }

    public function recordPointsForOrderEvent($observer) {

        //J2T magento 1.3.x fix
        if (version_compare(Mage::getVersion(), '1.4.0', '<')) {
            //$order = new Mage_Sales_Model_Order();
            $order = Mage::getModel('sales/order');
            $incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $order->loadByIncrementId($incrementId);

            $quote = Mage::getModel('sales/quote');
            $quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
            $quote->load($quoteId);
            $this->pointsOnOrder($order, $quote);
        } else {
            $event = $observer->getEvent();
            $order = $event->getOrder();
            $quote = $event->getQuote();

            $this->pointsOnOrder($order, $quote);
        }


        /* $event = $observer->getEvent();
          $order = $event->getOrder();
          $quote = $event->getQuote();

          $this->pointsOnOrder($order, $quote); */
        /*
          $rate = $order->getBaseToOrderRate();

          $order->setQuote($quote);
          $rewardPoints = Mage::helper('rewardpoints/data')->getPointsOnOrder($order, null, $rate);

          if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())){
          if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $rewardPoints){
          $rewardPoints = Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
          }
          }


          $customerId = $order->getCustomerId();

          //record points for item into db
          if ($rewardPoints > 0){
          $this->recordPoints($rewardPoints, $customerId, $order->getIncrementId());
          }



          //subtract points for this order
          $points_apply = (int) Mage::helper('rewardpoints/event')->getCreditPoints();
          if ($points_apply > 0){
          $this->useCouponPoints($points_apply, $customerId, $order);
          }

          //$this->sales_order_success_referral($order->getIncrementId());
          $this->sales_order_success_referral($order);
         */
    }

    protected function getMultishippingQuote($order) {
        $order_shipping_address = Mage::getModel('sales/order_address')->load($order->getShippingAddressId());
        $customer_shipping_address = $order_shipping_address->getCustomerAddressId();

        $order_billing_address = Mage::getModel('sales/order_address')->load($order->getBillingAddressId());
        $customer_billing_address = $order_billing_address->getCustomerAddressId();

        $quote_tmp = Mage::getModel('sales/quote');
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        foreach ($quote->getAddressesCollection() as $my_quote) {
            if ($my_quote->getAddressType() == 'shipping' && $my_quote->getCustomerAddressId() == $customer_shipping_address) {
                $quote_tmp->setShippingAddress($my_quote);
            } elseif ($my_quote->getAddressType() == 'billing' && $my_quote->getCustomerAddressId() == $customer_billing_address) {
                $quote_tmp->setBillingAddress($my_quote);
            }
        }
        return $quote_tmp;
    }

    public function recordPointsForMultiOrderEvent($observer) {

        $event = $observer->getEvent();
        $orders = $event->getOrders();
        $quote = $event->getQuote();

        if ($orders == array()) {
            $this->recordPointsForOrderEvent($observer);
            return true;
        }

        $customerId = "";
        $store_id = "";

        foreach ($orders as $order) {

            $order->setQuote($this->getMultishippingQuote($order));
            $rate = $order->getBaseToOrderRate();
            $customerId = $order->getCustomerId();
            $store_id = Mage::app()->getStore()->getId();

            if (!$store_id) {
                $store_id = $order->getStoreId();
            }


            $rewardPoints = Mage::helper('rewardpoints/data')->getPointsOnOrder($order, null, $rate);

            if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $store_id)) {
                if ((int) Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $store_id) < $rewardPoints) {
                    $rewardPoints = Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $store_id);
                }
            }

            //record points for item into db
            if ($rewardPoints > 0) {
                //recordPoints($pointsInt, $customerId, $orderId, $no_check = false, $link_id = false, $force_date_start = false, $store_id = null, $process_once = false, $max_point_per_customer = 0, $last_entry_gap = 0, $description = null, $object_name = null, $duration_value = null)
                $this->recordPoints($rewardPoints, $customerId, $order->getIncrementId(), $order, false, false, false, $store_id);
            }

            //subtract points for this order
            $points_apply = (int) Mage::helper('rewardpoints/event')->getCreditPoints();
            if ($points_apply > 0) {
                $this->useCouponPoints($points_apply, $customerId, $order);
            }

            $this->sales_order_success_referral($order, $quote);
        }
        //NEW VERSION 1.6.21 - this has been deactivated because flatstats has been automated
        /* if ($customerId && $store_id){
          $this->processRecordFlat($customerId, $store_id);
          } */
    }

    public function useCouponPoints($pointsAmt, $customerId, $order) {
        $orderId = $order->getIncrementId();
        $reward_model = Mage::getModel('rewardpoints/stats');

        $test_points = $reward_model->checkProcessedOrder($customerId, $orderId, false);

        if (!$test_points->getId()) {
            $post = array('order_id' => $orderId, 'customer_id' => $customerId,
                'store_id' => Mage::app()->getStore()->getId(), 'points_spent' => $pointsAmt,
                'rewardpoints_status' => $order->getStatus(),
                'rewardpoints_state' => $order->getState(),
                'convertion_rate' => Mage::getStoreConfig('rewardpoints/default/points_money', $order->getStoreId()));
            $reward_model->setData($post);
            $reward_model->save();
            Mage::helper('rewardpoints/event')->setCreditPoints(0);
        }
    }

    protected function processFirstOrder($order, $quote) {
        if (!$order->getCustomerId()) {
            return false;
        }

        $store_id = $order->getStoreId();
        if (!$store_id && $order->getCustomerId() && ($customer = Mage::getModel('customer/customer')->load($order->getCustomerId()))
        ) {
            $store_id = $customer->getStoreId();
        }

        if ($pointsInt = Mage::getStoreConfig(self::XML_PATH_FIRST_ORDER_POINT, $store_id)) {

            $base_subtotal = $order->getBaseSubtotalInclTax();
            if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $store_id)) {
                $base_subtotal = $base_subtotal - $order->getBaseTaxAmount();
            }
            $min_subtotal = Mage::getStoreConfig(self::XML_PATH_FIRST_ORDER_MIN, $store_id);
            $from_customer_id = Mage::getStoreConfig(self::XML_PATH_FIRST_ORDER_CUSTOMER_ID, $store_id);

            $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', $order->getStoreId());
            $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', $order->getStoreId());
            $order_states = explode(",", $statuses);

            if ($min_subtotal <= $base_subtotal && $order->getCustomerId() >= $from_customer_id && $order->getData($status_field) && in_array($order->getData($status_field), $order_states)) {
                //check if the customer already has first order points
                $firstOrderPointsModel = Mage::getModel('rewardpoints/stats');
                $firstOrderPointsModel->loadByFirstOrder($order->getCustomerId());

                $post = array('order_id' => $order->getIncrementId(), 'customer_id' => $order->getCustomerId(),
                    'store_id' => $store_id, 'points_current' => $pointsInt,
                    'convertion_rate' => Mage::getStoreConfig('rewardpoints/default/points_money', $store_id));

                $id = null;
                $reward_model = Mage::getModel('rewardpoints/stats');
                if ($firstOrderPointsModel->getId()) {
                    //check if already inserted
                    if (in_array($firstOrderPointsModel->getData('rewardpoints_' . $status_field), $order_states)) {
                        return false;
                    }
                    $id = $firstOrderPointsModel->getId();
                    $reward_model->load($firstOrderPointsModel->getId());
                }
                $add_delay = 0;
                if ($delay = Mage::getStoreConfig('rewardpoints/default/points_delay', $store_id)) {
                    if (is_numeric($delay)) {
                        $post['date_start'] = $reward_model->getResource()->formatDate(mktime(0, 0, 0, date("m"), date("d") + $delay, date("Y")));
                        $add_delay = $delay;
                    }
                }
                if ($duration = Mage::getStoreConfig('rewardpoints/default/points_duration', $store_id)) {
                    if (is_numeric($duration)) {
                        if (!isset($post['date_start'])) {
                            $post['date_start'] = $reward_model->getResource()->formatDate(time());
                        }
                        $post['date_end'] = $reward_model->getResource()->formatDate(mktime(0, 0, 0, date("m"), date("d") + $duration + $add_delay, date("Y")));
                    }
                }
                $post['rewardpoints_state'] = $order->getState();
                $post['rewardpoints_status'] = $order->getStatus();
                $post['rewardpoints_firstorder'] = '1';
                $reward_model->setData($post);
                $reward_model->setId($id);
                $reward_model->save();
            }
        }
    }

    public function processAddModelCallback($observer) {
        //J2T magento 1.3.x fix
        $object = $observer->getEvent()->getObject();

        if ($object instanceof Mage_Sales_Model_Order && Mage::helper('rewardpoints')->isModuleActive($object->getStoreId(), $object->getCustomerGroupId())) { //check points on saving orders
            $order = $object;
            //$quote = $object->getQuote();
            $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
            $this->pointsOnOrder($order, $quote);
            $this->processFirstOrder($order, $quote);
        }
    }

    public function recordPointStatus($observer) {
        $object = $observer->getEvent()->getObject();
        if ($object instanceof Mage_Sales_Model_Order) { //check points on saving orders
            $order = $object;

            //refresh all states & statuses
            $reward_items = Mage::getModel('rewardpoints/stats')->getCollection()
                    //->addFieldToFilter('store_id', $order->getStoreId())
                    ->addFieldToFilter('order_id', $order->getIncrementId());

            foreach ($reward_items as $model) {
                if ($order != null && $order->getId() && $model->getId()) {
                    $reward_model = Mage::getModel('rewardpoints/stats')->load($model->getId());
                    if ($reward_model->getId() && ($order->getStatus() || $order->getState())) {
                        $reward_model->setData('rewardpoints_status', $order->getStatus());
                        $reward_model->setData('rewardpoints_state', $order->getState());
                        $reward_model->save();
                    }
                }
            }
        }
    }

    public function processAddModelOrderSave($observer) {
        $order = $observer->getEvent()->getOrder();
        $quote = Mage::getModel("sales/quote")->load($order->getQuote());
        $this->pointsOnOrder($order, $quote);
    }

    public function processAddDynamicEvent($observer) {
        $object = $observer->getEvent()->getObject();

        $events = Mage::getStoreConfig(self::XML_PATH_EVENTS_EVENT_LIST);
        $events_array = unserialize($events);

        if (sizeof($events_array) && is_array($events_array)) {
            foreach ($events_array as $event) {
                if (isset($event['class_name']) && isset($event['model_id']) && isset($event['point_value']) && isset($event['process_once']) && isset($event['max_point']) && isset($event['duration']) && isset($event['description'])) {

                    if (($event_class_name = trim($event['class_name'])) && ($point_value = (int) $event['point_value'])) {
                        ///////////////////////////////////////
                        if (strtolower(get_class($object)) == strtolower($event_class_name)) {
                            $model_id = (int) $event['model_id'];
                            $process_once = (int) $event['process_once'];
                            $max_point = (int) $event['max_point'];
                            $use_end = (int) $event['use_end'];
                            $duration = (int) $event['duration'];
                            $description = $event['description'];
                            $verifications = trim($event['verifications']);

                            $customer_id = null;
                            if ($object->getCustomerId()) {
                                $customer_id = $object->getCustomerId();
                            } else if (Mage::getSingleton('customer/session')) {
                                if (Mage::getSingleton('customer/session')->getCustomer()) {
                                    if (Mage::getSingleton('customer/session')->getCustomer()->getId()) {
                                        $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();
                                    }
                                }
                            }

                            $store_id = null;
                            if ($object->getStoreId()) {
                                $store_id = $object->getStoreId();
                            }

                            $saved_id = null;
                            if ($model_id) {
                                $saved_id = $object->getId();
                            }
                            
                            $customer_group_id = Mage::getModel('customer/customer')->load($customer_id)->getGroupId();                            
                            $module_active = Mage::helper('rewardpoints')->isModuleActive($store_id, $customer_group_id);

                            if ($customer_id != null && $module_active) {
                                $process_once = (int) $event['process_once'];
                                $max_point = (int) $event['max_point'];
                                $use_end = (int) $event['use_end'];
                                $duration = (int) $event['duration'];
                                $description = $event['description'];

                                $process_record = true;
                                if ($verifications != '') {
                                    $verification_array = explode(";", $verifications);
                                    if (sizeof($verification_array) > 0) {
                                        foreach ($verification_array as $verif) {
                                            $verification_unit = explode("|", $verif);
                                            if (sizeof($verification_unit) == 2) {
                                                if ($object->getData(trim($verification_unit[0])) != trim($verification_unit[1])) {
                                                    $process_record = false;
                                                }
                                            }
                                        }
                                    }
                                }
                                if ($process_record) {
                                    $this->recordPoints($point_value, $customer_id, J2t_Rewardpoints_Model_Stats::TYPE_POINTS_DYN, null, true, $saved_id, false, $store_id, $process_once, $max_point, $duration, $description, trim($event_class_name), $use_end);
                                }
                            }
                        }
                        ///////////////////////////////////////
                    }
                }
            }
        }
    }

    public function processAddCallback($observer) {
        //if (!version_compare(Mage::getVersion(), '1.4.0', '>=')){
        $object = $observer->getEvent()->getObject();

        if ($object instanceof Mage_Customer_Model_Customer) {
            if (
                    $object->getRewardpointsReferrer() != $object->getId() && ($userId = $object->getRewardpointsReferrer()) && Mage::helper('rewardpoints')->isModuleActive($object->getStoreId(), $object->getGroupId())
            ) {
                //insert in rewardpoints_referral table if not already inserted
                $referralModel = Mage::getModel('rewardpoints/referral');
                $parent = Mage::getModel('customer/customer')
                        ->load($object->getRewardpointsReferrer());

                if (!$referralModel->isSubscribed($object->getEmail())) {
                    if ($referralModel->subscribe($parent, $object->getEmail(), $object->getName(), true)) {
                        //$session->addSuccess($this->__('Email %s was successfully invited.', $email));
                    }
                }
            }
        }


        if ($object instanceof Mage_Review_Model_Review) {
            $group_id = Mage::getModel('customer/customer')->load($object->getCustomerId())->getGroupId();
            if ($object->getStatusId() == Mage_Review_Model_Review::STATUS_APPROVED && Mage::helper('rewardpoints')->isModuleActive($object->getStoreId(), $group_id)) {
                if ($pointsInt = Mage::getStoreConfig('rewardpoints/other_points/review_points', $object->getStoreId())) {
                    if ($object->getCustomerId()) {
                        $this->recordPoints($pointsInt, $object->getCustomerId(), J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW, null, true, false, false, $object->getStoreId());
                    }
                }
            }
        }

        if ($object instanceof Mage_Tag_Model_Tag) {
            $store_id = $object->getStoreId();
            $store_id = ($store_id) ? $store_id : $object->getFirstStoreId();
            if (($pointsInt = Mage::getStoreConfig('rewardpoints/other_points/tag_points', $store_id)) && ($customer_id = $object->getFirstCustomerId()) && (Mage::helper('rewardpoints')->isModuleActive($store_id, Mage::getModel('customer/customer')->load($customer_id)->getGroupId()))) {
                if (($tag_id = $object->getId()) && $object->getStatus() == Mage_Tag_Model_Tag::STATUS_APPROVED) {
                    $this->recordPoints($pointsInt, $customer_id, J2t_Rewardpoints_Model_Stats::TYPE_POINTS_TAG, null, false, $tag_id, false, $store_id);
                }
            }
        }
        if ($object instanceof Mage_Poll_Model_Poll_Vote) {
            //register points for poll participating
            if (Mage::getSingleton('customer/session')) {
                if (($pointsInt = Mage::getStoreConfig('rewardpoints/other_points/poll_points', $object->getStoreId())) && (Mage::getSingleton('customer/session')->getCustomer()) && (Mage::helper('rewardpoints')->isModuleActive($object->getStoreId()))) {
                    if (($poll_id = $object->getPollId()) && ($customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId())) {
                        $this->recordPoints($pointsInt, $customer_id, J2t_Rewardpoints_Model_Stats::TYPE_POINTS_POLL, null, false, $poll_id, false, $object->getStoreId());
                    }
                }
            }
        }

        if ($object instanceof Mage_Newsletter_Model_Subscriber) {
            if ($object->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED && Mage::helper('rewardpoints')->isModuleActive($object->getStoreId())) {
                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($object->getSubscriberEmail())
                        ->getId();
                if ($ownerId) {
                    if ($pointsInt = Mage::getStoreConfig('rewardpoints/other_points/newsletter_points', $object->getStoreId())) {
                        $this->recordPoints($pointsInt, $ownerId, J2t_Rewardpoints_Model_Stats::TYPE_POINTS_NEWSLETTER, null, false, false, false, $object->getStoreId());
                    }
                }
            }
        }

        if ($object instanceof Mage_Customer_Model_Customer) {
            //register points if newsletter optin
            if ($object->getIsSubscribed() && Mage::helper('rewardpoints')->isModuleActive($object->getStoreId(), $object->getGroupId())) {
                if ($pointsInt = Mage::getStoreConfig('rewardpoints/other_points/newsletter_points', $object->getStoreId())) {
                    if ($customer_id = $object->getId()) {
                        $this->recordPoints($pointsInt, $object->getId(), J2t_Rewardpoints_Model_Stats::TYPE_POINTS_NEWSLETTER, null, false, false, false, $object->getStoreId());
                    }
                }
            } else {
                //if unsubscribe, don't remove line, only substract given points (only if line exists)
            }
        }

        if ($object instanceof Mage_Sales_Model_Order) {
            if (($customer_id = $object->getCustomerId()) && ($store_id = $object->getStoreId()) && (Mage::helper('rewardpoints')->isModuleActive($object->getStoreId(), $object->getCustomerGroupId()))) {
                //NEW VERSION 1.6.21 - this has been deactivated because flatstats has been automated
                /*
                  $this->processRecordFlat($customer_id, $store_id);
                 */
                //check referred friend in order to refresh referrer flat points
                $reward_model = Mage::getModel('rewardpoints/stats');
                $reward_object = $reward_model->loadReferrer($customer_id, $object->getIncrementId());

                //NEW VERSION 1.6.21 - this has been deactivated because flatstats has been automated
                /* if ($reward_object->getCustomerId()){
                  $this->processRecordFlat($reward_object->getCustomerId(), $store_id);
                  } */
            }
        }

        //}
    }

    public function processLoadModelCallback($observer) {
        $object = $observer->getEvent()->getObject();
        /* if ($object instanceof Mage_Customer_Model_Customer && Mage::helper('rewardpoints')->isModuleActive()) {
          if (($customer_id = $object->getId()) && ($store_id = $object->getStoreId())){
          $this->processRecordFlat($customer_id, $store_id, true);
          }
          } */
        if ($object instanceof Mage_Sales_Model_Quote && Mage::helper('rewardpoints')->isModuleActive($object->getStoreId(), $object->getCustomerGroupId())) {
            if (($customer_id = $object->getCustomerId()) && ($store_id = $object->getStoreId())) {
                $this->processRecordFlat($customer_id, $store_id, true);
            }
        }
    }

    protected function processRecordFlatAction($customerId, $store_id, $check_date = false) {
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id) && $customerId) {
            $reward_model = Mage::getModel('rewardpoints/stats');
            $points_current = $reward_model->getPointsCurrent($customerId, $store_id);
            $points_received = $reward_model->getRealPointsReceivedNoExpiry($customerId, $store_id);
            $points_spent = $reward_model->getPointsSpent($customerId, $store_id);
            $points_awaiting_validation = $reward_model->getPointsWaitingValidation($customerId, $store_id);
            $points_lost = $reward_model->getRealPointsLost($customerId, $store_id);

            $reward_flat_model = Mage::getModel('rewardpoints/flatstats');
            $reward_flat_model->loadByCustomerStore($customerId, $store_id);
            $reward_flat_model->setPointsCollected($points_received);
            $reward_flat_model->setPointsUsed($points_spent);
            $reward_flat_model->setPointsWaiting($points_awaiting_validation);
            $reward_flat_model->setPointsCurrent($points_current);
            $reward_flat_model->setPointsLost($points_lost);
            $reward_flat_model->setStoreId($store_id);
            $reward_flat_model->setUserId($customerId);

            if ($check_date && ($date_check = $reward_flat_model->getLastCheck())) {
                $date_array = explode("-", $reward_flat_model->getLastCheck());
                if ($reward_flat_model->getLastCheck() == date("Y-m-d")) {
                    return false;
                }
            }
            $reward_flat_model->setLastCheck(date("Y-m-d"));
            $reward_flat_model->save();
        }
    }

    public function processRecordFlat($customerId, $store_id, $check_date = false) {
        if (Mage::getStoreConfig('rewardpoints/default/store_scope', $store_id)) {
            $this->processRecordFlatAction($customerId, $store_id, $check_date);
        } else {
            //get all stores
            $allStores = Mage::app()->getStores();
            foreach ($allStores as $_eachStoreId => $val) {
                $this->processRecordFlatAction($customerId, Mage::app()->getStore($_eachStoreId)->getId(), $check_date);

                /* $_storeCode = Mage::app()->getStore($_eachStoreId)->getCode();
                  $_storeName = Mage::app()->getStore($_eachStoreId)->getName();
                  $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
                  echo $_storeId;
                  echo $_storeCode;
                  echo $_storeName; */
            }
        }
    }

    public function processOrderSaveRecordPoints($observer) {
        $object = $observer->getEvent()->getObject();
        if ($object instanceof Mage_Checkout_Model_Cart) {
            //refresh points
            $customerId = $object->getCustomerId();
            $store_id = $object->getStoreId();

            //NEW VERSION 1.6.21 - this has been deactivated because flatstats has been automated
            /* $this->processRecordFlat($customerId, $store_id); */
        }
    }

    public function recordPointsMultiOrSingle($observer) {
        if ($order = $observer->getEvent()->getOrder()) {
            $this->pointsOnOrder($order, $order->getQuote());
        } elseif ($orders = $observer->getEvent()->getOrders()) {
            $this->recordPointsForMultiOrderEvent($observer);
        }
    }

    protected function pointsOnOrder($order, $quote) {
        if ($order->getCustomerId() == 0) {
            return;
        }

        $rate = $order->getBaseToOrderRate();

        if (!$quote->getId() && ($order_quote = $order->getQuote())) {
            $quote = $order_quote;
        } elseif (!$order->getQuote() && ($quote_id = $order->getQuoteId()) && !$quote->getId()) {
            $quote = Mage::getModel('sales/quote')->load($quote_id);
            if ($quote->getId()) {
                $order->setQuote($quote);
            }
        } else {
            $order->setQuote($quote);
        }

        if (!$order->getQuote() && !$quote->getId() && Mage::getSingleton('adminhtml/session_quote')->getQuote()) {
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
            $order->setQuote($quote);
        }

        //$store_id = $order->getStoreId();
        $store_id = $order->getStoreId();
        if (!$store_id) {
            $store_id = Mage::app()->getStore()->getId();
        }

        /* if (!$store_id && $customerId && ($customer = Mage::getModel('customer/customer')->load($customerId))){
          $store_id = $customer->getStoreId();
          } */

        if (!$quote->getId() && ($order_quote = $order->getQuote())) {
            $quote = $order_quote;
        } else {
            $order->setQuote($quote);
        }
        $rewardPoints = Mage::helper('rewardpoints/data')->getPointsOnOrder($order, null, $rate, false, $store_id);

        if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $store_id)) {
            if ((int) Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $store_id) < $rewardPoints) {
                $rewardPoints = Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', $store_id);
            }
        }
        $customerId = $order->getCustomerId();

        if ($rewardPoints > 0) {
            $this->recordPoints($rewardPoints, $customerId, $order->getIncrementId(), $order, false, false, false, $store_id);
        }

        //subtract points for this order

        $points_apply = (int) Mage::helper('rewardpoints/event')->getCreditPoints($quote);

        if ($points_apply > 0) {
            $this->useCouponPoints($points_apply, $customerId, $order);
        }

        //$this->sales_order_success_referral($order->getIncrementId());
        $this->sales_order_success_referral($order, $quote);

        //NEW VERSION 1.6.21 - this has been deactivated because flatstats has been automated
        /* $this->processRecordFlat($customerId, $store_id); */
    }

    public function recordPoints($pointsInt, $customerId, $orderId, $order = null, $no_check = false, $link_id = false, $force_date_start = false, $store_id = null, $process_once = false, $max_point_per_customer = 0, $last_entry_gap = 0, $description = null, $object_name = null, $duration_value = null) {
        if (!$customerId)
            return false;
        $reward_model = Mage::getModel('rewardpoints/stats');
        if ($store_id == null) {
            $store_id = Mage::app()->getStore()->getId();
        }

        if ($process_once) {
            //check if has already been processed
            $test_process_once = $reward_model->checkProcessedOrder($customerId, $orderId, true, $link_id, true, true, 0, $object_name);
            if ($test_process_once->getId()) {
                return false;
            }
        }
        if ($max_point_per_customer) {
            //check if customer reaches max points limit (previous points + current points < max points)
            $test_process_once = $reward_model->checkProcessedOrder($customerId, $orderId, true, false, true, false, 0, $object_name, true);
            if (((int) $test_process_once->getPointsAccumulated() + $pointsInt) > $max_point_per_customer) {
                return false;
            }
        }
        if ($last_entry_gap) {
            //check if last entry is greater than $last_entry_gap days
            $test_process_once = $reward_model->checkProcessedOrder($customerId, $orderId, true, $link_id, true, false, $last_entry_gap, $object_name);
            if ($test_process_once->getId()) {
                return false;
            }
        }

        //check if points are already processed
        //$test_points = $reward_model->checkProcessedOrder($customerId, $orderId, true, $link_id);
        if (!$no_check)
            $test_points = $reward_model->checkProcessedOrder($customerId, $orderId, true, $link_id, true, false, 0, $object_name);
        if ($no_check || !$test_points->getId()) {
            $post = array('order_id' => $orderId, 'customer_id' => $customerId, 'store_id' => $store_id, 'points_current' => $pointsInt, 'convertion_rate' => Mage::getStoreConfig('rewardpoints/default/points_money', $store_id));
            //v.2.0.0

            if ($object_name != null) {
                $post['object_name'] = $object_name;
            }

            if ($description != null) {
                $post['rewardpoints_description'] = $description;
            }

            if ($link_id) {
                $post['rewardpoints_linker'] = $link_id;
            }

            $add_delay = 0;
            if ($delay = Mage::getStoreConfig('rewardpoints/default/points_delay', $store_id)) {
                if (is_numeric($delay)) {
                    $post['date_start'] = $reward_model->getResource()->formatDate(mktime(0, 0, 0, date("m"), date("d") + $delay, date("Y")));
                    $add_delay = $delay;
                }
            }

            if ((($duration = Mage::getStoreConfig('rewardpoints/default/points_duration', $store_id)) && $duration_value !== 0) || $duration_value) {
                if (is_numeric($duration_value)) {
                    $duration = $duration_value;
                }
                if (is_numeric($duration)) {
                    if (!isset($post['date_start'])) {
                        $post['date_start'] = $reward_model->getResource()->formatDate(time());
                    }
                    $post['date_end'] = $reward_model->getResource()->formatDate(mktime(0, 0, 0, date("m"), date("d") + $duration + $add_delay, date("Y")));
                }
            }

            /* if ($force_date_start && !isset($post['date_end'])){
              $post['date_end'] = Mage::getModel('core/date')->date('Y-m-d');
              } */

            if ($force_date_start && !isset($post['date_start'])) {
                $post['date_start'] = Mage::getModel('core/date')->date('Y-m-d');
            }

            $reward_model->setData($post);

            if ($link_id) {
                $reward_model->setRewardpointsLinker($link_id);
            }


            $reward_model->save();
        } elseif (Mage::app()->getStore()->isAdmin() && Mage::getStoreConfig('rewardpoints/default/allow_recalculate', $store_id) && $test_points->getId()) {
            $reward_model->load($test_points->getId());
            if ($reward_model->getData('points_current') != $pointsInt) {
                $reward_model->setPointsCurrent($pointsInt);
                $reward_model->save();
            }
            //refresh referral

            $this->refreshReferralPoints($reward_model);
        } else {
            //refresh state and status
            $reward_model->load($test_points->getId());
            if ($order = Mage::getModel('sales/order')->loadByIncrementId($orderId)) {
                if ($reward_model->getRewardpointsStatus() != $order->getStatus() || $reward_model->getRewardpointsState() != $order->getState()) {
                    $reward_model->setRewardpointsStatus($order->getStatus());
                    $reward_model->setRewardpointsState($order->getState());
                    $reward_model->save();
                }
            }
        }
    }

    protected function refreshReferralPoints($reward_model) {
        $order_increment = $reward_model->getOrderId();
        $customer_id = $reward_model->getCustomerId();

        if (Mage::app()->getStore()->isAdmin() && Mage::getStoreConfig('rewardpoints/default/allow_recalculate_referral', Mage::app()->getStore()->getId())) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_increment);
            //same order id + same customer id = child
            //same order id + different customer id = parent
            //check if for order id, there is a referral id
            $parentPointsModel = Mage::getModel('rewardpoints/stats');
            $parentPointsModel->loadByOrderIncrementId($order_increment, $customer_id, true, true);

            $childPointsModel = Mage::getModel('rewardpoints/stats');
            $childPointsModel->loadByOrderIncrementId($order_increment, $customer_id, true, false);


            if (($parent_credit_id = $parentPointsModel->getRewardpointsReferralId()) && ($child_credit_id = $childPointsModel->getRewardpointsReferralId())) {
                //1. Parent points        
                $rewardPoints = Mage::getStoreConfig('rewardpoints/registration/referral_points', $order->getStoreId());
                $referralPointMethod = Mage::getStoreConfig('rewardpoints/registration/referral_points_method', $order->getStoreId());
                if ($referralPointMethod != J2t_Rewardpoints_Model_Calculationtype::STATIC_VALUE) {
                    //$rewardPoints = $this->referralPointsEntry($order, $rewardPoints);
                    $referralPointMethod = Mage::getStoreConfig('rewardpoints/registration/referral_points_method', $order->getStoreId());
                    $rewardPoints = Mage::helper('rewardpoints')->referralPointsEntry($order, $referralPointMethod, $rewardPoints);

                    if ($parentPointsModel->getData('points_current') != $rewardPoints || $parentPointsModel->getOrderId() != $order->getIncrementId()) {
                        $parentPointsModel->setData("points_current", $rewardPoints);
                        $parentPointsModel->setOrderId($order->getIncrementId());
                        $parentPointsModel->save();
                    }
                }
                //2. Child points
                $rewardChildPoints = Mage::getStoreConfig('rewardpoints/registration/referral_child_points', $order->getStoreId());
                $referralChildPointMethod = Mage::getStoreConfig('rewardpoints/registration/referral_child_points_method', $order->getStoreId());
                if ($referralChildPointMethod != J2t_Rewardpoints_Model_Calculationtype::STATIC_VALUE) {
                    $rewardChildPoints = $this->referralChildPointsEntry($order, $rewardChildPoints);
                    if ($childPointsModel->getData('points_current') != $rewardChildPoints || $childPointsModel->getOrderId() != $order->getIncrementId()) {
                        $childPointsModel->setData("points_current", $rewardChildPoints);
                        $childPointsModel->setOrderId($order->getIncrementId());
                        $childPointsModel->save();
                    }
                }
            }
        } else {
            if ($order = Mage::getModel('sales/order')->loadByIncrementId($order_increment)) {
                $parentPointsModel = Mage::getModel('rewardpoints/stats');
                $parentPointsModel->loadByOrderIncrementId($order_increment, $customer_id, true, true);
                if ($parentPointsModel->getRewardpointsStatus() != $order->getStatus() || $parentPointsModel->getRewardpointsState() != $order->getState()) {
                    $parentPointsModel->setRewardpointsStatus($order->getStatus());
                    $parentPointsModel->setRewardpointsState($order->getState());
                    $parentPointsModel->save();
                }

                $childPointsModel = Mage::getModel('rewardpoints/stats');
                $childPointsModel->loadByOrderIncrementId($order_increment, $customer_id, true, false);
                if ($childPointsModel->getRewardpointsStatus() != $order->getStatus() || $childPointsModel->getRewardpointsState() != $order->getState()) {
                    $childPointsModel->setRewardpointsStatus($order->getStatus());
                    $childPointsModel->setRewardpointsState($order->getState());
                    $childPointsModel->save();
                }
            }
        }
    }

    /* protected function referralPointsEntry($order, $rewardPoints)
      {
      //J2t_Rewardpoints_Model_Calculationtype::STATIC_VALUE
      //J2t_Rewardpoints_Model_Calculationtype::RATIO_POINTS
      //J2t_Rewardpoints_Model_Calculationtype::CART_SUMMARY
      $referralPointMethod = Mage::getStoreConfig('rewardpoints/registration/referral_points_method', $order->getStoreId());
      if ($referralPointMethod == J2t_Rewardpoints_Model_Calculationtype::RATIO_POINTS){
      $rate = $order->getBaseToOrderRate();
      if ($rewardPoints > 0){
      $rewardPoints = Mage::helper('rewardpoints/data')->getPointsOnOrder($order, null, $rate, false, $order->getStoreId(), $rewardPoints);
      }
      } else if ($referralPointMethod == J2t_Rewardpoints_Model_Calculationtype::CART_SUMMARY) {
      if ( ($base_subtotal = $order->getBaseSubtotalInclTax()) && $rewardPoints > 0 ){
      $summary_points = $base_subtotal * $rewardPoints;
      //$summary_points = $base_subtotal * $rewardPointsChild;
      if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $order->getStoreId())){
      $summary_points = $summary_points - $order->getBaseTaxAmount();
      }
      $rewardPoints = Mage::helper('rewardpoints/data')->processMathValue($summary_points);
      }
      }
      return $rewardPoints;
      } */

    protected function referralChildPointsEntry($order, $rewardPointsChild) {
        //J2t_Rewardpoints_Model_Calculationtype::STATIC_VALUE
        //J2t_Rewardpoints_Model_Calculationtype::RATIO_POINTS
        //J2t_Rewardpoints_Model_Calculationtype::CART_SUMMARY
        $referralChildPointMethod = Mage::getStoreConfig('rewardpoints/registration/referral_child_points_method', $order->getStoreId());
        if ($referralChildPointMethod == J2t_Rewardpoints_Model_Calculationtype::RATIO_POINTS) {
            $rate = $order->getBaseToOrderRate();
            if ($rewardPointsChild > 0) {
                $rewardPointsChild = Mage::helper('rewardpoints/data')->getPointsOnOrder($order, null, $rate, false, $order->getStoreId(), $rewardPointsChild);
            }
        } else if ($referralChildPointMethod == J2t_Rewardpoints_Model_Calculationtype::CART_SUMMARY) {
            //if ( ($base_subtotal = $order->getBaseSubtotal()) && $rewardPointsChild > 0 ){
            if (!$order->getBaseSubtotalInclTax()) {
                $order->setBaseSubtotalInclTax($order->getBaseSubtotal() + $order->getBaseTaxAmount());
            }
            if (($base_subtotal = $order->getBaseSubtotalInclTax()) && $rewardPointsChild > 0) {
                $summary_points = $base_subtotal * $rewardPointsChild;
                if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $order->getStoreId())) {
                    $summary_points = $summary_points - $order->getBaseTaxAmount();
                }
                $rewardPointsChild = Mage::helper('rewardpoints/data')->processMathValue($summary_points);
            }
        }
        return $rewardPointsChild;
    }

    public function sales_order_success_referral($order, $quote = null) {
        if (!$order->getCustomerId()) {
            return;
        }

        if (!is_object($quote) || ($quote == null && !$quote->getId())) {
            //if ($quote == null && !$quote->getId()){
            $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        }

        $userId = 0;
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        if (Mage::getSingleton('rewardpoints/session')->getReferralUser() == $order->getCustomerId() || $quote->getRewardpointsReferrer() == $order->getCustomerId()) {
            Mage::getSingleton('rewardpoints/session')->setReferralUser(null);
            $quote->setRewardpointsReferrer(null);
        }
        if ($customer->getRewardpointsReferrer()) {
            $userId = $customer->getRewardpointsReferrer();
        } else if (Mage::getSingleton('rewardpoints/session')->getReferralUser() && Mage::getSingleton('rewardpoints/session')->getReferralUser() != $order->getCustomerId()) {
            $userId = Mage::getSingleton('rewardpoints/session')->getReferralUser();
        } else if ($quote->getRewardpointsReferrer() && $quote->getRewardpointsReferrer() != $order->getCustomerId()) {
            $userId = (int) $quote->getRewardpointsReferrer();
        }

        //check if referral from link...
        //if ($userId = Mage::getSingleton('rewardpoints/session')->getReferralUser()){
        if ($userId) {
            $referrer = Mage::getModel('customer/customer')->load($userId);
            $referree_email = $order->getCustomerEmail();
            $referree_name = $order->getCustomerName();

            $referralModel = Mage::getModel('rewardpoints/referral');
            if (!$referralModel->isSubscribed($referree_email) && $referrer->getEmail() != $referree_email) {
                $referralModel->setRewardpointsReferralParentId($userId)
                        ->setRewardpointsReferralEmail($referree_email)
                        ->setRewardpointsReferralName($referree_name);
                $referralModel->save();
            }
            Mage::getSingleton('rewardpoints/session')->setReferralUser(null);
            Mage::getSingleton('rewardpoints/session')->unsetAll();
        }

        //Mage::app()->getStore()->getId()
        $rewardPoints = Mage::getStoreConfig('rewardpoints/registration/referral_points', $order->getStoreId());
        $rewardPointsChild = Mage::getStoreConfig('rewardpoints/registration/referral_child_points', $order->getStoreId());
        $rewardPointsReferralMinOrder = Mage::getStoreConfig('rewardpoints/registration/referral_min_order', $order->getStoreId());


        //$rewardPoints = $this->referralPointsEntry($order, $rewardPoints);
        $referralPointMethod = Mage::getStoreConfig('rewardpoints/registration/referral_points_method', $order->getStoreId());
        $rewardPoints = Mage::helper('rewardpoints')->referralPointsEntry($order, $referralPointMethod, $rewardPoints);


        $rewardPointsChild = $this->referralChildPointsEntry($order, $rewardPointsChild);

        if (!$order->getBaseSubtotalInclTax()) {
            $order->setBaseSubtotalInclTax($order->getBaseSubtotal() + $order->getBaseTaxAmount());
        }

        $base_subtotal = $order->getBaseSubtotalInclTax();
        if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $order->getStoreId())) {
            $base_subtotal = $base_subtotal - $order->getBaseTaxAmount();
        }

        if (($rewardPoints > 0 || $rewardPointsChild > 0 && $order->getCustomerEmail()) && ($rewardPointsReferralMinOrder == 0 || $rewardPointsReferralMinOrder <= $base_subtotal)) {
            //$order = $observer->getEvent()->getInvoice()->getOrder();
            Mage::helper('rewardpoints')->processReferralInsertion($order, $rewardPoints, $rewardPointsChild);
        }


        /*
         * process advanced referral points / only valid orders
         */
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', $order->getStoreId());
        $statuses = Mage::getStoreConfig('rewardpoints/default/valid_statuses', $order->getStoreId());
        $order_states = explode(",", $statuses);

        if ($order->getData($status_field) && in_array($order->getData($status_field), $order_states)) {
            //verify if the order has already been processed (order_id, customer_id)
            //loadByOrderIncrementId($order_id, $customer_id = null, $referral = false,  $parent = false)
            $parentPointsModel = Mage::getModel('rewardpoints/stats');
            $parentPointsModel->loadByOrderIncrementId($order->getIncrementId(), $order->getCustomerId(), true, true);

            $childPointsModel = Mage::getModel('rewardpoints/stats');
            $childPointsModel->loadByOrderIncrementId($order->getIncrementId(), $order->getCustomerId(), true, false);

            $rewardPointsAdvance = 0;
            if (!$parentPointsModel->getRewardpointsReferralId()) {
                $rewardPointsAdvance = Mage::helper('rewardpoints')->getAdvancedPointValue($order, $order->getCustomerId(), true);
            }

            $rewardPointsChildAdvance = 0;
            if (!$childPointsModel->getRewardpointsReferralId()) {
                $rewardPointsChildAdvance = Mage::helper('rewardpoints')->getAdvancedPointValue($order, $order->getCustomerId(), false);
            }



            if (($rewardPointsAdvance > 0 || $rewardPointsChildAdvance > 0 && $order->getCustomerEmail()) && ($rewardPointsReferralMinOrder == 0 || $rewardPointsReferralMinOrder <= $base_subtotal)) {
                Mage::helper('rewardpoints')->processReferralInsertion($order, $rewardPointsAdvance, $rewardPointsChildAdvance, true);
            }
        }
    }

    public function sales_order_invoice_pay($observer) {
        $order = $observer->getEvent()->getInvoice()->getOrder();
        //Mage::app()->getStore()->getId()
        $rewardPoints = Mage::getStoreConfig('rewardpoints/registration/referral_points', $order->getStoreId());
        $rewardPointsChild = Mage::getStoreConfig('rewardpoints/registration/referral_child_points', $order->getStoreId());

        $rewardPointsReferralMinOrder = Mage::getStoreConfig('rewardpoints/registration/referral_min_order', $order->getStoreId());

        if (!$order->getBaseSubtotalInclTax()) {
            $order->setBaseSubtotalInclTax($order->getBaseSubtotal() + $order->getBaseTaxAmount());
        }

        $base_subtotal = $order->getBaseSubtotalInclTax();
        if (Mage::getStoreConfig('rewardpoints/default/exclude_tax', $order->getStoreId())) {
            $base_subtotal = $base_subtotal - $order->getBaseTaxAmount();
        }

        if (($rewardPoints > 0 || $rewardPointsChild > 0) && ($rewardPointsReferralMinOrder == 0 || $rewardPointsReferralMinOrder <= $base_subtotal)) {

            $referralModel = Mage::getModel('rewardpoints/referral');
            if ($referralModel->isSubscribed($order->getCustomerEmail())) {
                if (!$referralModel->isConfirmed($order->getCustomerEmail())) {
                    $referralModel->loadByEmail($order->getCustomerEmail());
                    $referralModel->setData('rewardpoints_referral_status', true);
                    $referralModel->setData('rewardpoints_referral_child_id', $order->getCustomerId());
                    $referralModel->save();

                    $parent = Mage::getModel('customer/customer')->load($referralModel->getData('rewardpoints_referral_parent_id'));
                    $child = Mage::getModel('customer/customer')->load($referralModel->getData('rewardpoints_referral_child_id'));
                    $referralModel->sendConfirmation($parent, $child, $parent->getEmail());

                    try {
                        if ($rewardPoints > 0) {
                            $reward_model = Mage::getModel('rewardpoints/stats');
                            $post = array('order_id' => $order->getIncrementId(), 'customer_id' => $referralModel->getData('rewardpoints_referral_parent_id'),
                                'store_id' => $order->getStoreId(), 'points_current' => $rewardPoints, 'rewardpoints_referral_id' => $referralModel->getData('rewardpoints_referral_id'));
                            $reward_model->setData($post);
                            $reward_model->save();
                        }
                        if ($rewardPointsChild > 0) {
                            $reward_model = Mage::getModel('rewardpoints/stats');
                            $post = array('order_id' => $order->getIncrementId(), 'customer_id' => $referralModel->getData('rewardpoints_referral_child_id'),
                                'store_id' => $order->getStoreId(), 'points_current' => $rewardPointsChild, 'rewardpoints_referral_id' => $referralModel->getData('rewardpoints_referral_id'));
                            $reward_model->setData($post);
                            $reward_model->save();
                        }
                    } catch (Exception $e) {
                        //Mage::getSingleton('session')->addError($e->getMessage());
                    }
                }
            }
        }
    }

    /*
      public function attachRewardPointsAttributes($observer) {

      if($observer->getEvent()->getRequest()->isPost()) {
      $rewardpoints_description = $observer->getEvent()->getRequest()->getPost('rewardpoints_description', '');
      $rewardpoints = $observer->getEvent()->getRequest()->getPost('rewardpoints', '');
      $base_rewardpoints = $observer->getEvent()->getRequest()->getPost('base_rewardpoints', '');

      $quote = $observer->getEvent()->getQuote();
      $quote->setRewardpointsDescription($rewardpoints_description);
      $quote->setRewardpoints($rewardpoints);
      $quote->setBaseRewardpoints($base_rewardpoints);
      }
      }
     */
}
