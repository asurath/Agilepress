<?php
/**
 * Class for handling all Module UI output as well as AJAX and menu registeration
 *
 * @since 1.0
 * @package AgilePress
 * @subpackage Module Administrator Page
 * @author AgilePress Core Developement Team
 *
 */
class AP_LoginRegistrationAdminPage extends AP_AdminPageBase {


	public static function Init(){
		add_submenu_page('ap-settings-home', 'Agile Press Login/Registration Manager', 'Login/Registration', 'administrator', 'ap-login-reg', array('AP_LoginRegistrationAdminPage', 'PageCreate'));
		if(!empty($_POST) && $_POST['action'] == "ajax")
			self::PageCreate();
			
	}

	/**
	 * Static method for creating and running this page class
	 *
	 * @since 1.0
	 * @return void
	 */
	public static function PageCreate(){
		if($_GET['page'] <> 'ap-login-reg') return;
		$strClass = get_class();
		$objPage =  new $strClass;
		$objPage->Run();
	}

	/**
	 * Method for handling all AJAX requests to this administrator page
	 *
	 * @since 1.0
	 * @return void;
	 */
	protected function DoAJAX(){
		$objXMLHandler = new AP_CoreCodeGenerator;
		if(isset($_POST['create-login-markup'])){
			if(isset($_POST['create-login-markup']['data']['facebook']) && is_array($_POST['create-login-markup']['data']['facebook']) && count($_POST['create-login-markup']['data']['facebook'])){
				update_option("FBAppID", $_POST['create-login-markup']['data']['facebook'][0] );
				update_option("FBAppSecret", $_POST['create-login-markup']['data']['facebook'][1]);
				update_option("FBAppRedirect", $_POST['create-login-markup']['data']['facebook'][2]);
			}
			if(isset($_POST['create-login-markup']['data']['linkedin']) && is_array($_POST['create-login-markup']['data']['linkedin']) && count($_POST['create-login-markup']['data']['linkedin'])){
				update_option("LIAppID", $_POST['create-login-markup']['data']['linkedin'][0] );
				update_option("LIAppSecret",  $_POST['create-login-markup']['data']['linkedin'][1] );
				update_option("LIAppRedirect",  $_POST['create-login-markup']['data']['linkedin'][2] );
			}
			if(isset($_POST['create-login-markup']['data']['google']) && is_array($_POST['create-login-markup']['data']['google']) && count($_POST['create-login-markup']['data']['google'])){
				update_option("GAppID", $_POST['create-login-markup']['data']['google'][0] );
				update_option("GAppSecret", $_POST['create-login-markup']['data']['google'][1]  );
				update_option("GAppRedirect", $_POST['create-login-markup']['data']['google'][2]  );
				update_option("GAppDevKey", $_POST['create-login-markup']['data']['google'][3]  );
			}
			if(isset($_POST['create-login-markup']['data']['twitter']) && is_array($_POST['create-login-markup']['data']['twitter']) && count($_POST['create-login-markup']['data']['twitter'])){
				update_option("TwtAppID", $_POST['create-login-markup']['data']['twitter'][0] );
				update_option("TwtAppSecret", $_POST['create-login-markup']['data']['twitter'][1] );
				update_option("TwtAppRedirect", $_POST['create-login-markup']['data']['twitter'][2] );
			}
			ob_start();
			$strFile = include( AP_MODULES_PATH . "/APLoginRegistration/LoginRegistrationMarkup.tpl.php");
			$strContents = ob_get_contents();
			ob_end_clean();
			echo json_encode(AP_IO::WriteFile(AP_PLUGIN_PATH . $objXMLHandler->strSiteSlug . "/pages/content/" . "LoginRegisration.mrkp.php", $strContents));
			print_r($_POST['create-login-markup']);
			die;
		}

	}

	/**
	 * Method for outputing all of this adminstrator page HTML content
	 *
	 * @since 1.0
	 * @return void
	 */
	protected function Content(){
		?>
		<h1 style="margin-left:15px;">AgilePress Login/Registration Manager</h1>
		<div style="width:850px;">
		<?php $this->AdminNavBarRender("mods"); ?>
		<div style="padding: 1px 20px; margin: 5px 15px 2px; margin-top:0; padding-top:0; height:850px; background:white; width:100%; ">
			<div style="clearfix:both; position:relative; padding-top:40px; float:left; width:100%; height:825px; overflow:auto; margin-bottom:60px;" >
				<input type="checkbox" name="echo" checked="checked"><label for="echo"> Show on function call </label><br>
				<input type="checkbox" name="remember" checked="checked"><label for="remember"> Show Remember me check box </label><br>
				<input type="checkbox" name="value-remember"  checked="checked"><label for="value-remember">Auto-Select remember me checkbox</label><br>
				<br>
				<label for="username-label">Username input label</label><br><input placeholder="Username: " type="textbox" name="username-label" value="Username: ">
				<br>
				<label for="password-label">Password input label</label><br><input  placeholder="Password: " type="textbox"  name="password-label" value="Password: ">
				<br>
				<label for="remember-label">Remember input label</label><br><input  placeholder="Keep me logged in: " type="textbox"  name="remember-label" value="Keep me logged in: ">
				<br>
				<label for="submit-label">Submit button label</label><br><input placeholder="Login" type="textbox"  name="submit-label" value="Login">
				<br><br>
				<input type="checkbox" class="social-enable" name="facebook-enable"><label for="facebook-enable"> Enable Facebook </label>
				<div>
					<input type="textbox" style="margin-left:20px; " class="facebook-enable" name="fb-app-id"  DISABLED><label for="fb-app-id"> Facebook App ID </label><br>
					<input type="textbox" style="margin-left:20px; " class="facebook-enable" name="fb-app-secret"  DISABLED><label for="fb-app-secret"> Facebook App Secret </label><br>
					<input type="textbox" style="margin-left:20px; " class="facebook-enable" name="fb-app-redirect"  DISABLED><label for="fb-app-redirect"> Facebook App Redirect </label><br>
				</div><br>
				<input type="checkbox" class="social-enable" name="linkedin-enable"><label for="linkedin-enable"> Enable LinkedIn </label>
				<div>
					<input type="textbox" style="margin-left:20px; " class="linkedin-enable" name="li-app-id"  DISABLED><label for="li-app-id"> LinkedIn App ID </label><br>
					<input type="textbox" style="margin-left:20px; " class="linkedin-enable" name="li-app-secret"  DISABLED><label for="li-app-secret"> LinkedIn App Secret </label><br>
					<input type="textbox" style="margin-left:20px; " class="facebook-enable" name="li-app-redirect"  DISABLED><label for="li-app-redirect"> LinkedIn App Redirect </label><br>
				</div><br>
				<input type="checkbox" class="social-enable" name="google-enable"><label for="google-enable"> Enable Google+ </label>
				<div>
					<input type="textbox" style="margin-left:20px; " class="google-enable" name="g-app-id"  DISABLED><label for="g-app-id"> Google App ID </label><br>
					<input type="textbox" style="margin-left:20px; " class="google-enable" name="g-app-secret"  DISABLED><label for="g-app-secret"> Google App Secret </label><br>
					<input type="textbox" style="margin-left:20px; " class="google-enable" name="g-app-redirect"  DISABLED><label for="g-app-redirect"> Google App Redirect </label><br>
					<input type="textbox" style="margin-left:20px; " class="google-enable" name="g-app-dev-id"  DISABLED><label for="g-app-dev-id"> Google App Developer ID </label><br>
				</div><br>
				<input type="checkbox" class="social-enable" name="twitter-enable"><label for="twitter-enable"> Enable Twitter </label>
				<div>
					<input type="textbox" style="margin-left:20px; " class="twitter-enable" name="twt-app-id"  DISABLED><label for="twt-app-id"> Twitter App ID </label><br>
					<input type="textbox" style="margin-left:20px; " class="facebook-enable" name="twt-app-secret"  DISABLED><label for="twt-app-secret"> Twitter App Secret </label><br>
					<input type="textbox" style="margin-left:20px; " class="facebook-enable" name="twt-app-redirect"  DISABLED><label for="twt-app-redirect"> Twitter App Redirect </label><br>
				</div><br>
				<button class="ap-admin-button-2" > Generate Markup File </button>
			</div>
		</div>
		<?php  }
		
	/**
	 * Method for outputing all of this administrators Javascript
	 *
	 * @since 1.0
	 * @return void
	 */
	protected function Scripts(){
		?>
			<script type="text/javascript">
				$(".social-enable").click(function(){ if($("." + $(this).attr('name')).prop("disabled")){$("." + $(this).attr('name')).prop('disabled', false); } else {$("." + $(this).attr('name')).prop('disabled', true); }  });

				$(document).ready(function(){
					$(".ap-admin-button-2").click(function(){

					var boolShowOnCall = $("input[name='echo']").prop("checked");
					var boolrememberUser = $("input[name='remember']").prop("checked");
					var boolAutoSelectRemember = $("input[name='value-remember']").prop("checked");
					var strUsernameLabel = $("input[name='username-label']").val();
					var strPasswordLabel = $("input[name='password-label']").val();
					var strRememberLabel = $("input[name='remember-label']").val();
					var strLoginButtonLabel = $("input[name='submit-label']").val();

					var boolEnableFacebook = $("input[name='facebook-enable']").prop("checked");
					if(boolEnableFacebook){
						var strFacebookID = $("input[name='fb-app-id']").val();
						var strFacebookSecret = $("input[name='fb-app-secret']").val();
						var strFacebookRedirect = $("input[name='fb-app-redirect']").val();
					}
					var boolEnableGoogle = $("input[name='google-enable']").prop("checked");
					if(boolEnableGoogle){
						var strGoogleID = $("input[name='g-app-id']").val();
						var strGoogleSecret = $("input[name='g-app-secret']").val();
						var strGoogleRedirect = $("input[name='g-app-redirect']").val();
						var strGoogleDevID = $("input[name='g-app-dev-id']").val();
					}
					var boolEnableLinkedIn = $("input[name='linkedin-enable']").prop("checked");
					if(boolEnableLinkedIn){
						var strLinkedInID = $("input[name='li-app-id']").val();
						var strLinkedInSecret = $("input[name='li-app-secret']").val();
						var strLinkedInRedirect = $("input[name='li-app-redirect']").val();
					}
					var boolEnableTwitter = $("input[name='twitter-enable']").prop("checked");
					if(boolEnableTwitter){
						var strTwitterID = $("input[name='twt-app-id']").val();
						var strTwitterSecret = $("input[name='twt-app-secret']").val();
						var strTwitterRedirect = $("input[name='twt-app-redirect']").val();
					}

					console.log({ "action" : "ajax", "create-login-markup" : { "data" :
						{ 
							"settings" : 
								[
									boolShowOnCall,
									boolrememberUser,
									boolAutoSelectRemember,
									strUsernameLabel,
									strPasswordLabel,
									strRememberLabel,
									strLoginButtonLabel
								]
							,
							"facebook" : 
								[
									strFacebookID,
									strFacebookSecret,
									strFacebookRedirect
								]
							,
							"google" : 
								[
									strGoogleID,
									strGoogleSecret,
									strGoogleRedirect,
									strGoogleDevID
								]
							,
							"linkedin" : 
								[
									strLinkedInID,
									strLinkedInSecret,
									strLinkedInRedirect
								]
							,
							"twitter" : 

								[
									strTwitterID,
									strTwitterSecret,
									strTwitterRedirect
								]
							
						}
					}});


					$.post("/wp-admin/admin.php?page=ap-login-reg", { "action" : "ajax", "create-login-markup" : { "data" :
						{ 
							"settings" : 
								[
									boolShowOnCall,
									boolrememberUser,
									boolAutoSelectRemember,
									strUsernameLabel,
									strPasswordLabel,
									strRememberLabel,
									strLoginButtonLabel
								]
							,
							"facebook" : 
								[
									strFacebookID,
									strFacebookSecret,
									strFacebookRedirect
								]
							,
							"google" : 
								[
									strGoogleID,
									strGoogleSecret,
									strGoogleRedirect,
									strGoogleDevID
								]
							,
							"linkedin" : 
								[
									strLinkedInID,
									strLinkedInSecret,
									strLinkedInRedirect
								]
							,
							"twitter" : 

								[
									strTwitterID,
									strTwitterSecret,
									strTwitterRedirect
								]
							
						}
					}}, function(data){
						console.log(data);
					});

				});
			});
				
			</script>
			<?php 
		}
	
	
}