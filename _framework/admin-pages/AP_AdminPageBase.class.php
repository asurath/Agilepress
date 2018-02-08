<?php

/**
 * Administrator Page Base is a near copy of Page Base and contains all the logic required to output an entire admin page.
 * All AP administrator pages extend from Admin Page Base
 * 
 * @property string $strID
 * @property string $strTemplate
 * @property boolean $boolUseSidebar
 * 
 * @since 1.0
 * @package AgilePress
 * @subpackage Administrator Page Base
 * @author AgilePress Core Developement Team
 * 
 */

class AP_AdminPageBase extends AP_Base {
	
	//Protected properties
	protected $strID = 'default-page';
	protected $strTemplate;
	protected $boolUseSidebar = false;
	
	/**
	 * Class constructor
	 * 
	 * @since 1.0
	 * @return boolean
	 */
	public function __construct(){
		return true;
	}
	
	/**
	 * Page Init functionality
	 * 
	 * @since 1.0
	 * @return unknown
	 */ 
	public static function Init(){}
	
	/**
	 *  Check if current request is an ajax request and diverts code flow depending on result
	 *  
	 *  @since 1.0
	 *  @return unknown 
	 */
	protected function CheckAJAX(){
		if ($this->IsAJAX()){
			$this->DoAJAX();
			exit();
		}
	}
	
	/**
	 * Returns a boolean depending on if current request is an 
	 * 
	 * @since 1.0
	 * @return boolean $boolIsAjax
	 */ 
	protected function IsAJAX(){
		return ($_POST['action'] == 'ajax') ? true : false;
	}
	
	/**
	 * Base function definition for a pages AJAX handler
	 * 
	 * @since 1.0
	 * @return unknown
	 */
	protected function DoAJAX(){}
	
	/**
	 * Includes code required before a page renders
	 * 
	 * @since 1.0
	 * @return unknown
	 */
	protected function Prerender(){}

	/**
	 * Start of the content
	 * 
	 * @since 1.0
	 * @return string $strContentStart
	 */
	protected function ContentStart(){ ?>
		<div id="main">
	<?php }
	
	/**
	 * Content
	 *
	 * @since 1.0
	 * @return string $strContent
	 */
	protected function Content(){}
	
	/**
	 * End of the content
	 *
	 * @since 1.0
	 * @return string $strContentEnd
	 */
	protected function ContentEnd(){ ?>
		</div><!-- #main -->
	<?php }
	
	/**
	 * Page sidebar functionality call
	 *
	 * @since 1.0
	 * @return string $strSidebar
	 */
	protected function Sidebar(){ GetSidebar(); }
	
	/**
	 * Page scripts call
	 *
	 * @since 1.0
	 * @return string $strScripts
	 */
	protected function Scripts(){}
	
	/**
	 * Page footer functionality call
	 *
	 * @since 1.0
	 * @return string $strFooter
	 */
	protected function Footer(){ GetFooter(); }
	
	/**
	 * Page pre-end functionality
	 * 
	 * @since 1.0
	 * @return unknown
	 */
	protected function End(){}
	
	
	/**
	 * Function wrapping for page method method call order
	 * 
	 * @since 1.0
	 * @return void
	 */
	public function Run(){
		$this->CheckAJAX();
		$this->Prerender();
		$this->ContentStart();
		$this->Content();
		$this->ContentEnd();

		if ($this->UseSidebar()){
			$this->Sidebar();
		}

		$this->Scripts();
		$this->End();
	}

	/**
	 * function for setting the boolean value that determines if the sidebar page method will be called
	 * 
	 * @param string $boolean
	 * @return boolean
	 */
	public function UseSidebar($boolean = null){
		if (isset($boolean)){
			$this->boolUseSidebar = $boolean;
			return $this;
		} else {
			return $this->boolUseSidebar;
		}
	}
	
	/**
	 * function for setting the template file associated with this class
	 *
	 * @param string $boolean
	 * @return void
	 */
	public function SetTemplate($strfile){
		$this->strTemplate = $strfile;
	}
	
	/**
	 * Error output wrapper to help render the appropriate error for any given admin error
	 * 
	 * @since 1.0
	 * @param string $strValue (Error)
	 * @return string $strErrorHTML
	 */
	protected function AdminError($strValue){
		?>
		<div class="error">
		<p><?= $strValue;?></p>
		</div>
		<?php 
	}
	
	/**
	 * Render method for checking if CodeGeneration is update and displaying the CodeGeneration error
	 * 
	 * @since 1.0
	 * @return string $ErrorHTML
	 */
	protected function AdminCodeGenError(){
		?>
		<script type="text/javascript">
		function CodeGenOffPage(){
			$.post("/wp-admin/admin.php?page=ap-code-gen-settings",{ "action" : "ajax" , "CodeGen" : true }, function(data){ document.location.reload(); });
		}
		</script>
		<div class="error" style="border-left: 4px solid rgb(241, 146, 7) !important; margin-top:30px;">
		<p style="padding-top:10px;" >You have changes to your AgilePress configuration that have not been codegened</p>
		<br>
		<a class="ap-admin-button" style="" onclick="CodeGenOffPage(); return false;" href = "">
			<span class="ap-admin-button-2">
				<span > Update Code </span>
			</span>
		</a>
		</div>
		<?php 
		}
	
	/**
	 * Render method for the Administrator UI navigation bar
	 * 
	 * @since 1.0
	 * @param string $strPage
	 * @return string $NavigationBarHTML
	 */
	protected function AdminNavBarRender($strPage){
		$strPage = strtolower($strPage);
		$objCodeCheck = new AP_CoreCodeGenerator;
		if($this->AdminTablesError()){
			get_footer();
			die;
		}
		if(!isset($objCodeCheck->objConfigData))
			$this->AdminInstall();
		elseif(intval(get_option("AP_CodeGen_Version"))<>intval($objCodeCheck->objConfigData->title->version))
			$this->AdminCodeGenError(); 
		?>
			<div class="ap-admin-nav" style=" padding: 1px 20px; margin: 5px 15px 2px; background: -moz-linear-gradient(rgb(115,115,115),rgb(76,76,76)); background: -o-linear-gradient(rgb(115,115,115),rgb(76,76,76)); background: -webkit-linear-gradient(rgb(115,115,115),rgb(76,76,76)); background: linear-gradient(rgb(115,115,115),rgb(76,76,76)); height:35px; margin-top:40px; width:100%;">
				<a id="ap-nav-home" href="/wp-admin/admin.php?page=ap-about" <?= ($strPage == 'about') ?  'style="background:rgb(52,52,52);"' : "" ?>><i class="fa fa-home fa-2x" style="margin-top:5px;"></i></a>
				<a href="/wp-admin/admin.php?page=ap-site-settings" <?= ($strPage == 'settings') ?  'style="background:rgb(52,52,52);"' : "" ?> >Settings</a>
				<a href="/wp-admin/admin.php?page=ap-post-types" <?= ($strPage == 'posts') ?  'style="background:rgb(52,52,52);"' : "" ?> >Posts</a>
				<a href="/wp-admin/admin.php?page=ap-user-types" <?= ($strPage == 'users') ?  'style="background:rgb(52,52,52);"' : "" ?> >Users</a>
				<a href="/wp-admin/admin.php?page=ap-taxonomies"  <?= ($strPage == 'tax') ?  'style="background:rgb(52,52,52);"' : "" ?> >Taxonomies</a>
				<a href="/wp-admin/admin.php?page=ap-modules" <?= ($strPage == 'mods') ?  'style="background:rgb(52,52,52);"' : "" ?> >Modules</a>
				<a href="/wp-admin/admin.php?page=ap-code-gen-settings" <?= ($strPage == 'codegen') ?  'style="background:rgb(52,52,52);"' : "" ?> >CodeGen</a>
			</div>
		<?php 
	}
	
	/**
	 * Render method for the Configuration file missing error
	 *
	 * @since 1.0
	 * @param string $strPage
	 * @return string $ErrorHTML
	 */
	protected function AdminInstall(){
			?>
			<script type="text/javascript">
			function CodeGenOffPage(){
				$.post("/wp-admin/admin.php?page=ap-code-gen-settings",{ "action" : "ajax" , "CodeGen" : true }, function(data){ document.location.reload(); });
			}
			</script>
			<div class="error" style="border-left: 4px solid rgb(241, 146, 7) !important; margin-top:30px; overflow:hidden;">
			<p style="padding-top:10px;" >You have changes to your AgilePress configuration that have not been codegened</p>
			<br>
			<a class="ap-admin-button" style="float:left;" href = "/wp-admin/admin.php?page=ap-site-settings">
				<span class="ap-admin-button-2">
					<span > GoTo Settings </span>
				</span>
			</a>
			<a class="ap-admin-button clearfix" style="float:left; margin-left:30px;" onclick="CodeGenOffPage(); return false;" href = "">
				<span class="ap-admin-button-2">
					<span > Update Code </span>
				</span>
			</a>
			</div>
			<?php 
	}
	
	/**
	 * Render method for the AgilePress tables missing error
	 *
	 * @since 1.0
	 * @return string $ErrorHTML
	 */
	protected function AdminTablesError(){
		global $wpdb;
		$strTableNameArray = array ( 
			strtolower($wpdb->prefix . AP_PREFIX . "taxonomies"),
			strtolower($wpdb->prefix . AP_PREFIX . "modules"),
			strtolower($wpdb->prefix . AP_PREFIX . "post_type"),
			strtolower($wpdb->prefix . AP_PREFIX . "post_field"),
			strtolower($wpdb->prefix . AP_PREFIX . "post_type_field"),
			strtolower($wpdb->prefix . AP_PREFIX . "user_type"),
			strtolower($wpdb->prefix . AP_PREFIX . "user_field"),
			strtolower($wpdb->prefix . AP_PREFIX . "user_type_field"),
			strtolower($wpdb->prefix . AP_PREFIX . "user_roles"),
			strtolower($wpdb->prefix . AP_PREFIX . "tracker")
		);
		
		
		
		$boolTablesExist = true;
		foreach($strTableNameArray as $strTableName){
			if($wpdb->get_var("SHOW TABLES LIKE '$strTableName'") != $strTableName) {
				$boolTablesExist = false;
				break;
			}
		}
		if(!$boolTablesExist){
			?>
			<div class="error" style="border-left: 4px solid red !important; margin-top:30px;">
				<p style="padding:10px 0;" >One or more AgilePress tables does not exists, please reactivate AgilePress to create required tables</p>
				<br>
				<p> Tables looked for: </p>
				<br>
				<pre>
					<?php print_r($strTableNameArray); ?>
				</pre>
			</div>
			<?php 
			return true;
		}
		return false;
	}
	
}