<?php

/**
 * AP_CoreCodeGenerator handles all code generation, table synchronization, as well as the configuration file/data.
 * Extends from AP_Base
 * 
 * @since 1.0
 * @package AgilePress
 * @subpackage Core Code Generator
 * @author AgilePress Core Developement Team
 *
 */
class AP_CoreCodeGenerator extends AP_Base {
	
	//Public properties
	public $objConfigData = null;
	public $strSiteSlug = null;
	
	//Protected properties
	protected $objCodeGenStatus = null;
	protected $strDate = null; 
	protected $objUser = null;
	
	/**
	 * AP_CoreCodeGenerator constructor, creates a log file if none exists and attempts to get the configuration data from the configuration file
	 * 
	 * @since 1.0
	 * @return boolean $boolCreated
	 */
		
	public function __construct(){
		$this->CreateLogObject();
		$this->objConfigData = $this->GetConfigurationData();
		$this->CreateSiteSlug();
		return true;
	}
	
	/**
	 * AP_CoreCodeGenerator codegen update highest level wrapper
	 * 
	 * handles codegen error reporting, log file creation as well as wrapping creation logic
	 *  
	 *  @since 1.0
	 *  @return void
	 */
	public function CodeGenRun(){
		
		// Turn off error/warning reporting
		error_reporting(0);
		
		//Run action line for AgilePress 'pre-codegen' hook
		AP_Modules::RunAction('pre-codegen');
		
		//Get current user information, if current user is not an administration then return
		$this->objUser = wp_get_current_user();
		if(!$this->objUser->caps['administrator'] == 1)
			return;
		
		//Get current date and create code generation log file if it does not exist
		$this->strDate = AP_DateTime::Now()->ToString();
		$this->CreateLogFile();
		
		//If configuration does not exist create it and load it with the current data, or if it does exist get the configuration data
		$this->objCodeGenStatus->ConfigurationXML = $this->CreateConfigurationXML();
		if($this->objCodeGenStatus->ConfigurationXML == 1){
			$this->objConfigData = $this->GetConfigurationData();
			$this->SetCurrentConfiguration();
			$this->SaveConfigurationData();
		}
		
		//Create site slug
		$this->objCodeGenStatus->SiteSlug = $this->CreateSiteSlug();
		
		//If site slug exists, create extension folder structure
		if($this->objCodeGenStatus->SiteSlug){
			$this->objCodeGenStatus->Folders = $this->CreateFolderStructure();
		$mixTempArray = $this->CreateExtensionClasses();			
		}
		
		//Take success/failure array returned by create extension classes and fill $this->objCodeGenStatus with the appropriate values
		$this->objCodeGenStatus->UserTypes = $mixTempArray['UserTypes'];
		$this->objCodeGenStatus->PostTypes = $mixTempArray['PostTypes'];
		$this->objCodeGenStatus->PostItems = $mixTempArray['PostItems'];
		$this->objCodeGenStatus->ExtensionFiles = $mixTempArray['ExtensionFiles'];
		$this->objCodeGenStatus->Controls = $mixTempArray['Controls'];
		
		//Write appropriate data from $this->objCodeGenStatus into the code generation log file
		$this->AppendLogFile();
		
		//Update the code generation version number in the WordPress options table
		update_option("AP_CodeGen_Version", intval($this->objConfigData->title->version));
		
		//Update the AgilePress tables to reflect the new code generation
		$this->UpdateTablesFromConfig();
		
		//If Email notifications are enabled, build and send an email to each person on the list
		$objEmailer = new AP_Email(true);
		$objEmailer->Subject = "Code Generation Completed for $this->strSiteSlug";
		$objEmailer->Body = "/////AGILEPRESS ALERT THIS SETTING CAN BE CHANGED IN THE SETTINGS PAGE OF THE AGILEPRESS MENU/////";
		$objEmailer->SendEmail();
		
		//Run action line for AgilePress 'post-codegen' hook
		AP_Modules::RunAction('post-codegen');
		
		//Turn error/warning reporting back on now that code generation has completed
		error_reporting(E_ALL);
	}
	
	/**
	 * Uses the configuration file to rebuild the custom post-type and user-type tables in case of loss,
	 * is also called in CodeGenRun() to create the proper data in the respective tables after code generation
	 * 
	 * @since 1.0
	 * @return void
	 */
	
	public function UpdateTablesFromConfig(){
		
		// Initiate all required variables
		global $wpdb;
		$mixUserTypesArray = array();
		$mixPostTypesArray = array();
		$mixPostFieldsArray = array();
		$mixUserFieldsArray = array();
		$mixUserTypesArray = array();
		$mixUserRolesArray = array();
		$mixTaxonomyArray = array();
		
		//get site slug
		$this->strSiteSlug = get_option("AP_Extension");
		
		//get all user types
		foreach($this->objConfigData->usertypes->usertype as $UserType)
			$mixUserTypesArray[] = unserialize($UserType);

		//get all user roles
		foreach($this->objConfigData->userroles->userrole as $UserRole)
			$mixUserRolesArray = AP_CustomUserTypeBase::GetConfigUserRoleArray();

		//get all global user fields
		foreach($this->objConfigData->userfields->userfield as $UserType)
			$mixUserFieldsArray[] = unserialize($UserType);
		
		//get all post types
		foreach($this->objConfigData->posttypes->posttype as $PostType)
			$mixPostTypesArray[] = unserialize($PostType);
		
		//get all global post fields
		foreach($this->objConfigData->postfields->postfield as $PostType)
			$mixPostFieldsArray[] = unserialize($PostType);
		
		//get all taxonomies
		foreach($this->objConfigData->taxonomies->taxonomy as $Taxonomy)
			$mixTaxonomyArray[] = unserialize($Taxonomy);

		
		
		//get code generated global user fields
		foreach($mixUserTypesArray as $strUserTypeArray){
			$strCurrentUserFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($strUserTypeArray['name']);
		}
		
		//////////////
		//Taxonomies//
		//////////////
		$boolChangedArray = array();
		$boolIsNewArray = array();
		//loop through each taxonomy
		foreach($mixTaxonomyArray as $keytop => $mixTaxonomy){
			$boolIsNewCheck = true;
			//get all already registered taxonomies
			$mixCurrentTaxonomies = AP_CustomTaxonomyTypeBase::GetTaxonomyTypeArray();
			
			//compare new taxonomy to old taxonomies
			foreach($mixCurrentTaxonomies as $keybottom => $mixCurrentTaxonomy){
				//if the taxonomy already exists then add it to $boolChangedArray
				if($mixCurrentTaxonomy['ap_id'] == $mixTaxonomy['ap_id']){
					$strDifferenceArray = array_diff($mixCurrentTaxonomy, $mixTaxonomy);
					$boolIsNewCheck = false;
					if(!empty($strDifferenceArray))
						$boolChangedArray[$mixTaxonomy['slug']] = $keybottom;
				}
			}
			
			//if the taxonomy is new add it to $boolIsNewArray
			if($boolIsNewCheck){
				$boolIsNewArray[$mixTaxonomy['slug']] = $keytop;
			}
		}
		
		//update all changed taxonomies
		foreach($boolChangedArray as $value){
			$intOldRow = $wpdb->update($wpdb->prefix . "ap_taxonomies", $mixTaxonomyArray[$value], array("ap_id" => $mixTaxonomyArray[$value]['ap_id']));
		}
		
		//add all new taxonomies
		foreach($boolIsNewArray as $value){
			$intNewRow = $wpdb->insert($wpdb->prefix . "ap_taxonomies", $mixTaxonomyArray[$value]);
		}
		
		//////////////////////
		//Post Global Fields//
		//////////////////////
		$boolChangedArray = array();
		$boolIsNewArray = array();
		//loop through each global post field
		foreach($mixPostFieldsArray as $keytop => $strPostFieldArray){
			$boolIsNewCheck = true; 
			//get registered global post fields
			$strCurrentGlobalFieldsArray = AP_CustomPostTypeBase::GetGlobalPostFieldArray();
			
			//compare new and old global post fields
			foreach($strCurrentGlobalFieldsArray as $keybottom => $strCurrentGlobalField){
				// if the global post field already exists then add it to $boolChangedArray
				if($strCurrentGlobalField['slug'] == $strPostFieldArray['slug']){
					$strDifferenceArray = array_diff($strCurrentGlobalField, $strPostFieldArray);
					$boolIsNewCheck = false;
					if(!empty($strDifferenceArray))
						$boolChangedArray[$strPostFieldArray['slug']] = $keybottom;
				}
			}
			//if the global post field is new add it to $boolIsNewArray
			if($boolIsNewCheck)
				$boolIsNewArray[$strPostFieldArray['slug']] = $keytop;
		}
		//updated all changed global post fields
		foreach($boolChangedArray as $value){
			$mixPostFieldsArray[$value]["global"] = 1;
			$intNewRow = $wpdb->update($wpdb->prefix . "ap_post_type_field", $mixPostFieldsArray[$value], array("ap_id" => $mixPostFieldsArray[$value]['ap_id']));
		}
		//add all new global post fields
		foreach($boolIsNewArray as $value){
			$mixPostFieldsArray[$value]["global"] = 1;
			$wpdb->insert($wpdb->prefix . "ap_post_type_field", $mixPostFieldsArray[$value]);
		}
		
		//////////////
		//Post Types//
		//////////////
		//Create boolean for if there is a new type
		$boolThereIsNewType = true;
		//while there is still a new post type added then repeat the loop
		while($boolThereIsNewType){
		$boolThereIsNewType = false;
		$boolIsNewArray = array();
		//update all changed post types
		foreach($mixPostTypesArray as $value){
			$intFieldRowArray = array();
			$intNewRow = null;
			$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "post_type";
			$query = "INSERT INTO $strTableName (singular_name, description, name, slug, ap_id) VALUES ('" . $value[0]['arguments']['labels']['singular_name'] . "', '" . $value[0]['arguments']['labels']['description'] . "', '" . $value[0]['arguments']['labels']['name'] . "', '" . $value[0]['slug'] . "', '" . $value[0]['slug'] . "') ON DUPLICATE KEY UPDATE singular_name = '" . $value[0]['arguments']['labels']['singular_name'] . "', description = '" . $value[0]['arguments']['labels']['description'] . "', name = '" . $value[0]['arguments']['labels']['name'] . "', slug= '" . $value[0]['slug'] . "', ap_id = '" . $value[0]['slug'] . "', id=LAST_INSERT_ID(id);";
			$wpdb->query($query);
			$intNewRow =  $wpdb->insert_id;
			//update all existing post type fields
			foreach($value[1] as $objNewField){
				if(!isset($objNewField['control_type'])){
					$objNewField['control_type'] = $objNewField['ctrl'];
					unset($objNewField['ctrl']);
				}
				$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "post_type_field";
				$query = "INSERT INTO $strTableName (name, description, control_type, slug, global) VALUES ('" . $objNewField['name'] . "', '" . $objNewField['description'] . "', '" . $objNewField['control_type'] . "', '" . $objNewField['slug'] . "', " . 0 . ") ON DUPLICATE KEY UPDATE name = '" . $objNewField['name'] . "', description = '" . $objNewField['description'] . "', control_type = '" . $objNewField['control_type'] . "', slug= '" .$objNewField['slug'] . "', global = " . 0 . ", id=LAST_INSERT_ID(id);";
				$wpdb->query($query);
				$intFieldRowArray[] = $wpdb->insert_id;
			}
			//add all new post-fields
			foreach($intFieldRowArray as $intNewFieldRow){
				$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "post_field";
				$query = "INSERT INTO $strTableName (post_type_id, post_field_id) VALUES ('" . $intNewRow . "', '" . $intNewFieldRow . "') ON DUPLICATE KEY UPDATE post_type_id = '" . $intNewRow . "', post_field_id = '" . $intNewFieldRow . "';";
				$wpdb->query($query);
			}
		}
		
		}
		
		//////////////////////
		//User Global Fields//
		//////////////////////
		$boolChangedArray = array();
		$boolIsNewArray = array();
		//Loop through global user fields from configuration
		foreach($mixUserFieldsArray as $keytop => $strUserFieldArray){
			$boolIsNewCheck = true;
			$strCurrentGlobalFieldsArray = AP_CustomUserTypeBase::GetGlobalUserFieldArray();
			// loop through global user fields in tables and sort new from updated
			foreach($strCurrentGlobalFieldsArray as $keybottom => $strCurrentGlobalField){
				if($strCurrentGlobalField['slug'] == $strUserFieldArray['slug']){
					$strDifferenceArray = array_diff($strCurrentGlobalField, $strUserFieldArray);
					$boolIsNewCheck = false;
					if(!empty($strDifferenceArray))
						$boolChangedArray[$strUserFieldArray['slug']] = $keybottom;
				}
			}
			if($boolIsNewCheck)
				$boolIsNewArray[$strUserFieldArray['slug']] = $keytop;
		}
		//update all changed global user fields
		foreach($boolChangedArray as $value){
			$intFieldRowArray = array();
			$mixUserFieldsArray[$value]["global"] = 1;
			$intNewRow = $wpdb->update($wpdb->prefix . "ap_user_type_field", $mixUserFieldsArray[$value], array("slug" => $mixUserFieldsArray[$value]['slug']));
		}
		//add all new global user fields
		foreach($boolIsNewArray as $value){
			$intFieldRowArray = array();
			$mixUserFieldsArray[$value]["global"] = 1;
			$wpdb->insert($wpdb->prefix . "ap_user_type_field", $mixUserFieldsArray[$value]);
		}


		//////////////
		//User Roles//
		//////////////

		$strUserRoleTablesArray = AP_CustomUserTypeBase::GetUserRoleArray();
		foreach($mixUserRoleArray as $keytop => $UserRole){
			$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "user_roles";
			$query = "INSERT INTO $strTableName (name, slug, capabilities, description) VALUES ('" . $UserRole['name'] . "', '" . $UserRole['slug'] . "', '" . serialize($UserRole['slug']) . "', '" . $UserRole['description'] . "') ON DUPLICATE KEY UPDATE name = '" . $UserRole['name'] . "', categories = '" . serialize($UserRole['slug']) . "', description = '" . $UserRole['description'] . "';";
			$wpdb->query($query);
		}

		
		//////////////
		//User Types//
		//////////////
		$boolThereIsNewType = true;
		while($boolThereIsNewType){
		$boolThereIsNewType = false;
		$boolChangedArray = array();
		$boolIsNewArray = array();
		//loop through User Types from configuration file
		foreach($mixUserTypesArray as $keytop => $strUserTypeArray){
			$boolIsNewCheck = true;
			$strCurrentUserFields =  AP_CustomUserTypeBase::GetUserTypeFieldArray($strUserTypeArray[0]['name']);
			//sort new user types from updated user types
			foreach(AP_CustomUserTypeBase::GetUserTypeArray() as $keybottom => $UserType){
				if($UserType['slug'] == $strUserTypeArray[0]['slug']){
					$strDifferenceArray = array_diff(array($strUserTypeArray, $strCurrentUserFields),$UserType);
					$boolIsNewCheck = false;
					if(!empty($strDifferenceArray))
						$boolChangedArray[$UserType['slug']] = $keybottom;
				}
			}
			if($boolIsNewCheck){
				$boolIsNewArray[$strUserTypeArray[0]['slug']] = $keytop;
			}
		}
		//update all changed user types
		foreach($boolChangedArray as $value){
			$intFieldRowArray = array();
			$intNewRow = null;
			$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "user_type";
			$query = "INSERT INTO $strTableName (singular_name, description, name, slug, ap_id) VALUES ('" . $mixUserTypesArray[$value][0]['singular_name'] . "', '" . $mixUserTypesArray[$value][0]['description'] . "', '" . $mixUserTypesArray[$value][0]['name'] . "', '" . $mixUserTypesArray[$value][0]['slug'] . "', '" . $mixUserTypesArray[$value][0]['ap_id'] . "') ON DUPLICATE KEY UPDATE singular_name = '" . $mixUserTypesArray[$value][0]['singular_name'] . "', description = '" . $mixUserTypesArray[$value][0]['description'] . "', name = '" . $mixUserTypesArray[$value][0]['name'] . "', slug= '" . $mixUserTypesArray[$value][0]['slug'] . "', ap_id = '" . $mixUserTypesArray[$value][0]['ap_id'] . "', id=LAST_INSERT_ID(id);";
			$wpdb->query($query);
			$intNewRow =  $wpdb->insert_id;
			//update all existing user type fields
			foreach($mixUserTypesArray[$value][1] as $objNewField){
				if(!isset($objNewField['control_type'])){
					$objNewField['control_type'] = $objNewField['ctrl'];
					unset($objNewField['ctrl']);
				}
				$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "user_type_field";
				$query = "INSERT INTO $strTableName (name, description, control_type, slug, global) VALUES ('" . $objNewField['name'] . "', '" . $objNewField['description'] . "', '" . $objNewField['control_type'] . "', '" . $objNewField['slug'] . "', " . 0 . ") ON DUPLICATE KEY UPDATE name = '" . $objNewField['name'] . "', description = '" . $objNewField['description'] . "', control_type = '" . $objNewField['control_type'] . "', slug= '" .$objNewField['slug'] . "', global = " . 0 . ", id=LAST_INSERT_ID(id);";
				$wpdb->query($query);
				$intFieldRowArray[] = $wpdb->insert_id;
			}
			//add all new user type fields
			foreach($intFieldRowArray as $intNewFieldRow){
				$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "user_field";
				$query = "INSERT INTO $strTableName (user_type_id, user_field_id) VALUES ('" . $intNewRow . "', '" . $intNewFieldRow . "') ON DUPLICATE KEY UPDATE user_type_id = '" . $intNewRow . "', user_field_id = '" . $intNewFieldRow . "';";
				$wpdb->query($query);
			}
		}
		//add all new user types
		foreach($boolIsNewArray as $value){
			$intFieldRowArray = array();
			$intNewRow = $wpdb->insert($wpdb->prefix . "ap_user_type", $mixUserTypesArray[$value][0]);
			$boolThereIsNewType = true;
		}
		}

		//////////////
		//User Roles//
		//////////////

		foreach ($mixUserRolesArray as $key => $mixUserRoleArray) {
			$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "user_roles";
			$query = "INSERT INTO $strTableName (name, description, capabilities, slug) VALUES ('" . $mixUserRoleArray['name'] . "', '" . $mixUserRoleArray['description'] . "', '" . serialize($mixUserRoleArray['capabilities']) . "', '" . $mixUserRoleArray['slug'] . "') ON DUPLICATE KEY UPDATE name = '" . $mixUserRoleArray['name'] . "', description = '" . $mixUserRoleArray['description'] . "', capabilities = '" . serialize($mixUserRoleArray['capabilities']) . "', slug= '" .$mixUserRoleArray['slug'] . "';";
			$wpdb->query($query);
		}
	}
	
	/**
	 * Method for creating log data class
	 * 
	 * @since 1.0
	 * @return void
	 */
	protected function CreateLogObject(){
		$this->objCodeGenStatus = (OBJECT) array(
			"ConfigurationXML" => null,
			"SiteSlug" => null,
			"Folders" => array(),
			"ExtensionFiles" => array(),
			"PostTypes" => null,
			"PostItems" => null,
			"UserTypes" => null,
			"UserRoles" => null,
			"Controls" => null
		);
	}
	
	/**
	 * Method for appending appropriate data to the CodeGeneration log file
	 * 
	 * @since 1.0
	 * @return integer $intErrorCode
	 */
	public function AppendLogFile(){
		ob_start();
		$strFile = include( AP_TEMPLATE_PATH . "files/CodeGenLog.tpl.php");
		$strContents = ob_get_contents();
		ob_end_clean();
		return AP_IO::AppendFile(AP_PATH . "/agilepress-codegen-log.txt", $strContents);
	}
	
	/**
	 * Attempts to get AgilePress configuration data from the configuration file specified by CONFIG_NAME in agilepress.php
	 * 
	 * @since 1.0
	 * @return SimpleXMLElement
	 */
	public function GetConfigurationData(){
		return AP_IO::ReadXMLFile(AP_PATH . "/" . CONFIG_NAME);
	}
	
	/**
	 * Method for creating a new AgilePress code generation log file, returns 1 for success
	 * 
	 * @since 1.0
	 * @return integer $intErrorCode
	 */
	protected function CreateLogFile(){
		$strFileContent = "--------------------------------------------------------------------------------\n  AgilePress Code Generation Log File \n--------------------------------------------------------------------------------\n";
		return AP_IO::WriteFile(AP_PATH . "/agilepress-codegen-log.txt", $strFileContent, true);
	}
	
	/**
	 * Method for creating a new AgilePress XML configuration file, returns 1 for success
	 * 
	 * @since 1.0
	 * @return integer $intErrorCode
	 */
	public function CreateConfigurationXML(){
		$strFileContent = "<?xml version='1.0'?><root><title><version>1</version>AgilePress Configuration</title><settings><sitename/><siteslug/></settings><taxonomies></taxonomies><userroles></userroles><usertypes></usertypes><userfields></userfields><posttypes></posttypes><postfields></postfields></root>";
		return AP_IO::WriteFile(AP_PATH . "/" . CONFIG_NAME, $strFileContent, true);
	
	}
	
	/**
	 * Method for creating the extension plugin folder structure, pulls folder names from AP_CodeGenConstants:$FolderNames
	 * Returns an array or folder names and their respective success/error codes
	 * 
	 * @since 1.0
	 * @param string $strFolderNameArray OPTIONAL DEFAULT NULL
	 * @return mixed $mixTestArray
	 */
	public function CreateFolderStructure ($strFolderNameArray = null){
		$boolTestArray = array();
		if($this->strSiteSlug <> null){
				if($strFolderNameArray == null)
					$strFolderNameArray = AP_CodeGenConstants::$FolderNames;
				if(!is_dir (AP_PLUGIN_PATH . $this->strSiteSlug) && !is_file(AP_PLUGIN_PATH . $this->strSiteSlug)) $boolTestArray[AP_PLUGIN_PATH . $this->strSiteSlug] = mkdir(AP_PLUGIN_PATH .  $this->strSiteSlug); else $boolTestArray[AP_PLUGIN_PATH . $this->strSiteSlug] = 2; 
				$this->strExtensionPath = AP_PLUGIN_PATH . $this->strSiteSlug;
				foreach($strFolderNameArray as $strFolderName => $value){
						if(!is_dir ($this->strExtensionPath . "/" . $value)) $boolTestArray[$this->strExtensionPath . "/" . $value] = mkdir($this->strExtensionPath . "/" . $value); else $boolTestArray[$this->strExtensionPath . "/" . $value] = 2;
						if(in_array($value , AP_CodeGenConstants::$HasGeneratedArray)){ $value = $value . "/generated"; if(!is_dir($this->strExtensionPath . "/" . $value)) $boolTestArray[$this->strExtensionPath . "/" . $value] = mkdir($this->strExtensionPath . "/" . $value); else $boolTestArray[$this->strExtensionPath . "/" . $value] = 2;}
						if($value == 'pages'){ if(!is_dir($this->strExtensionPath . "/" . $value . "/content")) $boolTestArray[$this->strExtensionPath . "/" . $value  . "/content"] = mkdir($this->strExtensionPath . "/" . $value . "/content"); else $boolTestArray[$this->strExtensionPath . "/" . $value . "/content"] = 2; }
					}
				return $boolTestArray;
			}
		else 
			return 0;
	
	}
	
	/**
	 * Set configuration file to the current configuration
	 * 
	 * @since 1.0
	 * @return void
	 */
	public function SetCurrentConfiguration(){
		$mixUserTypeArray = array();
		$mixPostTypeArray = array();
		$this->strSiteSlug = get_option("AP_Extension");
		
		$mixUserTypes = AP_CustomUserTypeBase::GetUserTypeArray();
		foreach($mixUserTypes as $strUserTypeArray){
			$this->objConfigData->usertypes->addChild("usertype", serialize(array($strUserTypeArray, AP_CustomUserTypeBase::GetUserTypeFieldArray($strUserTypeArray['name']))));
			$mixUserTypeArray[] = AP_CustomUserTypeBase::GetUserTypeFieldArray($strUserTypeArray['name']);
		}
		
		$mixPostTypes = AP_CustomPostTypeBase::GetPostTypeArray();
		foreach($mixPostTypes as $strPostTypeArray){
			$this->objConfigData->posttypes->addChild("posttype", serialize(array($strPostTypeArray, AP_CustomPostTypeBase::GetPostTypeFieldArray($strPostTypeArray['name']))));
			$mixPostTypeArray[] = AP_CustomPostTypeBase::GetPostTypeFieldArray($strPostTypeArray['name']);
		}
	}
	
	/**
	 * Method for getting and, if it does not exist, creating the site slug
	 * 
	 * @since 1.0
	 * @param string $strSiteSlug
	 * @return integer $intErrorCode
	 */
	public function CreateSiteSlug($strSiteSlug = null){
		if(isset($this->objConfigData->settings->siteslug))
			$strSiteSlug =  $this->objConfigData->settings->siteslug;
		elseif(get_option("AP_Extension")){
			$strSiteSlug = get_option("AP_Extension");
			return 2;
		}
		if($strSiteSlug == null){
			$strSiteSlug = get_bloginfo();
		}
		if($strSiteSlug == null)
			return 0;
		$strSiteSlug = str_replace(array(".com",".org",".net",".gov","www.") , "",  $strSiteSlug);		
		$strSiteSlug = strtolower(trim(preg_replace("/[^a-z0-9.]+/i", "", $strSiteSlug)));
		$this->strSiteSlug = "agilepress-" .  $strSiteSlug;	
		update_option("AP_Extension", $strSiteSlug);
		return 1; 
	}
	
	/**
	 * Method for saving the current configuration data in $this->objConfigData into the configuration XML file
	 * 
	 * @since 1.0
	 * @return integer $intErrorCode
	 */
	public function SaveConfigurationData(){
		if($this->objConfigData <> null && $this->objConfigData instanceof SimpleXMLElement){
			$intVersion = intval($this->objConfigData->title->version);
			$intVersion++;
			$this->objConfigData->title->version = $intVersion;
			AP_IO::WriteFile(AP_PATH . "/" . CONFIG_NAME, $this->objConfigData->asXML());
		}
	}
	
	/**
	 * Saves the data in $this->objDataConfig into a backup configuration XML file location in the config-backup directory in the parent AgilePress directory
	 * The method indexes the filename by $intIndex or 1 if left null
	 *
	 * @since 1.0
	 * @param integer $intIndex OPTIONAL DEFAULT NULL
	 */
	public function SaveConfigurationDataAsBackup($intIndex = null){
		if($intIndex == null)
			$intIndex = 1;
		if($this->objConfigData <> null && $this->objConfigData instanceof SimpleXMLElement)
			AP_IO::WriteFile(AP_PATH . "/" . "config-archive/" . CONFIG_NAME . "backupNo($intIndex)", $this->objConfigData->asXML());
	}
	
	/**
	 * Method for get sorting and sending to write method wrappers all data required for Code generation
	 * 
	 * @since 1.0
	 * @return void
	 */
	public function CreateExtensionClasses(){
		$boolSuccessArray = array(
			"PostTypes" => array(),
			"PostItems" => array(),
			"UserTypes" => array(),
			"ExtensionFiles" => array(),
			"Controls" => array()
		);
		
		////////////////////////
		// AP_UserTypes Create//
		////////////////////////
		foreach ($this->objConfigData->usertypes->usertype as $objUserType){
			$TypeArray = unserialize($objUserType);
			$objNewClass = new AP_ClassHolder();
			$objNewClass->Name = AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['singular_name']) . "TypeGen";
			$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/user-types/generated";
			$objNewClass->BaseName = "AP_CustomUserTypeBase"; 
			
			/// Name
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "Name");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $TypeArray[0]['name'];
			$objNewProperty->DocBlock = "Name";
			$objNewClass->AddProperty($objNewProperty);
				
			/// Slug
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "SingularName");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $TypeArray[0]['singular_name'];
			$objNewProperty->DocBlock = "Slug";
			$objNewClass->AddProperty($objNewProperty);
				
			///Label Name
				
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "LabelName");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $TypeArray[0]['description'];
			$objNewProperty->DocBlock = "Label Name";
			$objNewClass->AddProperty($objNewProperty);
				
			///Singular Name
				
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "Slug");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $TypeArray[0]['slug'];
			$objNewProperty->DocBlock = "Singular Name";
			$objNewClass->AddProperty($objNewProperty);
			
			foreach($TypeArray[1] as $UserTypeField){
				$objNewProperty = new AP_PropertyHolder;
				$objNewProperty->Name = str_replace("-", "", $UserTypeField['name']);
				$objNewProperty->AccessLevel = "protected";
				$objNewProperty->Type = "ctrl";
				$objNewProperty->DocBlock = $UserTypeField['description'];
				$objNewClass->AddProperty($objNewProperty);
			}

			$objNewClass->AddMethod(new AP_MethodAdminLoad);
			$objNewClass->AddMethod(new AP_MethodRenderMetaBoxes);
			$objNewClass->AddMethod(new AP_MethodMagicGetter);
			$objNewClass->AddMethod(new AP_MethodMagicSetter);
			$boolSuccessArray['UserTypes'][$objNewClass->Name] = $this->CodeGenClass($objNewClass,$TypeArray[1]);
			
			$objNewClass = null;
			$objNewClass = new AP_ClassHolder();
			$objNewClass->Name = AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['singular_name']) . "Type";
			$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/user-types";
			$objNewClass->BaseName =  AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['singular_name']) . "TypeGen";
			$objNewClass->AddMethod(new AP_MethodAdminCustomLoad);
			$boolSuccessArray['UserTypes'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		}
		
		///////////////////////////
		// Global PostItem Create//
		///////////////////////////
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = AP_PREFIX . "PostItemGen";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/post/generated";
		$objNewClass->BaseName = AP_PREFIX . "PostItemBase";
		$objFieldArray = array();
		foreach ($this->objConfigData->postfields->postfield as $objPostField){
			$objPostField = unserialize($objPostField);
			$objFieldArray[] = $objPostField;
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", $objPostField['name']);
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->DocBlock = $objPostField['description'];
			$objNewClass->AddProperty($objNewProperty);
		}
		$objNewClass->AddMethod(new AP_MethodInitGenProperties);
		$objNewClass->AddMethod(new AP_MethodMagicGetter);
		$objNewClass->AddMethod(new AP_MethodMagicSetter);
		$boolSuccessArray['PostItems'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, $objFieldArray);
		
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = AP_PREFIX . "PostItem";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/post";
		$objNewClass->BaseName = AP_PREFIX . "PostItemGen";
		$boolSuccessArray['PostItems'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		
		///////////////////////
		// AP_PostItem Create//
		///////////////////////
		foreach ($this->objConfigData->posttypes->posttype as $objPostType){
			$TypeArray = unserialize($objPostType);
			$objNewClass = new AP_ClassHolder();
			$objNewClass->Name = AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['arguments']['labels']['singular_name']) . "ItemGen";
			$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/post/generated";
			$objNewClass->BaseName = "AP_PostItemGen";
			if(isset($TypeArray[1]) && is_array($TypeArray[1])){
				foreach($TypeArray[1] as $PostTypeField){
					$objNewProperty = new AP_PropertyHolder;
					$objNewProperty->Name = str_replace("-", "", $PostTypeField['name']);
					$objNewProperty->AccessLevel = "protected";
					$objNewProperty->DocBlock = $PostTypeField['description'];
					$objNewClass->AddProperty($objNewProperty);
				}
			}
			$objNewClass->AddMethod(new AP_MethodInitGenProperties);
			$objNewClass->AddMethod(new AP_MethodMagicGetter);
			$objNewClass->AddMethod(new AP_MethodMagicSetter);
			$boolSuccessArray['PostItems'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, $TypeArray[1]);

			$objNewClass = null;
			$objNewClass = new AP_ClassHolder();
			$objNewClass->Name = AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['arguments']['labels']['singular_name']) . "Item";
			$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/post";
			$objNewClass->BaseName = AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['arguments']['labels']['singular_name']) . "ItemGen";
			$boolSuccessArray['PostItems'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		}
		
		////////////////////////////////
		// AP Custom Post Types Create//
		////////////////////////////////
		foreach ($this->objConfigData->posttypes->posttype as $objPostType){
			$TypeArray = unserialize($objPostType);
			$objNewClass = new AP_ClassHolder();
			$objNewClass->Name = AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['arguments']['labels']['singular_name']) . "TypeGen";
			$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/post-types/generated";
			$objNewClass->BaseName = "AP_PostType";
			
			/// Name
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "Name");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $TypeArray[0]['arguments']['labels']['name'];
			$objNewProperty->DocBlock = "Name";
			$objNewClass->AddProperty($objNewProperty);
			
			/// Singular Name
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "SingularName");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $TypeArray[0]['arguments']['labels']['singular_name'];
			$objNewProperty->DocBlock = "Slug";
			$objNewClass->AddProperty($objNewProperty);
			
			///Label Name
			
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "LabelName");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $TypeArray[0]['description'];
			$objNewProperty->DocBlock = "Label Name";
			$objNewClass->AddProperty($objNewProperty);
			
			///Registration Array
				
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = "RegistrationArray";
			$objNewProperty->AccessLevel = "public";
			$objNewProperty->Type = "mix";
			$objNewProperty->Array = true;
			$mixArgumentsArray = $TypeArray[0]['arguments'];
			$strDefault = "";
			foreach ($mixArgumentsArray as $strKey => $mixArgument){
				if(is_string($mixArgument)){
					if($strKey == "menu_position" )
						(integer) $mixArgument = intval($mixArgument);
					elseif(intval($mixArgument))
						$mixArgument = "true";
					else
						$mixArgument = "false";
					$strDefault .= '"' . $strKey . '" => ' . $mixArgument . ', ';	
				}
				if(is_array($mixArgument) && $strKey == "labels"){
					$strDefault .= '"labels" => array(';
					foreach($mixArgument as $strKey => $strValue){
						$strDefault .= '"' . $strKey . '" => "' . $strValue . '",'; 
					}
					$strDefault .= ")";
				}
				elseif(is_array($mixArgument) && $strKey == "supports"){
					$strDefault .= '"supports" => array(';
					foreach($mixArgument as $strKey => $strValue){
						$strDefault .= '"' . $strValue . '",';
					}
					$strDefault .= "),";
				}
			}
			$objNewProperty->Default = $strDefault;
			$objNewProperty->DocBlock = "Array for registering this custom post type with Wordpress";
			$objNewClass->AddProperty($objNewProperty);
			
			///Slug
			
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "Slug");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $TypeArray[0]['slug'];
			$objNewProperty->DocBlock = "Singular Name";
			$objNewClass->AddProperty($objNewProperty);
			if(isset($TypeArray[1]) && is_array($TypeArray[1])){
				foreach($TypeArray[1] as $PostTypeField){
					$objNewProperty = new AP_PropertyHolder;
					$objNewProperty->Name = $PostTypeField['name'];
					$objNewProperty->AccessLevel = "protected";
					$objNewProperty->Type = "ctrl";
					$objNewProperty->DocBlock = $PostTypeField['description'];
					$objNewClass->AddProperty($objNewProperty);
				}
			}
			$objNewClass->AddMethod(new AP_MethodAdminLoad);
			$objNewClass->AddMethod(new AP_MethodRenderMetaBoxes);
			$objNewClass->AddMethod(new AP_MethodMagicGetter);
			$objNewClass->AddMethod(new AP_MethodMagicSetter);
			$objNewClass->HasComments = false;
			$objNewClass->PreClassCode = "require_once(APEXT_POST_TYPES_PATH . '/AP_PostType.class.php');";
			$boolSuccessArray['PostTypes'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, $TypeArray[1]);
			
			$objNewClass = null;
			$objNewClass = new AP_ClassHolder();
			$objNewClass->Name = AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['arguments']['labels']['singular_name']) . "Type";
			$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/post-types";
			$objNewClass->BaseName = AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['arguments']['labels']['singular_name']) . "TypeGen";
			$objNewClass->AddMethod(new AP_MethodAdminCustomLoad);
			$boolSuccessArray['PostTypes'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		}

		/////////////////////////////////////
		// AP_CustomPostTypeDisplay Create //
		/////////////////////////////////////

		foreach ($this->objConfigData->posttypes->posttype as $objPostType){

			$TypeArray = unserialize($objPostType);

			$objNewClass = null;
			$objNewClass = new AP_ClassHolder();

			if(isset($TypeArray[1]) && is_array($TypeArray[1])){
				foreach($TypeArray[1] as $PostTypeField){
					$objNewProperty = new AP_PropertyHolder;
					$objNewProperty->Name = $PostTypeField['name'];
					$objNewProperty->AccessLevel = "protected";
					$objNewProperty->Type = "ctrl";
					$objNewProperty->DocBlock = $PostTypeField['description'];
					$objNewClass->AddProperty($objNewProperty);
				}
			}

			
			$objNewClass->Name = AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['arguments']['labels']['singular_name']) . "DisplayGen";
			$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/display/generated";
			$objNewClass->BaseName = AP_PREFIX . "DisplayBase";
			$objNewClass->AddMethod(new AP_MethodDisplayCustomPost);
			$objNewClass->AddMethod(new AP_MethodDisplayCustomPostCollection);
			$boolSuccessArray['PostTypesDisplay'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, $objNewClass->Properties);

			$objNewClass->Name = AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['arguments']['labels']['singular_name']) . "Display";
			$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/display";
			$objNewClass->BaseName =  AP_PREFIX . str_replace(" ", "" , $TypeArray[0]['arguments']['labels']['singular_name']) . "DisplayGen";
			$boolSuccessArray['PostTypesDisplay'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, $objNewClass->Properties, true);
		}

		/////////////////////////
		//AP Post Type Gen Create//
		/////////////////////////
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = AP_PREFIX . "PostTypeGen";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/post-types/generated";
		$objNewClass->BaseName = "AP_CustomPostTypeBase";
		
		/// Name
		$objNewProperty = new AP_PropertyHolder;
		$objNewProperty->Name = "Name";
		$objNewProperty->AccessLevel = "protected";
		$objNewProperty->Default = "Posts";
		$objNewProperty->DocBlock = "Name";
		$objNewClass->AddProperty($objNewProperty);
			
		/// Singular Name
		$objNewProperty = new AP_PropertyHolder;
		$objNewProperty->Name = "SingularName";
		$objNewProperty->AccessLevel = "protected";
		$objNewProperty->Default = "Post";
		$objNewProperty->DocBlock = "Singular Name";
		$objNewClass->AddProperty($objNewProperty);
			
		///Label Name
			
		$objNewProperty = new AP_PropertyHolder;
		$objNewProperty->Name = "LabelName";
		$objNewProperty->AccessLevel = "protected";
		$objNewProperty->Default = "Base Post Type";
		$objNewProperty->DocBlock = "Label Name";
		$objNewClass->AddProperty($objNewProperty);
		
		///Slug
		
		$objNewProperty = new AP_PropertyHolder;
		$objNewProperty->Name = "Slug";
		$objNewProperty->AccessLevel = "protected";
		$objNewProperty->Default = "post";
		$objNewProperty->DocBlock = "Slug";
		$objNewClass->AddProperty($objNewProperty);
		
		$TypeArray = array();
		foreach($this->objConfigData->postfields->postfield as $PostTypeField){
			$PostTypeField = unserialize($PostTypeField);
			$TypeArray[1][] = $PostTypeField;
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = $PostTypeField['name'];
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Type = "ctrl";
			$objNewProperty->DocBlock = $PostTypeField['description'];
			$objNewClass->AddProperty($objNewProperty);
		}
		$objNewClass->AddMethod(new AP_MethodAdminLoad);
		$objNewClass->AddMethod(new AP_MethodRenderMetaBoxes);
		$objNewClass->AddMethod(new AP_MethodMagicGetter);
		$objNewClass->AddMethod(new AP_MethodMagicSetter);
		$objNewClass->HasComments = true;
		$boolSuccessArray['PostTypes'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, $TypeArray[1]);
		
		//////////////////////
		//AP_PostType Create//
		//////////////////////
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = AP_PREFIX . "PostType";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/post-types";
		$objNewClass->BaseName = AP_PREFIX . "PostTypeGen";
		$objNewClass->AddMethod(new AP_MethodAdminCustomLoad);
		$objNewClass->AddMethod(new AP_MethodAdminLoad);
		$objNewClass->AddMethod(new AP_MethodRenderMetaBoxes);
		$objNewClass->PreClassCode = "require_once(APEXT_POST_TYPES_PATH . '/generated/AP_PostTypeGen.class.php');";
		$boolSuccessArray['PostTypes'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		
		// AP Custom Taxonomies Create
		foreach ($this->objConfigData->taxonomies->taxonomy as $objTaxonomy){
			$TypeArray = unserialize($objTaxonomy);
			$mixArgumentsArray = unserialize($TypeArray['arguments']);
			$strPostTypeArray = unserialize($TypeArray['post_types']);
			$objNewClass = new AP_ClassHolder();
			$objNewClass->Name = AP_PREFIX . str_replace(" ", "" , $mixArgumentsArray['labels']['singular_name']) . "TypeGen";
			$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/taxonomy/generated";
			$objNewClass->BaseName = "AP_CustomTaxonomyTypeBase";
				
			/// Name
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "Name");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $mixArgumentsArray['labels']['name'];
			$objNewProperty->DocBlock = "Name";
			$objNewClass->AddProperty($objNewProperty);
				
			/// Singular Name
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "SingularName");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $mixArgumentsArray['labels']['singular_name'];
			$objNewProperty->DocBlock = "Singular Name";
			$objNewClass->AddProperty($objNewProperty);
				
			///Description
				
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "Description");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $TypeArray['description'];
			$objNewProperty->DocBlock = "Description";
			$objNewClass->AddProperty($objNewProperty);
				
			///Slug
				
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "Slug");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = $TypeArray['slug'];
			$objNewProperty->DocBlock = "Slug";
			$objNewClass->AddProperty($objNewProperty);
			
			///Registration Array
			
			unset($TypeArray['ap_id']);
			unset($TypeArray['ap_id']);
			$TypeArray['arguments'] = $mixArgumentsArray;
			$TypeArray['post_types'] = $strPostTypeArray;
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", "RegistrationArray");
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Default = serialize($TypeArray);
			$objNewProperty->DocBlock = "RegistrationArray";
			$objNewClass->AddProperty($objNewProperty);
			
			$objNewClass->AddMethod(new AP_MethodAdminLoad);
			$objNewClass->AddMethod(new AP_MethodRenderMetaBoxes);
			$objNewClass->AddMethod(new AP_MethodMagicGetter);
			$objNewClass->AddMethod(new AP_MethodMagicSetter);
			$objNewClass->HasComments = false;
			$boolSuccessArray['Taxonomies'][$objNewClass->Name] = $this->CodeGenClass($objNewClass);

			$objNewClass = null;
			$objNewClass = new AP_ClassHolder();
			$objNewClass->Name = AP_PREFIX . str_replace(" ", "" , $mixArgumentsArray['labels']['singular_name']) . "Type";
			$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/taxonomy";
			$objNewClass->BaseName = AP_PREFIX . str_replace(" ", "" , $mixArgumentsArray['labels']['singular_name']) . "TypeGen";
			$objNewClass->AddMethod(new AP_MethodAdminCustomLoad);
			$boolSuccessArray['PostTypes'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		}
		
		//////////////////////////
		// AP_Application create//
		//////////////////////////
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_Application";
		$objNewClass->Path =  AP_PLUGIN_PATH . $this->strSiteSlug;  
		$objNewClass->BaseName = "AP_ApplicationBase";
		$objNewClass->AddMethod(new AP_MethodSiteSettings);
		$objNewClass->AddMethod(new AP_MethodAdminSettings);
		$objNewClass->AddMethod(new AP_MethodPreExitInit);
		$boolSuccessArray['ExtensionFiles'][$objNewClass->Name] = $this->CodeGenClass($objNewClass);
		
		///////////////////////////////
		//agilepress-extension create//
		///////////////////////////////
		ob_start();
		$strFile = include( AP_TEMPLATE_PATH . "files/agilepress-extension.tpl.php");
		$strContents = ob_get_contents();
		ob_end_clean();
		$boolSuccessArray['ExtensionFiles'][$this->strSiteSlug] = AP_IO::WriteFile(AP_PLUGIN_PATH . $this->strSiteSlug . "/" . $this->strSiteSlug . ".php", "<?php \n" . $strContents);
		
		////////////////////////
		//configuration create//
		////////////////////////
		ob_start();
		$strFile = include( AP_TEMPLATE_PATH . "files/configuration.tpl.php");
		$strContents = ob_get_contents();
		ob_end_clean();
		$boolSuccessArray['ExtensionFiles']["configuration"] = AP_IO::WriteFile(AP_PLUGIN_PATH . $this->strSiteSlug . "/configuration.inc.php", "<?php \n" . $strContents);
		
		//////////////////
		//prepend create//
		//////////////////
		ob_start();
		$strFile = include( AP_TEMPLATE_PATH . "files/prepend.tpl.php");
		$strContents = ob_get_contents();
		ob_end_clean();
		$boolSuccessArray['ExtensionFiles']["prepend"] = AP_IO::WriteFile(AP_PLUGIN_PATH . $this->strSiteSlug .  "/prepend.inc.php", "<?php \n" . $strContents);
		
		//////////////////
		//Install create//
		//////////////////
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_Install";
		$objNewClass->BaseName = "AP_InstallBase";
		$objNewClass->Type = "abstract";
		$objNewClass->Description = "
		///////////////////////////////////////
		// Add custom install code here such as creating
		// tables, files, etc.
		//
		// Status: Override
		///////////////////////////////////////";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/install";
		$boolSuccessArray['ExtensionFiles'][$objNewClass->Name] = $this->CodeGenClass($objNewClass);
		
		////////////////////
		//PostQuery Create//
		////////////////////
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_PostQuery";
		$objNewClass->BaseName = "AP_PostQueryBase";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/post";
		$boolSuccessArray['ExtensionFiles'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		
		///////////////
		//User Create//
		///////////////
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_User";
		$objNewClass->BaseName = "AP_UserBase";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/user";
		$boolSuccessArray['ExtensionFiles'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		
		//////////////////////
		//UserTypeGen Create//
		//////////////////////
		$objNewClass = null;
		$strTempArray = array();
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_UserTypeGen";
		$objNewClass->BaseName = "AP_CustomUserTypeBase";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/user-types/generated";
		
		/// Name
		$objNewProperty = new AP_PropertyHolder;
		$objNewProperty->Name = str_replace("-", "", "Name");
		$objNewProperty->AccessLevel = "protected";
		$objNewProperty->Default = 'Global';
		$objNewProperty->DocBlock = "Name";
		$objNewClass->AddProperty($objNewProperty);
			
		/// Slug
		$objNewProperty = new AP_PropertyHolder;
		$objNewProperty->Name = str_replace("-", "", "SingularName");
		$objNewProperty->AccessLevel = "protected";
		$objNewProperty->Default = 'user-global';
		$objNewProperty->DocBlock = "Slug";
		$objNewClass->AddProperty($objNewProperty);
		
		///Label Name		
		$objNewProperty = new AP_PropertyHolder;
		$objNewProperty->Name = str_replace("-", "", "LabelName");
		$objNewProperty->AccessLevel = "protected";
		$objNewProperty->Default = 'A Global User Type for managing global user fields';
		$objNewProperty->DocBlock = "Desacription";
		$objNewClass->AddProperty($objNewProperty);
			
		///Singular Name	
		$objNewProperty = new AP_PropertyHolder;
		$objNewProperty->Name = str_replace("-", "", "Slug");
		$objNewProperty->AccessLevel = "protected";
		$objNewProperty->Default = 'Global User Type';
		$objNewProperty->DocBlock = "Singular Name";
		$objNewClass->AddProperty($objNewProperty);
		
		///User Type Radio Control
		$objNewProperty = new AP_PropertyHolder;
		$objNewProperty->Name = "User Type";
		$objNewProperty->AccessLevel = "protected";
		$objNewProperty->DocBlock = "User type selection radio control";
		$objNewProperty->Type = 'ctrl';
		$objNewClass->AddProperty($objNewProperty);
		$strTempArray[] = array(
				'name' => 'User Type',
				'control_type' => 'radio',
				'slug' => 'user-type',
				'description' => 'AP User Types radio control'
				
		);
		foreach($this->objConfigData->userfields->userfield as $UserTypeField){
			$UserTypeField = unserialize($UserTypeField);
			$strTempArray[] = $UserTypeField;
			$objNewProperty = new AP_PropertyHolder;
			$objNewProperty->Name = str_replace("-", "", $UserTypeField['name']);
			$objNewProperty->AccessLevel = "protected";
			$objNewProperty->Type = "ctrl";
			$objNewProperty->DocBlock = $UserTypeField['description'];
			$objNewClass->AddProperty($objNewProperty);
		}
		$objNewClass->AddMethod(new AP_MethodUserAdminLoad);
		$objNewClass->AddMethod(new AP_MethodRenderMetaBoxes);
		$objNewClass->AddMethod(new AP_MethodMagicGetter);
		$objNewClass->AddMethod(new AP_MethodMagicSetter);
		$boolSuccessArray['ExtensionFiles'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, $strTempArray);
		
		///////////////////////////
		//Custom User Role Create//
		///////////////////////////

		$mixUserRolesArray = array();

		//get all user roles
		foreach($this->objConfigData->userroles->userrole as $UserRole)
			$mixUserRolesArray = AP_CustomUserTypeBase::GetConfigUserRoleArray();

		ob_start();
		$strFile = include( AP_TEMPLATE_PATH . "files/roles.tpl.php");
		$strContents = ob_get_contents();
		ob_end_clean();
		$boolSuccessArray['ExtensionFiles']["prepend"] = AP_IO::WriteFile(AP_PLUGIN_PATH . $this->strSiteSlug .  "/constants/generated/AP_UserRolesGen.class.php", "<?php \n" . $strContents);


		///////////////////
		//UserType Create//
		///////////////////
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_UserType";
		$objNewClass->BaseName = "AP_UserTypeGen";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/user-types";
		$objNewClass->PreClassCode = "require_once(APEXT_USER_TYPES_PATH . '/generated/AP_UserTypeGen.class.php');";
		$objNewClass->AddMethod(new AP_MethodAdminCustomLoad);
		$objNewClass->AddMethod(new AP_MethodAdminLoad);
		$objNewClass->AddMethod(new AP_MethodRenderMetaBoxes);
		$boolSuccessArray['ExtensionFiles'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		
		///////////////////
		//PostItem Create//
		///////////////////
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_PostItem";
		$objNewClass->BaseName = "AP_PostItemBase";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/post";
		$boolSuccessArray['ExtensionFiles'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		
		////////////////
		/// Controls ///
		////////////////
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_ListBox";
		$objNewClass->BaseName = "AP_ListBoxBase";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/controls";
		$boolSuccessArray['Controls'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_TextBox";
		$objNewClass->BaseName = "AP_TextBoxBase";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/controls";
		$boolSuccessArray['Controls'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_CheckBox";
		$objNewClass->BaseName = "AP_CheckBoxBase";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/controls";
		$boolSuccessArray['Controls'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_CheckBoxList";
		$objNewClass->BaseName = "AP_CheckBoxListBase";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/controls";
		$boolSuccessArray['Controls'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);
		
		$objNewClass = null;
		$objNewClass = new AP_ClassHolder();
		$objNewClass->Name = "AP_RadioButton";
		$objNewClass->BaseName = "AP_RadioButtonBase";
		$objNewClass->Path = AP_PLUGIN_PATH . $this->strSiteSlug . "/controls";
		$boolSuccessArray['Controls'][$objNewClass->Name] = $this->CodeGenClass($objNewClass, null, true);

		////////////////////////
		/// Content Examples ///
		////////////////////////
		
		AP_IO::WriteFile(AP_PLUGIN_PATH . $this->strSiteSlug .  "/pages/content/HeaderMain.mrkp.php", "", true);

		AP_IO::WriteFile(AP_PLUGIN_PATH . $this->strSiteSlug .  "/pages/content/ContentMain.mrkp.php", "", true);

		AP_IO::WriteFile(AP_PLUGIN_PATH . $this->strSiteSlug .  "/pages/content/FooterMain.mrkp.php", "", true);

		///////////////////////////
		/// End Code Generation ///
		///////////////////////////


		return $boolSuccessArray;
	}
	
	
	/**
	 * Method for converting a short data type string to a long data type string 
	 * 
	 * @since 1.0
	 * @param string $strValue
	 * @return string $strLongValue
	 */
	public static function ShortToLongType($strValue){
		switch ($strValue){
			case "int" : 
				return "integer"; 
			case "str" :
				return "string"; 
			case "bool" : 
				return "boolean"; 
			case "obj" : 
				return "object"; 
			case "arr" : 
				return "array"; 
			case "ctrl" : 
				return "control";
		}
	}
	
	/**
	 * A file write wrapper to simplify class creation during code generation, called many times in $this->CodeGenExtensionClasses()
	 * 
	 * @since 1.0
	 * @param AP_ClassHolder $objClassHolder
	 * @param mixed $mixExtraArgument
	 * @param boolean $boolExistsCheck
	 * @return integer $intErrorCode
	 */
	public function CodeGenClass(AP_ClassHolder $objClassHolder, $mixExtraArgument = null, $boolExistsCheck = false){
		//Remove extra whitespace from class name
		$strClassName = trim($objClassHolder->Name);
		
		//if the class is a PHP reserved word then return
		if(is_int(strpos($objClassHolder->Name, AP_CodeGenConstants::$PHPReservedWordArray)))
			return;
		
		//begin file string
		$strFileContent = "<?php  \n";
		
		//if the class has comments then add them
		if($objClassHolder->HasComments){
			ob_start();
			$strFile = include( AP_TEMPLATE_PATH . "ClassDocBlock.tpl.php" );
			$strFileContent = $strFileContent . ob_get_contents() . "\n\n";
			ob_end_clean();
		}
		
		//Write any code that must run before the class definition
		$strFileContent = $strFileContent . $objClassHolder->PreClassCode . "\n\n";
		
		//begin writing class to string
		$strFileContent = $strFileContent . "$objClassHolder->Type class $objClassHolder->Name";
		
		//if the class extends from another class add the extension code
		if($objClassHolder->BaseName <> null ) $strFileContent = $strFileContent . " extends $objClassHolder->BaseName";
			$strFileContent = $strFileContent . "{\n\n\t//Properties// \n\n";
			
		//write all properties to file string
		foreach ($objClassHolder->Properties as $objProperty){
			$objProperty->Name =  AP_Base::SanitizeString($objProperty->Name);
			$objProperty->Slug =  AP_Base::SanitizeString($objProperty->Slug);
			$strStatic = ($objProperty->Static)? "static" : "";
			if($objProperty->Array){
				$strDelimiterLeft = "array(";
				$strDelimiterRight = ")";
			}
			else{
				$strDelimiterLeft = "'";
				$strDelimiterRight = "'";
			}
			$strDefault = ($objProperty->Default <> null) ? " = {$strDelimiterLeft}{$objProperty->Default}{$strDelimiterRight}" : "";
			$strFileContent = $strFileContent . "\t $objProperty->AccessLevel " . $strStatic . " $". $objProperty->Type . $objProperty->Name . $strDefault .   ";";
			if($objProperty->DocBlock <> null) $strFileContent = $strFileContent . "\t // $objProperty->DocBlock \n"; else $strFileContent = $strFileContent . "\n";
		}
		$strFileContent = $strFileContent . "\n \n";
		
		//if the class contains methods, write the methods to the file string
		if($objClassHolder->Methods <> null){
			foreach ($objClassHolder->Methods as $objMethod){
				ob_start();
				$strFile = include( AP_TEMPLATE_PATH . $objMethod->Template);
				$contents = ob_get_contents();
				ob_end_clean();
				$strFileContent = $strFileContent . $contents . "\n";
			}
		}
		
		//add end of class to file string
		$strFileContent = $strFileContent . "\n}";
		
		//write the filestring to the appropriate file path and return the resulting Success/Error code
		return AP_IO::WriteFile($objClassHolder->Path . "/" . $objClassHolder->Name . ".class.php" , $strFileContent, $boolExistsCheck);
	}




}


