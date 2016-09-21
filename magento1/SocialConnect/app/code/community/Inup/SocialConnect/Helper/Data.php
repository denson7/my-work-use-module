<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Helper_Data extends Mage_Core_Helper_Abstract
{
    public static function log($message, $level = null, $file = '', $forceLog = false)
    {
        if (Mage::getIsDeveloperMode()) {
            Mage::log($message, $level, $file, $forceLog);
        }
    }
}