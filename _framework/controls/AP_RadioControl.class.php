<?php
	/**
	 *
	 *  @package Controls
	 *
	 *	@property integer 	$SelectedIndex (Read-only)
	 *	@property string	$SelectedValue (Read-only)
	 */
	class AP_RadioControl extends AP_Control {
		
		protected $item_array = array();
		protected $selected_index;
		protected $mixValue = null; 
		
		public function AddItem($name, $value = null, $selected = false, $item_group = null, $enabled = true) {
			$list_item = new AP_RadioItem($name, $value, $selected, $item_group, $enabled);
			array_push($this->item_array, $list_item);
			if ($selected)
				$this->selected_index = count($this->item_array) - 1;
		}
		
		public function GetControlHtml() {}
		
		/////////////////////////
		// Public Properties: GET
		/////////////////////////
		public function __get($name) {
			switch ($name) {
				case "SelectedIndex": 
					return $this->$selected_index;
				case "SelectedValue": 
					return $this->item_array[$this->$selected_index];
				case "Value":
					return $this->mixValue;
				default:
					return parent::__get($name);
			}
		}
		
		public function __set($strName, $mixValue){
			switch ($strName){
				case "Value" :
					return $this->mixValue = $mixValue;
			}
		}
	}