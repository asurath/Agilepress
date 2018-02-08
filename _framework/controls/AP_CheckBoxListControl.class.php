<?php
	/**
	 *
	 *  @package Controls
	 *
	 *	@property integer 	$SelectedIndex (Read-only)
	 *	@property string	$SelectedValue (Read-only)
	 */
	class AP_CheckBoxListControl extends AP_Control {
		
		protected $objItemArray = array();
		protected $intSelectedIndexArray = array();
		
		public function AddItem($strName, $mixValue = null, $boolSelected = false, $mixItemGroup = null, $boolEnabled = true) {
			$objListItem = new AP_CheckBoxListItem($strName, $mixValue, $boolSelected, $mixItemGroup, $boolEnabled);
			array_push($this->objItemArray, $objListItem);
			if ($boolSelected)
				$this->intSelectedIndex = count($this->objItemArray) - 1;
		}
		
		public function GetControlHtml() {}
		
		/////////////////////////
		// Public Properties: GET
		/////////////////////////
		public function __get($strName) {
			switch ($strName) {
				case "SelectedIndex": return $this->$intSelectedIndexArray;
				case "SelectedValue": 
					$objTempArray = array();
					foreach($this->$intSelectedIndexArray as $intIndex)
						$objTempArray[] = $this->objItemArray[$intIndex];
					return $objTempArray;
					break;
				case "ItemArray" :
					return $this->objItemArray;
				default:
					return parent::__get($strName);
			}
		}
	}