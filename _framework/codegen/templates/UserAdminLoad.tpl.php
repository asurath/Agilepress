	/**
	* Initialize the Admin controls for the post type
	*/
	protected function AdminLoad() {

	///////////////////////////
	// CONTROLS
	///////////////////////////

<?php  foreach($mixExtraArgument as $objProperty){?>
		// <?php _e($objProperty['name'] . "\n");?>
		$this->ctrl<?php _e(AP_Base::SanitizeString($objProperty['name']));?> = AP_Control::GetControl("<?php _e($objProperty['control_type']);?>" , "<?php _e("ap-" . $objProperty['slug'])?>");
		$this->ctrl<?php _e(AP_Base::SanitizeString($objProperty['name']));?>->Name = "<?php _e($objProperty['name']);?>";
<?php }?>

<?php  $objUserTypesArray = AP_CustomUserTypeBase::GetUserTypeArray(); foreach($objUserTypesArray as $objUserType){?>
		// <?php _e($objUserType['name'] . " radio initiation \n");?>
		$this->ctrlUserType->AddItem('<?php _e($objUserType['name']);?>','<?php _e($objUserType['slug']);?>');
<?php }?>

		parent::AdminLoad();
	}
