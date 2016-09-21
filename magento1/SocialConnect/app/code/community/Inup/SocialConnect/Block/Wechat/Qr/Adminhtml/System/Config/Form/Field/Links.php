<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Block_Wechat_Qr_Adminhtml_System_Config_Form_Field_Links
    extends Inup_SocialConnect_Block_Adminhtml_System_Config_Form_Field_Links
{

    protected function getAuthProviderLink()
    {
        return 'Wechat Developers';
    }

    protected function getAuthProviderLinkHref()
    {
        return 'https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN';
    }

}