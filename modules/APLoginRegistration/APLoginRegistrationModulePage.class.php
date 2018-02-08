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
class AP_ModuleAdminPage extends AP_AdminPageBase {

	/**
	 * Called on the wordpress hook admin_init, this method registers the sub_menu AgilePress item "Modules" and redirects admin AJAX calls back to the page
	 *
	 * @since 1.0
	 * @return void
	 */
	public static function Init(){
		add_submenu_page('ap-settings-home', 'Agile Press Module Manager', 'Modules', 'administrator', 'ap-modules', array('AP_ModuleAdminPage', 'PageCreate'));
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
		if($_GET['page'] <> 'ap-modules') return;
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
		global $wpdb;
		unset($_POST['action']);
		$strTableName = strtolower($wpdb->prefix . AP_PREFIX . "modules");
		if(isset($_POST['RegisterModule'])){
			$strModuleSlug = strtolower($_POST['RegisterModule']);
			$strCheckArray = $wpdb->get_results("SELECT slug FROM $strTableName", ARRAY_A);
			$boolExistsCheck = false;
			if(is_array($strCheckArray)){
				foreach($strCheckArray as $strSingleArray)
					if(in_array($strModuleSlug, $strSingleArray)){
						$boolExistsCheck = true;
						break;
					}
			}
			if(!$boolExistsCheck) {
				$wpdb->insert($strTableName, array("slug" => $strModuleSlug));
				echo json_encode(true);
			}
			else
				echo json_encode(false);
			die;	
		}
		if(isset($_POST['UnregisterModule'])){
			$strModuleSlug = strtolower($_POST['UnregisterModule']);
			$strCheckArray = $wpdb->get_results("SELECT slug FROM $strTableName", ARRAY_A);
			$boolExistsCheck = false;
			if(is_array($strCheckArray)){
				foreach($strCheckArray as $strSingleArray)
				if(in_array($strModuleSlug, $strSingleArray)){
					$boolExistsCheck = true;
					break;
				}
			}
			if($boolExistsCheck) {
				$wpdb->delete($strTableName, array("slug" => $strModuleSlug));
				echo json_encode(true);
			}
			else
				echo json_encode(false);
			die;
		}
		if(isset($_POST['DeleteModule'])){
			$wpdb->delete($strTableName, array("slug" => $_POST['DeleteModule']));
			unlink(AP_PATH . "/modules/" . $_POST['DeleteModule']);
			echo json_encode(true);
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
		<h1 style="margin-left:15px;">AgilePress Module Manager</h1>
		<div style="width:850px;">
		<?php $this->AdminNavBarRender("mods"); ?>
		<div style="padding: 1px 20px; margin: 5px 15px 2px; margin-top:0; padding-top:0; height:850px; background:white; width:100%; ">
			<p class="ap-settings-field" style="font-weight:bold; float:left; margin-top:15px; font-size:18px;">Modules </p>
				<div style="clearfix:both; position:relative; padding-top:40px; float:left; border-style:solid; top:40px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:715px; overflow:auto; margin-bottom:60px;" >
					<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-o-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-webkit-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background: linear-gradient(rgb(45,114,206),rgb(2,74,171));">
						<p class="ap-post-key">Name</p><p class="ap-post-key" style="width:44%;">Description</p><p class="ap-post-key" style="width:20%;">Menu</p>
					</div>
					<?php $objData = array(); $objModules = new AP_Modules(); $objData = $objModules->ModuleArray; $objDataTrack = array(); $boolBackground = true; $intPixCount = 40; foreach($objData as $value){ if(in_array(strtolower($value['path']), $objModules->RegisteredModuleArray )){ $objDataTrack[] = $value['path'];  $objDataTrack[] = $value->slug;?>
					<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:relative; left:0; overflow:hidden; width:100%; height:auto; ">
						<p class="ap-post-key-row" style="height:auto;"><?php _e($value['name']);?><br style="margin: 0 0 10px;"><a class="module-deactivate" data-module='<?php _e(json_encode($value));?>' >Deactivate</a>|<a class="module-delete" >Delete</a></p><p class="ap-post-key-row" style="height:auto; width:44%; float:left;"><?php _e($value['description']);?><br style="margin:0 0 10px;"><span>Version: <?php _e($value['version'])?></span> | <span>By: <a style="text-decoration:none;" href="<?php  _e($value['author_website'])?>"><?php _e($value['author'])?></a></span></p><p class="ap-post-key-row" style="width:20%; height:auto;"><button>Menu</button></p>
					</div>
					<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; }} ?> 
					<?php $objData = $objModules->ModuleArray; foreach($objData as $value){ if(!in_array(strtolower($value['path']), $objModules->RegisteredModuleArray)){ $objDataTrack[] = $value['path']; ?>
					<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:relative; left:0; overflow:hidden; width:100%; height:auto; ">
						<p class="ap-post-key-row" style="height:auto;"><?php _e($value['name']);?><br style="margin: 0 0 10px;"><a class="module-activate" data-module='<?php _e(json_encode($value));?>' >Activate</a>|<a class="module-delete" >Delete</a></p><p class="ap-post-key-row" style="height:auto; width:44%; float:left;"><?php _e($value['description']);?><br style="margin:0 0 10px;"><span>Version: <?php _e($value['version'])?></span> | <span>By: <a style="text-decoration:none;" href="<?php  _e($value['author_website'])?>"><?php _e($value['author'])?></a></span></p><p class="ap-post-key-row" style="width:20%; height:auto;"><button>Menu</button></p>
					</div>
					<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; }} ?> 
					<?php if(empty($objDataTrack)){?>
					<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:relative; left:0; width:100%; height:40px; ">
						<p class="ap-post-key-row">None</p>
					</div>
					<?php }?>
				</div>
				<?php if($boolCodeGenCheck){?>
					<p class="ap-settings-field-note" style="position:relative; top:425px; color:rgb(241, 146, 7); left:-80px;padding:0; margin:0;">*Taxonomy Types bordered in orange have not been had their code generated yet</p>
				<?php }?>
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
			
			var CurrentType;
	
			$(document).ready(function(){

				$(".module-delete").click(function(){
					CurrentType = JSON.parse($(this).siblings("a:first").attr("data-module"));
					var boolCheck = confirm("Are you sure you want to delete the " + CurrentType.name + " module?");
					if(boolCheck)
						$.post("",{"action":"ajax","DeleteModule":CurrentType.path }, function(data){
							var boolReturnCheck = JSON.parse(data);
							if(boolReturnCheck)
								document.location.reload();
						});

				});
				
				$(".module-activate").click(function(){
					CurrentType = JSON.parse($(this).attr("data-module"));
					$.post("",{"action":"ajax","RegisterModule": CurrentType.path}, function(data){
						var boolReturnCheck = JSON.parse(data);
						if(boolReturnCheck)
							document.location.reload();
						else
							alert("ERROR: This plugin is already activated");
					});
				});

				$(".module-deactivate").click(function(){
					CurrentType = JSON.parse($(this).attr("data-module"));
					$.post("",{"action":"ajax","UnregisterModule": CurrentType.path}, function(data){
						var boolReturnCheck = JSON.parse(data);
						if(boolReturnCheck)
							document.location.reload();
						else
							alert("ERROR: Module registration entry not found");
					});
				});
				
				});

			</script>
			<?php 
		}
	
	
}