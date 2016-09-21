<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Block_Qq_Adminhtml_System_Config_Form_Field_Links
    extends Inup_SocialConnect_Block_Adminhtml_System_Config_Form_Field_Links
{

    protected function getAuthProviderLink()
    {
        return 'QQ Connect';
    }

    protected function getAuthProviderLinkHref()
    {
        return 'http://connect.qq.com/';
    }

}