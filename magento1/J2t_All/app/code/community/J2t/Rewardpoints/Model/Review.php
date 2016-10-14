<?php
class J2t_Rewardpoints_Model_Review extends Mage_Review_Model_Review
{
    
    public function aggregate()
    {
        //if ($this->isApproved()){
        if ($this->getStatusId() == self::STATUS_APPROVED){
            if ($pointsInt = Mage::getStoreConfig('rewardpoints/other_points/review_points', Mage::app()->getStore()->getId())){
                //ret review id... $this->getId();
                //check store id
                if ($this->getCustomerId()){
                    $reward_model = Mage::getModel('rewardpoints/stats');
                    $data = array('customer_id' => $this->getCustomerId(), 'store_id' => $this->getStoreId(), 'points_current' => $pointsInt, 'order_id' => Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW);
                    $reward_model->setData($data);
                    $reward_model->save();

                    /*$points = Mage::getModel('rewardpoints/account')->load($this->getCustomerId());
                    $points->addPoints($pointsInt);
                    $points->storeId = $this->getStoreId();
                    $points->save(J2t_Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW, true);*/
                }
            }
        }
        parent::aggregate();
        return $this;
    }
}