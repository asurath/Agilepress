<?php
/**
 * Class for creating new AgilePress custom usertypes and Agilepress global user fields
 * 
 * @author AgilePress Core Developement Team
 * 
 * @property string Name
 * @property string SingularName
 * @property string Description
 * @property array ControlArray
 *
 */

class AP_CustomUserTypeBase extends AP_Base {
	
	protected $strName;
	protected $strSingName;
	protected $strDescription = null;
	protected $strControlArray = array();
	
	public function __construct(){
		return true;
	}
	
	
	public function __get($strName) {
		switch($strName) {
			case "Name" :
				return $this->strName;
			case "SingularName" :
				return $this->strSingularName;
			case "Description" :
				return $this->strDescription;
			case "ControlArray" :
				return $this->strControlArray;
		}
	}
	
	/** Magic Method Setter for AP_PostTypeGen
	 */
	
	public function __set($strName, $mixValue) {
		switch($strName) {
			case "Name" :
				return $this->strName = $mixValue;
			case "SingularName" :
				return $this->strSingularName = $mixValue;
			case "Description" :
				return $this->strDescription = $mixValue;
			case "ControlArray" :
				return $this->strControlArray = $mixValue;
		}
	}
	
	
	
	/**
	 * Called via Ajax
	 * 
	 * SHOULD NOT BE CALLED IN CUSTOM USER CODE
	 * 
	 * @param string $objValuesArray
	 */
	
	public function SetFromAdmin($objValuesArray = null){
		if($objValuesArray !== null){
			$boolArgTest = true;
			if(isset($objValuesArray['NewTypeData'][0]['type-name']))
				$this->strName = $objValuesArray['NewTypeData'][0]['type-name'];
			else
				$boolArgTest = false;
			if(isset($objValuesArray['NewTypeData'][0]['type-sing-name']))
				$this->strSingName = $objValuesArray['NewTypeData'][0]['type-sing-name'];
			else
				$boolArgTest = false;
			if(isset($objValuesArray['NewTypeData'][0]['type-description']))
				$this->strDescription = htmlentities($objValuesArray['NewTypeData'][0]['type-description']);
			if(isset($objValuesArray['NewFieldData']))
				foreach($objValuesArray['NewFieldData'] as $ANewField){
					if(is_array($ANewField))
						$this->strControlArray[] = $ANewField;}
			if(!$boolArgTest)
				$this->error("invalid argument array passed to SetFromAdmin()");
		}
		else
			$this->error("SetFromAdmin() should only be called from by the Admin page code an requires a specific object structure to be passed");
	}	
	
	/**
	 * Inserts a new AgilePress custom usertype into the WPDB based on the data in the currently instantiated object
	 */
	public static function GetUserTypeFieldConfigArray($strUserTypePluralName){
		global $wpdb;
		$strTableName1 = $wpdb->base_prefix . "ap_user_type";
		$strTableName2 = $wpdb->base_prefix . "ap_user_type_field";
		$strTableName3 = $wpdb->base_prefix . "ap_user_field";
		$sql = "
		SELECT r.slug FROM $strTableName1 s INNER JOIN $strTableName3 d on s.id = d.user_type_id INNER JOIN $strTableName2 r on d.user_field_id = r.id WHERE s.name = '$strUserTypePluralName'
		";
		$i = true;
		$n = 0;
		$mixUserTypeFieldArray = array();
		while($i <> null){
			$i = $wpdb->get_row($sql, ARRAY_A, $n);
			if($i <> null) $mixUserTypeFieldArray[] =  $i['slug'];
				$n++;
		}
		$mixUserTypeFieldConfigArray = array();
		$objXMLHandler = new AP_CoreCodeGenerator;
		foreach($objXMLHandler->objConfigData->usertypes->usertype as $UserType){
			$UserType = unserialize($UserType);
			if($UserType[0]['name'] == $strUserTypePluralName){
				foreach($UserType[1] as $UserTypeField){
					if(!in_array($UserTypeField['slug'], $mixUserTypeFieldArray )){
						$mixUserTypeFieldConfigArray[] = $UserTypeField;
					}
				} 
			}
		}
	
		return $mixUserTypeFieldConfigArray;
	
	}

	public static function GetUserRoleArray(){
		global $wpdb;
		$strTableName1 = $wpdb->base_prefix . "ap_user_roles";
		$sql = "
		SELECT * FROM $strTableName1;
		";
		$i = true;
		$n = 0;
		$mixUserRoleArray = array();
		while($i <> null){
			$i = $wpdb->get_row($sql, ARRAY_A, $n);
			if($i <> null) $mixUserRoleArray[] =  $i;
			$n++;
		}
		return $mixUserRoleArray;
	}

	public static function GetConfigUserRoleArray(){
		$objXMLHandler = new AP_CoreCodeGenerator;
		$mixRoleReturnArray = array();
		foreach($objXMLHandler->objConfigData->userroles->userrole as $UserRole){
			print_r($UserRole);
			$mixRoleReturnArray[] = unserialize($UserRole);

		}
		return $mixRoleReturnArray;
	}
	
	
	public function Insert(){
		$arrUserTypesArray = AP_CustomUserTypeBase::GetUserTypeArray();
		$boolDuplicateTest = false; 
		foreach($arrUserTypesArray as $UserTypeArray)
			if(in_array($this->strName, $UserTypeArray) || in_array($this->strSingName, $UserTypeArray))
				$boolDuplicateTest = true;
		if($boolDuplicateTest){
			echo "Cannot Register User Type with Duplicate Singluar or Plural Names";
			die;}
		$intControlIDArray = array();
		global $wpdb;
		$wpdb->insert($wpdb->base_prefix . "ap_user_type", array(
			'singular_name' => $this->strSingName,
			'description' => $this->strDescription,
			'name' => $this->strName,
		));
		$intNewTypeID = $wpdb->insert_id;
		foreach($this->strControlArray as $IndividualControlArray){
			$wpdb->insert($wpdb->base_prefix . "ap_user_type_field", array(
				'name' => $IndividualControlArray["type-field-key"],
				'description' => $IndividualControlArray["type-field-description"],
				'control_type' => $IndividualControlArray["type-field-control-type"],
				'slug' => "ap-" . $IndividualControlArray["type-field-slug"]
			));
			$intControlIDArray[] = $wpdb->insert_id;
		}
		foreach($intControlIDArray as $intControlID)
			$wpdb->insert($wpdb->base_prefix . "ap_user_field", array(
				'user_type_id' => $intNewTypeID,
				'user_field_id' => $intControlID
			));
		
	}
	
	/**
	 * Takes an AgilePress custom user type plural name ($this->Name) and returns all of its custom data fields
	 * 
	 * @param string $strUserTypePluralName
	 * @return array $mixUserTypeFieldArray
	 */
	
	public static function GetUserTypeFieldArray($strUserTypePluralName){
		global $wpdb;
		$strTableName1 = $wpdb->base_prefix . "ap_user_type";
		$strTableName2 = $wpdb->base_prefix . "ap_user_type_field";
		$strTableName3 = $wpdb->base_prefix . "ap_user_field";
		$sql = "
			SELECT r.name, r.description, r.control_type, r.slug, s.singular_name FROM $strTableName1 s INNER JOIN $strTableName3 d on s.id = d.user_type_id INNER JOIN $strTableName2 r on d.user_field_id = r.id WHERE s.name = '$strUserTypePluralName'
				";
		$i = true;
		$n = 0;
		$mixUserTypeFieldArray = array();
		while($i <> null){
			$i = $wpdb->get_row($sql, ARRAY_A, $n);
			if($i <> null) $mixUserTypeFieldArray[] =  $i;
			$n++;
		}
		return $mixUserTypeFieldArray;
		
	}
	
	public static function GetConfigUserTypeArray(){
		$objConfigClass = new AP_CoreCodeGenerator;
		$arrReturnArray = array();
		foreach($objConfigClass->objConfigData->usertypes->usertype as $value){
			$arrReturnArray[] = unserialize($value);
		}
		return $arrReturnArray;
	}
	
	public static function GetConfigFieldArray(){
		$objConfigClass = new AP_CoreCodeGenerator;
		$arrReturnArray = array();
		foreach($objConfigClass->objConfigData->userfields->userfield as $value){
			$arrReturnArray[] = unserialize($value);
		}
		return $arrReturnArray;
	}
	
	
	/**
	 * Called by AP_Application::AdminInit()
	 *
	 * This function sets up the standard metaboxes that will render on the admin page of a post type.
	 * Function can be overwritten to add additional metaboxes
	 */
	public function MetaBoxRun() {
		$this->AdminLoad();
		$this->AdminLoadCustom();
		$this->MetaBoxInit();
	}
	
	/**
	 * Returns an array of all the AgilePress custom global user data fields
	 * 
	 * @return array $mixGlobalUserTypeFieldArray
	 */
	
	public static function GetGlobalUserFieldArray(){
		global $wpdb;
		$strTableName = $wpdb->base_prefix . "ap_user_type_field";
		$sql = "SELECT * FROM $strTableName WHERE global = 1";
		$i = true;
		$n = 0;
		$mixGlobalUserTypeFieldArray = array();
		while($i <> null){
			$i = $wpdb->get_row($sql, ARRAY_A, $n);
			if($i <> null) $mixGlobalUserTypeFieldArray[] =  $i;
			$n++;
		}
		return $mixGlobalUserTypeFieldArray;
	}
	
	/**
	 * Registers an AgilePress global user field via AJAX from the admin page
	 * 
	 * SHOULD NOT BE CALLED IN CUSTOM USER CODE
	 * 
	 * @param unknown $objFieldProperties
	 */
	
	public static function SetGlobalFromAdmin($objFieldProperties){
		$strFieldArray = array();
		$strFieldArray['field-name'] = $objFieldProperties['global-field-name'];
		$strFieldArray['control-type'] = $objFieldProperties['global-control-type'];
		$strFieldArray['field-description'] = $objFieldProperties['global-field-description'];
		$strFieldArray['field-slug'] = $objFieldProperties['global-field-slug'];
		return AP_CustomUserTypeBase::AddGlobalUserFieldArray($strFieldArray);
	}
	
	/**
	 * Takes an array of data for a globa user field and registers it 
	 * 
	 * @param array $mixFieldArray
	 */
	public static function AddGlobalUserFieldArray($mixFieldArray){
		global $wpdb;
		$strTableName = $wpdb->base_prefix . "ap_user_type_field";
		if(array_key_exists('field-name',$mixFieldArray) && array_key_exists('field-slug',$mixFieldArray) && array_key_exists('control-type',$mixFieldArray)){
			$wpdb->insert($strTableName, array(
				'name' => $mixFieldArray['field-name'],
				'description' => $mixFieldArray['field-description'],
				'control_type' => $mixFieldArray['control-type'],
				'slug' => "ap-" . $mixFieldArray['field-slug'],
				'global' => 1
			));
			return $insert_id;
		}
		else
			$this->error("Invalid argument array format");
	}
	
	/**
	 * Returns an array of all currently registered AgilePress custom user types
	 * 
	 * @return array $mixUserTypeArray
	 */
	
	public static function GetUserTypeArray(){
		global $wpdb;
		$mixUserTypeArray = array();
		$i = true;
		$n = 0;
		$strTableName =  $wpdb->base_prefix . "ap_user_type";
		while($i <> null){
			$i = $wpdb->get_row("SELECT * FROM $strTableName", ARRAY_A, $n);
			if($i <> null) $mixUserTypeArray[] =  $i; 
			$n++;
		}
		 return $mixUserTypeArray;
	}
	
	protected function AdminLoad(){}
	protected function AdminLoadCustom(){}
	protected function RenderMetaBoxes(){}
	
	
	public function SaveUser($user_id, $user){
		
		$this->AdminLoad();
		$this->AdminLoadCustom();
		// prevent auto save for custom fields
		if(defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return $user_id;
		$i = 0;
		foreach (get_object_vars($this) as $param=>$object) {
	
			if ($this->{$param} instanceof AP_Control){
			//Save logic for AP_CheckBoxList
			if($this->{$param} instanceof AP_CheckBoxList){
				foreach($this->{$param}->ItemArray as $LowerParam => $lowerObject)
					delete_user_meta($post_id, $this->{$param}->ID . "[$LowerParam]");
				foreach($_POST[$this->{$param}->ID] as $LowerParam => $lowerObject){
					update_user_meta($post_id, $this->{$param}->ID . "[$LowerParam]", $lowerObject);
				}
			}
			//Save logic for all other controls
			update_user_meta($user_id, $this->{$param}->ID, $_POST[$this->{$param}->ID]);
		}}
		// call the custom save function for each post type
		//$this->SaveCustomPost();
		
		
	}
	
	protected function MetaBoxInit() {
		add_action('show_user_profile',array($this,'MetaBoxCallBack'));
		add_action('edit_user_profile',array($this,'MetaBoxCallBack'));
	}

	public function MetaBoxCallBack(){
		
		global $profileuser;
		
		if($this->strSingularName <> 'user-global')
			if(get_user_meta($profileuser->ID, 'ap-user-type', true) <> $this->strSlug)
				return;
		
		AP_Application::NonceField();

		foreach (get_object_vars($this) as $param=>$object) {
			if ($this->{$param} instanceof AP_Control)
				if($this->{$param} instanceof AP_CheckBoxList){
					$this->{$param}->Value = get_user_meta($profileuser->ID, $this->{$param}->ID);}
				else
					$this->{$param}->Value = get_user_meta($profileuser->ID, $this->{$param}->ID, true);
		}
		echo "<h3> $this->strName User Fields </h3>";
		$this->RenderMetaBoxes();
	}
	
}

