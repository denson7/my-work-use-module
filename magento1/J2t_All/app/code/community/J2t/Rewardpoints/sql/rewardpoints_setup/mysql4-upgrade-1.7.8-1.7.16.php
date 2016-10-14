<?php
/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2015 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

?>
<?php
$installer = $this;
$attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setCodeFilter('reward_points_groups')
                        ->getFirstItem();

$entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
$attributeGroup = Mage::getResourceModel('eav/entity_attribute_set_collection')
                        ->addFieldToFilter('attribute_set_name', array('eq' => 'Default'))
                        ->addFieldToFilter('entity_type_id', array('eq' => $entityTypeId))
                        ->getFirstItem();

if (!$attributeGroup->getAttributeSetId()){
    $installer->addAttributeSet(Mage_Catalog_Model_Product::ENTITY, 'Default');
    $attributeGroup = Mage::getResourceModel('eav/entity_attribute_set_collection')
                        ->addFieldToFilter('attribute_set_name', array('eq' => 'Default'))
                        ->addFieldToFilter('entity_type_id', array('eq' => $entityTypeId))
                        ->getFirstItem();
}

if(!$attributeInfo->getAttributeId() && $attributeGroup->getAttributeSetId()){
    
    $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
    $installer->startSetup();
    /**
     * Adding Different Attributes
     */

    // adding attribute group
    $setup->addAttributeGroup('catalog_product', 'Default', 'J2T Reward Points', 1000);
    $entityTypeId     = $installer->getEntityTypeId('catalog_product');
    $attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
    $attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

    // the attribute added will be displayed under the group/tab Special Attributes in product edit page
    $setup->addAttribute('catalog_product', 'reward_points_groups', array(
        'group'                         => 'J2T Reward Points',
        'input'                         => 'text',
        'type'                          => 'text',
        'label'                         => 'Group Reward points',
        'frontend_class'                => '',
        'backend'                       => '',
        'visible'                       => true,
        'required'                      => false,
        'user_defined'                  => false,
        'searchable'                    => false,
        'filterable'                    => false,
        'comparable'                    => false,
        'visible_on_front'              => false,
        'visible_in_advanced_search'    => false,
        'is_html_allowed_on_front'      => false,
        'used_in_product_listing'       => true,
        'note'                          => 'Point values per group. Specify different point value according to groups.',
        'global'                        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    ));

    $installer->endSetup();
}
