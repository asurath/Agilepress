<?php
require_once __FRAMEWORK_PATH . '/library/google/Google_Client.php';
require_once __FRAMEWORK_PATH . '/library/google/contrib/Google_Oauth2Service.php';

class GoogleLoginBase extends SocialLogin {
	
	protected $application_name;
	protected $client_id;
	protected $client_secret;
	protected $redirect_uri;
	protected $developer_key;
	protected $google_client;
	protected $oauth2;
	
	public function __construct($client_id, $client_secret, $redirect_uri, $developer_key) {
		
		// Initialize
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->redirect_uri = $redirect_uri;
		$this->developer_key = $developer_key;
		
		$this->Run();
	}
	
	protected function Init() {
		$this->google_client = new Google_Client();
		$this->google_client->setAccessType('');
		$this->google_client->setApprovalPrompt('auto');
		$this->google_client->setClientId($this->client_id);
		$this->google_client->setClientSecret($this->client_secret);
		$this->google_client->setRedirectUri($this->redirect_uri);
		$this->google_client->setDeveloperKey($this->developer_key);
		
		$this->oauth2 = new Google_Oauth2Service($this->google_client);
	}
	
	
	protected function Authorize() {
		if (isset($_GET['code'])) {
			$this->google_client->authenticate($_GET['code']);
			$_SESSION['token'] = $this->google_client->getAccessToken();
			$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?login=google";
			header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
			return;
		}
		
		if (isset($_SESSION['token'])) {
			$this->google_client->setAccessToken($_SESSION['token']);
		}
	}
	
	protected function SessionHandler() {
		if ($this->google_client->getAccessToken()) {
			$this->user_raw_data = $this->oauth2->userinfo->get();
				
			$this->email = $this->user_raw_data['email'];
			$this->name = $this->user_raw_data['name'];
			$this->first_name = $this->user_raw_data['given_name'];
			$this->last_name = $this->user_raw_data['family_name'];
			
			// The access token may have been updated lazily.
			$_SESSION['token'] = $this->google_client->getAccessToken();
		}
	}
	
	/**
	 * @see SocialLogin::IsUserLoggedIn()
	 */
	public function IsUserLoggedIn() {
		return $this->google_client->getAccessToken();
	}
	
	public function Logout() {
		$_SESSION['token'] = null;
		unset($_SESSION['token']);
	}
	
	/**
	 * Sets the application name. This is optional.
	 * @param unknown_type $application_name
	 */
	public function SetApplicationName($application_name) {
		$this->application_name = $application_name;
		$this->google_client->setApplicationName($this->application_name);
	}
}