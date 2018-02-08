<?php
class AP_CheckBoxBase extends AP_Control {
	
	protected $strCSSClass = 'ap-checkbox';
	protected $boolDefaultChecked = false;

	public function GetControlHtml() {
		$strChecked = ($this->boolDefaultChecked) ? 'checked="checked"':'';
		$strChecked = ($this->Value) ? 'checked="checked"':'';
		$output = sprintf('<input type="checkbox" %s name="%s" id="%s" Value="1" %s %s %s />',
				$strChecked,
				$this->id,
				$this->id,
				$this->strCSSClass ? 'class="' . $this->strCSSClass . '"' : null,
				$this->style ? 'style="' . $this->style . '"' : null,
				$this->tab_index ? 'tabindex="' . $this->tab_index . '"' : null
		);
		
		return $output;
	}
	
	
	public function __get($strName){
		switch ($strName){
			case "IsChecked" :
				return $this->boolDefaultChecked;
			default:
				return parent::__get($strName);
		}		
	}
	
	public function __set($strName,$mixValue){
		switch ($strName){
			case "IsChecked" :
				return $this->boolDefaultChecked = $mixValue;
			default:
				parent::__set($strName, $mixValue);
		}
	}
	
	
}