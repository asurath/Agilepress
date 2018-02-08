	/** Magic Method Setter for <?php _e($objClassHolder->Name) ?> 
	*/
 
	public function __set($strName, $mixValue) {
		switch($strName) {
			<?php
			foreach($objClassHolder->Properties as $objProperty){
				$objProperty->Name = str_replace(" ", "", $objProperty->Name);
				?>
	case "<?php _e($objProperty->Name);?>" :
			return $this-><?php _e($objProperty->Type . $objProperty->Name);?> = $mixValue;
			<?php } ?>
			default:
				return parent::__set($strName, $mixValue);
		}
	}
