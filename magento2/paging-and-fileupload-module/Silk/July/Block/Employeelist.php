<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\July\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Employeelist extends Template
{
    /**
     * @param Template\Context $context
     * @param array $data
     */
    protected $_employeeFactory;
    protected $_storeManager;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Silk\July\Model\EmployeeFactory $employeeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
     ) 
    {
        $this->_employeeFactory = $employeeFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
        //get collection of data 
        $collection = $this->_employeeFactory->create()->getCollection();
        $this->setCollection($collection);
        $this->pageConfig->getTitle()->set(__('Employee List'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getCollection()) {
            $pager = $this->getLayout()
                ->createBlock('Magento\Theme\Block\Html\Pager','silk.july.record.pager');
            $pager->setLimit(4)//设置显示行数
                ->setShowAmounts(true)
                ->setCollection($this->getCollection());// assign collection to pager
            $this->setChild('pager',$pager); // set pager block in layout
            //$this->getCollection()->load();
        }
        return $this;
    }


    /**
     * @return string
     */
    // method for get pager html
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    } 


    public function getBaseUrl()
    {
    return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
    }


    public function getConfigUrl()
    {
        $ConfigUrl =  $this->_scopeConfig->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $ConfigUrl;
    }

    /*public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }*/


}
