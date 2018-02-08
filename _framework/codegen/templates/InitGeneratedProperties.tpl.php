	protected function InitGeneratedProperties() {

<?php foreach($mixExtraArgument as $key => $objProperty){?>		$this-><?php _e(AP_Base::SanitizeString($objProperty['type'] . $objProperty['name'])); ?> = $this->GetMetaValue("<?php echo AP_META_PREFIX ?><?php _e($objProperty['slug']);?>");
<?php } ?>
		return parent::InitGeneratedProperties();
	}
