<?php
class Sandipan_Productfileupload_Adminhtml_ProductfileuploadbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Backend Page Title"));
	   $this->renderLayout();
    }
	public function uploadAction()
    {
		$targetFolder = Mage::getBaseDir('media') . '\productfileupload\\'; // Relative to the root

	$error = "";
	$msg = "";
	$fileElementName = 'fileToUpload';
	if(!empty($_FILES[$fileElementName]['error']))
	{
		switch($_FILES[$fileElementName]['error'])
		{

			case '1':
				$error = 'The uploaded file exceeds the max upload filesize';
				break;
			case '2':
				$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case '3':
				$error = 'The uploaded file was only partially uploaded';
				break;
			case '4':
				$error = 'No file was uploaded.';
				break;

			case '6':
				$error = 'Missing a temporary folder';
				break;
			case '7':
				$error = 'Failed to write file to disk';
				break;
			case '8':
				$error = 'File upload stopped by extension';
				break;
			case '999':
			default:
				$error = 'No error code avaiable';
		}
	}elseif(empty($_FILES['fileToUpload']['tmp_name']) || $_FILES['fileToUpload']['tmp_name'] == 'none')
	{
		$error = 'No file was uploaded..';
	}else 
	{
			//$msg .= " File Name: " . $_FILES['fileToUpload']['name'] . ", ";
			//$msg .= " File Size: " . @filesize($_FILES['fileToUpload']['tmp_name']);
			//for security reason, we force to remove all uploaded file
			//@unlink($_FILES['fileToUpload']);
			
			$check=explode('/',$_FILES['fileToUpload']['type']);
			$size=$_FILES['fileToUpload']['size'];
			$max_size=2*1024*1024;
			if($size<=$max_size){
				
				$image_name = $targetFolder . $_FILES['fileToUpload']['name'];
				move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $image_name);

			}
			else{
				$error = 'Upload valid image of filesize less than 2mb.';
			}
	
	}		
    }
}