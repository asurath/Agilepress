<?php
class AP_CustomTaxonomyTypeBase extends AP_Base {
	
	public static function GetTaxonomyTypeArray(){
		global $wpdb;
		$strTaxonomyArray = $wpdb->get_results("SELECT * FROM wp_ap_taxonomies", ARRAY_A);	
		return $strTaxonomyArray;
	}
	
	public static function GetConfigTaxonomyTypeArray(){
		$objXMLHandler = new AP_CoreCodeGenerator;
		$strTaxonomyArray = array();
		foreach($objXMLHandler->objConfigData->taxonomies->taxonomy as $mixTaxonomy){
			$strTaxonomyArray[] = unserialize($mixTaxonomy);			
		}
		return $strTaxonomyArray;
	}

}