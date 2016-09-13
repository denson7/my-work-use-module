<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-19
 * Time: 上午9:43
 */
namespace Webkul\MultiSelect\Controller\Multiselect;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Test extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * [__construct]
     * @param Context                          $context
     * @param PageFactory                      $resultPageFactory
     */
    public function __construct(Context $context,PageFactory $resultPageFactory)
    {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
 * loads custom layout
 *
 * @return \Magento\Framework\View\Result\Page
 */
public function execute()
{
    $resultPage = $this->_resultPageFactory->create();
    $resultPage->addHandle('mselect_multiselect_demo'); //loads the layout of module_custom_customlayout.xml file with its name
    return $resultPage;
}
}