<?php
/**
 * Class for handling all General Settings output as well as AJAX and menu registeration
 *
 * @since 1.0
 * @package AgilePress
 * @subpackage General Settings Administrator Page
 * @author AgilePress Core Developement Team
 *
 */
class AP_SettingsAdminPage extends AP_AdminPageBase {

	/**
	 * Called on the wordpress hook admin_init, this method registers the sub_menu AgilePress item "Settings" and redirects admin AJAX calls back to the page
	 *
	 * @since 1.0
	 * @return void
	 */
	public static function Init(){
		add_submenu_page('ap-settings-home', 'Agile Press Site Manager', 'Settings', 'administrator', 'ap-site-settings', array('AP_SettingsAdminPage', 'PageCreate'));
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
		if($_GET['page'] <> 'ap-site-settings') return;
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
		unset($_POST['action']);
		
		// If POST contains a save parameter, execute save logic
		if(isset($_POST['save'])){
			//Initiate configuration data handler
			$objConfigDataHandler = new AP_CoreCodeGenerator;
			if(!isset($objConfigDataHandler->objConfigData)){
				$objConfigDataHandler->CreateConfigurationXML();
				$objConfigDataHandler->objConfigData = $objConfigDataHandler->GetConfigurationData();
			}
			//Save site name
			if(!isset($objConfigDataHandler->objConfigData->settings->sitename))
				$objConfigDataHandler->objConfigData->settings->addChild("sitename",$_POST['save']['name']);
			else
				$objConfigDataHandler->objConfigData->settings->sitename = $_POST['save']['name'];
			
			//Save site slug
			if(!isset($objConfigDataHandler->objConfigData->settings->siteslug))
				$objConfigDataHandler->objConfigData->settings->addChild("siteslug",$_POST['save']['slug']);
			else
				$objConfigDataHandler->objConfigData->settings->siteslug = $_POST['save']['slug'];
			
			//Save production host
			if(!isset($objConfigDataHandler->objConfigData->settings->productionhost))
				$objConfigDataHandler->objConfigData->settings->addChild("productionhost",$_POST['save']['production_host']);
			else
				$objConfigDataHandler->objConfigData->settings->productionhost = $_POST['save']['production_host'];
			
			//Save email enabled boolean
			if(!isset($objConfigDataHandler->objConfigData->settings->emailcheck))
				$objConfigDataHandler->objConfigData->settings->addChild("emailcheck",$_POST['save']['email_check']);
			else 
				$objConfigDataHandler->objConfigData->settings->emailcheck = $_POST['save']['email_check'];
			
			//Parse and save email list
			$regex = "/[\n,]+/";
			$strEmailArray = preg_split($regex,$_POST['save']['email_list']);
			foreach($strEmailArray as $index => $value)
				$strEmailArray[$index] = trim($value);
			if(!isset($objConfigDataHandler->objConfigData->settings->emaillist))
				$objConfigDataHandler->objConfigData->settings->addChild("emaillist",serialize($strEmailArray));
			else
				$objConfigDataHandler->objConfigData->settings->emaillist = serialize($strEmailArray);
			
			//Save configuration file, return true to javascript function
			$objConfigDataHandler->SaveConfigurationData();
			echo json_encode(true);
			die;
		}
		die;
	}

	/**
	 * Method for outputing all of this adminstrator page HTML content
	 *
	 * @since 1.0
	 * @return void
	 */
	protected function Content(){
		$objCodeCheck = new AP_CoreCodeGenerator();
		?>
		<h1 style="margin-left:15px;">AgilePress Settings
			</h1>
			<div style="width:850px;">
			<?php $this->AdminNavBarRender('settings');?>
			<div style="padding: 1px 20px; margin: 5px 15px 2px; margin-top:0; padding-top:0; height:700px; background:white; width:100%; ">
				<p class="ap-settings-field"> Site Name </p>
				<input type="text" id="site-name" value="<?php _e($objCodeCheck->objConfigData->settings->sitename)?>" class="ap-settings-field-input"><br>
				<p class="ap-settings-field" > Site Slug </p>
				<input type="text" id="site-slug" class="ap-settings-field-input" value="<?php echo $objCodeCheck->objConfigData->settings->siteslug; ?>" <?= (!isset($objCodeCheck->objConfigData))  ? "":"DISABLED" ;  ?> /><br>
				<p class="ap-settings-field-note">(This field cannot be modified after initial configuration file generation)</p>
				<p class="ap-settings-field"> Production Hostname </p>
				<input type="text" id="production-host" value="<?php _e($objCodeCheck->objConfigData->settings->productionhost)?>" class="ap-settings-field-input" ><br>
				<p class="ap-settings-field-note">(e.g. Business-Software.com)</p> <br>
				<label for="uses-email-notifications" class="ap-settings-field" style="margin:0; padding:0; padding-bottom:15px;">  Email notifications </label><br>
				<input type="radio" class="email-check" value="1" <?php checked($objCodeCheck->objConfigData->settings->emailcheck, 1);?> name="uses-email-notifications"><label for="uses-email-notifications" class="ap-settings-field" style="margin:0; padding:0; padding-bottom:5px; font-size:12px;">  Enable </label><br>
				<input type="radio" class="email-check" value="0" <?php checked($objCodeCheck->objConfigData->settings->emailcheck, 0);?> name="uses-email-notifications"><label for="uses-email-notifications" class="ap-settings-field" style="margin:0; padding:0; padding-bottom:5px; font-size:12px;">  Disable </label><br>
				<textarea id="email-list" style="width:400px; height:200px; border-radius:5px; margin-top:10px;"><?php $strEmailArray = unserialize($objCodeCheck->objConfigData->settings->emaillist); if(is_array($strEmailArray)) foreach($strEmailArray as $strEmail) _e($strEmail . " , ");?></textarea>
				<p class="ap-settings-field-note" style="margin-left:25px; margin-bottom:30px;">Email addresses can be seperated by line or comma</p>
				<a class="ap-admin-button" style="" onclick="SaveSettings(); return false;">
					<span class="ap-admin-button-2">
						<span style=""> Save Settings </span>
					</span>
				</a>
			</div>
		</div>
		<?php }
			
	/**
	 * Method for outputing all of this administrators Javascript
	 *
	 * @since 1.0
	 * @return void
	 */
	protected function Scripts(){
		?>
		<script type="text/javascript">
			function SaveSettings(){
				var strSiteName = $("#site-name").val();
				var strSiteSlug = $("#site-slug").val();
				var strProductionHost = $("#production-host").val();
				var boolEmailOn = $(".email-check:checked").val();
				var strEmailList = $("#email-list").val();

				var mixSiteDataArray = {
						"name" : strSiteName,
						"slug" : strSiteSlug,
						"production_host" : strProductionHost,	
						"email_check" : boolEmailOn,
						"email_list" : strEmailList	
				}

				$.post("",{
					"action" : "ajax",
					"save" : mixSiteDataArray	
				},function(data){
					console.log(data);
					var $objError = $.parseJSON(data); 
					if($objError){
						document.location.reload(); 
					}
					else{
					}
				});
			}
		</script>
		<?php 
	}
			
}
	
	