<?php

/**
 * Base class for wrapping an array of Users
 * 
 * @author AgilePress Core Developement Team
 * 
 * @property mixed UserArray
 * @property integer Count
 *
 */

class AP_UserCollectionBase extends AP_Base {
	
	protected $objUserArray = array();
	protected $intUserCount;
	
	public function __construct(){
		return true;
	}
	
	/**
	 * Takes a numeric User ID and attempts to find and remove it from the current collection
	 * 
	 * @param integer $intUserID
	 */
	
	public function RemoveUser($intUserID){
		if(is_array($this->objUserArray) && array_key_exists($intUserID, $this->objUserArray))
			unset($this->objUserArray[$intUserID]);
		else
			$this->error('$intUserID argument must be a integer user ID currently contained in the collection');
	}
	
	/**
	 * Takes a single AP_User, WP_User or numeric UserID and adds the correct AP_User object to the collection
	 * 
	 * @param mixed $mixUser
	 */
	
	public function AddUser($mixUser){
		if($mixUser instanceof AP_UserBase)
			$this->objUserArray[$mixUser->ID] = $mixUser;
		elseif($mixUser instanceof WP_User)
			$this->objUserArray[$mixUser->ID] = new AP_UserBase($mixUser);
		elseif(is_int($mixUser))
			$this->objUserArray[$mixUser] = new AP_UserBase($mixUser);
		else
			$this->error('$mixUser argument must be one of the following types: WP_User, interger (user id), AP_UserBase, or an extension of AP_UserBase ');
	}
	
	/**
	 * A wrapper for AddUser() and MergeCollections(), if a single WP_User, AP_User or numeric UserID is passed it will add the correct post to the collection, if a AP_UserCollection is passed it will add all the posts to the current collection
	 * 
	 * @param unknown $mixValue
	 */
	
	public function Add($mixValue) {
		if(is_array($mixValue))
			foreach($mixValue as $mixUser)
				$this->AddUser($mixUser);
		elseif($mixValue instanceof AP_UserCollectionBase)
			$this->objUserArray = AP_UserCollectionBase::MergeCollections($this,$mixValue)->UserArray;
		else
			$this->AddUser($mixValue);
		
	}
	
	/**
	 * Takes two AP_UserCollections and returns one with all posts from both
	 * 
	 * @param AP_UserCollection $objUsers
	 * @param AP_UserCollection $objUsersSecond
	 * @return AP_UserCollectionBase
	 */
	
	public static function MergeCollections($objUsers, $objUsersSecond){
		if($objUsers instanceof AP_UserCollectionBase && $objUsersSecond instanceof AP_UserCollectionBase){
			$objMergedCollection = new AP_UserCollectionBase;
			foreach($objUser->UserArray as $objUser)
				$objMergedCollection->AddUser($objUser);
			foreach($objUsersSecond->UserArray as $objUser)
				$objMergedCollection->AddUser($objUser);
			return $objMergedCollection;
		}
		else 
			$this->error('To merge collections both collections must be AP_UserCollectionBase type or extensions of AP_UserCollectionBase');
	}
	
	public function __get($mixName) {
		switch ($mixName) {
			case 'UserArray' :
				return $this->objUserArray;
			case 'Count' :
				return count($this->objUserArray);
					
		}
	
	}
	
	public function __set($mixName , $mixValue){
		switch($mixName) {
			case 'UserArray' :
				if(is_array($mixValue)){
					$boolIsAPUserObject = true;
					foreach($mixValue as $objPost)
					if(!$objPost instanceof AP_UserBase)  {
						$boolIsAPUserObject = false;
						break;
					}
	
					return $boolIsAPPostObject ? $this->objUserArray = $mixValue : null;
				}
				else
					$this->error( 'AP_UserCollectionBase->UserArray must be set to an array of AP_UserBase objects');
				break;
		}
	}
	
}