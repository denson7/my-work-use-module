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
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class J2t_Rewardpoints_Block_Adminhtml_Rules_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'rewardpoints';
        $this->_controller = 'adminhtml_rules';


        $this->_updateButton('save', 'label', Mage::helper('rewardpoints')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('rewardpoints')->__('Delete Rule'));



        $this->_formScripts[] = "

            checkTarget = function(){
                if ($('rewardpoints_rule_type').getValue() == '".J2t_Rewardpoints_Model_Rules::TARGET_CART."'){
                    $('rewardpoints_rule_test').value = '';
                    $('rewardpoints_rule_points').value = '';
                    $('rewardpoints_rule_operator').style.display = 'block';
                    if($('show_operator')){
                        $('show_operator').style.display = 'none';
                    }

                    //$('order_id').up(1).hide();
                } else {
                    $('rewardpoints_rule_test').value = '';
                    $('rewardpoints_rule_points').value = '';
                    $$('select#rewardpoints_rule_operator option')[0].selected = true;
                    if($('show_operator')){
                        $('show_operator').style.display = 'block';
                    } else {
                        var div_el = new Element('div', { name: 'show_operator', id: 'show_operator' });
                        div_el.innerHTML = '=';
                        $('rewardpoints_rule_operator').parentNode.insert(div_el);
                        div_el.style.display = 'block';
                    }
                    $('rewardpoints_rule_operator').style.display = 'none';

                    $('rewardpoints_rule_test').style.display = 'block';
                    $('rewardpoints_rule_test').value = '';
                    if ($('span_from')){
                        $('show_between').style.display = 'none';
                    }

                }
            };

            changeOperator = function(){
                if ($('rewardpoints_rule_operator').getValue() == '".J2t_Rewardpoints_Model_Rules::OPERATOR_6."'){
                    if ($('span_from')){
                        $('show_between').style.display = 'block';
                    } else {
                        var spanfrom_el = new Element('span', { id: 'span_from' });
                        var spanto_el = new Element('span', {id: 'span_to' });
                        spanfrom_el.innerHTML = '".Mage::helper('rewardpoints')->__('From')."';
                        spanto_el.innerHTML = '".Mage::helper('rewardpoints')->__('To')."';

                        var div_el = new Element('div', { name: 'show_between', id: 'show_between' });
                        var from_el = new Element('input', { name: 'from_value', id: 'from_value', value: '', className: 'input-text', onKeyup: 'checkInput()' });
                        
                        var to_el = new Element('input', { name: 'to_value', id: 'to_value', value: '', className: 'input-text', onKeyup: 'checkInput()' });

                        div_el.insert(spanfrom_el);
                        div_el.insert(from_el);
                        div_el.insert(spanto_el);
                        div_el.insert(to_el);
                        $('rewardpoints_rule_test').parentNode.insert(div_el);
                        div_el.style.display = 'block';
                    }
                    $('from_value').value = '';
                    $('to_value').value = '';
                    $('rewardpoints_rule_test').value = '';

                    $('rewardpoints_rule_test').style.display = 'none';
                } else {
                    $('rewardpoints_rule_test').style.display = 'block';
                    $('rewardpoints_rule_test').value = '';
                    if ($('span_from')){
                        $('show_between').style.display = 'none';
                    }
                }
            };

            checkInput = function(){
                //alert('test');
                if ($('from_value') && $('to_value')) {
                    if ($('from_value').value.trim() != '' && $('to_value').value.trim() != ''){
                        var n1 = ($('from_value').value.replace(',','.')).trim();
                        n1 = parseFloat(n1);
                        var n2 = ($('to_value').value.replace(',','.')).trim();
                        if ( (parseFloat(n1) || parseInt(n1)) && (parseFloat(n2) || parseInt(n2)) ){
                            //OK
                            //alert(parseFloat(n1)+' > '+parseFloat(n2));
                            if (parseFloat(n1) < parseFloat(n2)){
                                $('rewardpoints_rule_test').value = parseFloat(n1)+';'+parseFloat(n2);
                            }
                            else {
                                $('rewardpoints_rule_test').value = '';
                            }
                            //alert(n1+';'+n2);
                        } else {
                            //KO
                            $('rewardpoints_rule_test').value = '';
                        }
                    }
                }
            }

            document.observe('dom:loaded', function() {
                if ($('rewardpoints_rule_operator').getValue() == '".J2t_Rewardpoints_Model_Rules::OPERATOR_6."'){
                    if ($('span_from')){
                        $('show_between').style.display = 'block';
                    } else {
                        var spanfrom_el = new Element('span', { id: 'span_from' });
                        var spanto_el = new Element('span', {id: 'span_to' });
                        spanfrom_el.innerHTML = '".Mage::helper('rewardpoints')->__('From')."';
                        spanto_el.innerHTML = '".Mage::helper('rewardpoints')->__('To')."';

                        var div_el = new Element('div', { name: 'show_between', id: 'show_between' });
                        var from_el = new Element('input', { name: 'from_value', id: 'from_value', value: '', className: 'input-text', onKeyup: 'checkInput()' });

                        var to_el = new Element('input', { name: 'to_value', id: 'to_value', value: '', className: 'input-text', onKeyup: 'checkInput()' });

                        div_el.insert(spanfrom_el);
                        div_el.insert(from_el);
                        div_el.insert(spanto_el);
                        div_el.insert(to_el);
                        $('rewardpoints_rule_test').parentNode.insert(div_el);
                        div_el.style.display = 'block';
                    }
                    var values_test = $('rewardpoints_rule_test').value.split(';');

                    $('from_value').value = values_test[0];
                    $('to_value').value = values_test[1];
                    //$('rewardpoints_rule_test').value = '';
                    

                    $('rewardpoints_rule_test').style.display = 'none';
                }
                var tr_line = $('rewardpoints_rule_extra').parentNode.parentNode;
                tr_line.style.display = 'none';
            });



        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('pointrules_data') && Mage::registry('pointrules_data')->getId() ) {
            return Mage::helper('rewardpoints')->__('Edit Rule');
        } else {
            return Mage::helper('rewardpoints')->__('Add Rule');
        }
    }

    public function getFormHtml()
    {
        return $this->getLayout()
            ->createBlock('rewardpoints/adminhtml_rules_edit_form')
            ->setAction($this->getSaveUrl())
            ->toHtml();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
}