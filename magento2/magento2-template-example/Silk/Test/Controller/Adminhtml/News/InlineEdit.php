<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-11
 * Time: 下午5:56
 */
namespace Silk\Test\Controller\Adminhtml\News;

abstract class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * JSON Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * news Factory
     *
     * @var \Maxime\Newss\Model\NewsFactory
     */
    protected $newsFactory;

    /**
     * constructor
     *
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Mageplaza\Blog\Model\newsFactory $newsFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Maxime\Newss\Model\NewsFactory $newsFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->newsFactory = $newsFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $newsItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($newsItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        foreach (array_keys($newsItems) as $newsId) {
            /** @var \Maxime\Newss\Model\News $news */
            $news = $this->newsFactory->create()->load($newsId);
            try {
                $newsData = $newsItems[$newsId];//todo: handle dates
                $news->addData($newsData);
                $news->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithnewsId($news, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithnewsId($news, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithnewsId(
                    $news,
                    __('Something went wrong while saving the news.')
                );
                $error = true;
            }
        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add news id to error message
     *
     * @param \Maxime\Newss\Model\News $news
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithnewsId(\Maxime\Newss\Model\News $news, $errorText)
    {
        return '[news ID: ' . $news->getId() . '] ' . $errorText;
    }
}
