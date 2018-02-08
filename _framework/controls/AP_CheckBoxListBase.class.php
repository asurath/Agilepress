<?php
class AP_CheckBoxListBase extends AP_CheckBoxListControl {
	
	protected $strCSSClass = 'ap-checkboxlist';
	
	public function GetControlHtml() {
		global $post;
		if($post){
			$strType = 'post';
			$intID = $post->ID;
		}
		global $profileuser;
		if($profileuser){
			$strType = 'user';
			$intID = $profileuser->ID;
		}
		$strOutput = "<div class='$this->strCSSClass'>";
		foreach ($this->objItemArray as $intIndex=>$objListItem) {
			if(in_array($objListItem->Value, get_metadata($strType,$intID, $this->intID,true))){
			$strOutput .= sprintf('<label for="%s"><input type="checkbox" id="%s" name="%s" value="%s "%s>%s</label><br>',
				$this->intID . "[" . $intIndex . "]",
				$this->intID . "[" . $intIndex . "]",
				$this->intID . "[" . $intIndex . "]",
				$objListItem->Value,
				"CHECKED",
				$objListItem->Name
			);
			}
		}
		foreach ($this->objItemArray as $intIndex=>$objListItem) {
			if(!in_array($objListItem->Value, get_post_meta($post->ID, $this->intID,true))){
			$strOutput .= sprintf('<label for="%s"><input type="checkbox" id="%s" name="%s" value="%s ">%s</label><br>',
					$this->intID . "[" . $intIndex . "]",
					$this->intID . "[" . $intIndex . "]",
					$this->intID . "[" . $intIndex . "]",
					$objListItem->Value,
					$objListItem->Name
			);
			}
		}
		$strOutput .= "</div>";
		return $strOutput;
	}
}