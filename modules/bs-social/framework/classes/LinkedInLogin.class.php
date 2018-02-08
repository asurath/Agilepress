<?php
require (SOCIAL_LOGIN_BASE_CLASSES . '/LinkedInLoginBase.class.php');

/**
 * The FacebookLogin class defined here contains any customized code
 * to modify functionality pertaining to Facebook login.
 *
 * For instance, this could be how a Login button renders.
 *
 */
class LinkedInLogin extends LinkedInLoginBase {
	
	public function RenderLogin($html) {
		$strRedirect = array_key_exists('redirect', $_GET) ? $_GET['redirect'] : null;
		$strUrl = $this->GetAuthUrl() . '&redirect=' . $strRedirect;
		echo '<a class="login linkedin" href="#" onclick="window.open(\''.$strUrl.'\',\'linkedin_auth\',\'width=400,height=600\');">'.$html.'</a>';
	}
}