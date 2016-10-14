<?php
/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2015 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_Block_Adminhtml_Listpointgroup
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;
    protected $_customerGroups;

    /**
     * Initialize block
     */
    public function __construct()
    {
        $this->setTemplate('rewardpoints/listpointgroup.phtml');
    }
    
    public function getProduct()
    {
        return Mage::registry('product');
    }
    
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
    
    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }
    
    public function getElement()
    {
        return $this->_element;
    }
    
    public function getValues()
    {
        $values = array();
        $data = array();
        $data_string = $this->getElement()->getValue();
        $data_array = array();
        
        if (!$data_string){
            return null;
        }
        
        $pos = strpos($data_string, '{');
        $pos2 = strpos($data_string, '}]');
        if ($pos === false) {
            $isJson = false;
            $data_array = explode(',', $data_string);
        } elseif($pos2 !== false) {
            $isJson = true;
            $data_array = Mage::helper('core')->jsonDecode($data_string);
        } else {
            $isJson = false;
        }
        
        foreach ($data_array as $value) {
            if (!$isJson){
                $current_value = explode("|",$value);
                $group_id = (!isset($current_value[1])) ? 0 : $current_value[1];
                $data[] = array('point' => $current_value[0], 'group_id' => $group_id, 'readonly' => false);
            } else {
                $data[] = array('point' => $value['point'], 'group_id' => $value['group_id'], 'readonly' => false);
            }
        }

        if (is_array($data)) {
            $values = $this->_sortValues($data);
        }
        
        return $values;
    }
    
    /**
     * Sort values
     *
     * @param array $data
     * @return array
     */
    protected function _sortValues($data)
    {
        usort($data, array($this, '_sortListPoints'));
        return $data;
    }

    /**
     * Sort tier price values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortListPoints($a, $b)
    {
        //if ($a['point'] != $b['point']) {
            return $a['point'] < $b['point'] ? -1 : 1;
        //}
        
        /*if ($a['duration'] != $b['duration']) {
            return $a['duration'] < $b['duration'] ? -1 : 1;
        }*/

        //return 0;
    }

    /**
     * Prepare global layout
     * Add "Add tier" button to layout
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('rewardpoints')->__('Add Group Point'),
                'onclick' => 'return pointListControl.addItem()',
                'class' => 'add'
            ));
        $button->setName('add_group_point_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }
    
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
    
    public function getCustomerGroups()
    {
        if (is_null($this->_customerGroups)) {
            $this->_customerGroups = array();
            $collection = Mage::getModel('customer/group')
                ->getCollection()
                ->addFieldToFilter('customer_group_id', array('gt'=> 0));
            foreach ($collection as $group) {
                $this->_customerGroups[$group->getCustomerGroupCode()] = $group->getId();
            }
        }
        return $this->_customerGroups;
    }

}
