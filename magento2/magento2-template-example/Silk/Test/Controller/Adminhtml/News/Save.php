<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-4
 * Time: 下午5:49
 */
namespace Silk\Test\Controller\Adminhtml\News;

use Magento\Backend\App\Action;

class Save extends Action
{
    /**
     * @var \Silk\Test\Model\News
     */
    protected $_model;

    /**
     * @param Action\Context $context
     * @param \Silk\Test\Model\News $model
     */
    public function __construct(
        Action\Context $context,
        \Silk\Test\Model\News $model
    ) {
        parent::__construct($context);
        $this->_model = $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Silk_Test::news_save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Silk\Test\Model\News $model */
            $model = $this->_model;

            $id = $this->getRequest()->getParam('entity_id');

            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

//            $this->_eventManager->dispatch(
//                'test_news_prepare_save',
//                ['news' => $model, 'request' => $this->getRequest()]
//            );

            try {
                $model->save();
                $this->messageManager->addSuccess(__('News saved'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getEntityId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the news'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}