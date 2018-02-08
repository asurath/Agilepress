<?php

/**
 * Base class holder to be passed to AP_CoreCodeGenerator->CodeGenClass()
 * 
 * @since 1.0
 * @package AgilePress
 * @subpackage Psuedo Class Definitions
 * @author AgilePress Core Developement Team
 *
 */

class AP_ClassHolder {

	protected $strClassPath;
	protected $strClassName;
	protected $strClassBaseName;
	protected $strClassType;
	protected $objMethodHolderArray = array();
	protected $objPropertyHolderArray = array();
	protected $strDescription;
	protected $strPreClassCode = null;
	protected $boolHasComments = true;

	public function __get($strName){
		switch ($strName) {
			case "Methods" :
				return $this->objMethodHolderArray;
			case "Properties" :
				return $this->objPropertyHolderArray;
			case "Description" :
				return $this->strDescription;
			case "Name" :
				return $this->strClassName;
			case "BaseName" :
				return $this->strClassBaseName;
			case "Path" :
				return $this->strClassPath;
			case "Type" :
				return $this->strClassType;
			case "HasComments" :
				return $this->boolHasComments;
			case "PreClassCode" :
				return $this->strPreClassCode;
		}
	}

	public function __set($strName, $mixValue){
		switch ($strName) {
			case "Methods" :
				return $this->objMethodHolderArray = $mixValue;
			case "Properties" :
				return $this->objPropertyHolderArray = $mixValue;
			case "Description" :
				return $this->strDescription = $mixValue;
			case "Name" :
				return $this->strClassName = $mixValue;
			case "BaseName" :
				return $this->strClassBaseName = $mixValue;
			case "Path" :
				return $this->strClassPath = $mixValue;
			case "Type" :
				return $this->strClassType = $mixValue;
			case "HasComments" :
				return $this->boolHasComments = $mixValue;
			case "PreClassCode" :
				return $this->strPreClassCode = $mixValue;
		}
	}

	/**
	 * Method for adding a Method Definition to the AP_ClassHolder object
	 * 
	 * @param AP_MethodHolder $objMethodHolder
	 * @return void
	 */
	public function AddMethod($objMethodHolder){
		if($objMethodHolder instanceof AP_MethodHolder && is_array($this->objMethodHolderArray))
			$this->objMethodHolderArray[] = $objMethodHolder;
	}

	public function AddProperty($objPropertyHolder){
		if($objPropertyHolder instanceof AP_PropertyHolder){
			$this->objPropertyHolderArray[] = $objPropertyHolder;
		}
	}

}

/**
 * Base method holder
 * 
 * @author AgilePress Core Developement Team
 *
 */

class AP_MethodHolder {

	protected $boolCommented;
	protected $strDocBlock;
	protected $strAccessLevel;
	protected $strType;
	protected $strMethodName;
	protected $strMethodTemplate;
	protected $strArgumentArray;

	public function __get($strName){
		switch($strName){
			case "Type":
				return $this->strType;
			case "Name":
				return $this->strMethodName;
			case "Arguments":
				return $this->strArgumentArray;
			case "Template":
				return $this->strMethodTemplate;
			case "AccessLevel":
				return $this->strAccessLevel;
			case "DocBlock":
				return $this->strDocBlock;
			case "Commented":
				return $this->boolCommented;
					
		}
	}

	public function __set($strName, $mixValue){
		switch($strName){
			case "Type":
				return $this->strType = $mixValue;
			case "Name":
				return $this->strMethodName = $mixValue;
			case "Arguments":
				return $this->strArgumentArray = $mixValue;
			case "Template":
				return $this->strMethodTemplate = $mixValue;
			case "AccessLevel":
				return $this->strAccessLevel = $mixValue;
			case "DocBlock":
				return $this->strDocBlock = $mixValue;
			case "Commented":
				return $this->boolCommented = $mixValue;
					
		}
	}



}

/**
 * Base property holder
 * 
 * @author AgilePress Core Developement Team
 *
 */

class AP_PropertyHolder {

	protected $strName;
	protected $strType = "str";
	protected $strAccessLevel = "protected";
	protected $strDocBlock = null;
	protected $boolStatic = false;
	protected $boolArray = false;
	protected $strDefault = null;

	public function __get($strName){
		switch($strName){
			case "Name":
				return $this->strName;
			case "Type":
				return $this->strType;
			case "AccessLevel" :
				return $this->strAccessLevel;
			case "DocBlock":
				return $this->strDocBlock;
			case "Static":
				return $this->boolStatic;
			case "Array":
				return $this->boolArray;
			case "Default":
				return $this->strDefault;
					
		}
	}

	public function __set($strName, $mixValue){
		switch($strName){
			case "Name":
				return $this->strName = $mixValue;
			case "Type":
				return $this->strType = $mixValue;
			case "AccessLevel" :
				return $this->strAccessLevel = $mixValue;
			case "DocBlock":
				return $this->strDocBlock = $mixValue;
			case "Static":
				return $this->boolStatic = $mixValue;
			case "Array":
				return $this->boolArray = $mixValue;
			case "Default":
				return $this->strDefault = $mixValue;
					
		}
	}

}

/**
 * Method code-gen definitions
 * 
 * @author AgilePress Core Developement Team
 *
 */

class AP_MethodMagicGetter extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "public";
	protected $strType = "";
	protected $strMethodName = "__get";
	protected $strArgumentArray = array("strName");
	protected $strMethodTemplate = "MagicGetter.tpl.php";

}

class AP_MethodMagicSetter extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "public";
	protected $strType = "";
	protected $strMethodName = "__set";
	protected $strArgumentArray = array("strName", "mixValue");
	protected $strMethodTemplate = "MagicSetter.tpl.php";

}

class AP_MethodTypeMagicGetter extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "public";
	protected $strType = "";
	protected $strMethodName = "__get";
	protected $strArgumentArray = array("strName");
	protected $strMethodTemplate = "TypeMagicGetter.tpl.php";

}

class AP_MethodTypeMagicSetter extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "public";
	protected $strType = "";
	protected $strMethodName = "__set";
	protected $strArgumentArray = array("strName", "mixValue");
	protected $strMethodTemplate = "TypeMagicSetter.tpl.php";

}

class AP_MethodAdminSettings extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "protected";
	protected $strType = "";
	protected $strMethodName = "AdminSettings";
	protected $strArgumentArray = array();
	protected $strMethodTemplate = "AdminSettings.tpl.php";
	
}

class AP_MethodPreExitInit extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "protected";
	protected $strType = "";
	protected $strMethodName = "PreExitInit";
	protected $strArgumentArray = array();
	protected $strMethodTemplate = "PreExitInit.tpl.php";

}

class AP_MethodSiteSettings extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "protected";
	protected $strType = "";
	protected $strMethodName = "SiteSettings";
	protected $strArgumentArray = array();
	protected $strMethodTemplate = "SiteSettings.tpl.php";

}

class AP_MethodAdminLoad extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "protected";
	protected $strType = "";
	protected $strMethodName = "AdminLoad";
	protected $strMethodTemplate = "AdminLoad.tpl.php";
	
}

class AP_MethodUserAdminLoad extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "protected";
	protected $strType = "";
	protected $strMethodName = "AdminLoad";
	protected $strMethodTemplate = "UserAdminLoad.tpl.php";

}

class AP_MethodAdminCustomLoad extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "protected";
	protected $strType = "";
	protected $strMethodName = "AdminCustomLoad";
	protected $strMethodTemplate = "AdminCustomLoad.tpl.php";

}


class AP_MethodRenderMetaBoxes extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "protected";
	protected $strType = "";
	protected $strMethodName = "RenderMetaBoxes";
	protected $strMethodTemplate = "RenderMetaBoxes.tpl.php";

}

class AP_MethodUserRenderMetaBoxes extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "protected";
	protected $strType = "";
	protected $strMethodName = "RenderMetaBoxes";
	protected $strMethodTemplate = "UserRenderMetaBoxes.tpl.php";

}

class AP_MethodInitGenProperties extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "protected";
	protected $strType = "";
	protected $strMethodName = "InitGeneratedProperties";
	protected $strMethodTemplate = "InitGeneratedProperties.tpl.php";

}

class AP_MethodDisplayCustomPostCollection extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "public";
	protected $strType = "static";
	protected $strMethodName = "DisplayCustomPostCollection";
	protected $strMethodTemplate = "DisplayCustomPostCollection.tpl.php";

}

class AP_MethodDisplayCustomPost extends AP_MethodHolder {
	protected $boolCommented = false;
	protected $strAccessLevel = "public";
	protected $strType = "static";
	protected $strMethodName = "DisplayCustomPost";
	protected $strMethodTemplate = "DisplayCustomPost.tpl.php";

}

