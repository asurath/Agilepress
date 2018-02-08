<?php
require(__FRAMEWORK_PATH . '/library/fb/facebook.php');
class FacebookLoginBase extends SocialLogin {
	
	private $application_id;
	private $application_secret;
	private $facebook;
	private $channel_file;
	private $fb_uid;
	private $redirect_uri;
	
	public function __construct($application_id, $application_secret, $redirect_uri) {
		$this->application_id = $application_id;
		$this->application_secret = $application_secret;
		$this->redirect_uri = $redirect_uri;

		$this->Run();
	}
	
	protected function Init() {
		$this->facebook = new Facebook(array(
				'appId'  => $this->application_id,
				'secret' => $this->application_secret,
		));

		$this->SetChannelFile();
	}
	
	protected function SessionHandler() {
		$this->fb_uid = $this->facebook->getUser();
		
		if ($this->fb_uid) {
			try {
				$this->user_raw_data = $this->facebook->api('/me');
				
				if ($this->user_raw_data) {
					$this->email = $this->user_raw_data['email'];
					$this->name = $this->user_raw_data['name'];
					$this->first_name = $this->user_raw_data['first_name'];
					$this->last_name = $this->user_raw_data['last_name'];
					$this->username = $this->user_raw_data['username'];
					$this->location = $this->user_raw_data['location']['name'];
				}
				else {
					$this->fb_uid = null;
				}
			} catch (FacebookApiException $e) {
				$this->fb_uid = null;
			}
		}
	}
	
	public function SetChannelFile($path = null, $host = null) {
		if (!$host)
			$host = $_SERVER['HTTP_HOST'];
		
		if (!$path)
			$path = '/social/library/fb/channel.php';
		
		$this->channel_file = "//$host$path";
	}
	
	public function IsUserLoggedIn() {
		if ($this->fb_uid) {
			return true;
		}
		
		return false;
	}
	
	public function PrintScripts() { ?>
		<div id="fb-root"></div>
		<script>
		  // Additional JS functions here
		  window.fbAsyncInit = function() {
		    FB.init({
		      appId      : '<?=$this->application_id?>', // App ID
		      channelUrl : '<?=$this->channel_file?>', // Channel File
		      status     : true, // check login status
		      cookie     : true, // enable cookies to allow the server to access the session
		      xfbml      : true  // parse XFBML
		    });	
		  };
		  
	
		  function login() {
			    FB.login(function(response) {
			        if (response.authResponse) {
			        	window.location.href = '<?=$this->redirect_uri?>';
			        } else {
			            // cancelled
			        }
			    }, {scope:'email'});
			}

			function logout() {
				FB.logout(function(response) {
					// user is now logged out
					window.location.reload();
				});
			}
		
		  // Load the SDK Asynchronously
		  (function(d){
		     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
		     if (d.getElementById(id)) {return;}
		     js = d.createElement('script'); js.id = id; js.async = true;
		     js.src = "//connect.facebook.net/en_US/all.js";
		     ref.parentNode.insertBefore(js, ref);
		   }(document));

		  fbAsyncInit();
		</script>
	<?php	
	}
}