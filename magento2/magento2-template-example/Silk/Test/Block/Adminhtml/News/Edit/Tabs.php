<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-8
 * Time: 下午12:09
 */
namespace Silk\Test\Block\Adminhtml\News\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('news_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Information'));
    }

}
