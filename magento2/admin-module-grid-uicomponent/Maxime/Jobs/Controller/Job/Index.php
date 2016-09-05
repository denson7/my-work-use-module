<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-5
 * Time: 上午11:48
 */
namespace Maxime\Jobs\Controller\Job;
class Index extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}