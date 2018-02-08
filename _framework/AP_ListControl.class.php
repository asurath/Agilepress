<?php
	/**
	 *
	 *  @package Controls
	 *
	 *	@property integer 	$SelectedIndex (Read-only)
	 *	@property string	$SelectedValue (Read-only)
	 */
	class AP_ListControl extends AP_Control {
		
		protected $item_array = array();
		protected $selected_index;
		
		public function AddItem($name, $value = null, $selected = false, $item_group = null, $enabled = true) {
			$list_item = new AP_ListItem($name, $value, $selected, $item_group, $enabled);
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
				case "SelectedIndex": return $this->$selected_index;
				case "SelectedValue": return $this->item_array[$this->$selected_index];
				default:
					return parent::__get($name);
			}
		}
	}