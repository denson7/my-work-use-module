<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-4
 * Time: 下午5:51
 */
namespace Silk\Test\Block\Adminhtml\News;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * News edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Silk_Test';
        $this->_controller = 'adminhtml_news';

        parent::_construct();

        if ($this->_isAllowedAction('Silk_Test::news_save')) {
            $this->buttonList->update('save', 'label', __('Save Test'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

    }

    /**
     * Get header with News name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('test_news')->getId()) {
            return __("Edit Test '%1'", $this->escapeHtml($this->_coreRegistry->registry('test_news')->getTitle()));
        } else {
            return __('New Test');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('test/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }

//    public function getFormHtml()
//    {
//        // get the current form as html content.
//        $html = parent::getFormHtml();
//        //Append the phtml file after the form content.
//        $html .= $this->setTemplate('Silk_Test::demo/demo.phtml')->toHtml();
//        return $html;
//    }

//    protected function _prepareLayout()
//    {
//
//        $this->_formScripts[] = "
//            require([
//                'jquery',
//                'mage/mage',
//                'knockout'
//            ], function ($){
//
//            });
//
//        ";
//        return parent::_prepareLayout();
//    }

}