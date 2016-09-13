<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\Test\Cron;
 
class DisableTest
{
    /**
     * @var \Silk\Test\Model\News
     */
    protected $_news;
 
    /**
     * @param \Silk\Test\Model\News $news
     */
    public function __construct(
        \Silk\Test\Model\News $news
    ) {
        $this->_news = $news;
    }
 
    /**
     * Disable test which date is less than the current date
     *
     * @return void
     */
    public function execute()
    {
        $nowDate = date('Y-m-d');
        $testCollection = $this->_news->getCollection()
            ->addFieldToFilter('date', array ('lt' => $nowDate));
 
        foreach($testCollection AS $news) {
            $news->setStatus($news->getDisableStatus());
            $news->save();
        }
    }
}
