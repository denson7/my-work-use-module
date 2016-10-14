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

$installer = $this;

$installer->startSetup();

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

$table = $resource->getTableName('core_config_data');

$query = "SELECT * FROM $table WHERE path = 'searchanise/config/input_id_search'";
$results = $readConnection->fetchAll($query);

foreach ($results as $row) {
    $writeConnection->query(
        "
        INSERT INTO $table (scope, scope_id, path, value) VALUES(
            '{$row['scope']}', '{$row['scope_id']}', 'searchanise/config/search_input_selector', '#{$row['value']}'
        )
        "
    );
}

$writeConnection->query("DELETE FROM $table WHERE path = 'searchanise/config/input_id_search'");

$installer->endSetup();
