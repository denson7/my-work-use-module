<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-9-22
 * Time: 下午2:04
 */
class Silk_CustomLinkedProducts_Model_Observer extends Varien_Object
{
    public function catalogProductPrepareSave($observer)
    {
        $event = $observer->getEvent();

        $product = $event->getProduct();
        $request = $event->getRequest();

        $links = $request->getPost('links');
        if (isset($links['custom']) && !$product->getCustomReadonly()) {
            $product->setCustomLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['custom']));
        }
    }

    public function catalogModelProductDuplicate($observer)
    {
        $event = $observer->getEvent();

        $currentProduct = $event->getCurrentProduct();
        $newProduct = $event->getNewProduct();

        $data = array();
        $currentProduct->getLinkInstance()->useCustomLinks();
        $attributes = array();
        foreach ($currentProduct->getLinkInstance()->getAttributes() as $_attribute) {
            if (isset($_attribute['code'])) {
                $attributes[] = $_attribute['code'];
            }
        }
        foreach ($currentProduct->getCustomLinkCollection() as $_link) {
            $data[$_link->getLinkedProductId()] = $_link->toArray($attributes);
        }
        $newProduct->setCustomLinkData($data);
    }

}