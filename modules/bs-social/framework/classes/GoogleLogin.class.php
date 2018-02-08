<?php
require (SOCIAL_LOGIN_BASE_CLASSES . '/GoogleLoginBase.class.php');

/**
 * The GoogleLogin class defined here contains any customized code
 * to modify functionality pertaining to Google login.
 *
 * For instance, this could be how a Login button renders.
 *
 */
class GoogleLogin extends GoogleLoginBase {
	
	public function RenderLogin($html) {
		echo '<a class="login google" href="" onclick="window.open(\''.$this->google_client->createAuthUrl().'\', \'\' ,\'width=400,height=600\');">'.$html.'</a>';
	}
}