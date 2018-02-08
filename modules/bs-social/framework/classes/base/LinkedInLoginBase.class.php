<?php
require(__FRAMEWORK_PATH . '/library/linkedin/linkedin_3.2.0.class.php');
class LinkedInLoginBase extends SocialLogin {
	
	private $linkedin_uid;
	private $app_key;
	private $app_secret;
	private $config;
	private $linkedin;
	private $redirect_uri;
	
	public function __construct($app_key, $app_secret, $redirect_uri) {
		$this->app_key = $app_key;
		$this->app_secret = $app_secret;
		$this->redirect_uri = $redirect_uri;
		
		// display constants
		$this->config = array(
			'appKey'       => $this->app_key,
			'appSecret'    => $this->app_secret,
			'callbackUrl'  => $this->GetCallbackUrl()
		);

		$this->Run();
	}
	
	protected function Init() {
		$this->linkedin = new LinkedIn($this->config);
	}
	
	protected function Authorize() {
		$_REQUEST[LINKEDIN::_GET_TYPE] = (isset($_REQUEST[LINKEDIN::_GET_TYPE])) ? $_REQUEST[LINKEDIN::_GET_TYPE] : '';
		switch($_REQUEST[LINKEDIN::_GET_TYPE]) {
			case 'initiate':
				if ($response = array_key_exists(LINKEDIN::_GET_RESPONSE, $_GET) ? $_GET[LINKEDIN::_GET_RESPONSE] : null) {
					$response = $this->linkedin->retrieveTokenAccess($_SESSION['oauth']['linkedin']['request']['oauth_token'], $_SESSION['oauth']['linkedin']['request']['oauth_token_secret'], $_GET['oauth_verifier']);
					if($response['success'] === TRUE) {
						// the request went through without an error, gather user's 'access' tokens
						$_SESSION['oauth']['linkedin']['access'] = $response['linkedin'];
					
						// set the user as authorized for future quick reference
						$_SESSION['oauth']['linkedin']['authorized'] = TRUE;
					
						// redirect the user back to the demo page
						$strRedirect = array_key_exists('redirect', $_GET) ? $_GET['redirect'] : null;
						header('Location: /wp-content/plugins/bs-social/handler.php?login=linkedin&redirect=' .$strRedirect);
					} else {
						// bad token access
						echo "Access token retrieval failed:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
					}
				}
				else {
					$response = $this->linkedin->retrieveTokenRequest();
					if($response['success'] === TRUE) {
						// store the request token
						$_SESSION['oauth']['linkedin']['request'] = $response['linkedin'];
							
						// redirect the user to the LinkedIn authentication/authorisation page to initiate validation.
						header('Location: ' . LINKEDIN::_URL_AUTH . $response['linkedin']['oauth_token']);
					} else {
						// bad token request
						echo "Request token retrieval failed:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
					}
				}
				break;
			case 'revoke':
				break;
			default:
				break;
		}
	}
	
	protected function GetCallbackUrl() {
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$protocol = 'https';
		} else {
			$protocol = 'http';
		}
		
		$url = $_SERVER['REQUEST_URI'];
		
		$strRedirect = array_key_exists('redirect', $_GET) ? $_GET['redirect'] : null;
		
		$url = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		
		// set the callback url
		return $protocol . '://' . $_SERVER['SERVER_NAME'] . ((($_SERVER['SERVER_PORT'] != 80) || ($_SERVER['SERVER_PORT'] != 446)) ? ':' . $_SERVER['SERVER_PORT'] : '') . $url . '?' . LINKEDIN::_GET_TYPE . '=initiate&' . LINKEDIN::_GET_RESPONSE . '=1&redirect=' . $strRedirect;
		
	}
	
	public function GetAuthUrl() {
		$url = $_SERVER['REQUEST_URI'];
		$url = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		return $url . '?' . LINKEDIN::_GET_TYPE . '=initiate';
	}
	
	protected function SessionHandler() {
		$blnAuthorized = (isset($_SESSION['oauth']['linkedin']['authorized'])) ? $_SESSION['oauth']['linkedin']['authorized'] : FALSE;
		
		if ($blnAuthorized) {
			$this->linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
			$this->linkedin->setResponseFormat(LINKEDIN::_RESPONSE_XML);
			
			$response = $this->linkedin->profile('~:(id,first-name,last-name,picture-url,email-address,main-address,phone-numbers,three-current-positions)');
			
			
			
			/*
			$this->name = $this->user_raw_data['name'];
			$this->first_name = $this->user_raw_data['first_name'];
			$this->last_name = $this->user_raw_data['last_name'];
			$this->username = $this->user_raw_data['username'];
			$this->location = $this->user_raw_data['location']['name'];
			*/
				
			if($response['success'] === TRUE) {
				$response['linkedin'] = new SimpleXMLElement($response['linkedin']);
				$this->linkedin_uid = (string) $response['linkedin']->{'id'};
				$this->user_raw_data = $response;
				$this->email = (string) $response['linkedin']->{'email-address'};
				$this->name = (string) $response['linkedin']->{'first-name'} . ' ' . (string) $response['linkedin']->{'last-name'};
				$this->first_name = (string) $response['linkedin']->{'first-name'};
				$this->last_name = (string) $response['linkedin']->{'last-name'};
				if ($response['linkedin']->{'three-current-positions'}) {
					if ($response['linkedin']->{'three-current-positions'}->{'@attributes'} > 0) {
						$position = $response['linkedin']->{'three-current-positions'}->{'position'}[0];
						$this->company = (string) $position->{'company'}->{'name'};
					}
				}
			} else {
				// request failed
				// echo "Error retrieving profile information:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response) . "</pre>";
			}
		}
	}
	
	public function IsUserLoggedIn() {
		if ($this->linkedin_uid) {
			return true;
		}
		
		return false;
	}
}