	/**Magic Method Setter for <?php _e($objClassHolder->Name) ?>
	 */
 
	public function __set($strName, $strValue) {
		switch($strName) {
<?php foreach($objClassHolder->Properties as $objProperty){?>
			case "<?php _e($objProperty->Name);?>" :
				return $this->SetMetaValue("ap-<?php _e(strtolower($objProperty->Name));?>", $strValue);
<?php } ?>
 			default:
				return parent::__set($strName, $strValue);
		}
	}