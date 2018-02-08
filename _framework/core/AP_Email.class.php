<?php
class AP_Email extends AP_Base {
	
	protected $objConfigHandler;
	protected $strEmailArray = array();
	protected $strEmailString = "";
	protected $strEmailSubject;
	protected $strEmailBody;
	
	public function __construct($boolIsAdminCheck = false){
		if($boolIsAdminCheck){
			$objConfigHandler = new AP_CoreCodeGenerator;
			$strEmailArray = unserialize($objConfigHandler->objConfigData->settings->emaillist);
			$this->strEmailString = AP_Email::ConvertEmailArrayToString($strEmailArray);
		}
		
	}
	
	public static function ConvertEmailArrayToString($strEmailArray = false){
		$strEmailString = "";
		if(!$strEmailArray)
			return;
		if(is_array($strEmailArray)){
			$i = 1;
			$length = count($strEmailArray);
			foreach($strEmailArray as $strEmail){
				$strDelimiter = ($count == $i) ? "" : ",";
				$strEmailString = $strEmailString . $strEmail . $strDelimiter;
				$i++;
			}
			return $strEmailString;
		}
		if(is_string($strEmailArray))
			return $strEmailArray;
	}
	
	public function SendEmail(){
		if(isset($strEmailString) && isset($strEmailSubject))
			$strResponse = mail($this->strEmailString, $this->strEmailSubject, $this->strEmailBody);
		return $strResponse;		
	}
	
	public function __get($strName){
		switch($strName){
			case "EmailArray":
				return $this->strEmailArray;
			case "EmailString" :
				return $this->strEmailString;
			case "Subject" :
				return $this->strEmailSubject;
			case "Body" : 
				return $this->strEmailBody;
		}
	}
	
	public function __set($strName,$mixValue){
		switch($strName){
			case "EmailArray" :
				return $this->strEmailArray = $mixValue;
			case "EmailString" :
				return $this->strEmailString = $mixValue;
			case "Subject" :
				return $this->strEmailSubject = $mixValue;
			case "Body" :
				return $this->strEmailBody = $mixValue;
		}
		
		
	}
}