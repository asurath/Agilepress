<?php

/**
 * @property BS_Company $company
 * @property BS_MemberDisplay $display
 *
 */
class BS_Member extends BS_Base {
	
	const SESSION_ROOT = "bs_session";
	const SESSION_KEY_SESSION_ID = "bs_session_id";
	const SESSION_KEY_ORIGINAL_REFERRER = "bs_original_referrer";
	const SESSION_KEY_CURRENT_PAGE = "bs_current_page";
	const SESSION_KEY_LAST_PAGE = "bs_last_page";
	const SESSION_KEY_LOGIN_REDIRECT = "bs_login_redirect";
	const SESSION_KEY_PENDING_QUESTION = 'bs_pending_question';
	const SESSION_KEY_ANSWER_REDIRECT = 'bs_answer_redirect';
	const SESSION_KEY_IGNORE_ACTIVATION_NAG = 'ignore_activation_nag';
	const SESSION_KEY_LANDING_QUESTION = 'bs_landing_question';
	
	const ROLE_CONTRIBUTOR = "contributor";
	const ROLE_CONTRIBUTOR_PLUS = "contributorplus";
	
	const USER_NICENAME_SEED = 18521469;
	
	protected $user;
	protected $meta = array();  // Array of BS_UserMetaField objects
	protected $activities;  //BS_Activites object
	protected $update_user_fields = array();  // non-meta user fields
	protected $_company; // BS_Company
	protected $_display; // BS_MemberDisplay
	
	public function __construct($user = null){
		if($user)
			$this->load($user);
	}
	
	
	
 ////////////////////////////////////////////////////////////
  ///  STATIC METHODS
   ///////////////////////////////////////////////////////////
	
	public static function Init(){
		global $wp_query;
		if (!is_404() && !is_admin() && strlen(session_id()) < 1){
			session_start();
			if (!isset($_SESSION[BS_Member::SESSION_ROOT]))
				$_SESSION[BS_Member::SESSION_ROOT] = array();
		}
		global $_qa_edit;
		remove_action('user_register', array(&$_qa_edit, 'user_register'), 1000);
		remove_action('wp_login', array(&$_qa_edit, 'wp_login'));
	}

	
	public static function FindMemberByEmail($email){
		$user_query = new WP_User_Query(array(
				"search" => $email,
				"search_columns" => array('user_email'),
				"fields" => "all_with_meta"
		));
		
		if ($user_query->get_total() == 1){
			$user = $user_query->get_results();
			$user = array_pop($user);
		
			if ($user instanceof WP_User){
				$member = new BS_Member($user);
				return $member;
			}
		}
		return null;
	}
	
	public static function EmailLogin($username){
		$user = get_user_by('email', $username);
		$username = $user->user_login;
		return $username;
	}
	
	public static function SetupCurrentUser(){
		global $BS_CurrentUser;
		$BS_CurrentUser = new BS_Member();
		$BS_CurrentUser->load_current_user();
	}
	
	public static function AuthorBaseRewrite(){
		global $wp_rewrite;
		$wp_rewrite->author_base = 'member';
	}
	
	public static function SetSessionLoginRedirect($url = null){
		if (!$url) $url = $_SERVER['HTTP_REFERER'];
		$_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_LOGIN_REDIRECT] = $url;
	}
	
	public static function UnsetSessionLoginRedirect(){
		unset($_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_LOGIN_REDIRECT]);
	}
	
	public static function GetSessionLoginRedirect(){
		return $_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_LOGIN_REDIRECT];
	}
	
	public static function SetIgnoreActivationNag(){
		$_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_IGNORE_ACTIVATION_NAG] = true;
	}
	public static function GetIgnoreActivationNag(){
		return $_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_IGNORE_ACTIVATION_NAG];
	}

	public static function RegistrationChecks($args){
		extract($args);

		// Spam checks
		if ($first_name == $last_name) {
			return false;
		}

		global $BS_CurrentUser;
		if ( strpos($BS_CurrentUser->get_useragent(), "iPhone") > -1 ){
			return false;
		}
		
		if ( strpos($BS_CurrentUser->get_useragent(), "Android") > -1 ){
			return false;
		}

		return true;
	}
	
	/*
	 * if no password is given, one will be generated, and the generated password meta flag will be set.
	 */
	public static function RegisterNewUser($email, $first_name = null, $last_name = null, $password = null){
		
		$user_login = $user_email = $email;
		if (!$password){
			$password = wp_generate_password();
			$generated_password = true;
		}
		
		$error_msgs = array();
		
		$sanitized_user_login = sanitize_user( $user_login );
		$user_email = apply_filters( 'user_registration_email', $user_email );

		// Check the username
		if ( $sanitized_user_login == '' || ! validate_username( $user_login ) ) {
			return "Invalid registration information.";
		} elseif ( username_exists( $sanitized_user_login ) ) {
			return "There is already an account registered with this information.";
		}
	
		// Check the e-mail address
		if ( $user_email == '' ) {
			return "You must provide an email address to register.";
		} elseif ( ! is_email( $user_email ) ) {
			return "You must provide a valid email address to register.";
		} elseif ( email_exists( $user_email ) ) {
			return "This email address is already registered.";
		}

		if (!BS_Member::RegistrationChecks( array('email' => $email, 'first_name' => $first_name, 'last_name' => $last_name, 'password' => $password, 'user_login' => $user_login ) )){
			return "We could not register you at this time. Please try again later.";
		}

		// Insert User
		$user_id = wp_insert_user(array(
			'user_pass' => $password,
			'user_login' => $user_login,
			'user_email' => $user_email,
			'display_name' => $first_name . ' ' . $last_name[0] . '.',
			'first_name' => $first_name,
			'last_name' => $last_name,
			'show_admin_bar_front' => 'false'
		));
		
		if ( ! $user_id ) {
			return "Registration failed.  Please contact an administrator for further assistance.";
		}
		
		wp_update_user(array('ID'=>$user_id, 'user_nicename'=> BS_Member::USER_NICENAME_SEED + $user_id ));
		
		if ($generated_password)
			update_user_meta($user_id, BS_UserMeta::GENERATED_PASSWORD, true);
	
		$member = new BS_Member($user_id);
		return $member;
	}
	
 ////////////////////////////////////////////////////////////
  ///  MAGIC METHODS
   ///////////////////////////////////////////////////////////
	
	public function __get( $key ) {
		
		switch ($key){
			case 'company':
				$this->load_company();
				return $this->_company;
				break;
			case 'display':
				$this->load_display();
				return $this->_display;
				break;
		}
		
		
		// From WP_User
		if ( 'id' == $key )	$key = 'ID';
	
		if ( isset( $this->user->$key ) ) $value = $this->user->$key;
		else $value = get_user_meta( $this->ID, $key, true );
	
		return $value;
	}
	
	public function __set( $key, $value ) {
		if ( 'id' == $key ) {
			// _deprecated_argument( 'WP_User->id', '2.1', __( 'Use <code>WP_User->ID</code> instead.' ) );
			$this->ID = $value;
			return;
		}

		$this->data->$key = $value;
	}
	
	
	
 ////////////////////////////////////////////////////////////
  ///  DATA LOADERS
   ///////////////////////////////////////////////////////////
   
	/**
	 * Load WP_User object from userID or WP_User argument
	 * @param $user user_id or WP_User
	 */
	public function load($user = null){
		if($user instanceof WP_User){
			$this->user = $user;
		} elseif (is_numeric($user) && $user > 0){
			$this->user = get_userdata($user);
		} elseif (!isset($user)){
			$this->user = wp_get_current_user();
		} else {
			$this->error("Invalid arguments", __CLASS__, __METHOD__, __LINE__);
		}
		
		if($this->user instanceof WP_User){
			return true;
		} else {
			return false;
		}
	}
	
	public function load_current_user(){
		
		if ($this->get_cookie()){
			//wp_signon();
		} else {
			$this->user = wp_get_current_user();
		}
		
		$this->load_session();
	}
	
	public function load_session(){
		
		$this->set_original_referrer();
		//$this->set_last_page();
		//$this->set_current_page();
	}
	
	public function load_activities(){
		$this->activities = new BS_Activities($this);
	}
	
	/**
	 * Load all user meta for this user
	 */
	public function load_meta(){
		if (!$this->meta_loaded){
			$meta = get_user_meta($this->get_id());
			if (is_array($meta)){
				foreach ($meta as $k=>$v){
					if (is_array($v) && count($v) <= 1)
						$v = $v[0];
					$this->meta[$k] = new BS_UserMetaField($this, $k, $v);
				}
				$this->meta_loaded = true;
			}
		}
	}
	
	public function load_company(){
		if (!$this->_company){
			$this->_company = new BS_Company($this->get_company_id());
		}
	}
	
	protected function load_display(){
		if (!$this->_display){
			$this->_display = new BS_MemberDisplay($this);
		}
	}
	
	
 ////////////////////////////////////////////////////////////
  ///  IS_*
   ///////////////////////////////////////////////////////////
   
	public function is_current(){
		if ($this->get_id() == wp_get_current_user()->ID){
			return true;
		} else {
			return false;
		}
	}
	
	public function is_activated(){
		if (!$this->user->{BS_UserMeta::UNVERIFIED})
			return true;
		
		return false;
	}
	
	public function is_anonymous(){
		return !is_user_logged_in();
	}
	
	
	
 ////////////////////////////////////////////////////////////
  ///  DEBUG, DISPLAY, HELPERS
   ///////////////////////////////////////////////////////////
	
	public function debug(){
		$this->load_meta();
	?>
		<div class="bs-toggle">
			<img src="<?=$this->get_avatar()?>" height="44" />
			<h3 class="bs-toggle-btn"><?=$this->get_display_name() ?> (<?= $this->get_id() ?>) BS_Member Object</h3>
			<ul class="bs-debug-prop-list bs-toggle-content">
				<li><span>First Name:</span> <?= $this->get_first_name(); ?></li>
				<li><span>Display Name:</span> <?= $this->get_display_name(); ?></li>
				<li><span>User ID:</span> <?= $this->get_id(); ?></li>
				<li><span>Original Referrer:</span> <?= $this->get_original_referrer(); ?></li>
				<li><span>Referrer:</span> <?= $this->get_referrer(); ?></li>
				<li><span>IP:</span> <?= $this->get_ip(); ?></li>
				<li><span>Session ID:</span> <?= $this->get_session_id(); ?></li>
				<li><span>User Agent:</span> <?= $this->get_useragent(); ?></li>
				<li><span>Is anonymous:</span> <?= $this->is_anonymous(); ?></li>
				<li><span>Activation Key:</span> <?= $this->get_activation_key() ?></li>
				<?php foreach ($this->meta as $meta): ?>
					<li><span><?=$meta->get_key()?>:</span> <?=$meta->get_value()?></li>
				<?php endforeach; ?>
				<li class="clear"></li>
			</ul>
		</div>
	<?php 
	
	}
	
	public function display_image_upload_form(){
	
	
	?>
	<form action="<?=BS_Locations::AJAX_IMG_UPLOAD()?>" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field(BS_BASENAME, 'wp_custom_attachment_nonce'); ?>
		<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" value="" size="25">
		<input type="submit">
	</form>
	<?php 
	}
	
	public function display_notifications(){
		if(!$this->activities)
			$this->load_activities();
		
		$this->activities->display_notifications();
	}

	public function save(){
		$fields_return = true;
		$meta_return = true;

		if ($this->update_user_fields){
			$fields_return = false;
			$userdata = array('ID' => $this->get_id());
			foreach ($this->update_user_fields as $key=>$value){
				$userdata[$key] = $value;
			}
				
			if(wp_update_user($userdata) == $this->get_id())
				$fields_return = true;
		}
		
		if (is_array($this->meta))
			foreach ($this->meta as $meta_field)
				$meta_return = $meta_field->save();
		
		if ($meta_return && $fields_return)
			return true;
	
		return false;
	}
	
	public function save_meta($key, $value){
		if (update_user_meta($this->get_id(), $key, $value))
			return true;
	
		return false;
	}
	
	
	
 ////////////////////////////////////////////////////////////
  ///  GETTER/SETTERS
   ///////////////////////////////////////////////////////////
	
	// SESSION ID
	public function get_session_id(){
		return session_id();
	}
	
	// IP
	public function get_ip(){
		return $_SERVER['REMOTE_ADDR'];
	}
	
	// USERAGENT
	public function get_useragent(){
		return $_SERVER['HTTP_USER_AGENT'];
	}
	
	// REFERRER
	public function get_referrer(){
		return $_SERVER['HTTP_REFERER'];
	}

	// ORIGINAL REFERRER
	public function get_original_referrer(){
		return $_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_ORIGINAL_REFERRER];
	}
	
	public function set_original_referrer(){
		if (!isset($_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_ORIGINAL_REFERRER])){
			$_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_ORIGINAL_REFERRER] = $this->get_referrer();
		}
	}
	
	public function reset_original_referrer(){
		unset($_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_ORIGINAL_REFERRER]);
	}
	
	public function set_last_page(){
		if (isset($_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_CURRENT_PAGE])){
			$_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_LAST_PAGE] = $_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_CURRENT_PAGE];
		}
	}
	
	public function set_current_page(){
		$_SESSION[BS_Member::SESSION_ROOT][BS_Member::SESSION_KEY_CURRENT_PAGE] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		//$_SESSION[BS_Member::SESSION_ROOT]['tmp_pg_history'][] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
	
	// COOKIE
	public function set_cookie(){
		
	}
	
	public function get_cookie(){
		return false;
	}
	
	public function update_cookie(){
		
	}
	
	// ID
	public function get_id(){
		return $this->user->ID;
	}
	
	// USER PROFILE URL (/member/nice-name/)
	public function get_profile_url(){
		return get_author_posts_url($this->get_id());
	}
	
	public function get_profile_link(){
		if ($this->get_site_contributor())
			return '<a href="' . $this->get_profile_url() . '">' . $this->get_display_name() . '</a>';
		
		return $this->get_display_name();
	}
	
	// UNIQUE CAPS
	public function get_unique_caps(){
		return $this->user->caps;
	}
	
	// ROLES
	public function get_roles(){
		return $this->user->roles;
	}
	
	// ALL CAPS
	public function get_caps(){
		return $this->user->allcaps;
	}
	
	// FIRST NAME
	public function get_first_name(){
		return $this->user->first_name;
	}
	
	public function set_first_name($value){
		if ($this->get_first_name() != $value){
			$this->set_field(BS_UserFields::FIRST_NAME, $value);
			return true;
		}
		return false;
	}
	
	// LAST NAME
	public function get_last_name(){
		return $this->user->last_name;
	}
	
	// FULL NAME
	public function get_full_name() {
		return $this->get_first_name() . ' ' . $this->get_last_name();
	}
	
	public function set_last_name($value){
		if ($this->get_last_name() != $value){
			$this->set_field(BS_UserFields::LAST_NAME, $value);
			return true;
		}
		return false;
	}
	
	// LOGIN
	public function get_login(){
		return $this->user->user_login;
	}
	// Setting the user_login is probably not a great idea

	// PASSWORD
	public function get_password(){
		return $this->user->user_pass;
	}
	
	// NICENAME
	public function get_nicename(){
		return $this->user->user_nicename;
	}
	
	// EMAIL
	public function get_email(){
		return $this->user->user_email;
	}
	
	public function set_email($value){
		if ($this->get_email() != $value){
			$this->set_field(BS_UserFields::EMAIL, $value);
			return true;
		}
		return false;
	}
	
	// URL
	public function get_url(){
		return $this->user->user_url;
	}
	
	// REGISTERED
	public function get_registered(){
		return $this->user->user_registered;
	}
	
	// ACTIVATION KEY
	public function get_activation_key(){
		if ($this->user->{BS_UserMeta::ACTIVATION_KEY})
			return $this->user->{BS_UserMeta::ACTIVATION_KEY};
		
		$activation_key = wp_hash($this->get_id() . $this->get_ip() . time());
		update_user_meta($this->get_id(), BS_UserMeta::ACTIVATION_KEY, $activation_key);
		return $activation_key;
	}
	
	// WP ACTIVATION KEY
	public function get_wp_activation_key(){
		return $this->user->user_activation_key;
	}
	
	// STATUS
	public function get_status(){
		return $this->user->user_status;
	}
	
	// DISPLAY NAME
	public function get_display_name(){
		return $this->user->display_name;
	}
	
	public function set_display_name($value){
		if ($this->get_display_name() != $value){
			$this->set_field(BS_UserFields::DISPLAY_NAME, $value);
			return true;
		}
		return false;
	}
	
	// USERDATA
	public function get_userdata(){
		return $this->user;
	}
	
	// HEADLINE
	public function get_headline(){
		return $this->get_meta(BS_UserMeta::HEADLINE);
	}
	
	public function set_headline($value){
		if ($this->get_headline() != $value){
			$this->set_meta(BS_UserMeta::HEADLINE, $value);
			return true;
		}
		return false;
	}
	
	// REGISTRATION META
	public function set_registration_meta($social_registration = false){
		update_user_meta($this->get_id(), BS_UserMeta::REGISTRATION_IP, $this->get_ip());
		update_user_meta($this->get_id(), BS_UserMeta::REGISTRATION_ORIGINAL_REFERRER, $this->get_original_referrer());
		update_user_meta($this->get_id(), BS_UserMeta::REGISTRATION_REFERRER, $this->get_referrer());
		update_user_meta($this->get_id(), BS_UserMeta::REGISTRATION_USER_AGENT, $this->get_useragent());
		update_user_meta($this->get_id(), BS_UserMeta::REGISTRATION_URL, $_SERVER['REQUEST_URI']);
		update_user_meta($this->get_id(), BS_UserMeta::REGISTRATION_SERVER_GLOBAL, $_SERVER);
		if ($social_registration)
			update_user_meta($this->get_id(), BS_UserMeta::REGISTRATION_SOCIAL_LOGIN, true);
	}
	
	// GFE_ACTIVITY
	
	public function get_gfe_activity(){
		return $this->get_meta(BS_UserMeta::ACTIVITY);
	}
	
	public function get_gfe_activity_score(){
		return $this->get_meta(BS_UserMeta::ACTIVITY_SCORE);
	}

	// PREVENT CONTACT
	public function get_prevent_contact(){
		return $this->get_meta(BS_UserMeta::PREVENT_CONTACT);
	}
	
	public function set_prevent_contact($value){
		if (!$value){
			delete_user_meta($this->get_id(), BS_UserMeta::PREVENT_CONTACT);
			return true;
		} else {
			update_user_meta($this->get_id(), BS_UserMeta::PREVENT_CONTACT, $value);
			return true;
		}
		return false;
	}
	
	
	// CONTRIBUTOR REQUEST
	public function get_contributor_request(){
		return $this->get_meta(BS_UserMeta::CONTRIBUTOR_REQUEST);
	}
	
	public function set_contributor_request($value){
		if (!$value){
			delete_user_meta($this->get_id(), BS_UserMeta::CONTRIBUTOR_REQUEST);
			return true;
		} else {
			update_user_meta($this->get_id(), BS_UserMeta::CONTRIBUTOR_REQUEST, $value);
			return true;
		}
		return false;
	}
		
	// JOB TITLE
	public function get_job_title(){
		return $this->get_meta(BS_UserMeta::JOB_TITLE);
	}
	
	public function set_job_title($value){
		if ($this->get_job_title() != $value){
			$this->set_meta(BS_UserMeta::JOB_TITLE, $value);
			return true;
		}
		return false;
	}
	
	// COMPANY
	public function get_company(){
		return $this->company;
	}
	
	public function get_company_image(){
		return $this->company->get_image();
	}
	
	// COMPANY ID
	public function get_company_id(){
		return $this->get_meta(BS_UserMeta::COMPANY_ID);
	}

	public function set_company_id($value){
		if ($this->get_company_id() != $value){
			$this->set_meta(BS_UserMeta::COMPANY_ID, $value);
			return true;
		}
		return false;
	}
	
	// COMPANY NAME
	public function get_company_name(){
		return $this->company->get_post_title();
	}
	
	// JOB COMPANY LINE
	public function get_job_company_line(){
		$job_company_line = array();
	
		if ($job_title = $this->get_job_title())
			array_push($job_company_line, $job_title);
	
		if ($company_name = $this->get_company_name())
			array_push($job_company_line, $company_name);
	
		return implode(', ', $job_company_line);
	}
	
	// User RT
	public function get_total_score($term_id){
		$term_id = $term_id ? $term_id : "0";
		return $this->get_meta(BS_UserMeta::RT_TOTAL_SCORE . $term_id);
	}
	
	/**
	 * returns an array(
	 * 		term_id => total_score
	 * );
	 */
	public function get_all_rts(){
		$rts = array();
		$custom = get_user_meta($this->get_id());
		foreach ($custom as $k=>$v){
			if (strpos($k, BS_UserMeta::RT_TOTAL_SCORE) !== 0)
				continue;
			$term_id = (integer) str_replace(BS_UserMeta::RT_TOTAL_SCORE, '', $k);
			if ($term_id)
				$rts[$term_id] = $v[0];
			if ($term_id === 0)
				$rts = array(0 => $v[0]) + $rts; // this puts the 0 term at the beginning
		}
		return $rts;
	}
	
	public function get_expertise_rts(){
		$all_rts = $this->get_all_rts();
		$expertise = $this->get_expertise_array();
		foreach ($all_rts as $term_id => $total_score){
			if (($term_id !== 0) && !in_array($term_id, $expertise)){
				unset($all_rts[$term_id]);
			}
		}
		return $all_rts;
	}

	public function set_total_score($term_id, $value){
		$term_id = $term_id ? $term_id : "0";
		return $this->set_meta(BS_UserMeta::RT_TOTAL_SCORE . $term_id, $value);
	}
	
	public function delete_total_score($term_id){
		return delete_user_meta($this->get_id(), BS_UserMeta::RT_TOTAL_SCORE . $term_id);
	}
	
	public function get_global_rt_suppress(){
		return $this->get_meta(BS_UserMeta::RT_GLOBAL_SUPPRESS);
	}
	
	public function clear_global_rt_suppress(){
		delete_user_meta($this->get_id(), BS_UserMeta::RT_GLOBAL_SUPPRESS);
	}
	
	public function get_expertise_array(){
		return get_user_meta($this->get_id(), BS_UserMeta::EXPERTISE);
	}
	
	public function add_expertise($term_id){
		add_user_meta($this->get_id(), BS_UserMeta::EXPERTISE, $term_id);
		if (is_null($this->get_total_score($term_id)))
			$this->set_total_score($term_id, 0);
	}
	
	public function delete_expertise($term_id){
		delete_user_meta($this->get_id(), BS_UserMeta::EXPERTISE, $term_id);
		if ($this->get_total_score($term_id) == 0)
			$this->delete_total_score($term_id);
	}
	
	public function get_role(){
		foreach ($this->user->roles as $role){
			$ret .= $role;
		}
		return $ret;
	}
	
	public function get_qa_score(){
		if ($this->get_site_contributor())
			return qa_get_user_rep($this->get_id());
		else 
			return 0;
	}
	
	
	// DISQUS USERNAME
	public function get_disqus_username(){
		return $this->get_meta(BS_UserMeta::DISQUS_USERNAME);
	}
	
	public function set_disqus_username($value){
		if ($this->get_disqus_username() != $value){
			$this->set_meta(BS_UserMeta::DISQUS_USERNAME, $value);
			return true;
		}
		return false;
	}
	
	// DISQUS USERID
	public function get_disqus_userid(){
		return $this->get_meta(BS_UserMeta::DISQUS_USER_ID);
	}
	
	public function set_disqus_userid($value){
		if ($this->get_disqus_userid() != $value){
			$this->set_meta(BS_UserMeta::DISQUS_USER_ID, $value);
			return true;
		}
		return false;
	}
	
	// TWITTER USERNAME
	public function get_twitter_username(){
		return $this->get_meta(BS_UserMeta::TWITTER_USER);
	}
	
	public function get_twitter_page(){
		return "http://twitter.com/" . $this->get_twitter_username();
	}
	
	public function set_twitter_username($value){
		if ($this->get_twitter_username() != $value){
			$this->set_meta(BS_UserMeta::TWITTER_USER, $value);
			return true;
		}
		return false;
	}
	
	// FACEBOOK PAGE
	public function get_facebook_page(){
		if ($this->get_meta(BS_UserMeta::FACEBOOK_PAGE))
			return "http://www.facebook.com/" . $this->get_meta(BS_UserMeta::FACEBOOK_PAGE);
	}
	
	public function set_facebook_page($value){
		if ($this->get_facebook_page() != $value){
			$this->set_meta(BS_UserMeta::FACEBOOK_PAGE, $value);
			return true;
		}
		return false;
	}
	
	// LINKEDIN PAGE
	public function get_linkedin_page(){
		if ($this->get_meta(BS_UserMeta::LINKEDIN_PAGE))
			return "http://www.linkedin.com/" . $this->get_meta(BS_UserMeta::LINKEDIN_PAGE);
	}
	
	public function set_linkedin_page($value){
		if ($this->get_linkedin_page() != $value){
			$this->set_meta(BS_UserMeta::LINKEDIN_PAGE, $value);
			return true;
		}
		return false;
	}

	// LINKEDIN PAGE
	public function get_googleplus_page(){
		if ($this->get_meta(BS_UserMeta::GOOGLEPLUS_PAGE))
			return "http://plus.google.com/" . $this->get_meta(BS_UserMeta::GOOGLEPLUS_PAGE);
	}
	
	public function set_googleplus_page($value){
		if ($this->get_googleplus_page() != $value){
			$this->set_meta(BS_UserMeta::GOOGLEPLUS_PAGE, $value);
			return true;
		}
		return false;
	}
	
	// CONTRIBUTOR TYPES
	// The getter/setter for these have to be different because they are arrays
	public function get_contributor_types(){
		return get_user_meta($this->get_id(), BS_UserMeta::CONTRIBUTOR_TYPES);
	}
	
	
	// ACHIEVEMENTS
	// The getter/setter for these have to be different because they are arrays
	public function get_achievements(){
		return get_user_meta($this->get_id(), BS_UserMeta::ACHIEVEMENTS);
	}
	
	// BADGES
	public function get_badges(){
		return array_merge($this->get_achievements(), $this->get_contributor_types());
	}
	
	// SITE CONTRIBUTOR
	public function get_site_contributor(){
		return $this->get_meta(BS_UserMeta::SITE_CONTRIBUTOR);
	}
	
	public function set_site_contributor($value){
		if ($this->get_site_contributor() != $value){
			$this->set_meta(BS_UserMeta::SITE_CONTRIBUTOR, $value);
			return true;
		}
		return false;
	}
	
	// AVATAR
	public function get_avatar(){
		$meta_avatar = $this->get_meta(BS_UserMeta::AVATAR);
		return $meta_avatar ? $meta_avatar : BS_UserMeta::AVATAR_MYSTERY_MAN;
	}
	
	public function set_avatar($value){
		if ($this->get_avatar() != $value){
			$this->set_meta(BS_UserMeta::AVATAR, $value);
			return true;
		}
		return false;
	}
	
	// BIO
	public function get_bio($wpautop = true){
		return $wpautop ? wpautop($this->get(BS_UserFields::BIO)) : $this->get(BS_UserFields::BIO);
	}
	
	public function set_bio($value){
		if ($this->get_bio() != $value){
			$this->set_field(BS_UserFields::BIO, $value);
			return true;
		}
		return false;
	}
	
	
	// COUNTRY
	public function get_country(){
		return $this->get_meta(BS_UserMeta::COUNTRY);
	}
	
	public function set_country($value){
		if ($this->get_country() != $value){
			$this->set_meta(BS_UserMeta::COUNTRY, $value);
			return true;
		}
		return false;
	}
	
	// CITY
	public function get_city(){
		return $this->get_meta(BS_UserMeta::CITY);
	}
	
	public function set_city($value){
		if ($this->get_city() != $value){
			$this->set_meta(BS_UserMeta::CITY, $value);
			return true;
		}
		return false;
	}
	
	// STATE
	public function get_state(){
		return $this->get_meta(BS_UserMeta::STATE);
	}
	
	public function set_state($value){
		if ($this->get_state() != $value){
			$this->set_meta(BS_UserMeta::STATE, $value);
			return true;
		}
		return false;
	}
	
	// ACTIVATION
	public function activate_user(){
		delete_user_meta($this->get_id(), BS_UserMeta::UNVERIFIED);
	}
	
	/**
	 * Used to make a user 'unverified'.
	 *
	 * @param bool $value = TRUE for UNVERIFIED
	 * NOTE:  Use activate_user() after verification
	 */
	public function set_unverified($value = true){
		// TODO should this use the set + save method instead of saving immediately?
		update_user_meta($this->get_id(), BS_UserMeta::UNVERIFIED, $value);
	}
	
	// GENERAL GETTERS/SETTERS
	public function get_meta($key){
		if (empty($this->meta))
			$this->load_meta();
	
		if (array_key_exists($key, $this->meta))
			return $this->meta[$key]->get_value();
	}

	public function get($key){
		return $this->user->$key;
	}
	
	public function set_meta($key, $value){
		// Return true if the value was added or changed, false if nothing was changed
		if (!array_key_exists($key, $this->meta)){
			$this->meta[$key] = new BS_UserMetaField($this, $key, $value, true);
			return true;
		} else {
			return $this->meta[$key]->set_value($value);
		}
	}
	
	public function delete_meta($key){
		delete_user_meta($this->get_id(), $key);
	}
	
	public function set_field($key, $value){
		$this->update_user_fields[$key] = $value;
		$this->set($key, $value);
	}
	
	public function set($key, $value){
		$this->user->$key = $value;
	}
}