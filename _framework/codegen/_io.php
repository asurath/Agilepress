<?php
class AP_IO extends AP_Base { 
	public static function WriteFile( $strFilePath , $strFileContent, $boolExistsCheck = false){
		$intFileExists = (file_exists($strFilePath)) ? 3 : 1;
		if($boolExistsCheck)
			if(file_exists($strFilePath))
				return 2;
		if(is_writeable(AP_PATH)){
			$refHandle = fopen( $strFilePath, 'w') or die('Cannot open/create file : ' . $strFilePath);
			if (fwrite($refHandle, $strFileContent) === FALSE) {
				return 0;
			}
			fclose($refHandle);
			return $intFileExists;
		}
		else
			return 0;
	}
	
	public static function AppendFile( $strFilePath, $strFileContent){
		if(is_writeable(AP_PATH)){
			ob_start();
			$strFile = include( $strFilePath );
			$strContents = ob_get_contents();
			ob_end_clean();
			$refHandle = fopen( $strFilePath, 'w') or die('Cannot open/create file : ' . $strFilePath);
			$strFileContent = $strContents . $strFileContent;
			if (fwrite($refHandle, $strFileContent) === FALSE) {
				return 0;
			}
			fclose($refHandle);
			return 1;
		}
		else 
			return 0;
	}
	
	public static function ReadXMLFile($strFilePath){
		if(is_readable($strFilePath)){
			$objElement = simplexml_load_file($strFilePath);
			return $objElement;
		}
	}
	
	public static function WriteXMLFile($strFilePath, $strContents){
		if(is_writable($strFilePath)){
			$refHandle = fopen($strFilePath, 'w') or Die ('Cannot open/create file: ' . $strFilePath);
			if(fwrite($refHandle, $strFileContent) === FALSE){
				echo "Cannot write to file ($strFilePath)";
				exit;
			}
			fclose($refHandle);
			return true;
		}
		
	}

}