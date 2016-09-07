<?php
/**
 * Video Plugin for Magento
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Niveus
 * @package    Niveus_ProductVideo
 * @copyright  Copyright (c) 2013 Niveus Solutions (http://www.niveussolutions.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Niveus Solutions <support@niveussolutions.com>
 */


class Niveus_ProductVideo_Block_Video extends Mage_Catalog_Block_Product_View_Abstract
{
    protected $_videosCollection = null;
	
	
    public function getCode($video)
    {
	   return $video->getVideoCode();
    }
    
    public function getTitle($video)
    {
	if ($video->getVideoTitle())
	{
		return $video->getVideoTitle();
	}
	else
	{
		return $this->getProduct()->getName();
	}
    }
  
    
    protected function _getProductVideos()
    {
        $storeId = Mage::app()->getStore()->getId();
        
        if (is_null($this->_videosCollection))
    	{
            $this->_videosCollection = $this->_getVideosCollection($storeId)->getSize() ? $this->_getVideosCollection($storeId) : $this->_getVideosCollection();
    	}
        
        return $this->_videosCollection;
    }
    
    
    protected function _getVideosCollection($storeId = 0)
    {
        return Mage::getModel('productvideo/videos')
            ->getCollection()
 	    ->addFieldToFilter('product_id', $this->getProduct()->getId())
            ->addFieldToFilter('store_id', $storeId);
    }
    
    
    /**
     * This function checks the url is utube
     * @param type $url
     * @return type
     */
    function is_youtube($url)
    {
    	return (preg_match('/youtu\.be/i', $url) || preg_match('/youtube\.com\/watch/i', $url));
    }
    
    /**
     * This function checks url has vimeo
     * @param type $url
     * @return type
     */
    function is_vimeo($url)
    {
    	return (preg_match('/vimeo\.com/i', $url));
    }
    
    /**
     * This function get the youtube id.
     * @param type $url
     * @return string
     */
    function youtube_video_id($url)
    {
    	$pattern = '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/';
    	preg_match($pattern, $url, $matches);
    	if (count($matches) && strlen($matches[7]) == 11)
    	{
    		return $matches[7];
    	}
    }
    
    /**
     * This function get the vimeo id
     * @param type $url
     * @return string
     */
    function vimeo_video_id($url)
    {
    	$pattern = '/\/\/(www\.)?vimeo.com\/(\d+)($|\/)/';
   		preg_match($pattern, $url, $matches);
   		if (count($matches))
   		{
   			return $matches[2];
    	}
    	
    	return '';
    }

}
