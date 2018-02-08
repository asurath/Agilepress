<?php
require (SOCIAL_LOGIN_BASE_CLASSES . '/SocialLoginBase.class.php');

/**
 * The SocialLogin class defined here contains any customized code
 * for social login pertaining to your specific application.
 * 
 * For instance, checking if an email exists in your system is
 * custom to your data structure and querying. Therefore, that 
 * function is meant to be overridden in this class.
 *
 */
class SocialLogin extends SocialLoginBase {
	
	/**
	 * checks if the account exists
	 * @param string $email
	 */
	protected function AccountExists($email) {
		return false;
	}
}