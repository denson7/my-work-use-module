<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Image Helper
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Helper_Image extends Mage_Core_Helper_Abstract
{
    protected $_obj;
    protected $_pathname;
    protected $_placeholder;
    protected function _reset()
    {
        $this->_obj = null;
        $this->_pathname = null;
        $this->_placeholder = null;
        return $this;
    }
    public function init($obj,$pathName)
    {
        $this->_reset();
        $this->setObj($obj);
        $this->setPathname($pathName);
        return $this;
    }
    public function resize($width, $height)
    {
    	$imagePathInput = Mage::getBaseDir('media') .DS.$this->getPathname().DS.$this->getObj()->getImage();
        $imageName=$this->getObj()->getImage();
    	if($imageName==''){
            $imageName='no-image.png';
            $imagePathInput=Mage::getBaseDir('media') . DS .'brands'.DS.'no-image.png';
    	}
    	$imagePathOutput =$this->getPathname().DS.$width.'x'.$height.DS.$imageName;
        $imageUrl=str_replace(DS,'/',$imagePathOutput);
        $imageUrl=Mage::getBaseUrl('media').$imageUrl;
        $pathBaseDir=Mage::getBaseDir('media').DS.$imagePathOutput;
        $this->setPlaceholder($imageUrl);
        if(!is_file($pathBaseDir)) {
            $_backgroundColor  = array(255, 255, 255);
            try {
                $imageObj = new Varien_Image($imagePathInput);
                $imageObj->backgroundColor($_backgroundColor);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(TRUE);
                $imageObj->keepFrame(TRUE);
                $h=$imageObj->getOriginalHeight();
                $w=$imageObj->getOriginalWidth();
                if ((float)$w/$h >= (float)$width/$height){
                    $height = $height;
                    $width=$w/$h*$height;
                    $imageObj->resize($width,$height);
                }else{
                    $width = $width;
                    $height=$h/$w*$width;
                    $imageObj->resize($width,$height);
                }
                $imageObj->save($pathBaseDir);
            } catch (Exception $e) {}
        }
        return $this->getPlaceholder();
    }
    
    
    public function resizeThumbnail($width, $height)
    {
    	$imagePathInput = Mage::getBaseDir('media') .DS.$this->getPathname().DS.$this->getObj()->getThumbnailImage();
        $imageName=$this->getObj()->getThumbnailImage();
    	if($imageName==''){
            $imageName='no-image.png';
            $imagePathInput=Mage::getBaseDir('media') . DS .'brands'.DS.'no-image.png';
    	}
    	$imagePathOutput =$this->getPathname().DS.$width.'x'.$height.DS.$imageName;
        $imageUrl=str_replace(DS,'/',$imagePathOutput);
        $imageUrl=Mage::getBaseUrl('media').$imageUrl;
        $pathBaseDir=Mage::getBaseDir('media').DS.$imagePathOutput;
        $this->setPlaceholder($imageUrl);
        if(!is_file($pathBaseDir)) {
            $_backgroundColor  = array(255, 255, 255);
            try {
                $imageObj = new Varien_Image($imagePathInput);
                $imageObj->backgroundColor($_backgroundColor);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(TRUE);
                $imageObj->keepFrame(TRUE);
                $h=$imageObj->getOriginalHeight();
                $w=$imageObj->getOriginalWidth();
            if ((float)$w/$h <= (float)$width/$height){
                $height = $height;
                $width=$w/$h*$height;
                $imageObj->resize($width,$height);
            }else{
                $width = $width;
                $height=$h/$w*$width;
                $imageObj->resize($width,$height);
            }
            $imageObj->save($pathBaseDir);
            } catch (Exception $e) {}
        }
        return $this->getPlaceholder();
    }
    
    public function resizeThumbnail1($thumbImage, $pathName, $width, $height)
    {
    	$imagePathInput = Mage::getBaseDir('media') .DS.$pathName.DS.$thumbImage;
        $imageName = $thumbImage;
    	if($imageName==''){
            $imageName='no-image.png';
            $imagePathInput=Mage::getBaseDir('media') . DS .'brands'.DS.'no-image.png';
    	}
    	$imagePathOutput = $pathName.DS.$width.'x'.$height.DS.$imageName;
        $imageUrl=str_replace(DS,'/',$imagePathOutput);
        $imageUrl=Mage::getBaseUrl('media').$imageUrl;
        $pathBaseDir=Mage::getBaseDir('media').DS.$imagePathOutput;
        $this->setPlaceholder($imageUrl);
        if(!is_file($pathBaseDir)) {
            $_backgroundColor  = array(255, 255, 255);
            try {
                $imageObj = new Varien_Image($imagePathInput);
                $imageObj->backgroundColor($_backgroundColor);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(TRUE);
                $imageObj->keepFrame(TRUE);
                $h=$imageObj->getOriginalHeight();
                $w=$imageObj->getOriginalWidth();
            if ((float)$w/$h <= (float)$width/$height){
                $height = $height;
                $width=$w/$h*$height;
                $imageObj->resize($width,$height);
            }else{
                $width = $width;
                $height=$h/$w*$width;
                $imageObj->resize($width,$height);
            }
            $imageObj->save($pathBaseDir);
            } catch (Exception $e) {}
        }
        return $this->getPlaceholder();
    }
    
    protected function setObj($obj)
    {
        $this->_obj = $obj;
        return $this;
    }

    protected function getObj()
    {
        return $this->_obj;
    }
    protected function setPlaceholder($placeholder)
    {
        $this->_placeholder = $placeholder;
        return $this;
    }

    protected function getPlaceholder()
    {
        return $this->_placeholder;
    }
    protected function setPathname($pathname)
    {
        $this->_pathname = $pathname;
        return $this;
    }

    protected function getPathname()
    {
        return $this->_pathname;
    }
}