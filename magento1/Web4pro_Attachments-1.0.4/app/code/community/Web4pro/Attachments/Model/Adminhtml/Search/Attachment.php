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
 * Admin search model
 *
 * @category    Web4pro
 * @package     Web4pro_Attachments
 * @author      WEB4PRO <srepin@corp.web4pro.com.ua>
 */
class Web4pro_Attachments_Model_Adminhtml_Search_Attachment extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return Web4pro_Attachments_Model_Adminhtml_Search_Attachment
     * @author WEB4PRO <srepin@corp.web4pro.com.ua>
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('web4pro_attachments/attachment_collection')
            ->addFieldToFilter('title', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $attachment) {
            $arr[] = array(
                'id'          => 'attachment/1/'.$attachment->getId(),
                'type'        => Mage::helper('web4pro_attachments')->__('Attachment'),
                'name'        => $attachment->getTitle(),
                'description' => $attachment->getTitle(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/attachments_attachment/edit',
                    array('id'=>$attachment->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
