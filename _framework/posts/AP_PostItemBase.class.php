<?php

/** 
 * 
 * Base class for all individual Post Items
 * 
 * @author AgilePress Core Development Team
 * 
 * @property integer ID
 * @property integer AuthorID
 * @property AP_User Author
 * @property string Name
 * @property string Type
 * @property string Title
 * @property string Date
 * @property string DateGMT
 * @property string Content
 * @property string Excerpt
 * @property string Status
 * @property string CommentStatus
 * @property string PingStatus
 * @property string Password
 * @property string Parent
 * @property string Modified
 * @property string ModifiedGMT
 * @property integer CommentCount
 * @property string ThumbnailURL
 * @property array MetaArray
 * @property WP_Post Post
 */

class AP_PostItemBase extends AP_Base {

	protected $objPost = null;
	protected $mixMetaArray = array();
	protected $boolIsPendingPost = false;
	protected $boolModifiedArray = array(); 
	protected $strThumbnailURL = null;
	
	/**
	 * Creates a new AP_PostItem, either empty or with information corresponding to the ID/Name/Object passed 
	 * 
	 * @param [WP_Post or numeric(post_id) or string(post_name)] $mixValue 
	 */
	
	public function __construct($mixValue = null ) {
		$this->Init($mixValue); 
		$this->InitGeneratedProperties();
		$this->InitGlobalProperties();
		$this->InitProperties();
	}
	
	/**
	 * Initiates the properties a new AP_PostItem, either empty or with information corresponding to the ID/Name/Object passed 
	 * 
	 * @param [WP_Post or numeric(post_id) or string(post_name)] $mixValue 
	 */

	protected function Init($mixValue){
		
		// Initialize settings
		$this->boolIsPendingPost = false;
		
		if ($mixValue instanceof WP_Post) {
			$this->objPost = $mixValue;
			}
		elseif (is_numeric($mixValue))  {
			$this->objPost = get_post($mixValue);
			}
		elseif (is_string($mixValue)) {
			$this->objPost = get_page_by_title($mixValue, OBJECT, 'post');
			}
		else {
			$this->objPost =  (object) array();			
			$this->boolIsPendingPost = true;
		}
	}
	
	protected function InitGeneratedProperties() {
		
	}
	
	protected function InitGlobalProperties() {
		
		
	}
	
	protected function InitProperties() {
	
	}
	
	/**
	 * Takes a WP_Post, post_id or AP_PostItemBase and returns the correct Custom PostItem class extended from PostItemBase
	 * 
	 * @param [integer or WP_Post or AP_PostItemBase] $mixValue
	 */
	public static function Convert($mixValue){
		if($mixValue instanceof WP_Post){
			$objPostType= get_post_type_object($mixValue->post_type);
			$strClassName = 'AP_' . $objPostType->labels->singular_name . "Item";
			if (class_exists($strClassName))
				$objConvertedPost = new $strClassName($mixValue);
			else 
				$objConvertedPost = new AP_PostItemBase($mixValue);
			return $objConvertedPost;
		}
		elseif($mixValue instanceof AP_PostItemBase){
			$objPostType = get_post_type_object($mixValue->Type);
			$strClassName = 'AP_' . $objPostType->labels->singular_name . "Item"; 
			if (class_exists($strClassName))
				$objConvertedPost = new $strClassName($mixValue->Post);
			else
				$objConvertedPost = new AP_PostItemBase($mixValue->Post);
			return $objConvertedPost;
		}
		elseif(is_int($mixValue)){
			$objOriginalPost = get_post($mixValue);
			$objPostType = get_post_type_object($objOriginalPost->post_type);
			$strClassName = 'AP_' . $objPostType->labels->singular_name . "Item";
			if (class_exists($strClassName))
				$objConvertedPost = new $strClassName($objOriginalPost);
			else 
				$objConvertedPost = new AP_PostItemBase($objOriginalPost);
			return $objConvertedPost;
		}
		else {
			$this->error('$mixValue argument must be one of the following types: WP_Post, integer (post-id), AP_PostItemBase or an extension of AP_PostItemBase');
		}
		
		
	}
	
	/**
	 *  attempts to set the current object to the pages current post
	 *  
	 */
	
	
	public function GetCurrentPost(){
		global $post;
		if($post){
			$this->Init($post);
			return $this->objPost;
		}
		else {
			$this->error("Page is not a post");
		}
		
	}
	
	/**
	 * Sets the current objects meta array to the current posts meta or returns the meta array of a post whose ID was passed
	 * 
	 * @param integer $id
	 * @return array $mixMetaArray:
	 */
	public function GetPostMeta($id = null){
		if ($id == null )
			$id = $this->ID;
		if(is_numeric($id) && $id != 0) {
			$this->mixMetaArray = get_post_meta($id);
			//$this->mixOldMetaArray = $this->mixMetaArray;
			return $this->mixMetaArray;
		}
		
		return array();
		
	}
	
	/**
	 * Saves the current data in $this->MetaArray to the wordpress database
	 */
	
	
	protected function UpdatePostMeta(){
		if(!empty($this->mixMetaArray)){
			if($this->ID <> null)
				$id = $this->ID;
			else
				$this->error('Cannot Update meta information for post that does not exist');
			foreach ($this->mixMetaArray as $key => $value)
				if($this->boolModifiedArray[$key])
					update_post_meta($this->ID, $key, $value);
		} 
		else 
			$this->error('$this->mixMetaArray is empty');
	}

	/**
	 * Clears and sets the meta array with a single meta key-value pair, if $boolIsArray is false it takes a string, if true it takes an array 
	 * 
	 * @param string  $strName
	 * @param [string or string[]] $strValue
	 * @param boolean $boolIsArray
	 */
	
	protected function SetMetaArray($strName, $strValue, $boolIsArray = false){
		$this->boolModifiedArray[$strName] = true;
		if($boolIsArray)
			$this->mixMetaArray[$strName] = array($strValue);
		else
			$this->mixMetaArray[$strName] = $strValue;
		
	}
	
	/**
	 * Gets a meta value of the current post when passed a key, returns a string if boolIsArray is false, an array if boolIsArray is true
	 * 
	 * @param string $strName
	 * @param boolean $boolIsArray
	 * @return [string or string[]]
	 */
	
	public function GetMetaValue($strName, $boolIsArray = false){
		if(empty($this->mixMetaArray)) $this->GetPostMeta(); 
		if(array_key_exists($strName, $this->mixMetaArray)){
			if(!$boolIsArray)
				return $this->mixMetaArray[$strName][0];
			else
				return $this->mixMetaArray[$strName];
		}else{
			$this->mixMetaArray[$strName] = get_post_meta($this->ID, $strName, $boolIsArray );
			return $this->mixMetaArray[$strName];
		}
	}
	
	
	/**
	 * Sets a meta value in the current mixMetaArray, must be of type array 
	 * 
	 * @param string $strField
	 * @param mixed[] $mixValue
	 */
	
	
	public function SetMetaValue($strField, $mixValue){
		$this->mixMetaArray[$strField] = $mixValue; 
		$this->UpdatePostMeta(); 
	}
	
	/**
	 * Attempts to delete a meta field-value pair from the current mixMetaArray 
	 * 
	 * @param string $strField
	 */
	
	public function DeleteMetaField($strField){
		if(!empty($this->mixMetaArray)){
			if (array_key_exists($strField, $this->mixMetaArray)){
				if($this->ID)
					delete_post_meta($this->ID,$strField);
				else
					$this->error("Object is not set to a post; cannot delete meta data from post that does not exist");
			}
			else 
				$this->error("Meta field does not exist for the current post");
		}
		else
			$this->error('$this->mixMetaArray is empty');
	}
	
	/**
	 * Takes all information in the current AP_PostItem object and updates the values in the wordpress database
	 * 
	 */
	
	protected function Update(){
		$mixNewValuesArray = array();
		foreach ($this->objPost as $key => $value)
				$mixNewValuesArray[$key] = $value; 
		if(!$this->boolIsPendingPost){
			wp_update_post($mixNewValuesArray); 
			$this->UpdatePostMeta();
		}
		else 
			$this->error("You cannot update a post that does not exist");
	}
	
	/**
	 * Takes all information in the current AP_PostItem and attempts to register/insert a new post with the respective infomation
	 * Returns new post ID on success, false or throws exception on failure
	 * @return integer $intNewID
	 */
	
	protected function Insert(){
		$mixNewValuesArray = array();
		foreach ($this->objPost as $key => $value)
				$mixNewValuesArray[$key] = $value;
		if($this->boolIsPendingPost){
			$intNewID = wp_insert_post($mixNewValuesArray);
			$this->ID = $intNewID;
			return $intNewID; 
		}
		else
			$this->error("You must set the Post Content and Title; do not user Register() to update use Update()");
	}
	
	/**
	 * Attempts to delete a post with the current AP_PostItem's ID 
	 * Returns true on success, false on failure
	 * @return boolean
	 */
	
	public function Delete(){
		if($this->ID){
			$boolDeleteTest = wp_delete_post($this->ID);
			if($boolDeleteTest){
				$this->objPost = new stdClass;
				$boolDeleteTest = true; 
			}
			return $boolDeleteTest; 	
		}
		else
			return false; 
	}
	
	/**
	 * Wrapper for $this->Insert() and $this->Update(), checks which is appropriates and calls it
	 * 
	 */
	
	
	public function Save(){
		if($this->boolIsPendingPost)
			$this->Insert();
		else
			$this->Update();
	}
	
	/////////////////////////
	// Public Properties: GET
	/////////////////////////
	
	public function __get($strName){
		switch ($strName) {
			case 'ID' :
				return $this->objPost->ID;
			case 'AuthorID' :
				return $this->objPost->post_author;
			case 'Author':
				return new AP_User($this->objPost->post_author);
			case 'Name' :
				return $this->objPost->post_name;
			case 'Type' :
				return $this->objPost->post_type; 
			case 'Title' :
				return $this->objPost->post_title;
			case 'Date' :
				return $this->objPost->post_date;
			case 'DateGMT' :
				return $this->objPost->post_date_gmt;
			case 'Content' :
				return $this->objPost->post_content;
			case 'Permalink' :
				return $this->objPost->guid;
			case 'Excerpt' :
				return $this->objPost->post_excerpt;
			case 'Status' :
				return $this->objPost->post_status;
			case 'CommentStatus' :
				return $this->objPost->comment_status; 
			case 'PingStatus' :
				return $this->objPost->ping_status;
			case 'Password' :
				return $this->objPost->post_password;
			case 'Parent' :
				return $this->objPost->post_parent;
			case 'Modified' :
				return $this->objPost->post_modified;
			case 'ModifiedGMT' :
				return $this->objPost->post_modified_gmt;
			case 'CommentCount' :
				return $this->objPost->comment_count;
			case 'ThumbnailURL' :
				return $this->strThumbnailURL;
			case 'MetaArray':
				$this->GetPostMeta(); 
				return $this->mixMetaArray;
				break;
			case 'Post' :
				return $this->objPost; 
				
		}
	
	}
		
	/////////////////////////
	// Public Properties: SET
	/////////////////////////
	
	public function __set($strName, $mixValue) {
		switch ($strName) {
			case 'ID' :
				if ($this->boolIsPendingPost)
					return ($this->objPost->ID = $mixValue);
				else
					$this->error("cannot set post ID on already existing post"); 
			case 'AuthorID' :
				return ($this->objPost->post_author = $mixValue);
			case 'Name' :
				return ($this->objPost->post_name = $mixValue);
			case 'Type' :
				return ($this->objPost->post_type = $mixValue);
			case 'Title' :
				return ($this->objPost->post_title = $mixValue);
			case 'Date' :
				return ($this->objPost->post_date = $mixValue);
			case 'DateGMT' :
				return ($this->objPost->post_date_gmt = $mixValue);
			case 'Content' :
				return ($this->objPost->post_content = $mixValue);
			case 'Permalink' :
				return ($this->objPost->guid = $mixValue);
			case 'Excerpt' :
				return ($this->objPost->post_excerpt = $mixValue);
			case 'Status' :
				if ($mixValue == 'publish' || $mixValue == 'draft' || $mixValue == 'auto-draft' || $mixValue == 'inherit')
					return ($this->objPost->post_status = $mixValue); 
				else 
					$this->error("Use AP_PostStatusType constants to correctly set the post status");
				break;
			case 'CommentStatus' :
				return ($this->objPost->comment_status = $mixValue);
			case 'PingStatus' :
				return ($this->objPost->ping_status = $mixValue);
			case 'Password' :
				return ($this->objPost->post_password = $mixValue);
			case 'Parent' :
				return ($this->objPost->post_parent = $mixValue);
			case 'Modified' :
				return ($this->objPost->post_modified = $mixValue);
			case 'ModifiedGMT' :
				return ($this->objPost->post_modified_gmt = $mixValue);
			case 'CommentCount' :
				return ($this->objPost->comment_count = $mixValue);
			case 'ThumbnailURL' :
				return ($this->strThumbnailURL = $mixValue);
			}
		}
}

