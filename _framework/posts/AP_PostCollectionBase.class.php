<?php

/**
 * 
 * Base class for wrapping an array of PostItems
 * 
 * @author AgilePress Core Developement Team
 *
 * @property array PostArray
 * @property integer Count
 */

class AP_PostCollectionBase extends AP_Base {
	
	protected $objPostArray = array(); 
	protected $intPostCount;
	
	public function __construct(){
		return true;
	}
	
	/**
	 * Takes a numeric post ID and attempts to find and remove that post from the current collection
	 * 
	 * @param integer $intPostID
	 */
	
	public function RemovePost($intPostID){
		if(array_key_exists($intPostID, $this->objPostArray))
			unset($this->objPostArray[$intPostID]);
		else
			$this->error('$intPostID argument must be a integer post ID currently contained in the collection');
	}
	
	/**
	 * A wrapper for AddPost and MergeCollections, if argument is single WP_Post, AP_PostItem or PostID it will attempt to add the post to the current collection, if it is an AP_Collection, it will merge the collections into the collection where Add() was called
	 * 
	 * @param mixed $mixValue
	 */
	
	public function Add($mixValue) {
		if(is_array($mixValue))
			foreach($mixValue as $mixPost)
				$this->AddPost($mixPost);
		elseif($mixValue instanceof AP_PostCollectionBase)
			$this->objPostArray = AP_PostCollectionBase::MergeCollections($this, $mixValue)->PostArray;
		else
			$this->AddPost($mixValue);
	}
		
	/**
	 * Takes either a single WP_Post, AP_PostItem, or integer PostID and adds the post to the collection as the correct type of AP_PostItem
	 * 
	 * @param mixed $mixPost
	 */
	
	public function AddPost($mixPost){
		if($mixPost instanceof AP_PostItemBase)
			$this->objPostArray[$mixPost->ID] = $mixPost;
		elseif($mixPost instanceof WP_Post)
			$this->objPostArray[$mixPost->ID] = AP_PostItemBase::Convert($mixPost); 
		elseif(is_int($mixPost))
			$this->objPostArray[$mixPost] = AP_PostItemBase::Convert($mixPost);
		else
			$this->error('$mixPost argument must be one of the following types: WP_Post, integer (post_id), AP_PostItemBase or an extension of AP_PostItemBase');
	}
	
	/**
	 * Static function, takes two AP_PostCollections and returns a post collection containing the posts from both collections
	 * 
	 * @param AP_PostCollection $objPosts
	 * @param AP_PostCollection $objPostsSecond
	 * @return AP_PostCollectionBase|error
	 */
	
	public static function MergeCollections($objPosts, $objPostsSecond){
		if($objPosts instanceof AP_PostCollectionBase && $objPostsSecond instanceof AP_PostCollectionBase){
			$objMergedCollection = new AP_PostCollectionBase;
				foreach($objPosts->PostArray as $objPost)
					$objMergedCollection->AddPost($objPost);
				foreach($objPostsSecond->PostArray as $objPost)
					$objMergedCollection->AddPost($objPost);
				return $objMergedCollection; 
		}
		else 
			return 'To merge collections both collections must be AP_PostCollectionBase or AP_PostCollectionBase extended objects';
	}
	
	public function __get($mixName) {
		switch ($mixName) {
			case 'PostArray' :
				return array_merge($this->objPostArray);
			case 'Count' :
				return count($this->objPostArray);
			
		}
		
	}
	
	public function __set($mixName , $mixValue){
		switch($mixName) {
			case 'PostArray' :
				if(is_array($mixValue)){
					$boolIsAPPostObject = true;
					foreach($mixValue as $objPost)
						if(!$objPost instanceof AP_PostItemBase)  {
							$boolIsAPPostObject = false;
							break; 
						}
						
					return $boolIsAPPostObject ? $this->objPostArray = $mixValue : null;
				}
				else 
					$this->error( 'AP_PostCollectionBase->Posts must be set to an array of AP_PostItemBase objects');	
			break;
		}
	}
}