<?php
/**
 * Copyright © 2015 Silk. All rights reserved.
 */

namespace Silk\Pms\Controller\Adminhtml\Items;

class Index extends \Silk\Pms\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Silk_Pms::pms');
        $resultPage->getConfig()->getTitle()->prepend(__('Silk Items'));
        $resultPage->addBreadcrumb(__('Silk'), __('Silk'));
        $resultPage->addBreadcrumb(__('Items'), __('Items'));
        return $resultPage;
    }
}
