<?php
require (SOCIAL_LOGIN_BASE_CLASSES . '/SocialLoginPluginBase.class.php');

class SocialLoginPlugin extends SocialLoginPluginBase{
	protected $facebook;
	protected $google;
	protected $linkedin;
	
	/**
	 * Add the social buttons you want to include
	 */
	public function Init() {
		$this->facebook = new FacebookLogin(FACEBOOK_APP_ID, FACEBOOK_APP_SECRET, FACEBOOK_REDIRECT);
		$this->google = new GoogleLogin(GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT, GOOGLE_DEVELOPER_KEY);
		$this->linkedin = new LinkedInLogin(LINKEDIN_APP_KEY, LINKEDIN_APP_SECRET, LINKEDIN_REDIRECT);
	}
	
	protected function CustomLogin() {
		if ( false && $user = get_user_by('email', $_SESSION['email']) ){
			if ($user instanceof WP_User){
				$member = new AP_UserBase($user->ID);
			}
		} else {
			// Register new user

			$network = $_SESSION['active_network'];
			$member = new AP_UserBase;
			$member->Email = $_SESSION['email'];
			$member->Login = $_SESSION['email'];

			$member->Register();
			
			$strTempLastName = $this->{$network}->GetLastName();
			$member->Name = $this->{$network}->GetFirstName() . " " . strtoupper($strTempLastName{0}) . ".";
			$member->NiceName = $this->{$network}->GetFirstName() . " " . strtoupper($strTempLastName{0}) . ".";
			$member->FirstName = $this->{$network}->GetFirstName();
			$member->LastName =  $this->{$network}->GetLastName(); 	
			
			$member->Update();
			die;
			/*
			$member = BS_Member::RegisterNewUser(
											$_SESSION['email'], 
											$this->{$network}->GetFirstName(), 
											$this->{$network}->GetLastName() );
			if ($member){
				$member->set_avatar('http://0.gravatar.com/avatar/?d=mm');
				$member->set_site_contributor(true);
				$member->set_registration_meta(true);
				$member->save();
				
				BS_WelcomeEmail::SendMail($member);
			}
			*/
		}
		
		
		if ($this->active_network == 'linkedin') {
			// LinkedIn is different because we are using a popup, so we want the popup to close and the
			// parent page to redirect.
			$this->SetLoginRedirect($_GET['redirect']);
		}
		else {
			// Set redirect to session redirect which is set on the Login Form
			// This redirect can be modified below based on whether a user
			// asked or answered a question.
			//$this->SetLoginRedirect(BS_Member::GetSessionLoginRedirect());
		}

		if ($member){
			//if ($redirect = BS_Login::SuccessHandler($member))

			$this->SetLoginRedirect($redirect);
			wp_set_current_user( $member->ID,   $member->Login);
			wp_set_auth_cookie($member->ID, true);
			do_action( 'wp_login', $member->Login );

			?>

			<?php
		}
	}
	
	protected function CustomLogout() {
	
	}
	
	/**
	 * Set the url to redirect to after Login
	 * @param unknown_type $url
	 */
	public function SetLoginRedirect($url) {		
		if ($this->active_network == 'linkedin') {
			// LinkedIn is different because we are using a popup, so we want the popup to close and the
			// parent page to redirect.
			$this->login_redirect = '/wp-content/plugins/bs-social/popup.php?redirect=' . $url;
		}
		else {
			$this->login_redirect = $url;
		}
	}
	
	public function Render() {

		// add styles here

		
		$this->linkedin->Render("LinkedIn");
		$this->google->Render('Google+');
		$this->facebook->Render('Facebook');
		
		
	}
}