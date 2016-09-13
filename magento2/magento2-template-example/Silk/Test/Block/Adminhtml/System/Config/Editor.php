<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-19
 * Time: 下午7:00
 */
namespace Silk\Test\Block\Adminhtml\System\Config;

class Editor extends \Magento\Config\Block\System\Config\Form\Field {

    protected $_wysiwygConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig, array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }


    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // set wysiwyg for element
        $element->setWysiwyg(true);
        // set configuration values
        $element->setConfig($this->_wysiwygConfig->getConfig($element));
        return parent::_getElementHtml($element);
    }

}