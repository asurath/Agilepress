<?php
require (SOCIAL_LOGIN_BASE_CLASSES . '/FacebookLoginBase.class.php');

/**
 * The FacebookLogin class defined here contains any customized code
 * to modify functionality pertaining to Facebook login.
 *
 * For instance, this could be how a Login button renders.
 *
 */
class FacebookLogin extends FacebookLoginBase {
	
	public function RenderLogin($html) {
		echo '<a class="login fb" href="#" onclick="login()">'.$html.'</a>';
	}
}