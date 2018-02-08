<?php

/**
 * AP_Modules handles all modules functionality except for admin UI
 * 
 * @since 1.0
 * @package AgilePress
 * @subpackage Modules
 * @author AgilePress Core Developement Team
 *
 */
class AP_Modules extends AP_Base {
	
	
	// Array of all valid module filename paths
	protected $strModuleArray = array();
	// Array of raw php file paths from AP_PATH/modules directory
	protected $strModuleRawArray = array();
	// Array of currently activated modules, retrieved from AP modules table
	protected $strRegisteredModuleArray = array();
	// Array of various errors that have occured
	protected $strErrorArray = array();
	
	/**
	 * AP Modules constructor, gets appropriate data for all class properties from their respective locations
	 * 
	 * @since 1.0
	 * @return boolean (true on success)
	 */
	public function __construct(){
		$this->strModuleRawArray = $this->GetModuleFolderFileList();
		$this->strModuleArray = $this->GetModuleData();
		$this->strRegisteredModuleArray = $this->GetRegisteredModules();
		return true;	
	}
	
	/**
	 * Gets a list of php files from the modules directory and fills the strModuleRawArray with the values
	 * 
	 * @since 1.0
	 * @return string strModuleArray
	 */
	public function GetModuleFolderFileList(){
		$strModuleFileArray = scandir(AP_PATH . "/modules");
		foreach($strModuleFileArray as $intKey => $strModule){
			$strModule = trim($strModule);
			$strModuleCheck = substr($strModule,-4);
			if(strtolower($strModuleCheck) == ".php")
				$this->strModuleArray[] = $strModule;
			elseif(is_dir(AP_PATH . "/modules/" . $strModule) && $strModule <> "." && $strModule <> ".."){
				$strModuleLowerFileArray = scandir(AP_PATH . "/modules/" . $strModule);
				foreach($strModuleLowerFileArray as $intKey => $strLowerModule){
					$strLowerModule = trim($strLowerModule);
					$strLowerModuleCheck = substr($strLowerModule,-4);
					if(strtolower($strLowerModuleCheck) == ".php")
						$this->strModuleArray[] =  $strModule . "/" . $strLowerModule;
				}
			}
		}
		return $this->strModuleArray;
		
	}
	
	/**
	 * Takes a php file from strModuleRawArray and extracts the usable data from the pages first document block
	 * 
	 * @since 1.0
	 * @return string strModuleDataReturnArray
	 */
	public function GetModuleData(){
		$strContents = "";
		$strModuleDataArray = array();
		$strModuleDataReturnArray = array();
		foreach($this->strModuleRawArray as $intKey => $strModule){
			$strModuleTemporaryArray = array();
			$strContents = htmlspecialchars(file_get_contents(AP_PATH . "modules/" . $strModule));
			$strModuleDataArray = explode("/*", $strContents);
			$strModuleDataArray = explode("*/", $strModuleDataArray[1]);
			$strModuleDataArray = explode("\n", $strModuleDataArray[0]);
			unset($strModuleDataArray[0]);
			$strModuleDataArray = array_merge(array_filter($strModuleDataArray));
			foreach($strModuleDataArray as $intKey => $strModuleData){
				if(preg_match("/(module name)/i",$strModuleData))
					$strModuleTemporaryArray["name"] = trim(end(explode(":",$strModuleData)));
				if(preg_match("/(description)/i",$strModuleData))
					$strModuleTemporaryArray["description"]  = trim(end(explode(":",$strModuleData)));
				if(preg_match("/(version)/i",$strModuleData))
					$strModuleTemporaryArray["version"]  = trim(end(explode(":",$strModuleData)));
				if(preg_match("/(author)/i",$strModuleData))
					$strModuleTemporaryArray["author"]  = trim(end(explode(":",$strModuleData)));
				if(preg_match("/(menu)/i",$strModuleData))
					$strModuleTemporaryArray["menu"]  = trim(end(explode(":",$strModuleData)));
			}
			if($strModuleTemporaryArray["name"]){
				$strModuleTemporaryArray['path'] =  $strModule;
				$strModuleDataReturnArray[] = $strModuleTemporaryArray;
			}
		}
		return $strModuleDataReturnArray;
	}
	
	
	/**
	 * Static function called in AP_ApplicationBase to include code from all modules registered in the module table
	 * 
	 * @since 1.0
	 * @return null
	 */
	public static function RegisterModules(){
		$objModuleHandler = new AP_Modules();
		foreach($objModuleHandler->RegisteredModuleArray as $strRegisteredModulePath){
			include_once(AP_PATH . "modules/" . $strRegisteredModulePath);
		}
	}
	
	/**
	 * Static function that runs any actions associated with a passed $strHook at the location of the method call
	 *
	 * @since 1.0
	 * @param string $strHook
	 * @return null
	 */
	public static function RunAction($strHook){
		if(!isset($GLOBALS['ap-hook-array']) || !isset($GLOBALS['ap-hook-array'][$strHook]))
			return;
		foreach($GLOBALS['ap-hook-array'][$strHook] as $mixCallback){
			if(is_callable($mixCallback))
				call_user_func($mixCallback);
		}
	}
	
	/**
	 * Static function that adds a callable to any passed $strHook, callables are then called at any location with the respective AP_Modules::RunAction($strHook)
	 * 
	 * @since 1.0
	 * @param string $strHook
	 * @param unknown $mixFunctionCallbackReturnValues
	 */
	public static function AddAction($strHook, $mixFunctionCallback){
		if(!isset($GLOBALS['ap-hook-array']) || !is_array($GLOBALS['ap-hook-array']))
			$GLOBALS['ap-hook-array'] = array();
		if(isset($GLOBALS['ap-hook-array'][$strHook]) && is_array($GLOBAL['ap-hook-array'][$strHook]))
			$GLOBALS['ap-hook-array'][$strHook][] = $mixFunctionCallback;
		else
			$GLOBALS['ap-hook-array'][$strHook] = array($mixFunctionCallback);
	}
	
	/**
	 * Static function that return an array of all modules registered in the AP modules table. Paths returned are based on modules as a parent directory
	 * 
	 * @since 1.0
	 * @return string $strReturnArray (Array of module file paths)
	 */
	public static function GetRegisteredModules(){
		global $wpdb;
		$strTableName = $wpdb->prefix . AP_PREFIX . modules;
		$strTableName = strtolower($strTableName);
		$strSQLQuery = "SELECT slug FROM $strTableName;";
		$strRegisteredModuleArray = $wpdb->get_results($strSQLQuery, ARRAY_A);
		$strReturnArray = array();
		foreach($strRegisteredModuleArray as $intKey => $strModuleName){
			$strReturnArray[] = $strModuleName['slug'];
		}
		return $strReturnArray;
	}
	
	/**
	 * Magic method getters for various module arrays of the AP_Modules class
	 * 
	 * @since 1.0
	 * @param string $strName
	 * @return multitype:
	 */
	public function __get($strName){
		switch ($strName) {
			case "ModuleArray" :
				return $this->strModuleArray;
			case "ModuleRawArray" :
				return $this->strModuleRawArray;
			case "RegisteredModuleArray" :
				return $this->strRegisteredModuleArray;
			case "ErrorArray" :
				return $this->strErrorArray;
		}
	}

	
}