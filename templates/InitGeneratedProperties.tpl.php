	protected function InitGeneratedProperties() {

<?php foreach($mixExtraArgument as $key => $objProperty){?>		$this-><?php _e(AP_Base::SanitizeString($objProperty['type'] . $objProperty['name'])); ?> = $this->GetMetaValue("ap-<?php _e($objProperty['slug']);?>");
<?php } ?>

	}
