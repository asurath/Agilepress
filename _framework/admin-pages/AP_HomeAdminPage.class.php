<?php
/**
 * Class for handling all About UI output as well as AJAX and menu registeration
 *
 * @since 1.0
 * @package AgilePress
 * @subpackage About Administrator Page
 * @author AgilePress Core Developement Team
 *
 */
class AP_HomeAdminPage extends AP_AdminPageBase {
	
	/**
	 * Called on the wordpress hook admin_init, this method registers the sub_menu AgilePress item "About" and redirects admin AJAX calls back to the page
	 *
	 * @since 1.0
	 * @return void
	 */
	public static function Init(){
		add_submenu_page('ap-settings-home', 'Agile Press Manager', 'About', 'administrator', 'ap-about', array('AP_HomeAdminPage', 'PageCreate'));
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
		if($_GET['page'] <> 'ap-about') return;
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
			die;
	}
	
	/**
	 * Method for outputing all of this adminstrator page HTML content
	 *
	 * @since 1.0
	 * @return void
	 */
	protected function Content(){
		?>
		<h1 style="margin-left:15px;">AgilePress About</h1>
		<div style="width:850px;">
			<?php  $this->AdminNavBarRender('about'); ?>
			<div style="padding: 1px 20px; margin: 5px 15px 2px; margin-top:0; padding-top:0; height:850px; background:white; width:100%; ">
				<br>
				<h2> AgilePress BETA V0.0
				<br>
				<h2> Developed By: Arun Surath, Phillam Lau </h2>
			</div>
		</div>
		<?php 
			}
			
	/**
	 * Method for outputing all of this administrators Javascript
	 *
	 * @since 1.0
	 * @return void
	 */
	protected function Scripts(){}
		
	}
	
	