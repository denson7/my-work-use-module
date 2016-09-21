<?php

/**
 * User: GROOT (pzyme@outlook.com)
 * Date: 2016/8/15
 * Time: 10:13
 */
class Inup_SocialConnect_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    protected $_customerAttributes = array();

    public function setCustomerAttributes($customerAttributes)
    {
        $this->_customerAttributes = $customerAttributes;

        return $this;
    }

    /**
     * Add our custom attributes
     *
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function installCustomerAttributes()
    {
        foreach ($this->_customerAttributes as $code => $attr) {
            $this->addAttribute('customer', $code, $attr);
        }

        return $this;
    }

    /**
     * Remove custom attributes
     *
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function removeCustomerAttributes()
    {
        foreach ($this->_customerAttributes as $code => $attr) {
            $this->removeAttribute('customer', $code);
        }

        return $this;
    }
}