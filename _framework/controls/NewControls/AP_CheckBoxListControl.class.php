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
		protected $intSelectedIndex;
		
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
				case "SelectedIndex": return $this->$intSelectedIndex;
				case "SelectedValue": return $this->objItemArray[$this->$intSelectedIndex];
				default:
					return parent::__get($strName);
			}
		}
	}