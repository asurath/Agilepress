<?php
class AP_ListBoxBase extends AP_ListControl {
	
	protected $strCSSClass = 'ap-listbox';
	
	public function GetControlHtml() {
		$strOutput = sprintf('<select name="%s" id="%s" %s%s%s>',
			$this->intID,
			$this->intID,
			$this->strCSSClass ? ' class="' . $this->strCSSClass . '"' : null,
			$this->strStyle ? ' style="' . $this->strStyle . '"' : null,
			$this->intTabIndex ? ' tabindex="' . $this->intTabIndex . '"' : null
		);
		
		foreach ($this->objItemArray as $intIndex=>$objListItem) {
			
			$strOutput .= sprintf('<option value="%s"%s>%s</option>',
				$objListItem->Value,
				($intIndex == $this->SelectedIndex || $objListItem->Value == $this->mixValue) ? ' SELECTED' : null,
				$objListItem->Name
			);
		}
		
		$strOutput .= '</select>';
		
		return $strOutput;
	}
}