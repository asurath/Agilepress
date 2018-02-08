<?php
	/**
	 *
	 *  @package Controls
	 *
	 *	@property string 	$Name
	 *	@property string	$Value
	 *	@property boolean	$Selected
	 *	@property string	$ItemGroup
	 *	@property boolean	$Enabled
	 */

	class AP_CheckBoxListItem {
		protected $strName = null;
		protected $mixValue = null;
		protected $boolSelected = false;
		protected $mixItemGroup = null;
		protected $boolEnabled = true;
		
		public function __construct($strName, $mixValue, $boolSelected = false, $mixItemGroup = null, $override_parameters = null) {
			$this->strName = $strName;
			$this->mixValue = $mixValue;
			$this->boolSelected = $boolSelected;
			$this->mixItemGroup = $mixItemGroup;
		}
		
		
		/////////////////////////
		// Public Properties: GET
		/////////////////////////
		public function __get($strName) {
			switch ($strName) {
				case "Name": return $this->strName;
				case "Value": return $this->mixValue;
				case "Selected": return $this->boolSelected;
				case "ItemGroup": return $this->mixItemGroup;
				case "Enabled": return $this->boolEnabled;
			}
		}
		
		/////////////////////////
		// Public Properties: SET
		/////////////////////////
		public function __set($strName, $mixValue) {
			switch ($strName) {
				case "Name":
					return ($this->strName = $mixValue);
				case "Value":
					return ($this->mixValue = $mixValue);
				case "Selected":
					return ($this->boolSelected = $mixValue);
				case "ItemGroup":
					return ($this->mixItemGroup = $mixValue);
				case "Enabled":
					return ($this->boolEnabled = $mixValue);
			}
		}
		
	}