<?php
/**
 * 
 * 
 * 
 * @author AgilePress Core Development Team
 *
 */


class AP_DisplayBase extends AP_Base {
	
	public static function ContentToExcerpt($strContent, $intWordCount=50) {
				$strContent = strip_shortcodes($strContent);
				$strContent = strip_tags($strContent);
				$strContentArray = explode(' ', $strContent);
				if (count($strContentArray) > $intWordCount) {
					$k = $intWordCount;
					$boolDotDotDot = 1;
				} else {
					$k = count($strContentArray);
					$boolDotDotDot = 0;
				}
				$strExcerpt = '';
				for ($i=0; $i<$k; $i++) {
					$strExcerpt .= $strContentArray[$i] . ' ';
				}
				$strExcerpt .= ($boolDotDotDot) ? '...' : '';
				return $strExcerpt;
	}
	
	
	public static function DisplayPost($objAPPostItem){
		if(!$objAPPostItem instanceof AP_PostItemBase)
			self::error('invalid argument passed to AP_DisplayBase::DisplayPost, $objAPPostItem must be an object extended from AP_PostItemBase');
		$objAPPostItem->Excerpt = ($objAPPostItem->Excerpt) ? $objAPPostItem->Excerpt : AP_DisplayBase::ContentToExcerpt($objAPPostItem->Content, 50);
		echo "<div class='post-item-wrap' style='background:white; padding:20px; width:100%;'>";
		echo "<a href='../?p=$objAPPostItem->ID'><h1 class='post-title'>" . $objAPPostItem->Title . "</h1></a><br>";
		echo "<a href='../author/" . $objAPPostItem->Author->Slug . "'><h3 class='post-author'>" . $objAPPostItem->Author->NiceName . "</h3></a><br>";
		echo "<p class='post-excerpt'>" . $objAPPostItem->Excerpt . "</p><br>";
		echo "<img class='post-thumbnail' src='" . $objAPPostItem->ThumbnailURL . "'><br>";
		echo "</div>";
	}
	
	public static function DisplayPostCollection($objPostCollection) {
		if(!$objPostCollection instanceof AP_PostCollectionBase)
				self::error('invalid argument passed to AP_DisplayBase::DisplayPostCollection, $objPostCollectionArray must be an object extended from AP_PostCollectionBase');
		foreach($objPostCollection->PostArray as $objAPPostItem){
			AP_DisplayBase::DisplayPost($objAPPostItem);
		}
		
	
		
	}
	
	
	
}