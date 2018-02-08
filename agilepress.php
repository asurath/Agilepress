<?php
/*
Plugin Name: AgilePress
Description: An extensible object oriented WordPress framework that allows quicker development by wrapping WordPress functionality in easy-to-use classes.
Version: 0.1.0
Author: AgilePress Core Development Team
*/

// ==================================
// 	DEFINITIONS
// ==================================

//Prefixes and Slugs
define("AP_PREFIX", 'AP_');
define("AP_META_PREFIX", 'gfe_');
define("CONFIG_NAME", 'agilepress-config.xml' );

// Includes
define( 'AP_VERSION', '0.1.0');
define( 'AP_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define( 'AP_PATH', plugin_dir_path(__FILE__) );
define( 'AP_BASENAME', plugin_basename( __FILE__ ) );
define( 'AP_PLUGIN_PATH' , ABSPATH . 'wp-content/plugins/' );


// Folder Paths
define( 'AP_FRAMEWORK_PATH', AP_PATH . '/_framework' );
define( 'AP_CORE_CONTROLS_PATH', AP_FRAMEWORK_PATH . '/controls' );
define( 'AP_MEMBER_PATH', AP_PATH . '/member' );
define( 'AP_CONTROLS_PATH', AP_PATH . '/controls');
define( 'AP_CSS', '/wp-content/plugins/agilepress/css/');
define( 'AP_JS', '/wp-content/plugins/agilepress/js/');

define( 'AP_CORE_PATH', AP_FRAMEWORK_PATH . '/core/' );
define( 'AP_POSTS_PATH', AP_FRAMEWORK_PATH . '/posts/' );
define( 'AP_TAXONOMY_PATH',  AP_FRAMEWORK_PATH . '/taxonomies/' );
define( 'AP_POST_TYPE_PATH', AP_FRAMEWORK_PATH . '/post-type/' );
define( 'AP_USERS_PATH', AP_FRAMEWORK_PATH . '/users/' );
define( 'AP_USER_TYPE_PATH', AP_FRAMEWORK_PATH . '/user-type/' );
define( 'AP_CONTROL_PATH', AP_FRAMEWORK_PATH . '/controls/' );
define( 'AP_PAGE_PATH', AP_FRAMEWORK_PATH . '/pages/' );
define( 'AP_ADMIN_PAGE_PATH', AP_FRAMEWORK_PATH . '/admin-pages/' );
define( 'AP_DISPLAY_PATH', AP_FRAMEWORK_PATH . '/display/' );
define( 'AP_CODE_GEN_PATH', AP_FRAMEWORK_PATH . '/codegen/' );
define( 'AP_DISPLAY_PATH', AP_PATH . '/_framework/display/' );
define( 'AP_TEMPLATE_PATH', AP_CODE_GEN_PATH . '/templates/' );
define( 'AP_MODULES_PATH', AP_PATH . '/modules' );


// ================================== 
// 	REQUIRED FILES
// ==================================

// Constants
require_once( AP_CORE_PATH . '/_constants.php');

// Application include
require_once( AP_CORE_PATH . '/AP_ApplicationBase.class.php' );

// Framework files
require_once( AP_CORE_PATH . '/AP_Base.class.php' );
require_once( AP_POST_TYPE_PATH . '/AP_CustomPostTypeBase.class.php' );
require_once( AP_TAXONOMY_PATH . '/AP_CustomTaxonomyTypeBase.class.php' );
require_once( AP_USER_TYPE_PATH . '/AP_CustomUserTypeBase.class.php' );
require_once( AP_CORE_PATH . '/AP_DateTime.class.php' );
require_once( AP_CORE_PATH . '/AP_DateTimeSpan.class.php' );
require_once( AP_CORE_PATH . '/AP_Email.class.php' );
require_once( AP_CORE_PATH . '/AP_Exception.class.php' );
require_once( AP_CORE_PATH . '/AP_InstallBase.class.php' );
require_once( AP_CORE_PATH . '/AP_Modules.class.php' );
require_once( AP_USERS_PATH . '/AP_UserBase.class.php' );
require_once( AP_USERS_PATH . '/AP_UserQueryBase.class.php' );
require_once( AP_POSTS_PATH . '/AP_PostItemBase.class.php');
require_once( AP_POSTS_PATH . '/AP_PostQueryBase.class.php');
require_once( AP_POSTS_PATH . '/AP_PostCollectionBase.class.php');
require_once( AP_USERS_PATH . '/AP_UserCollectionBase.class.php');
require_once( AP_PAGE_PATH . '/AP_PageBase.class.php');
require_once( AP_ADMIN_PAGE_PATH . '/AP_AdminPageBase.class.php');
require_once( AP_ADMIN_PAGE_PATH . '/AP_UserAdminPage.class.php');
require_once( AP_ADMIN_PAGE_PATH . '/AP_PostAdminPage.class.php');
require_once( AP_ADMIN_PAGE_PATH . '/AP_HomeAdminPage.class.php');
require_once( AP_ADMIN_PAGE_PATH . '/AP_SettingsAdminPage.class.php');
require_once( AP_ADMIN_PAGE_PATH . '/AP_CodeGenAdminPage.class.php');
require_once( AP_ADMIN_PAGE_PATH . '/AP_TaxonomyAdminPage.class.php');
require_once( AP_ADMIN_PAGE_PATH . '/AP_ModuleAdminPage.class.php');
require_once( AP_DISPLAY_PATH . '/AP_DisplayBase.class.php');
require_once( AP_CODE_GEN_PATH . '/_io.php');
require_once( AP_CODE_GEN_PATH . '/AP_CoreCodeGenerator.class.php');
require_once( AP_CODE_GEN_PATH . '/AP_PseudoClassDefinitions.php');


// Core Controls
require_once( AP_CORE_CONTROLS_PATH . '/AP_Control.class.php' );
require_once( AP_CORE_CONTROLS_PATH . '/AP_TextBoxBase.class.php' );
require_once( AP_CORE_CONTROLS_PATH . '/AP_ListControl.class.php' );
require_once( AP_CORE_CONTROLS_PATH . '/AP_ListBoxBase.class.php' );
require_once( AP_CORE_CONTROLS_PATH . '/AP_ListItem.class.php' );
require_once( AP_CORE_CONTROLS_PATH . '/AP_RadioControl.class.php' );
require_once( AP_CORE_CONTROLS_PATH . '/AP_RadioButtonBase.class.php' );
require_once( AP_CORE_CONTROLS_PATH . '/AP_RadioItem.class.php' );
require_once( AP_CORE_CONTROLS_PATH . '/AP_CheckBoxBase.class.php' );
require_once( AP_CORE_CONTROLS_PATH . '/AP_CheckBoxListControl.class.php');
require_once( AP_CORE_CONTROLS_PATH . '/AP_CheckBoxListItem.class.php' );
require_once( AP_CORE_CONTROLS_PATH . '/AP_CheckBoxListBase.class.php' );

// Core Install - DO NOT MOVE TO AP_ApplicationBase

	// AgilePress menu registration
	register_activation_hook(__FILE__,array(AP_ApplicationBase, 'CoreInstall'));
	
	add_action('admin_menu', 'AP_MainMenu');
	
	add_action('admin_menu', array( AP_HomeAdminPage, 'Init'));
	add_action('admin_menu', array( AP_SettingsAdminPage, 'Init'));
	add_action('admin_menu', array( AP_PostAdminPage, 'Init'));
	add_action('admin_menu', array( AP_UserAdminPage, 'Init'));
	add_action('admin_menu', array( AP_TaxonomyAdminPage, 'Init'));
	add_action('admin_menu', array( AP_ModuleAdminPage, 'Init'));
	add_action('admin_menu', array( AP_CodeGenAdminPage, 'Init'));
	
	wp_enqueue_style('ap-admin-stylesheet', AP_CSS . 'admin-styles.css');
	wp_enqueue_style('ap-font-awesome', 'http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css');
	wp_enqueue_script('ap-jquery', AP_JS . 'jquery.min.js');
	
	function AP_MainMenu() {
		add_menu_page('Agile Press Manager', 'AgilePress', null, 'ap-settings-home', array('AP_HomeAdminPage', 'PageCreate'), plugins_url() . "/agilepress/images/APICO2.png");
	}

	// ==================================
	// 	AGILEPRESS MUST LOAD FIRST
	// 	Extension plugins using AgilePress need classes loaded
	// ==================================

	add_action( 'activated_plugin', 'AP_AgilePressLoadFirst' );
	
	function AP_AgilePressLoadFirst()
	{
		$strPath = str_replace( WP_PLUGIN_DIR . '/', '', __FILE__ );
		if ( $strPluginsArray = get_option( 'active_plugins' ) ) {
			if ( $intKey = array_search( $strPath, $strPluginsArray ) ) {
				array_splice( $strPluginsArray, $intKey, 1 );
				array_unshift( $strPluginsArray, $strPath );
				update_option( 'active_plugins', $strPluginsArray );
			}
		}
}
