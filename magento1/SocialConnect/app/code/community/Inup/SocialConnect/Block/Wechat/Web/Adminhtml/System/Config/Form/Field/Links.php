<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Block_Wechat_Web_Adminhtml_System_Config_Form_Field_Links
    extends Inup_SocialConnect_Block_Adminhtml_System_Config_Form_Field_Links
{

    protected function getAuthProviderLink()
    {
        return 'Wechat Developers';
    }

    protected function getAuthProviderLinkHref()
    {
        return 'https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842&token=&lang=zh_CN';
    }

}