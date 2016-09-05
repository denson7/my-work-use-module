<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-5
 * Time: 下午5:56
 */
namespace Maxime\Jobs\Controller\Job;

class Testlog extends \Magento\Framework\App\Action\Action
{
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
//        \Psr\Log\LoggerInterface $logger
        \Maxime\Jobs\Logger\Logger $logger
    )
    {
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
//        var_dump(get_class($this->_logger));
        $this->_logger->addDebug('My debug log');
        $this->_logger->addInfo('My info log');
        $this->_logger->addNotice('My notice log');
        $this->_logger->addWarning('My warning log');
        $this->_logger->addError('My error log');
        $this->_logger->addCritical('My critical log');
        $this->_logger->addAlert('My alert log');
        $this->_logger->addEmergency('My emergency log');

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}