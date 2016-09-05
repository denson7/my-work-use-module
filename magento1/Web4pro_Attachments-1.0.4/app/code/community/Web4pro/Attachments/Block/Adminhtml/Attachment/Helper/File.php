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
 * Attachment file field renderer helper
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Block_Adminhtml_Attachment_Helper_File extends Varien_Data_Form_Element_Abstract
{
    /**
     * constructor
     *
     * @access public
     * @param array $data
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('file');
    }

    /**
     * get element html
     *
     * @access public
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getElementHtml()
    {
        $html = '';
        $this->addClass('input-file');
        $html .= parent::getElementHtml();
        if ($this->getValue()) {
            $url = $this->_getUrl();
            if (!preg_match("/^http\:\/\/|https\:\/\//", $url)) {
                $url = Mage::helper('web4pro_attachments/attachment')->getFileBaseUrl() . $url;
            }
            $html .= '<br /><a href="'.$url.'">'.$this->_getUrl().'</a> ';
        }
        $html .= $this->_getDeleteCheckbox();
        return $html;
    }

    /**
     * get the delete checkbox HTML
     *
     * @access protected
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
        if ($this->getValue()) {
            $label = Mage::helper('web4pro_attachments')->__('Delete File');
            $html .= '<span class="delete-image">';
            $html .= '<input type="checkbox" name="'.
                parent::getName().'[delete]" value="1" class="checkbox" id="'.
                $this->getHtmlId().'_delete"'.($this->getDisabled() ? ' disabled="disabled"': '').'/>';
            $html .= '<label for="'.$this->getHtmlId().'_delete"'.($this->getDisabled() ? ' class="disabled"' : '').'>';
            $html .= $label.'</label>';
            $html .= $this->_getHiddenInput();
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * get the hidden input
     *
     * @access protected
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _getHiddenInput()
    {
        return '<input type="hidden" name="'.parent::getName().'[value]" value="'.$this->getValue().'" />';
    }

    /**
     * get the file url
     *
     * @access protected
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    protected function _getUrl()
    {
        return $this->getValue();
    }

    /**
     * get the name
     *
     * @access public
     * @return string
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function getName()
    {
        return $this->getData('name');
    }
}
