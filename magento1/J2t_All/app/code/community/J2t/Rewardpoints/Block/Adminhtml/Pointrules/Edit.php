<?php

class J2t_Rewardpoints_Block_Adminhtml_Pointrules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
	$this->_objectId = 'id';
        $this->_blockGroup = 'rewardpoints';
        $this->_controller = 'adminhtml_pointrules';


        //J2t_Rewardpoints_Model_Pointrules::RULE_ACTION_TYPE_ADD
        $this->_formScripts[] = "

            checkTypes = function(){
                if ($('rule_action_type').getValue() == '".J2t_Rewardpoints_Model_Pointrules::RULE_ACTION_TYPE_DONTPROCESS."'){
                    $('rule_points').value = '1';
                    $('rule_points').up(1).hide();
                } else if ($('rule_action_type').getValue() == '".J2t_Rewardpoints_Model_Pointrules::RULE_ACTION_TYPE_DONTPROCESS_USAGE."'){
                    $('rule_points').value = '1';
                    $('rule_points').up(1).hide();
                } else {
                    $('rule_points').up(1).show();
                }
            };

            Event.observe($('rule_action_type'), 'change', function(event) {
                checkTypes();
            });


            document.observe('dom:loaded', function() {
                checkTypes();
                $('rule_rule_type').up(1).hide();
            });

            function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'back/edit/') }
        ";


        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('rewardpoints')->__('Save Rule'));
        
        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('salesrule')->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class' => 'save'
        ), 10);
        
        $this->_updateButton('delete', 'label', Mage::helper('rewardpoints')->__('Delete Rule'));
        $filter = Mage::registry('pointrules_data');
        $this->setFormActionUrl($this->getUrl('*/rewardpointsadmin_pointrules/save'));
    }

    public function getHeaderText()
    {
        $rule = Mage::registry('pointrules_data');
        if ($rule->getRuleId()) {
            return Mage::helper('rewardpoints')->__("Edit Rule '%s'", $this->htmlEscape($rule->getTitle()));
        }
        else {
            return Mage::helper('rewardpoints')->__('New Rule');
        }
    }
    
    /*public function getFormAction()
    {
        return Mage::getUrl('admin/rewardpointsadmin_pointrules/save', array('_secure' => $this->getRequest()->isSecure()));
    }*/
}
