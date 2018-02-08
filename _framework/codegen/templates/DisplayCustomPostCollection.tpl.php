
	////////////////////////////////////////////////
	// Wrapper for Custom Display Post Collection //
	////////////////////////////////////////////////

	public static function DisplayPostCollection(AP_PostCollectionBase $objPostCollection) {
		if(!$objPostCollection instanceof AP_PostCollectionBase)
				self::error('invalid argument passed to AP_DisplayBase::DisplayPostCollection, $objPostCollectionArray must be an object extended from AP_PostCollectionBase');
		foreach($objPostCollection->PostArray as $objAPPostItem){
			self::DisplayPost($objAPPostItem);
		}
	}