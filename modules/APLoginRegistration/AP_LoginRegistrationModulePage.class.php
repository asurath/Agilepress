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
			if(!isset($objXMLHandler->strSiteSlug) || !is_string($objXMLHandler->strSiteSlug) || strlen($objXMLHandler->strSiteSlug) < 3)
				return;
			ob_start();
			$strFile = include( AP_MODULES_PATH . "/APLoginRegistration/LoginRegistrationMarkup.tpl.php");
			$strContents = ob_get_contents();
			ob_end_clean();
			echo json_encode(AP_IO::WriteFile(AP_PLUGIN_PATH . $objXMLHandler->strSiteSlug . "/pages/content/" . "LoginRegisration.mrkp.php", $strContents));
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
		<div class="ap-admin-nav" style=" padding: 1px 20px; margin: 5px 15px 2px; background: -moz-linear-gradient(rgb(76,76,76),rgb(115,115,115)); background: -o-linear-gradient(rgb(76,76,76),rgb(115,115,115)); background: -webkit-linear-gradient(rgb(76,76,76),rgb(115,115,115),); background: linear-gradient(rgb(76,76,76),rgb(115,115,115)); height:35px; margin-top:-2px; width:100%;">
		</div>
		<div style="padding: 1px 20px; margin: 5px 15px 2px; margin-top:0; padding-top:0; height:850px; background:white; width:100%; ">
			<div style="clearfix:both; position:relative; padding-top:40px; float:left; width:100%; height:825px; overflow:auto; margin-bottom:60px;" >
				<input type="checkbox" name="echo" checked="checked"><label for="echo"> Show on function call </label><br>
				<input type="checkbox" name="remember" checked="checked"><label for="remember"> Show Remember me check box </label><br>
				<input type="checkbox" name="value-remember"  checked="checked"><label for="value-remember">Auto-Select remember me checkbox</label><br>
				<br>
				<label for="username-label">Username input label</label><br><input placeholder="Username: " type="textbox" name="username-label">
				<br>
				<label for="password-label">Password input label</label><br><input  placeholder="Password: " type="textbox"  name="password-label">
				<br>
				<label for="remember-label">Remember input label</label><br><input  placeholder="Keep me logged in: " type="textbox"  name="remember-label">
				<br>
				<label for="submit-label">Submit button label</label><br><input placeholder="Login" type="textbox"  name="submit-label">
				<br><br>
				<input type="checkbox" class="social-enable" name="facebook-enable"><label for="facebook-enable"> Enable Facebook </label>
				<div>
					<input type="textbox" style="margin-left:20px; " class="facebook-enable" name="fb-client-id"  DISABLED><label for="fb-client-id"> Facebook Client ID </label><br>
					<input type="checkbox" style="margin-left:20px;"class="facebook-enable" name="fb-use-current" CHECKED="CHECKED" DISABLED><label for="fb-use-current"> Use initial page as redirect URL </label><br>
					<input type="textbox" style="margin-left:40px;" class="facebook-enable" name="fb-redirect-url" DISABLED><label for="fb-redirect-url"> Redirect URL on success</label>
				</div><br>
				<input type="checkbox" class="social-enable" name="linkedin-enable"><label for="linkedin-enable"> Enable LinkedIn </label>
				<div>
					<input type="textbox" style="margin-left:20px; " class="linkedin-enable" name="li-client-id"  DISABLED><label for="li-client-id"> LinkedIn Client ID </label><br>
					<input type="checkbox" style="margin-left:20px;" class="linkedin-enable"  name="li-use-current" CHECKED="CHECKED" DISABLED><label for="li-use-current"> Use initial page as redirect URL </label><br>
					<input type="textbox" style="margin-left:40px;" class="linkedin-enable" name="li-redirect-url" DISABLED><label for="li-redirect-url"> Redirect URL on success</label>
				</div><br>
				<input type="checkbox" class="social-enable" name="google-enable"><label for="google-enable"> Enable Google+ </label>
				<div>
					<input type="textbox" style="margin-left:20px; " class="google-enable" name="g-client-id"  DISABLED><label for="g-client-id"> Google Client ID </label><br>
				</div><br>
				<input type="checkbox" class="social-enable" name="twitter-enable"><label for="twitter-enable"> Enable Twitter </label>
				<div>
					<input type="textbox" style="margin-left:20px; " class="twitter-enable" name="twt-client-id"  DISABLED><label for="twt-client-id"> Twitter Client ID </label><br>
					<input type="checkbox" style="margin-left:20px;"  class="twitter-enable" name="twt-use-current" CHECKED="CHECKED" DISABLED><label for="twt-use-current"> Use initial page as redirect URL </label><br>
					<input type="textbox" style="margin-left:40px;" class="twitter-enable" name="twt-redirect-url" DISABLED><label for="twt-redirect-url"> Redirect URL on success</label>
				</div><br>
				<button class="ap-admin-button-2" onclick="GenerateMarkup();" > Generate Markup File </button>
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
				$(".social-enable").click(function(){ console.log(); if($("." + $(this).attr('name')).prop("disabled")){$("." + $(this).attr('name')).prop('disabled', false); } else {$("." + $(this).attr('name')).prop('disabled', true); }  });

				function GenerateMarkup(){
					$.post("/wp-admin/admin.php?page=ap-login-reg", { "action" : "ajax", "create-login-markup" : true}, function(data){
						console.log(data);
					});

				}
			</script>
			<?php 
		}
	
	
}