<?php
class AP_RadioButtonBase extends AP_RadioControl {
	
	protected $strCSSClass = 'ap-radiobutton';
	
	public function GetControlHtml() {
		$strOutput = "";
		$strChecked = (0 == $this->Value) ? 'CHECKED' : "";
		$strOutput .= sprintf('<input name="%s" type="radio" id="radio-none" ' . $strChecked . ' value="0" ><label for="radio-none"> None </label><br>',
				$this->ID
		);
		foreach ($this->item_array as $index=>$list_item) {
			$strOutput .= sprintf('<input name="%s" id="%s" type="radio" value="%s" %s><label for="%s"> %s</label><br>',
				$this->ID,
				$list_item->Value,
				$list_item->Value,
				($list_item->Value == $this->Value) ? 'CHECKED' : null,
				$list_item->Value,
				$list_item->Name
			);
		}
		
		return $strOutput;
	}
}