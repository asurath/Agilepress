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
	}
