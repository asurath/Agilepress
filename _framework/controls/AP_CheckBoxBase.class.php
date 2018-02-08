<?php
class AP_CheckBoxBase extends AP_Control {
	
	protected $strCSSClass = 'ap-checkbox';
	protected $boolDefaultChecked = false;

	public function GetControlHtml() {
		$strChecked = ($this->boolDefaultChecked) ? 'checked="checked"':'';
		$strChecked = ($this->Value) ? 'checked="checked"':'';
		$strOutput = sprintf('<input type="checkbox" %s name="%s" id="%s" Value="1" %s %s %s />',
				$strChecked,
				$this->intID,
				$this->intID,
				$this->strCSSClass ? 'class="' . $this->strCSSClass . '"' : null,
				$this->strStyle ? 'style="' . $this->strStyle . '"' : null,
				$this->intTabIndex ? 'tabindex="' . $this->intTabIndex . '"' : null
		);
		
		return $strOutput;
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