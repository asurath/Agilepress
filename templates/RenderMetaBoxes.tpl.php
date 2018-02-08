	/**
	 * Renders metaboxes for the custom post type
	 * @see AP_CustomPostTypeBase::RenderMetaBoxes()
	 */
	protected function RenderMetaBoxes($metabox_id, $metabox_index) {
	
	///////////////////////////
	// Meta Box Render Calls //
	///////////////////////////
	
<?php  foreach($mixExtraArgument as $objProperty){?>
		$this->ctrl<?php _e(AP_Base::SanitizeString($objProperty['name']));?>->RenderWithName();
<?php }?>
	
}
