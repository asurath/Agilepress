<?php
class AP_Base {
	public function error($msg){
		throw new AP_Exception($msg);
	}
	
	public static function SanitizeString($strValue){
		$regexp = '';
		$strValue = preg_replace("/[^A-Za-z0-9 ]/", "", $strValue);
		$strValue = str_replace(" ", "", $strValue);
		$strValue = (is_numeric($strValue[0])) ? "ap_" . $strValue : $strValue;
		return $strValue;
	}
	
	
}