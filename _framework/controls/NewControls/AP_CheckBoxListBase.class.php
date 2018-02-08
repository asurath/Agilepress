<?php
class AP_CheckBoxListBase extends AP_CheckBoxListControl {
	
	protected $strCSSClass = 'ap-checkbox';
	
	public function GetControlHtml() {
		$strOutput = "";
		foreach ($this->item_array as $intIndex=>$objListItem) {
			$strOutput .= sprintf('<label for="%s"><input type="checkbox" id="%s" name="%s" value="%s "%s>%s</label>',
				$this->ID . "[" . $intIndex . "]",
				$this->ID . "[" . $intIndex . "]",
				$this->ID . "[" . $intIndex . "]",
				$objListItem->Value,
				($objListItem->Value == $this->Value) ? ' SELECTED' : null,
				$objListItem->Name
			);
		}
		return $strOutput;
	}
}