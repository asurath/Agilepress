	/** Magic Method Getter for <?php _e($objClassHolder->Name) ?> 
	*/
 
	public function __get($strName) {
		switch($strName) {
			<?php 
			foreach($objClassHolder->Properties as $objProperty){
				$objProperty->Name = str_replace(" ", "", $objProperty->Name);
				?>
	case "<?php _e($objProperty->Name);?>" :
			return $this-><?php _e($objProperty->Type . $objProperty->Name);?>;
			<?php } ?>
			default:
				return parent::__get($strName);
		}
	}
