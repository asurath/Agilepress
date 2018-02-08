
	///////////////////////////////////////////////
	// Template for Custom Display Post Function //
	///////////////////////////////////////////////
	
	public static function DisplayPost(AP_PostItemBase $objAPPostItem) {
		if(!$objAPPostItem instanceof AP_PostItemBase)
			self::error('invalid argument passed to AP_DisplayBase::DisplayPost, $objAPPostItem must be an object extended from AP_PostItemBase');
		$objAPPostItem->Excerpt = ($objAPPostItem->Excerpt) ? $objAPPostItem->Excerpt : AP_DisplayBase::ContentToExcerpt($objAPPostItem->Content, 50);
		echo "<div class='post-item-wrap' style='background:white; padding:20px; width:100%;'>";
		echo "<a href='../?p=$objAPPostItem->ID'><h1 class='post-title'>" . $objAPPostItem->Title . "</h1></a><br>";
		echo "<a href='../author/" . $objAPPostItem->Author->Slug . "'><h3 class='post-author'>" . $objAPPostItem->Author->NiceName . "</h3></a><br>";
		echo "<p class='post-excerpt'>" . $objAPPostItem->Excerpt . "</p><br>";
<?php
if(is_array($mixExtraArgument)) foreach($mixExtraArgument as $objProperty){ ?>		echo "<span>" . $objAPPostItem-><?php echo $objProperty->Name . ' . "</span>";' . "\n"; }
?>		echo "<img class='post-thumbnail' src='" . $objAPPostItem->ThumbnailURL . "'><br>";
		echo "</div>";
	}