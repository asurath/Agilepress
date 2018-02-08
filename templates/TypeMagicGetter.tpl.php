	/**Magic Method Getter for <?php _e($objClassHolder->Name) ?>
 	 */
 
	public function __get($strName) {
		switch($strName) {
<?php foreach($objClassHolder->Properties as $objProperty){?>
			case "<?php _e($objProperty->Name);?>" :
				return $this->GetMetaValue("ap-<?php _e(strtolower($objProperty->Name));?>");
<?php } ?>
 			default:
				return parent::__get($strName);
		}
	}
