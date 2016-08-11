<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-5
 * Time: 下午2:50
 */
namespace Maxime\Jobs\Block\Department;
class View extends \Magento\Framework\View\Element\Template
{
    protected $_jobCollection = null;

    protected $_department;

    protected $_job;

    protected $_helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Maxime\Jobs\Model\Department $department
     * @param \Maxime\Jobs\Model\Job $job
     * @param \Maxime\Jobs\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Maxime\Jobs\Model\Department $department,
        \Maxime\Jobs\Model\Job $job,
        \Maxime\Jobs\Helper\Data $helper,
        array $data = []
    ) {
        $this->_department = $department;

        $this->_job = $job;

        $this->_helper = $helper;

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

        // Get department
        $department = $this->getLoadedDepartment();

        // Title is department's name
        $title = $department->getName();
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

    protected function _getDepartment()
    {
        if (!$this->_department->getId()) {
            // our model is already set in the construct
            // but I put this method to load in case the model is not loaded
            $entityId = $this->_request->getParam('id');
            $this->_department = $this->_department->load($entityId);
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

    protected function _getJobsCollection(){
        if($this->_jobCollection === null && $this->_department->getId()){
            $jobCollection = $this->_job->getCollection()
                ->addFieldToFilter('department_id', $this->_department->getId())
                ->addStatusFilter($this->_job, $this->_department);
            $this->_jobCollection = $jobCollection;
        }
        return $this->_jobCollection;
    }

    public function getLoadedJobsCollection()
    {
        return $this->_getJobsCollection();
    }

    public function getJobUrl($job){
        if(!$job->getId()){
            return '#';
        }

        return $this->getUrl('jobs/job/view', ['id' => $job->getId()]);
    }

    public function getConfigListJobs() {
        return $this->_helper->getListJobEnabled();
    }
}