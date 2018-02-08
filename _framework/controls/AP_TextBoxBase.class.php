<?php
/**
 *
 *  @package Controls
 *
 *	@property string 	$TextMode
 */
class AP_TextBoxBase extends AP_Control {
	
	protected $strCSSClass = 'ap-textbox';
	protected $strTextMode = AP_TextMode::SINGLELINE;
	
	public function GetControlHtml() {
		
		switch ($this->strTextMode) {
			case AP_TextMode::SINGLELINE:
			case AP_TextMode::PASSWORD:				
				$strInputType = $this->strTextMode == AP_TextMode::PASSWORD ? 'password' : 'text';
				
				$output = sprintf('<input type="%s" name="%s" id="%s" value="%s" %s %s %s />',
					$strInputType,
					$this->intID,
					$this->intID,
					$this->mixValue,
					$this->strCSSClass ? 'class="' . $this->strCSSClass . '"' : null,
					$this->strStyle ? 'style="' . $this->strStyle . '"' : null,
					$this->intTabIndex ? 'tabindex="' . $this->intTabIndex . '"' : null
				);
				
				break;
			case AP_TextMode::MULTILINE:
				$output = sprintf('<textarea name="%s" id="%s" %s %s %s>%s</textarea>',
					$this->intID,
					$this->intID,
					$this->strCSSClass ? 'class="' . $this->strCSSClass . '"' : null,
					$this->strStyle ? 'style="' . $this->strStyle . '"' : null,
					$this->intTabIndex ? 'tabindex="' . $this->intTabIndex . '"' : null,
					$this->mixValue
				);
				break;
		}
	
		return $output;
	}
	
	/////////////////////////
	// Public Properties: GET
	/////////////////////////
	public function __get($strName) {
		switch ($strName) {
			case "TextMode": return $this->strTextMode;
			default:
				return parent::__get($strName);
		}
	}
	
	/////////////////////////
	// Public Properties: SET
	/////////////////////////
	public function __set($strName, $mixValue) {
		switch ($strName) {
			case "TextMode":
				return ($this->strTextMode = $mixValue);
			default:
				parent::__set($strName, $mixValue);
				break;
		}
	}
}