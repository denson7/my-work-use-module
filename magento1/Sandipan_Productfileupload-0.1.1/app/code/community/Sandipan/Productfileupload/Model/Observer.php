<?php
class Sandipan_Productfileupload_Model_Observer
{
    /**
     * Flag to stop observer executing more than once
     *
     * @var static bool
     */
    static protected $_singletonFlag = false;
 
    /**
     * This method will run when the product is saved from the Magento Admin
     * Use this function to update the product model, process the
     * data or anything you like
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveProductTabData(Varien_Event_Observer $observer)
    {
        if (!self::$_singletonFlag) {
            self::$_singletonFlag = true;
             
            $product = $observer->getEvent()->getProduct();
         
            try {
                /**
                 * Perform any actions you want here
                 *
                 */
				 
				 //echo '<pre>'; print_r($_POST['addi_file']); echo '</pre>';
				 //echo '<pre>'; print_r($_FILES); echo '</pre>'; exit;
				
				if(isset($_POST['addi_file'])) {
					foreach($_POST['addi_file'] as $key => $deleteFile) {
						if($deleteFile == 0){
							$dmodel = Mage::getModel('productfileupload/productfileupload');
							$dmodel->setId($key)
								->delete();

						}
					}
				}
				
				if(isset($_FILES['energy_guide_file'])) {
					//echo '<pre>'; print_r($_FILES['energy_guide_file']); echo '</pre>';
					
					for($fi=0; $fi< count($_FILES['energy_guide_file']['name']); $fi++){
						if($_FILES['energy_guide_file']['name'][$fi] != '') {
							try {

                                $new_file_name = str_replace(' ', '_', $_FILES['energy_guide_file']['name'][$fi]);
								$target_path = Mage::getBaseDir('media') . DS . 'productfileupload' . DS;
								$target_path = $target_path . basename( $new_file_name );
								
								if(move_uploaded_file($_FILES['energy_guide_file']['tmp_name'][$fi], $target_path)) {
									//$fdata = array();
									$fdata['filename'] = $new_file_name;
									$fdata['productid'] = $product->getId();
									$fdata['fileplace'] = 1;
									$fmodel = Mage::getModel('productfileupload/productfileupload');		
									$fmodel->setData($fdata);
										//->setId($this->getRequest()->getParam('id'));
									
										if ($fmodel->getCreatedTime == NULL || $fmodel->getUpdateTime() == NULL) {
											$fmodel->setCreatedTime(now())
												->setUpdateTime(now());
										} else {
											$fmodel->setUpdateTime(now());
										}	
										
									$fmodel->save();
								} else{
									Mage::getSingleton('adminhtml/session')->addError("There was an error uploading the file, please try again!");;
								}
									
							} catch (Exception $e) {
						  		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
							}
							
						}
					}
				}
				

				//exit;
 
                /**
                 * Uncomment the line below to save the product
                 *
                 */
                //$product->save();
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }
      
    /**
     * Retrieve the product model
     *
     * @return Mage_Catalog_Model_Product $product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }
     
    /**
     * Shortcut to getRequest
     *
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
}
