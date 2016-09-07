<?php

class Magestore_Shopbybrand_Block_Adminhtml_System_Config_Implementcode extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
        $layout  =  Mage::helper('shopbybrand')->returnlayout();
        $block = Mage::helper('shopbybrand')->returnblock();
        $text =  Mage::helper('shopbybrand')->returntext();
        $template = Mage::helper('shopbybrand')->returntemplate();
        return '
<!-- <div class="entry-edit-head collapseable"><a onclick="Fieldset.toggleCollapse(\'shopbybrand_template\'); return false;" href="#" id="shopbybrand_template-head" class="open">Implement Code</a></div> -->
<input id="shopbybrand_template-state" type="hidden" value="1" name="config_state[shopbybrand_template]">
<fieldset id="shopbybrand_template" class="config collapseable" style="">
    <div id="messages" class="div-mess-shopbybrand">
        <ul class="messages mess-megamennu">
            <li class="notice-msg notice-shopbybrand">
                <ul>
                    <li>
                    '.$text.'
                    </li>				
                </ul>
            </li>
        </ul>
    </div>
    <br/>  
    <div id="messages" class="div-mess-shopbybrand">
        <ul class="messages mess-megamennu">
            <li class="notice-msg notice-shopbybrand">
                <ul>
                    <li>
                    '.Mage::helper('shopbybrand')->__('Option 1: Add the code below to a CMS Page or a Static Block').'
                    </li>
                </ul>
            </li>
        </ul>
    </div>
        <ul>
            <li>
                <code>
                '.$block.'
                </code>	
            </li>
        </ul>     
    <br/>
    <div id="messages" class="div-mess-shopbybrand">
       <ul class="messages mess-megamennu">
            <li class="notice-msg notice-shopbybrand">
                <ul>
                    <li>
                    '.Mage::helper('shopbybrand')->__('Option 2: Add the code below to a template file').'
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <ul>
        <li>
            <code>
            &lt;?php echo'.$template.' ?&gt;
            </code>	
        </li>
    </ul>
    <br/>
    <div id="messages" class="div-mess-shopbybrand">
        <ul class="messages mess-megamennu">
            <li class="notice-msg notice-shopbybrand">
                <ul>
                    <li>
                    '.Mage::helper('shopbybrand')->__('Option 3: Add the code below to a layout file').'
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <ul>
        <li>
            <code>
            '.$layout.'
            </code>	
        </li>
    </ul>
</fieldset>';
    }
}
