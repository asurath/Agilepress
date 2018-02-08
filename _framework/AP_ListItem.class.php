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

	class AP_ListItem {
		protected $name = null;
		protected $value = null;
		protected $selected = false;
		protected $item_group = null;
		protected $enabled = true;
		
		public function __construct($name, $value, $selected = false, $item_group = null, $override_parameters = null) {
			$this->name = $name;
			$this->value = $value;
			$this->selected = $selected;
			$this->item_group = $item_group;
		}
		
		
		/////////////////////////
		// Public Properties: GET
		/////////////////////////
		public function __get($name) {
			switch ($name) {
				case "Name": return $this->name;
				case "Value": return $this->value;
				case "Selected": return $this->selected;
				case "ItemGroup": return $this->item_group;
				case "Enabled": return $this->enabled;
			}
		}
		
		/////////////////////////
		// Public Properties: SET
		/////////////////////////
		public function __set($name, $value) {
			switch ($name) {
				case "Name":
					return ($this->name = $value);
				case "Value":
					return ($this->value = $value);
				case "Selected":
					return ($this->selected = $value);
				case "ItemGroup":
					return ($this->item_group = $value);
				case "Enabled":
					return ($this->enabled = $value);
			}
		}
		
	}