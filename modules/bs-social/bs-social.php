<?php
/*
module Name: BS Social
Description: Social Login functionality
Version: 0.1.0
Author: Philamn Lau and Michael Lewis and Arun Surath
 Menu: ap-login-reg
*/

// Includes
define( 'BS_SOCIAL_VERSION', '0.1.0');
define( 'BS_SOCIAL_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define( 'BS_SOCIAL_PATH', plugin_dir_path(__FILE__) );
define( 'BS_SOCIAL_BASENAME', plugin_basename( __FILE__ ) );

// Init function will run on every page load
function bs_social_init(){
	require( AP_MODULES_PATH . '/bs-social/framework/prepend.inc.php');

}


require_once(str_replace("bs-social.php", "" , __FILE__) . "/AP_LoginRegistrationModulePage.class.php" ); 

add_action('admin_menu', array(AP_LoginRegistrationAdminPage , "Init"));
add_action('init','bs_social_init');
