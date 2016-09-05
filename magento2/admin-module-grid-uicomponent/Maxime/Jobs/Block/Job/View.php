<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-5
 * Time: 下午2:49
 */
namespace Maxime\Jobs\Block\Job;
class View extends \Magento\Framework\View\Element\Template
{
    protected $_job;

    protected $_department;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Maxime\Jobs\Model\Job $job
     * @param \Maxime\Jobs\Model\Department $department
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Maxime\Jobs\Model\Job $job,
        \Maxime\Jobs\Model\Department $department,
        array $data = []
    ) {
        $this->_job = $job;
        $this->_department = $department;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        // Get job and department
        $job = $this->getLoadedJob();
        $department = $this->getLoadedDepartment();

        // Title is job's title and department's name
        $title = $job->getTitle() .' - '.$department->getName();
        $description = __('Look at the jobs we have got for you');
        $keywords = __('job,hiring');

        $this->getLayout()->createBlock('Magento\Catalog\Block\Breadcrumbs');

        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb(
                'jobs',
                [
                    'label' => __('We are hiring'),
                    'title' => __('We are hiring'),
                    'link' => $this->getListJobUrl() // No link for the last element
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'job',
                [
                    'label' => $title,
                    'title' => $title,
                    'link' => false // No link for the last element
                ]
            );
        }

        $this->pageConfig->getTitle()->set($title);
        $this->pageConfig->setDescription($description);
        $this->pageConfig->setKeywords($keywords);


        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($title);
        }

        return $this;
    }

    protected function _getJob()
    {
        if (!$this->_job->getId()) {
            // our model is already set in the construct
            // but I put this method to load in case the model is not loaded
            $entityId = $this->_request->getParam('id');
            $this->_job = $this->_job->load($entityId);
        }
        return $this->_job;
    }


    public function getLoadedJob()
    {
        return $this->_getJob();
    }

    protected function _getDepartment()
    {
        if (!$this->_department->getId()) {
            // Get the job to retrieve department_id
            $job = $this->getLoadedJob();
            // Load department with id
            $this->_department->load($job->getDepartmentId());
        }
        return $this->_department;
    }


    public function getLoadedDepartment()
    {
        return $this->_getDepartment();
    }


    public function getListJobUrl(){
        return $this->getUrl('jobs/job');
    }

    public function getDepartmentUrl($job){
        if(!$job->getDepartmentId()){
            return '#';
        }

        return $this->getUrl('jobs/department/view', ['id' => $job->getDepartmentId()]);
    }

    public function test()
    {
        return $arr = array('A','B','C');
    }
}
