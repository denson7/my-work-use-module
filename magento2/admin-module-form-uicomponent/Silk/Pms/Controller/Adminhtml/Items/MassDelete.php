<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-11
 * Time: 上午10:48
 */
namespace Silk\Pms\Controller\Adminhtml\Items;

use Silk\Pms\Controller\Adminhtml\Items;

class MassDelete extends Items
{
    /**
     * @return void
     */
    public function execute()
    {
        // Get Ids of the selected items
        $Ids = $this->getRequest()->getParam('pms');//这里pms与silk_pms_items_index.xml中的name="form_field_name"节点命名有关

        foreach ($Ids as $Id) {
            try {
                //实例化model对象
                $Model = $this->_objectManager->create('Silk\Pms\Model\Items');
                $Model->load($Id)->delete();
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        if (count($Ids)) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were deleted.', count($Ids))
            );
        }

        $this->_redirect('*/*/index');
    }
}