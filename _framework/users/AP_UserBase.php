<?php

/**
 * Wrapper for WP_User objects
 * 
 * @author AgilePress Core Developement Team
 *
 *@property boolean CustomUserType
 *@property WP_User User
 *@property mixed MetaArray
 *@property boolean IsPendingRegistration
 *@property boolean MetaModifiedArray
 */

class AP_UserBase Extends AP_Base {

	
	
	// MEMBER VARIABLES
	protected $boolMetaModifiedArray = array(); 
	protected $boolIsPendingRegistration = false; // $intWhatever, $strSomething, $mixValue, $fltPrice, $arrNames
	protected $boolCustomUserType = false; 
	protected $objUser = null; // $blnUser $objUser User
	protected $mixMetaArray = array(); // $

	// TODO: Create a AP_UserTracker
	
	
	/**
	 * Instantiates either an empty AP_User class or a Full AP_User class if an ID or Object is passed
	 * 
	 * @param numeric $value - optional: User ID or WP_User object 
	 */
	
	public function __construct($mixValue = null){
		error_reporting(E_ERROR | E_PARSE);
		$this->Init($mixValue);
	}
	
	/**
	 * Loads a WP_User into the user property
	 *
	 */
	
	protected function Init($mixValue = null){
		
		if (is_numeric($mixValue))
			$this->objUser = get_user_by('id',$mixValue);
		elseif ($mixValue instanceof WP_User)
			$this->objUser = $mixValue;
		elseif ($mixValue == null) {
			$this->GeneratePseudoWpObject();
		} 
		else {
			print_r($mixValue);
			$this->error('Value Argument to instantiate AP_User type class is Invalid');
		}
		if(!$this->boolIs_being_registered){
			$this->GetUserMeta();
		}
		
		
	}
	
	/**
	 * if AP_User constructor is not passed any arguments, PsuedoWpObjectGenerator() instantiates a 
	 * php stdClass of the same format as a WP_User class so as to make the magic methods functional
	 */
	
	protected function GeneratePseudoWpObject() {
		$this->objUser = (object) array(
				'data' => (object) array( 
						'ID' => '', 
						'user_login' => '', 
						'user_pass' => '', 
						'user_nicename' => '',
						'user_email' => '',
						'user_url' => '',
						'user_registered' => '',
						'user_activation_key' => '',
						'user_status' => '',
						'display_name' => '',
				),
				'ID' => '',
				'caps' => array(),
				'cap_key' => '',
				'roles' => array(),
				'allcaps' => array()
		);
		
		$this->boolIsPendingRegistration = true; 
	}
	
	/**
	 * Sets the object to the current user viewing the page
	 * 
	 * @return AP_UserBase
	 */
	
	public function GetCurrentUser() {
		$this->boolIsPendingRegistration = false;
		$this->objUser = wp_get_current_user();
		$this->Init($this->objUser); 
		return $this; 
		// Return a AP_User object
	}
	
	/**
	 * If an ID or Object is given to the constructor or if CurrentUser() is called, 
	 * GetUserMeta() fills the classes @MetaArray property with the appropriate meta data
	 */
	
	
	
	protected function GetUserMeta(){
		$this->mixMetaArray = get_user_meta($this->objUser->ID);
		foreach ($this->mixMetaArray as $key => $value)
			$this->mixOldMetaArray[$key] = $value; 
		if(is_array($this->mixMetaArray) && array_key_exists('custom_user_type',$this->mixMetaArray))
			$this->boolCustomUserType = true;
	}

	
	/////////////////////////
	// Public Properties: GET
	/////////////////////////
	
	/**
	 * Standard Magic Method getters
	 * @param string $name
	 * @return mixed
	 */
	public function __get($strName) {
		switch ($strName) {
			case 'ID':
				return $this->objUser ? $this->objUser->ID : 0;
			case 'Caps':
				return $this->objUser->caps;
			case 'Roles':
				return $this->objUser->roles;
			case 'Allcaps':
				return $this->objUser->allcaps;
			case 'FirstName': // TODO: Casing, Meta
				return $this->mixMetaArray['first_name'][0];
			case 'LastName': // TODO: Casing, Meta
				return $this->mixMetaArray['last_name'][0];
			case 'Meta':
				return $this->mixMetaArray; 
			case 'Bio':
				return $this->mixMetaArray['description'][0];
			case 'Email' :
				return $this->objUser->data->user_email;
			case 'Login':
				return $this->objUser->data->user_login;
			case 'Name':
				return $this->objUser->data->display_name;
			case 'NiceName':
				return $this->mixMetaArray['nickname'][0];
			case 'Slug':
				return $this->objUser->data->user_nicename;
			case 'URL' :
				return $this->objUser->data->user_url;
			case 'ActivationKey' : // TODO: Casing
				return $this->objUser->data->user_activation_key;
			case 'Status' :
				return $this->objUser->data->user_status;
			case 'Registration' :
				return $this->objUser->data->user_registered;
		}
	}
	
	/////////////////////////
	// Public Properties: SET
	/////////////////////////
	
	/**
	 * Standard Magic method setters
	 * @param mixed $strName
	 * @param mixed $value
	 * @return void|unknown|multitype:unknown |string
	 */
	
	public function __set($strName, $mixValue) {
		switch ($strName) {
			case 'Caps':
				return ($this->objUser->caps = $mixValue);
			case 'Roles':
				return ($this->objUser->roles = $mixValue);
			case 'Allcaps':
				return ($this->objUser->allcaps = $mixValue);
			case 'FirstName': // TODO: Casing
				return ($this->mixMetaArray['first_name'] = array( 0 => $mixValue));
			case 'LastName': // TODO: Casing
				return ($this->mixMetaArray['last_name'] = array( 0 => $mixValue));
			case 'Meta':
				return ($this->mixMetaArray = $mixValue);
			case 'Bio':
				return ($this->mixMetaArray['description'] = array( 0 => $mixValue));
			case 'Email' :
				return ($this->objUser->data->user_email = $mixValue);
			case 'Name':
				return ($this->objUser->data->display_name = $mixValue); // Philamn Lau
			case 'Login':
				if($this->boolIsPendingRegistration)
					return ($this->objUser->data->user_login = $mixValue);
				else
					$this->error('Cannot modify user_login values of registered users');
				break;
			case 'URL' :
				return ($this->objUser->data->user_url = $mixValue);
			case 'ActivationKey' :
				return ($this->objUser->data->user_activation_key = $mixValue);
			case 'Status' :
				return ($this->objUser->data->user_status = $mixValue);
			case 'Registration' :
				return ($this->objUser->data->user_registered = $mixValue);
			case 'Password' :
				return (wp_set_password($mixValue, $this->ID) && $this->objUser->data->user_pass = $mixValue);
		}
	}
	
	/**
	 * Updates all meta-values that have been changed in the AP_User object
	 */
	
	public function UpdateMeta(){
		foreach($this->mixMetaArray as $meta_key => $meta_value)
			$this->boolMetaModifiedArray[$meta_key] = ($meta_value != $this->mixOldMetaArray[$meta_key]) ? true : false; 
		foreach($this->boolMetaModifiedArray as $meta_key => $is_meta_modified){
			if($is_meta_modified) {
				 $var = update_user_meta($this->Id, $meta_key, $this->mixMetaArray[$meta_key][0]);
			}
			else
				continue ; 
		}
	}
	
	/**
	 * Sets and individual meta key-value pair
	 * @param string $key
	 * @param string $value
	 */
	
	public function SetMeta($strKey,$mixValue){
		$this->mixMetaArray[$strKey] = $mixValue;
		$this->UpdateMeta();
	}
	
	
	/**
	 * Gets and individual meta value from the meta key
	 * @param string $strKey
	 */
	
	public function GetMeta($strKey){
		if(array_key_exists($strKey,$this->mixMetaArray))
			return $this->mixMetaArray[$strKey];
		else 
			$this->error("Unrecognized Meta Key");
	}
	
	/** 
	 * Registers a new WP_User with WP, the AP_User object must have a Login and Password set or the function will return an error,
	 * If WP is unable to register the new user it will return a WP_Error object
	 * 
	 * *WILL NOT UPDATE: ONLY REGISTERS*
	 * 
	 * @return string PostID
	 */
	
	
	public function Register(){
		if($this->Login <> ''){
			$intNewID = wp_create_user($this->Login, $this->Password, $this->Email);
			if(is_wp_error($test))
				return $intNewID;
			$temp_pass = $this->Password;
			unset($this->objUser->data->user_pass);
			wp_update_user($this->objUser);
			$this->objUser->data->user_pass = $temp_pass;
			$this->objUser = get_user_by('login',$this->Login);
			$this->UpdateMeta(); 
			$this->ID = $intNewID; 
			return $intNewID;
		}
		else 
			$this->error('both Name and Login field values must be provided to register a User');
	}
	
	/** 
	 * Updates all WP_User and Meta-values that have been changed since instantiation. 
	 * To use, $this->user must be set to a WP object, the function will return if you attempt to register a new user
	 * 
	 * @return string $error
	 */
	
	
	public function Update(){
		if($this->objUser instanceof WP_User ){
			$temp_pass = $this->Password;
			unset($this->objUser->data->user_pass);
			wp_update_user($this->objUser);
			$this->objUser->data->user_pass = $temp_pass; 
			$this->UpdateMeta(); 
		}
		else 
			$this->error('You cannot register new users with $this->Update(), please instantiate the class to a user which exists before calling Update()');
	}
	
	/**
	 * Attempts to delete the WP_User that AP_User is currently instantiated
	 * 
	 */
	
	public function Delete(){
		if($this->objUser instanceof WP_User){
			return wp_delete_user($this->objUser);
		}
		Else
			$this->error('You cannot delete users who don\'t exist, please instantiate the class to a user which exists before calling Delete()');
	}
	
}