<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-21
 * Time: 下午8:15
 */
namespace Silk\Block\Block\Widget;

class Mwcontact extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/contact_widget.phtml');
    }
}