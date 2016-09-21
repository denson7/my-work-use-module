<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Block_Wechat_Qr_Adminhtml_System_Config_Form_Field_Redirects
    extends Inup_SocialConnect_Block_Adminhtml_System_Config_Form_Field_Redirects
{

    protected function getAuthProvider()
    {
        return 'wechat';
    }

}