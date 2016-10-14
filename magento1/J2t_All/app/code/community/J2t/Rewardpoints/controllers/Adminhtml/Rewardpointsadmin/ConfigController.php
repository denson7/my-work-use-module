<?php

class J2t_Rewardpoints_Adminhtml_Rewardpointsadmin_ConfigController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
    {
        return true;
    }
    
    public function exportTablepointsAction()
    {
        
        $tableratesCollection = Mage::getResourceModel('rewardpoints/stats_collection');
        $tableratesCollection->load();
        $csv = '';

        
        $csvHeader = array('"'.Mage::helper('rewardpoints')->__('Email').'"', '"'.Mage::helper('rewardpoints')->__('Points').'"', '"'.Mage::helper('rewardpoints')->__('Order ID').'"', '"'.Mage::helper('rewardpoints')->__('Store Ids').'"');
        $csv .= implode(',', $csvHeader)."\n";
       
        foreach ($tableratesCollection->getItems() as $item) {

            $customer = Mage::getModel('customer/customer')->load($item->getData('customer_id'));

            if ($item->getData('points_current') > 0){
                $points = $item->getData('points_current');
            } else {
                $points = '-'.$item->getData('points_spent');
            }
            
            if ($customer->getId()){
                $csvData = array('"'.$customer->getEmail().'"', '"'.$points.'"', '"'.$item->getData('order_id').'"', '"'.$item->getData('store_id').'"');
            }
            /*foreach ($csvData as $key_p => $points_arr) {
                foreach($points_arr as $key => $points_val){
                    $csvData[$key_p][$key] = '"'.str_replace('"', '""', $points_val).'"';
                }
            }*/
            $csv .= implode(',', $csvData)."\n";
        }

        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=rewardpoints.csv");
        echo $csv;
        exit;
    }

}

