<?php


class J2t_Rewardpoints_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $namespace = 'rewardpoints';
        $this->init($namespace);
    }

    public function unsetAll()
    {
        parent::unsetAll();
    }
}
