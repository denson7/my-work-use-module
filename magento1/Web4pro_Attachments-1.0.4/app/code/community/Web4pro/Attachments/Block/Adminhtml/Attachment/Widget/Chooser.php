<?php
/**
 * WEB4PRO - Creating profitable online stores
 * 
 * @author WEB4PRO <srepin@corp.web4pro.com.ua>
 * @category  WEB4PRO
 * @package   Web4pro_Attachments
 * @copyright Copyright (c) 2015 WEB4PRO (http://www.web4pro.net)
 * @license   http://www.web4pro.net/license.txt
 */
/**
 * Attachment admin widget chooser
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */

class Web4pro_Attachments_Block_Adminhtml_Attachment_Widget_Chooser extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Block construction, prepare grid params
     *
     * @access public
     * @param array $arguments Object data
     * @return void
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function __construct($arguments=array())
    {
        parent::__construct($arguments);
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('chooser_status' => '1'));
    }

    /**
     * Prepare chooser element HTML
     *
     * @access public
     * @param Varien_Data_Form_Element_Abstract $element Form Element
     * @return Varien_Data_Form_Element_Abstract
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $uniqId = Mage::helper('core')->uniqHash($element->getId());
        $sourceUrl = $this->getUrl(
            'adminhtml/attachments_attachment_widget/chooser',
            array('uniq_id' => $uniqId)
        );
        $chooser = $this->getLayout()->createBlock('widget/adminhtml_widget_chooser')
            ->setElement($element)
            ->setTranslationHelper($this->getTranslationHelper())
            ->setConfig($this->getConfig())
            ->setFieldsetId($this->getFieldsetId())
            ->setSourceUrl($sourceUrl)
            ->setUniqId($uniqId);
        if ($element->getValue()) {
            $attachment = Mage::getModel('web4pro_attachments/attachment')->load($element->getValue());
            if ($attachment->getId()) {
                $chooser->setLabel($attachment->getTitle());
            }
        }
        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Grid Row JS Callback
     *
     * @access public
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var attachmentId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                var attachmentTitle = trElement.down("td").next().innerHTML;
                '.$chooserJsObject.'.setElementValue(attachmentId);
                '.$chooserJsObject.'.setElementLabel(attachmentTitle);
                '.$chooserJsObject.'.close();
            }
        ';
        return $js;
    }

    /**
     * Prepare a static blocks collection
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Adminhtml_Attachment_Widget_Chooser
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('web4pro_attachments/attachment')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for the a grid
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Adminhtml_Attachment_Widget_Chooser
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'chooser_id',
            array(
                'header' => Mage::helper('web4pro_attachments')->__('Id'),
                'align'  => 'right',
                'index'  => 'entity_id',
                'type'   => 'number',
                'width'  => 50
            )
        );

        $this->addColumn(
            'chooser_title',
            array(
                'header' => Mage::helper('web4pro_attachments')->__('Title'),
                'align'  => 'left',
                'index'  => 'title',
            )
        );
        $this->addColumn(
            'chooser_status',
            array(
                'header'  => Mage::helper('web4pro_attachments')->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => array(
                    0 => Mage::helper('web4pro_attachments')->__('Disabled'),
                    1 => Mage::helper('web4pro_attachments')->__('Enabled')
                ),
            )
        );
        return parent::_prepareColumns();
    }

    /**
     * get url for grid
     *
     * @access public
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'adminhtml/attachments_attachment_widget/chooser',
            array('_current' => true)
        );
    }

    /**
     * after collection load
     *
     * @access protected
     * @return Web4pro_Attachments_Block_Adminhtml_Attachment_Widget_Chooser
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
}
