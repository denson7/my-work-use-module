<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

class Simtech_Searchanise_Model_Config extends Mage_Core_Model_Abstract
{
    /**
     * Resource model
     * Used for operations with DB
     *
     * @var Mage_Searchanise_Model_Mysql4_Config
     */
    protected $_resourceModel;

    protected function _construct()
    {
        $this->_init('searchanise/config', 'config_id');
    }

    /**
     * Get config resource model
     *
     * @return Mage_Searchanise_Store_Mysql4_Config
     */
    public function getResourceModel()
    {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = Mage::getResourceModel('searchanise/config');
        }

        return $this->_resourceModel;
    }

    /**
     * Retrieve store configuration data
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return  string|null
    */
    public function getConfig($path, $scope = 'default', $scopeId = 0)
    {
        $path = rtrim($path, '/');
        
        $collection = $this->getCollection()
            ->addFieldToFilter('path', $path)
            ->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('scope_id', $scopeId)
            ->setPageSize(1)
            ->load();

        if (!empty($collection)) {
            foreach ($collection as $key => $data) {
                $value = $data->getValue();

                if (!empty($value)) {
                    return $value;
                }
            }
        }

        return;
    }

    /**
     * Save config value to DB
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return Mage_Searchanise_Store_Config
     */
    public function saveConfig($path, $value, $scope = 'default', $scopeId = 0)
    {
        $resource = $this->getResourceModel();
        $resource->saveConfig(rtrim($path, '/'), $value, $scope, $scopeId);

        return $this;
    }

    /**
     * Delete config value from DB
     *
     * @param   string $path
     * @param   string $scope
     * @param   int $scopeId
     * @return  Mage_Searchanise_Model_Config
     */
    public function deleteConfig($path, $scope = 'default', $scopeId = 0)
    {
        $resource = $this->getResourceModel();
        $resource->deleteConfig(rtrim($path, '/'), $scope, $scopeId);

        return $this;
    }
}