<?php
abstract class AP_ApplicationBase {
	
	public static $strDocumentRoot;
	public static $strScriptFilename;
	public static $strScriptName;
	public static $strPathInfo;
	public static $strIPAddress;
	public static $strQueryString;
	public static $strRequestURI;
	public static $strOriginalRequestURI;
	public static $strReferer;
	public static $strUserAgent;
	public static $strOriginalReferer;
	public static $strOriginalEnteredDate;
	public static $strServerAddress;
	public static $intSessionID;
	public static $boolEnableSession = true;
	public static $intCodeGenVersion;
	
	
	
	
	/**
	 * Main function that runs the application. This is called by the extension
	 * plugin via AP_Application, NOT AP_ApplicationBase
	 */
	public function Run() {
		add_action( 'init', array($this, 'Initialize'), -1 );
		add_action( 'admin_init', array($this, 'AdminInit') );
		add_action( 'save_post', array($this, 'SavePost'), 1, 2);
		add_action( 'wp_head', array($this, 'RunTracking'));
		add_action( 'edit_user_profile_update',  array($this, 'SaveUser'));
		add_action( 'personal_options_update',  array($this, 'SaveUser'));
		add_action( 'edit_post',  array($this, 'SaveUser'));
		
		AP_Modules::RunAction('pre-role-reg');
		$this->RegisterUserRoles();
		AP_Modules::RunAction('post-role-reg');
		
		register_activation_hook(__FILE__,array($this, 'Install'));
	}
	
	// Loading functions
	protected function SiteSettings(){}
	protected function AdminSettings(){}
	protected function PreExitInit(){}

	public function RunTracking(){
		if(!is_404()){
			AP_Application::StoreTrackingData();
			return true;
		}
		else
			return false;
	}
	
	
	/**
	 * Customize installer in AP_Install->Run();
	 */
	public function Install() {
		$installer = new AP_Install();
		$installer->Run();
	}
	
	
	
	/**
	 * Runs on every page load
	 */
	public function Initialize() {
		
		// Include AgilePress Module code
		AP_Modules::RegisterModules();
		
		// Initialize functions
		AP_Modules::RunAction('pre-session-init');
		AP_Application::InitializePhpSession();
		AP_Modules::RunAction('post-session-init');
		
		AP_Modules::RunAction('pre-user-init');
		AP_Application::InitializeCurrentUser();
		AP_Modules::RunAction('post-user-init');
		
		AP_Modules::RunAction('pre-server-init');
		AP_Application::InitializeServerAddress();
		AP_Application::InitializeScriptInfo();
		AP_Modules::RunAction('post-server-init');
		
		add_action( 'wp_head', array($this, 'RunTracking'));
		
		$this->SiteSettings();
		
		// Register post types
		AP_Modules::RunAction('pre-post-reg');
		$this->RegisterPostTypes();
		AP_Modules::RunAction('post-post-reg');
		
		// Register taxonomies
		AP_Modules::RunAction('pre-tax-reg');
		$this->RegisterTaxonomies();
		AP_Modules::RunAction('post-tax-reg');
		
		// Handle custom functions before exiting
		$this->PreExitInit();


	}
	
	/**
	 * Dynamically runs through each custom post type and displays the metabox
	 */
	public function AdminInit() {
		
		$this->AdminSettings();
		
		$this->intCodeGenVersion = get_option("AP_CodeGen_Version");
		if(!$this->intCodeGenVersion)
			add_option("AP_CodeGen_Version");
	
		// Run through each post type and see if there is a admin init
		$strPostTypeArray = unserialize(AP_CUSTOM_POST_TYPES);
		foreach ($strPostTypeArray as $strPostType) {
			$o = new $strPostType(); // create an instance of the custom post type
			$o->MetaBoxRun();		
		}
		
		
		AP_Modules::RunAction('pre-user-reg');
		$strUserTypeArray = unserialize(AP_CUSTOM_USER_TYPES);
		foreach ($strUserTypeArray as $strUserType) {
			$o = new $strUserType(); // create an instance of the custom user type
			$o->MetaBoxRun();
		}
		AP_Modules::RunAction('post-user-reg');
		
	}
	
	/**
	 * Dynamically runs through each custom post type and calls the SavePost function
	 * @param integer $post_id
	 * @param WP_Post $post
	 */
	public function SavePost($post_id, $post) {
		$strPostTypeArray = unserialize(AP_CUSTOM_POST_TYPES);
		foreach ($strPostTypeArray as $strPostType) {
			$object_post = new $strPostType();
			$object_post->SavePost($post_id, $post);
		}
	}
	
	/**
	 * Dynamically runs through each custom user type and calls the SaveUser function
	 * @param integer $user_id
	 * @param WP_User $user
	 */
	public function SaveUser($user_id, $user) {
		$strUserTypeArray = unserialize(AP_CUSTOM_USER_TYPES);
		foreach ($strUserTypeArray as $user_type) {
			$object_user = new $user_type();
			$object_user->SaveUser($user_id, $user);
		}
	}
	
	/**
	 * Loops through all custom post types and registers each one
	 */
	protected function RegisterTaxonomies() {
	$taxonomies = unserialize(AP_TAXONOMIES);
		foreach ($taxonomies as $taxonomy) {
			$objTaxonomy = new $taxonomy();
			$objTaxonomy = unserialize($objTaxonomy->RegistrationArray);
			register_taxonomy($objTaxonomy['slug'],$objTaxonomy['post_types'], $objTaxonomy['arguments']);
		}
	}


	/**
	 * Functionality for registering a single custom user role
	 */
	protected function RegisterUserRole($strRoleArray) {
		if(!is_array($strRoleArray))
			return;
		return add_role($strRoleArray['slug'], $strRoleArray['name'], $strRoleArray['capabilities']);
	}
	
	

	/**
	 * Loops through all custom user roles and registers each one
	 */
	protected function RegisterUserRoles(){
		require_once(APEXT_CONSTANTS_PATH . "/generated/" . AP_PREFIX . "UserRolesGen.class.php"); 
		if(is_array(AP_UserRoleConstants::$strRolesArray))
			foreach(AP_UserRoleConstants::$strRolesArray as $strRoleArray){
				$this->RegisterUserRole($strRoleArray);
		}
	}
	

	
	
	/**
	 * Loops through all custom post types and registers each one
	 */
	protected function RegisterPostTypes() {
		$strPostTypeArray = unserialize(AP_CUSTOM_POST_TYPES);
		foreach ($strPostTypeArray as $strPostType) {
			if($strPostType == AP_PREFIX . "PostType")
				continue;
			$this->RegisterCustomPostType($strPostType);
		}
	}
	
	/**
	 * Creates the NonceField so we make sure any post request comes from the site and not an
	 * outside source.
	 */
	
	public static function NonceField() {
		wp_nonce_field( plugin_basename( __FILE__ ), __NONCE_KEY__ );
	}
	
	/**
	 * Registers an individual post type
	 * @param string $custom_post_type
	 */
	protected function RegisterCustomPostType($strAPPostType) {
	
		if (!class_exists($strAPPostType)){
			return;
		}

		$objPostType = new $strAPPostType();
		
		register_post_type( $objPostType->Slug, $objPostType->mixRegistrationArray);
	}
	
	/**
	 * Initialize the current user
	 */
	protected static function InitializeCurrentUser() {
		global $objUser;
		$objUser = new AP_User();
		$objUser->GetCurrentUser();
		
		AP_Application::$strRequestURI = $_SERVER['REQUEST_URI'];
		AP_Application::$strIPAddress = $_SERVER['REMOTE_ADDR'];
		AP_Application::$strReferer = $_SERVER['HTTP_REFERER'];
		AP_Application::$strUserAgent = $_SERVER['HTTP_USER_AGENT'];
		
		
		if (!isset($_SESSION['strOriginalReferer'])) {
			$_SESSION['strOriginalReferer'] = $_SERVER['HTTP_REFERER'];
			AP_Application::$strOriginalReferer = $SERVER['HTTP_REFERER'];
		}
		else
			AP_Application::$strOriginalReferer = $_SESSION['strOriginalReferer'];
		
		if (!isset($_SESSION['strOriginalEnteredDate'])) {
			$objDate =  AP_DateTime::Now();
			$_SESSION['strOriginalEnteredDate'] = $objDate->ToString(AP_DateTime::FORMAT_ISO);
			AP_Application::$strOriginalEnteredDate = $objDate->ToString(AP_DateTime::FORMAT_ISO);
		}
		else
			AP_Application::$strOriginalEnteredDate = $_SESSION['strOriginalEnteredDate'];
		
		if (!isset($_SESSION['strOriginalRequestURI'])) {
			$_SESSION['strOriginalRequestURI'] = $_SERVER['REQUEST_URI'];
			AP_Application::$strOriginalRequestURI = $SERVER['REQUEST_URI'];
		}
		else
			AP_Application::$strOriginalRequestURI = $_SESSION['strOriginalRequestURI'];
	}

	
	protected static function StoreTrackingData(){
		global $wpdb;
		global $objUser;
		$strTable_name = $wpdb->prefix . 'ap_tracker';
		$strQuery = "
		INSERT INTO $strTable_name
		(
		user_id,
		session_id,
		ip_address,
		user_agent,
		page_url,
		original_referer,
		original_request_uri,
		last_referer,
		entered_date
		)
		VALUES
		(
		'$objUser->ID',
		'" . AP_Application::$intSessionID . "',
		'" . AP_Application::$strIPAddress . "',
		'" . AP_Application::$strUserAgent . "',
		'" . AP_Application::$strRequestURI . "',
		'" . AP_Application::$strOriginalReferer . "',
		'" . AP_Application::$strOriginalRequestURI . "',
		'" . AP_Application::$strReferer . "',
		'" . AP_Application::$strOriginalEnteredDate . "'
		)
		ON DUPLICATE KEY UPDATE
		user_id = '$objUser->ID',
		ip_address = '" . AP_Application::$strIPAddress . "',
		user_agent = '" . AP_Application::$strUserAgent . "',
		page_url = '" . AP_Application::$strRequestURI . "',
		last_referer = '" . AP_Application::$strReferer . "',
		entered_date = '" . AP_Application::$strOriginalEnteredDate . "';
		";
		$wpdb->query($strQuery);
	}
	
	/**
	 * Called by AP_Application::Initialize() to initialize the AP_Application::$ServerAddress setting.
	 * @return void
	 */
	protected static function InitializeServerAddress() {
		if (array_key_exists('LOCAL_ADDR', $_SERVER))
			AP_Application::$strServerAddress = $_SERVER['LOCAL_ADDR'];
		else if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
			AP_Application::$strServerAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if (array_key_exists('SERVER_ADDR', $_SERVER))
			AP_Application::$strServerAddress = $_SERVER['SERVER_ADDR'];
	}
	
	/**
	 * Called by AP_Application::Initialize() to initialize the various
	 * AP_Application settings on ScriptName, DocumentRoot, etc.
	 * @return void
	 */
	protected static function InitializeScriptInfo() {
		// Setup strScriptFilename and ScriptName
		AP_Application::$strScriptFilename = $_SERVER['SCRIPT_FILENAME'];
		AP_Application::$strScriptName = $_SERVER['SCRIPT_NAME'];
			
		// Ensure both are set, or we'll have to abort
		if (!AP_Application::$strScriptFilename) {
			throw new Exception('Error on AP_Application::Initialize() - ScriptFilename or ScriptName was not set');
		}
	
		// Setup PathInfo and QueryString (if applicable)
		AP_Application::$strPathInfo = array_key_exists('PATH_INFO', $_SERVER) ? trim($_SERVER['PATH_INFO']) : null;
		AP_Application::$strQueryString = array_key_exists('QUERY_STRING', $_SERVER) ? $_SERVER['QUERY_STRING'] : null;
			
		// Setup DocumentRoot
		AP_Application::$strDocumentRoot = trim(__DOCROOT__);
	}
	
	protected static function InitializePhpSession() {
		// Go ahead and start the PHP session if we have set EnableSession to true
		if (AP_Application::$boolEnableSession) session_start();
		
		AP_Application::$intSessionID = session_id();
	}
	
	public static function RunPage($strPageName){
		if( null !== APEXT_PAGES_PATH && is_dir(APEXT_PAGES_PATH)){
			$strFileArray = scandir(APEXT_PAGES_PATH);
			foreach($strFileArray as $strFile){
				if(strtolower($strFile) == strtolower(AP_PREFIX . $strPageName . "Page.class.php")){
					require_once(APEXT_PAGES_PATH . $strFile);
					$strClassName = AP_PREFIX . $strPageName . "Page";
					$objNewPage = New $strClassName;
					$objNewPage->Run();
				}
			}
		}
	}

	public static function IncludePages(){
		if( null !== APEXT_PAGES_PATH && is_dir(APEXT_PAGES_PATH)){
			$strFileArray = scandir(APEXT_PAGES_PATH);
			foreach($strFileArray as $strFile){
				require_once(APEXT_PAGES_PATH . $strFile);
			}
		}

	}
	
	////////////////////////////////////////////////////
	//
	////////////////////////////////////////////////////

	/**
	 * CoreInstall is the only function that is run via AP_ApplicationBase call. All other functions
	 * should be run by calling AP_Application.
	 */
	public static function CoreInstall(){

		// Store the plugin base name in WP Options, so extension plugins have access
		
		$strSettingsArray = get_option('agilepress_settings');
		if (!is_array($strSettingsArray))
			$strSettingsArray = array();
		$strSettingsArray['basename'] = AP_BASENAME;
		update_option('agilepress_settings', $strSettingsArray);
		
		// Create AP database tables
		
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$table_name = $wpdb->prefix . "ap_tracker";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE `$table_name` (
			`id` int NOT NULL AUTO_INCREMENT,
			`user_id` int(11) DEFAULT NULL,
			`session_id` varchar(32) DEFAULT NULL UNIQUE,
			`ip_address` varchar(15) DEFAULT NULL,
			`user_agent` varchar(512) DEFAULT NULL,
			`page_url` varchar(500) DEFAULT NULL,
			`original_referer` varchar(500) DEFAULT NULL,
			`original_request_uri` varchar(500) DEFAULT NULL,
			`last_referer` varchar(500) DEFAULT NULL,
			`entered_date` datetime DEFAULT NULL,
			PRIMARY KEY (`id`)
			);
			CREATE INDEX sess_idx ON $table_name (session_id);
			";
			dbDelta( $sql );
		}
		$table_name = $wpdb->prefix . "ap_user_type";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE `$table_name` (
					`id` int NOT NULL AUTO_INCREMENT,
					`singular_name` varchar(256) DEFAULT NULL,
					`description` varchar(512) DEFAULT NULL,
					`name` varchar(256) DEFAULT NULL,
					`ap_id` varchar(256) DEFAULT NULL UNIQUE,
					`slug` varchar(256) DEFAULT NULL,
					PRIMARY KEY (`id`)
					
			)";
			dbDelta( $sql );
		}
		$table_name = $wpdb->prefix . "ap_user_field";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE `$table_name` (
					`user_type_id`  int (30) DEFAULT NULL,
					`user_field_id` int (30) DEFAULT NULL,
					PRIMARY KEY (`user_type_id`,`user_field_id`)
			)";
			dbDelta( $sql );
		}
		$table_name = $wpdb->prefix . "ap_user_type_field";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE `$table_name` (
			`id` int (32) NOT NULL AUTO_INCREMENT,
			`name` varchar(256) DEFAULT NULL,
			`description` varchar(512) DEFAULT NULL,
			`control_type` varchar(512) DEFAULT NULL,
			`slug` varchar(256) DEFAULT NULL UNIQUE,
			`global` BIT DEFAULT NULL,
			PRIMARY KEY (`id`)
			)";
			dbDelta( $sql );
		}
		$table_name = $wpdb->prefix . "ap_post_type";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE `$table_name` (
			`id` int NOT NULL AUTO_INCREMENT,
			`singular_name` varchar(256) DEFAULT NULL,
			`description` varchar(512) DEFAULT NULL,
			`name` varchar(256) DEFAULT NULL,
			`slug` varchar(256) DEFAULT NULL,
			`ap_id` varchar(256) DEFAULT NULL UNIQUE,
			PRIMARY KEY (`id`)
			)";
			dbDelta( $sql );
		}
		$table_name = $wpdb->prefix . "ap_post_field";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE `$table_name` (
				`post_type_id`  int (30) DEFAULT NULL,
				`post_field_id` int (30) DEFAULT NULL,
				PRIMARY KEY (`post_type_id`,`post_field_id`)
		)";
				dbDelta( $sql );
		}
		$table_name = $wpdb->prefix . "ap_post_type_field";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE `$table_name` (
				`id` int NOT NULL AUTO_INCREMENT,
				`name` varchar(256) DEFAULT NULL,
				`description` varchar(512) DEFAULT NULL,
				`control_type` varchar(512) DEFAULT NULL,
				`slug` varchar(256) DEFAULT NULL UNIQUE,
				`global` BIT DEFAULT NULL,
				PRIMARY KEY (`id`)
		)";
		dbDelta( $sql );
		}
		$table_name = $wpdb->prefix . "ap_taxonomies";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE `$table_name` (
			`id` int NOT NULL AUTO_INCREMENT,
			`slug` varchar(256) DEFAULT NULL,
			`ap_id` varchar(256) DEFAULT NULL,
			`post_types` varchar(512) DEFAULT NULL,
			`arguments` varchar(2024) DEFAULT NULL,
			`description` varchar(512) DEFAULT NULL,
			PRIMARY KEY (`id`)
			)";
			dbDelta( $sql );
		}
		$table_name = $wpdb->prefix . "ap_modules";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE `$table_name` (
			`id` int NOT NULL AUTO_INCREMENT,
			`slug` varchar(256) DEFAULT NULL,
			PRIMARY KEY (`id`)
			)";
			dbDelta( $sql );
		}

		$table_name = $wpdb->prefix . "ap_user_roles";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE TABLE `$table_name` (
			`id` int NOT NULL AUTO_INCREMENT,
			`name` varchar(256) DEFAULT NULL,
			`slug` varchar(256) DEFAULT NULL UNIQUE,
			`description` varchar(512) DEFAULT NULL,
			`capabilities` varchar(2056) DEFAULT NULL,
			PRIMARY KEY (`id`)
			)";
			dbDelta( $sql );
		}

		}
}