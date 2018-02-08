<?php
class SocialLoginPluginBase {
	protected $facebook;
	protected $google;
	protected $login_redirect;
	protected $logout_redirect;
	protected $active_network;
	
	public function __construct() {
		// default the login redirect when it happens with social login
		// we will NOT default the LOGOUT redirect because your likely wants to handle that
		if (!$this->login_redirect) {
			$this->login_redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		}
		
		$this->Init();
	}
	
	/**
	 * Add the social buttons you want to include
	 */
	public function Init() {
		//$this->facebook = new FacebookLogin('270603973059560', 'e676df9fd26b676d1cee9a87fc011c7a', 'http://bs-v2.local/social/index.php?login=facebook');
		//$this->google = new GoogleLogin('414632513125-t0jvda5er9t14mfrb5p17mijg4bmpf86.apps.googleusercontent.com', 'oagbJhvnh7PFCIqem10073QM', 'http://localhost/social/index.php?login=google', 'AIzaSyCYZXoHUDhU1BQ-vSDlTw_yXJWgpufOqiI');
	}
	
	public function Run() {
		$this->SiteLoginHandler();
		$this->SiteLogoutHandler();
	}
	
	/**
	 * Set the url to redirect to after Login
	 * @param unknown_type $url
	 */
	public function SetLoginRedirect($url) {
		$this->login_redirect = $url;
	}
	
	/**
	 * Set the url to redirect to after logout
	 * @param unknown_type $url
	 */
	public function SetLogoutRedirect($url) {
		$this->logout_redirect = $url;
	}
	
	/**
	 * Login function that is meant to be overriden per application. This allows an application
	 * to implement application specific functionality related to a login.
	 */
	protected function CustomLogin() {
		// intentionally leave blank
	}
	/* 
	 * Logout function that is meant to be overriden per application. This allows an application
	 * to implement application specific functionality related to a logout.
	 */
	protected function CustomLogout() {
		// intentionally leave blank
	}
	
	public function IsLoginRequest() {
		if (isset($_GET['login'])) {
			$login_network = $_GET['login'];
				
			if ($this->{$login_network} instanceof SocialLogin) {
				return $login_network;
			}
		}
		
		return false;
	}
	
	public function IsLogoutRequest() {
		if (isset($_GET['logout'])) {
			return true;
		}
		
		return false;
	}
	
	
	protected function SiteLoginHandler() {
		if ($this->active_network = $this->IsLoginRequest()) {
			echo "hello";
			if ($this->{$this->active_network}->IsUserLoggedIn()) {
				echo "hello";
				$_SESSION['logged_in'] = true;
				echo "fails0";
				$_SESSION['email'] = $this->{$this->active_network}->GetEmail();
				echo "fails1";
				$_SESSION['active_network'] = $this->active_network;
				echo "fails2";
					
				$this->CustomLogin();
			}
			
			if ($this->login_redirect) {
				$this->Redirect($this->login_redirect);
			}
		}
	}
	
	protected function SiteLogoutHandler() {
		if ($this->IsLogoutRequest()) {
			// cleanup in the networks
			if ($login_network = $_SESSION['active_network']) {
				$this->{$login_network}->Logout();
			}
			
			$_SESSION['logged_in'] = false;
			$_SESSION['email'] = null;
			$_SESSION['active_network'] = null;
			unset($_SESSION['logged_in']);
			unset($_SESSION['email']);
			unset($_SESSION['active_network']);
			
			$this->CustomLogout();
		
			if ($this->logout_redirect)
				$this->Redirect($this->logout_redirect);
		}
	}
	
	protected function Redirect($url) {
		header('Location: ' . filter_var($url, FILTER_SANITIZE_URL));
	}
	
	
	public function Render() {
		// add styles here
		$this->facebook->Render();
		echo '<br />';
		$this->google->Render();
	}
}