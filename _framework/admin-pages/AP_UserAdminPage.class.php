<?php
/**
 * Class for handling all User Type output as well as AJAX and menu registeration
 *
 * @since 1.0
 * @package AgilePress
 * @subpackage User Type Administrator Page
 * @author AgilePress Core Developement Team
 *
 */
class AP_UserAdminPage extends AP_AdminPageBase {

	/**
	 * Called on the wordpress hook admin_init, this method registers the sub_menu AgilePress item "User Types" and redirects admin AJAX calls back to the page
	 *
	 * @since 1.0
	 * @return void
	 */
	public static function Init(){
		add_submenu_page('ap-settings-home', 'Agile Press Custom User Types Manager', 'Users', 'administrator', 'ap-user-types', array('AP_UserAdminPage', 'PageCreate'));
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
		if($_GET['page'] <> 'ap-user-types') return;
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
		$objXMLHandler = new AP_CoreCodeGenerator;
		if(isset($_POST['CodeGen'])){
			$obj = new AP_CoreCodeGenerator;
			$obj->CodeGenRun();
			die;
		}
		if(isset($_POST['new-role-data'])){
			$boolIsIn = false;
			foreach($objXMLHandler->objConfigData->userroles->userrole as $UserRole){
				$UserRole = unserialize($UserRole);
				if($UserRole['name'] == $_POST['new-role-data']['name'] )
					$boolIsIn = true;
				if($UserRole['slug'] == $_POST['new-role-data']['name'] )
					$boolIsIn = true;
			}
			if($boolIsIn)
				echo json_encode(true);
			else {
				$objXMLHandler->objConfigData->userroles->addChild("userrole", serialize($_POST['new-role-data']));
				$objXMLHandler->SaveConfigurationData();
				echo json_encode(false);
			}
		}
		if(isset($_POST['old-role-data'])){
			$boolIsIn = false;
			$intIndex = 0;
			foreach($objXMLHandler->objConfigData->userroles->userrole as $UserRole){
				$UserRole = unserialize($UserRole);
				if($UserRole['name'] == $_POST['old-role-data']['name'] )
					$boolDuplicateName = false;
				if($UserRole['slug'] == $_POST['old-role-data']['slug'] && !$boolDuplicateName){
					$objXMLHandler->objConfigData->userroles->userrole[$intIndex] = serialize($_POST['old-role-data']);
					$objXMLHandler->SaveConfigurationData();
					$boolIsIn = true;
				}
				$boolDuplicateName = false;
			}
			if($boolIsIn){		
				echo json_encode(false);
			}
			else {
				echo json_encode(true);
			}
		}
		if(isset($_POST['TypeFieldDelete'])){
			$intIndex = 0;
			foreach ($objXMLHandler->objConfigData->usertypes->usertype as $UserType){
				$UserType = unserialize($UserType);
				if($_POST["type"] == $UserType[0]['slug']){
					$intLowerIndex = 0;
					$UserType[1] = array_merge($UserType[1]);
					foreach ($UserType[1] as $FieldArray){
						if($FieldArray['slug'] == $_POST['TypeFieldDelete']){
							print_r($UserType[1][$intLowerIndex]);
							unset($UserType[1][$intLowerIndex]);
							$objXMLHandler->objConfigData->usertypes->usertype[$intIndex] = serialize($UserType);
							$objXMLHandler->SaveConfigurationData();
							$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "user_type_field";
							$strTableName2 = $wpdb->prefix . strtolower(AP_PREFIX) . "user_type";
							$strTableChecker = $_POST['TypeFieldDelete'];
							$strTableChecker2 = $_POST['type'];
							$intFieldID = $wpdb->get_results("SELECT id FROM $strTableName WHERE slug = '$strTableChecker';");
							$intTypeID = $wpdb->get_results("SELECT id FROM $strTableName2 WHERE slug = '$strTableChecker2';");
							$wpdb->delete($wpdb->prefix . strtolower(AP_PREFIX) . "user_field", array("user_type_id" => $intTypeID[0]->id, "user_field_id" => $intFieldID[0]->id));
							$wpdb->delete($wpdb->prefix . strtolower(AP_PREFIX) . "user_type_field", array("id" => $intFieldID[0]->id, "slug" => $_POST['TypeFieldDelete']));
							echo json_encode(false);
							return;
						}
						$intLowerIndex++;
					}
				}
				$intIndex++;
			}
		}
		if(isset($_POST['UserTypeDelete'])){
			$intIndex = 0;
			foreach ($objXMLHandler->objConfigData->usertypes->usertype as $UserType){
				$UserType = unserialize($UserType);
				if($_POST["UserTypeDelete"] == $UserType[0]['slug']){
					unset($objXMLHandler->objConfigData->usertypes->usertype[$intIndex]);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->delete($wpdb->prefix . "ap_user_type", array('slug' => $_POST["UserTypeDelete"]));
					return;
				}
				$intIndex++;
			}
		}
		if(isset($_POST["OldTypeData"])){
			$intIndex = 0;
			foreach ($objXMLHandler->objConfigData->usertypes->usertype as $UserType){
				$UserType = unserialize($UserType);
				if($_POST["OldTypeData"]['slug'] == $UserType[0]['slug'] && $_POST["OldTypeData"]['ap_id'] != $UserType[0]['ap_id']){
					echo json_encode(true);
					return;
				}
			}
			foreach ($objXMLHandler->objConfigData->usertypes->usertype as $UserType){
				$UserType = unserialize($UserType);
				if($_POST["OldTypeData"]['ap_id'] == $UserType[0]['ap_id']){
					$UserType[0]['slug'] = $_POST["OldTypeData"]['slug'];
					$UserType[0]['description'] = $_POST["OldTypeData"]['description'];
					$objXMLHandler->objConfigData->usertypes->usertype[$intIndex] = serialize($UserType);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->update($wpdb->prefix . "ap_user_type", array('description' => $UserType[0]['description'], 'slug' => $UserType[0]['slug']), array('ap_id' => $UserType[0]['ap_id']));
				}
				$intIndex++;
			}
			echo json_encode(false);
			return;
		}
		if(isset($_POST['NewFieldData'])){
			if(!isset($_POST['type'])){
				echo "Error No User Type Selected for Field";
				return;
			}
			else{
				foreach ($objXMLHandler->objConfigData->userfields->userfield as $UserType){
					$UserType = unserialize($UserType);
					if($_POST["NewFieldData"]['slug'] == $UserType['slug']){
						echo json_encode(true);
						return;
					}
				}
				foreach ($objXMLHandler->objConfigData->usertypes->usertype as $UserType){
					$UserType = unserialize($UserType);
					if($_POST["type"] == $UserType[0]['slug']){
						foreach($UserType[1] as $UserTypeField){
							if($_POST["NewFieldData"]['name'] == $UserTypeField['name']){
								echo json_encode(true);
								return;
							}
						}
					}
						
					foreach($UserType[1] as $UserTypeField){
						if($_POST["NewFieldData"]['slug'] == $UserTypeField['slug']){
							echo json_encode(true);
							return;
						}
					}
				}
				$intIndex = 0;
				foreach ($objXMLHandler->objConfigData->usertypes->usertype as $UserType){
					$UserType = unserialize($UserType);
					if($_POST["type"] == $UserType[0]['slug']){
						if(!is_array($UserType[1]))
							$UserType[1] = array();
						else{
							$UserType[1][] = $_POST["NewFieldData"];
						}
						$objXMLHandler->objConfigData->usertypes->usertype[$intIndex] = serialize($UserType);
						$objXMLHandler->SaveConfigurationData();
					}
					$intIndex++;
				}
				echo json_encode(false);
				return;
			}
		}
		if(isset($_POST['OldFieldData'])){
			if(!isset($_POST['type'])){
				echo "Error No User Type Selected for Field";
				return;
			}
			else{
				$intIndex = 0;
				foreach ($objXMLHandler->objConfigData->usertypes->usertype as $UserType){
					$UserType = unserialize($UserType);
					if($_POST["type"] == $UserType[0]['slug']){
						foreach($UserType[1] as $UserTypeField){
							if($_POST["OldFieldData"]['name'] == $UserTypeField['name'] && $_POST["OldFieldData"]['slug'] <> $UserTypeField['slug']){
								echo json_encode(true);
								return;
							}
						}
						$intLowerIndex = 0;
						foreach($UserType[1] as $UserTypeField){
							if($_POST["OldFieldData"]['slug'] == $UserTypeField['slug']){
								$UserType[1][$intLowerIndex] =  $_POST["OldFieldData"];
								$objXMLHandler->objConfigData->usertypes->usertype[$intIndex] = serialize($UserType);
								$objXMLHandler->SaveConfigurationData();
								echo json_encode(false);
								return;
							}
							$intLowerIndex++;
						}
					}
					$intIndex++;
				}
			}
		}
		if(isset($_POST["OldGlobalData"])){
			$intIndex = 0;
			foreach ($objXMLHandler->objConfigData->userfields->userfield as $UserType){
				$UserType = unserialize($UserType);
				if($_POST["OldGlobalData"]['name'] == $UserType['name'] && $_POST["OldGlobalData"]['slug'] != $UserType['slug']){
					echo json_encode(true);
					return;
				}
			}
			foreach ($objXMLHandler->objConfigData->userfields->userfield as $UserType){
				$UserType = unserialize($UserType);
				if($_POST["OldGlobalData"]['slug'] == $UserType['slug']){
					$UserType['description'] = $_POST["OldGlobalData"]['description'];
					$UserType['name'] = $_POST["OldGlobalData"]['name'];
					$objXMLHandler->objConfigData->userfields->userfield[$intIndex] = serialize($UserType);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->update($wpdb->prefix . "ap_user_type_field", array('description' => $UserType['description'], 'name' => $UserType['name']), array('slug' => $UserType['slug']));
				}
				$intIndex++;
			}
			echo json_encode(false);
			return;
		}
		if(isset($_POST['GlobalFieldDelete'])){
			$intIndex = 0;
			foreach ($objXMLHandler->objConfigData->userfields->userfield as $UserType){
				$UserType = unserialize($UserType);
				print_r(json_encode($UserType));
				if($_POST["GlobalFieldDelete"] == $UserType['slug']){
					unset($objXMLHandler->objConfigData->userfields->userfield[$intIndex]);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->delete($wpdb->prefix . "ap_user_type_field", array('slug' => $_POST["GlobalFieldDelete"]));
					return;
				}
				$intIndex++;
			}
		}
		if(isset($_POST["NewTypeData"])) {
			$strErrorArray = array();
			$strNewTypeArray = $_POST['NewTypeData'];
			foreach ($objXMLHandler->objConfigData->usertypes->usertype as $UserType){
				$UserTypeArray = unserialize($UserType);
				if(strtolower($UserTypeArray[0]["singular_name"]) == strtolower($strNewTypeArray['singular_name']))
					$strErrorArray[] = "Singular Name";
				if(strtolower($UserTypeArray[0]["name"]) == strtolower($strNewTypeArray['name']))
					$strErrorArray[] = "Plural Name";
				if(strtolower($UserTypeArray[0]["slug"]) == strtolower($strNewTypeArray['slug']))
					$strErrorArray[] = "Slug";
				if(strtolower($UserTypeArray[0]["ap_id"]) == strtolower($strNewTypeArray['ap_id']))
					$strErrorArray[] = "AgilePress ID";
			}
			if(!empty($strErrorArray)){
				echo json_encode(array_merge(array_unique($strErrorArray)));
				die;
			}
			else{
				$newUserType = $objXMLHandler->objConfigData->usertypes->addChild("usertype", serialize(array($strNewTypeArray,array())));
				$objXMLHandler->SaveConfigurationData();
				echo json_encode(false);
			}
			die;
		}
		if(isset($_POST["NewGlobalData"])){
			$strErrorArray = array();
			$strNewTypeArray = $_POST['NewGlobalData'];
			foreach ($objXMLHandler->objConfigData->userfields->userfield as $UserType){
				$UserTypeArray = unserialize($UserType);
				if(strtolower($UserTypeArray["name"]) == strtolower($strNewTypeArray['name']))
					$strErrorArray[] = "Name";
				if(strtolower($UserTypeArray["slug"]) == strtolower($strNewTypeArray['slug']))
					$strErrorArray[] = "Slug";
			}
			$objCurrentFieldsArray = AP_CustomPostTypeBase::GetGlobalPostFieldCheckArray();
			foreach ($objXMLHandler->objConfigData->usertypes->usertype as $UserType){
				$UserType = unserialize($UserType);
				foreach($UserType[1] as $UserTypeArray){
					if(strtolower($UserTypeArray["name"]) == strtolower($strNewTypeArray['name']))
						$strErrorArray[] = "Name";
					if(strtolower($UserTypeArray["slug"]) == strtolower($strNewTypeArray['slug']))
						$strErrorArray[] = "Slug";
				}
			}
			if(!empty($strErrorArray)){
				echo json_encode(array_merge(array_unique($strErrorArray)));
				die;
			}
			else{
				$newUserType = $objXMLHandler->objConfigData->userfields->addChild("userfield", serialize($strNewTypeArray));
				$objXMLHandler->SaveConfigurationData();
				echo json_encode(false);
			}
		}
		if(isset($_POST['CurrentCustomTypes'])){
			echo "<?xml version='1.0'?>";
			echo "<root>";
			$strCustomUserTypeArray = AP_CustomUserTypeBase::GetUserTypeArray();
			foreach ($strCustomUserTypeArray as $strCustomUserType){
				$strFieldArray = AP_CustomUserTypeBase::GetUserTypeFieldArray($strCustomUserType['name']);
				echo "<item plural_name='"; echo $strCustomUserType['name']; echo "' singular_name='"; echo $strCustomUserType['singular_name']; echo "' "; echo "slug='"; echo $strCustomUserType['slug'] . "'>";
				foreach($strFieldArray as $strField){
					echo "<field>";
					echo json_encode($strField);
					echo "</field>";
				}
				echo "</item>";}
				echo "</root>";
				die;
		}
		if(isset($_POST['CurrentGlobalFields'])){
			echo "<?xml version='1.0'?>";
			echo "<root>";
			$strCustomUserTypeArray = AP_CustomUserTypeBase::GetGlobalUserFieldArray();
			foreach ($strCustomUserTypeArray as $strCustomUserType){
				echo "<item singular_name='"; echo $strCustomUserType['name']; echo "' slug='"; echo $strCustomUserType['slug']; echo "'></item>";
			}
			echo "</root>";
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
		<h1 style="margin-left:15px;">AgilePress User Manager
			</h1>
			<div style="width:850px;">
			<?php $this->AdminNavBarRender('users');?>
			<div style="padding: 1px 20px; margin: 5px 15px 2px; margin-top:0; padding-top:0; height:1290px; background:white; width:100%; ">
				<p class="ap-settings-field" style="font-weight:bold; float:left; margin-top:15px; font-size:18px;">Custom User Roles </p>
					<span class="role-adder" style="position:relative;  cursor:pointer !important; display:block; float:right; width:200px; height:50px; margin-top:20px; margin-right:0%; padding-right:0;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:97px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; right:0px; font-size:14px;">Add new role</p>
					</span>
					<div style="clearfix:both; position:relative; float:left; border-style:solid; top:40px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:300px; overflow:auto; margin-bottom:60px;" >
						<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-o-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-webkit-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background: linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
						</div>
						<?php $objData = AP_CustomUserTypeBase::GetUserRoleArray(); $objDataTrack = array(); $boolBackground = true; $intPixCount = 40; foreach($objData as $key => $value){ $objDataTrack[] = $value['slug'];?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row"><?php _e($value['name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="role-viewer" data-role="<?php _e($value['slug']);?>">View Capabilities</a>|<a class="type-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } ?> 
						<?php $objNewData = AP_CustomUserTypeBase::GetConfigUserRoleArray(); $boolCodeGenCheck = false; foreach($objNewData as $key => $value){ if(!in_array($value['slug'],$objDataTrack)){$boolCodeGenCheck = true;?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7); top:<?php _e($intPixCount);?>px; left:0; width:99%; height:36px; ">
							<p class="ap-post-key-row"><?php _e($value['name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="role-viewer" data-role="<?php _e($value['slug']);?>">View Capabilities</a>|<a class="type-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; }} ?> 
						<?php if(empty($objData) && empty($objNewData)){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row">None</p>
						</div>
						<?php }?>
					</div>
					<?php if($boolCodeGenCheck){?>
						<p class="ap-settings-field-note" style="position:relative; top:425px; color:rgb(241, 146, 7); left:-80px;padding:0; margin:0;">*User Types bordered in orange have not been had their code generated yet</p>
					<?php }?>
				<p class="ap-settings-field" style="font-weight:bold; float:left; margin-top:15px; font-size:18px;">User Types </p>
					<span class="type-adder" style="position:relative;  cursor:pointer !important; display:block; float:right; width:200px; height:50px; margin-top:20px; margin-right:0%; padding-right:0;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:135px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; right:0px; font-size:14px;">Add new user type</p>
					</span>
					<div style="clearfix:both; position:relative; float:left; border-style:solid; top:40px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:300px; overflow:auto; margin-bottom:60px;" >
						<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-o-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-webkit-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background: linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
						</div>
						<?php $objData = AP_CustomUserTypeBase::GetUserTypeArray(); $objDataTrack = array(); $boolBackground = true; $intPixCount = 40; foreach($objData as $key => $value){ $objDataTrack[] = $value['slug'];?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row"><?php _e($value['singular_name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="field-viewer" data-type="<?php _e($value['slug']);?>">View Fields</a>|<a class="edit-viewer" >Edit</a>|<a class="type-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } ?> 
						<?php $objNewData = AP_CustomUserTypeBase::GetConfigUserTypeArray(); $boolCodeGenCheck = false; foreach($objNewData as $key => $value){ if(!in_array($value[0]['slug'],$objDataTrack)){ $boolCodeGenCheck = true;?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7); top:<?php _e($intPixCount);?>px; left:0; width:99%; height:36px; ">
							<p class="ap-post-key-row"><?php _e($value[0]['singular_name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value[0]['slug']);?></p><p class="ap-post-key-row"><a class="field-viewer" data-type="<?php _e($value[0]['slug']);?>">View Fields</a>|<a class="edit-viewer" >Edit</a>|<a class="type-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; }} ?> 
						<?php if(empty($objData) && empty($objNewData)){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row">None</p>
						</div>
						<?php }?>
					</div>
					<?php if($boolCodeGenCheck){?>
						<p class="ap-settings-field-note" style="position:relative; top:425px; color:rgb(241, 146, 7); left:-80px;padding:0; margin:0;">*User Types bordered in orange have not been had their code generated yet</p>
					<?php }?>
				<p class="ap-settings-field" style="font-weight:bold; float:left; margin-top:0px; font-size:18px;">Global User Fields </p>
					<span id="ap-global-viewer" style="position:relative; cursor:pointer !important; display:block; float:right; width:200px; height:50px; margin-top:5px; margin-right:0%; padding-right:0;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:175px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; right:0px; font-size:14px;">Add new global user field</p>
					</span>
					<div id="ap-global-holder" style="clearfix:both; position:relative; float:left; border-style:solid; top:40px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:300px;" >
						<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-o-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-webkit-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
						</div>
						<?php $objDataTrack = array(); $objData = AP_CustomUserTypeBase::GetGlobalUserFieldArray(); $boolBackground = true; $intPixCount = 40; foreach($objData as $key => $value){ $objDataTrack[] = $value['slug'];?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row"><?php _e($value['name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="edit-viewer">Edit</a>|<a class="global-field-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; }  ?> 
						<?php $objNewData = AP_CustomUserTypeBase::GetConfigFieldArray(); $boolCodeGenCheck = false; foreach($objNewData as $key => $value){ if(!in_array($value['slug'],$objDataTrack)){ $boolCodeGenCheck = true;?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute;  border-style: solid; border-width:4px; border-color:rgb(241, 146, 7); top:<?php _e($intPixCount);?>px; left:0; width:99%; height:40px; ">
							<p class="ap-post-key-row"><?php _e($value['name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row" ><a class="edit-viewer" >Edit</a>|<a class="global-field-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; }}  if(empty($objData) && empty($objNewData)){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row">None<?php  print_r($objData2);?></p>
						</div>
						<?php }?>
					</div>
					
				</div>
					<div id="field-view">
					</div>
					<div id="overlay">
					</div>
			<?php $objData = AP_CustomUserTypeBase::GetUserTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($value['name']); $objNewFields = AP_CustomUserTypeBase::GetUserTypeFieldConfigArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-fields" class="field-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:850px; background:white; width:800px; ">
					<div style="height:40px;  width:840px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:-20px;">Custom <?php _e($value['name']);?> Fields</p>
					<a class="field-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<span  class="field-adder" style="position:absolute; display:block; float:left; width:200px; height:50px; margin-top:20px; left:20px;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:175px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; left:45px; font-size:14px;  width:100%;">Add new <?php _e(strtolower($value['singular_name']));?> field</p>
					</span>
					<div class="field-holder-bar" style="width:802px; height:40px; margin:70px 0px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-webkit-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-o-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
					</div>
					<div id="field-row-holder" style="clearfix:both; position:absolute; border-style:solid; top:150px; margin-left:0px; border-width:2px; border-top-width:0; border-color:rgb(179,179,179); background:white; z-index: 2; width:798px; height:670px; overflow:auto; margin-bottom:100px;" >
						<?php $boolBackground = true; $intPixCount = 0; foreach($objFields as $key => $value){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row"><?php _e($value['name']);?></p><p class="ap-post-key-row"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="field-edit-viewer" data-type='<?php _e(json_encode($value));?>'>Edit</a>|<a data-slug='<?php _e($value['slug']);?>' onclick="TypeFieldDelete(this); return false;">Delete</a></p>
						</div><div></div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } foreach($objNewFields as $key => $value[0]){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7); top:<?php _e($intPixCount);?>px; left:0; width:99%; height:36px; ">
							<p class="ap-post-key-row"><?php _e($value[0]['name']);?></p><p class="ap-post-key-row"><?php _e($value[0]['slug']);?></p><p class="ap-post-key-row"><a class="field-edit-viewer" data-type='<?php _e(json_encode($value[0]));?>'>Edit</a>|<a data-slug='<?php _e($value[0]['slug']);?>' onclick="TypeFieldDelete(this); return false;">Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } if(empty($objData)){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row">None</p>
						</div>
						<?php }?>
					</div>
			</div>
			<?php }?>
			<?php $objData = AP_CustomUserTypeBase::GetUserTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:690px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:-30px; height:20px;"><?php _e($value['singular_name']);?> Type Editor</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field"> Singular Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input"  value="<?php _e($value['singular_name']);?>" DISABLED /><br>
					<p class="ap-settings-field" > Plural Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" value="<?php _e($value['name']);?>" DISABLED /><br>
					<p class="ap-settings-field"> Slug </p>
					<input autocomplete='off' type="text" id="type-edit-slug" class="ap-settings-field-input"  value="<?php _e($value['slug']);?>"><br>
					<p class="ap-settings-field"> AgilePress Identifier </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="type-edit-apid" value="<?php _e($value['ap_id']);?>" DISABLED ><br>
					<p  class="ap-settings-field"> Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="type-edit-description" ><?php _e($value['description']);?></textarea>
					<a class="ap-admin-button" style="float:left" onclick="UpdateType(this); return false;" href = "">
					<span class="ap-admin-button-2">
						<span style=""> Save Type </span>
					</span>
					</a>
			</div>
			<?php }?>
			<?php $objData = AP_CustomUserTypeBase::GetConfigUserTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($value[0]['name']); $objNewFields = AP_CustomUserTypeBase::GetUserTypeFieldConfigArray($value[0]['name']);?>
			<div id="ap-<?php _e($value[0]['slug']);?>-fields" class="field-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:850px; background:white; width:800px; ">
					<div style="height:40px;  width:840px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:-20px;">Custom <?php _e($value[0]['name']);?> Fields</p>
					<a class="field-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<span  class="field-adder" style="position:absolute; display:block; float:left; width:200px; height:50px; margin-top:20px; left:20px;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:175px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; left:45px; font-size:14px; width:100%;">Add new <?php _e(strtolower($value[0]['singular_name']));?> field</p>
					</span>
					<div class="field-holder-bar" style="width:802px; height:40px; margin:70px 0px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171));  background:-webkit-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-o-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
					</div>
					<div id="field-row-holder"  style="clearfix:both; position:absolute; border-style:solid; top:150px; margin-left:0px; border-width:2px; border-top-width:0; border-color:rgb(179,179,179); background:white; z-index: 2; width:798px; height:670px; overflow:auto; margin-bottom:100px;" >
						<?php $boolBackground = true; $intPixCount = 0; foreach($objFields as $key => $value[0]){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row"><?php _e($value[0]['name']);?></p><p class="ap-post-key-row"><?php _e($value[0]['slug']);?></p><p class="ap-post-key-row"><a class="field-edit-viewer"  data-type='<?php _e(json_encode($value[0]));?>'>Edit</a>|<a data-slug='<?php _e($value[0]['slug']);?>' onclick="TypeFieldDelete(); return false;" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } foreach($objNewFields as $key => $value[0]){ ?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7); top:<?php _e($intPixCount);?>px; left:0; width:99%; height:36px; ">
							<p class="ap-post-key-row"><?php _e($value[0]['name']);?></p><p class="ap-post-key-row"><?php _e($value[0]['slug']);?></p><p class="ap-post-key-row"><a class="field-edit-viewer"  data-type='<?php _e(json_encode($value[0]));?>'>Edit</a>|<a data-slug='<?php _e($value[0]['slug']);?>' onclick="TypeFieldDelete(); return false;">Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } if(empty($objData)){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row">None</p>
						</div>
						<?php }?>
					</div>
			</div>
			<?php }?>
			<?php $objData = AP_CustomUserTypeBase::GetConfigUserTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($value[0]['name']);?>
			<div id="ap-<?php _e($value[0]['slug']);?>-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:690px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:-30px; height:20px;"><?php _e($value[0]['singular_name']);?> Type Editor</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field"> Singular Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input"  value="<?php _e($value[0]['singular_name']);?>" DISABLED /><br>
					<p class="ap-settings-field" > Plural Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" value="<?php _e($value[0]['name']);?>" DISABLED /><br>
					<p class="ap-settings-field"> Slug </p>
					<input autocomplete='off' type="text" id="type-edit-slug" class="ap-settings-field-input"  value="<?php _e($value[0]['slug']);?>"><br>
					<p class="ap-settings-field"> AgilePress Identifier </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="type-edit-apid" value="<?php _e($value[0]['ap_id']);?>" DISABLED ><br>
					<p  class="ap-settings-field"> Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="type-edit-description" ><?php _e($value[0]['description']);?></textarea>
					<a class="ap-admin-button" style="float:left" onclick="UpdateType(this); return false;" href = "">
					<span class="ap-admin-button-2">
						<span style=""> Save Type </span>
					</span>
					</a>
			</div>
			<?php }?>
			<?php $objData = AP_CustomUserTypeBase::GetGlobalUserFieldArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:690px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:20px; text-align:left; height:20px;"><?php _e($value['name']);?> Global Field Editor</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field" > Field Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="global-edit-name" value="<?php _e($value['name']);?>" /><br>
					<p class="ap-settings-field"> Field Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="global-edit-slug" value="<?php _e($value['slug']);?>" DISABLED><br>
					<p class="ap-settings-field"> Control Type</p>
					<select id="field-ctrl" style="width:400px; text-align:center;" DISABLED>
						<option ><?php switch ($value['control_type']){ case "textarea" : _e("Text Area");  break; case "textbox" : _e("Text Box"); break; case "listbox" : _e("List Box");  break; case "radio" : _e("Radio Button");  break; case "checkbox" : _e("Check Box"); }?></option>
					</select>
					<p class="ap-settings-field"> Field Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="global-edit-description" ><?php _e($value['description']);?></textarea>
					<a class="ap-admin-button" style="float:left" href = "" onclick="UpdateGlobal(this); return false;">
					<span class="ap-admin-button-2">
						<span style=""> Save Field </span>
					</span>
					</a>
			</div>
			<?php }?>
			<?php $objData = AP_CustomUserTypeBase::GetConfigFieldArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:640px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px;  margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:20px; text-align:left; height:20px;"><?php _e($value['name']);?> Global Field Editor</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field" > Field Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="global-edit-name" value="<?php _e($value['name']);?>" /><br>
					<p class="ap-settings-field"> Field Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="global-edit-slug" value="<?php _e($value['slug']);?>" DISABLED><br>
					<p class="ap-settings-field"> Control Type</p>
					<select id="field-ctrl" style="width:400px; text-align:center;" DISABLED>
						<option ><?php switch ($value['ctrl']){ case "textarea" : _e("Text Area");  break; case "textbox" : _e("Text Box"); break; case "listbox" : _e("List Box");  break; case "radio" : _e("Radio Button");  break; case "checkbox" : _e("Check Box"); }?></option>
					</select>
					<p class="ap-settings-field"> Field Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="global-edit-description" ><?php _e($value['description']);?></textarea>
					<a class="ap-admin-button" style="float:left" onclick="UpdateGlobal(this); return false;" href = "" >
					<span class="ap-admin-button-2">
						<span style=""> Save Field </span>
					</span>
					</a>
			</div>
			<?php }?>
			<?php $objData = AP_CustomUserTypeBase::GetUserTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-field-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:620px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left !important; width:80%;" id="field-header"></p>
					<a class="field-edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field" > Field Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="field-plural" value="" /><br>
					<p class="ap-settings-field"> Field Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="field-slug" DISABLED><br>
					<p class="ap-settings-field"> Control Type </p>
					<select id="field-ctrl" style="width:400px; text-align:center;">
						<option value="textarea"> Text Area </option>
						<option value="textbox"> Text Box </option>
						<option value="listbox"> List Box </option>
						<option value="radio"> Radio Button </option>
						<option value="checkbox"> Check Box </option><option value="checkboxlist"> Check Box List</option>
					</select>
					<p class="ap-settings-field"> Field Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="field-description"></textarea>
					<a class="ap-admin-button" style="float:left" onclick="SaveLoadedField(); return false;" href = "">
					<span class="ap-admin-button-2">
						<span style=""> Save Field </span>
					</span>
					</a>
			</div>
			<?php }?>
			<div id="ap-field-adder" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:620px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" id="field-header-add" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left !important; width:80%;" id="field-header"></p>
					<a class="field-edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field" > Field Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="field-add-name" value="" /><br>
					<p class="ap-settings-field"> Field Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="field-add-slug"><br>
					<p class="ap-settings-field"> Control Type </p>
					<select id="field-add-ctrl" style="width:400px; border-radius:5px; text-align:center;">
						<option value="textbox"> Text Box </option>
						<option value="textarea"> Text Area </option>
						<option value="listbox"> List Box </option>
						<option value="radio"> Radio Button </option>
						<option value="checkbox"> Check Box </option><option value="checkboxlist"> Check Box List</option>
					</select>
					<p class="ap-settings-field"> Field Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="field-add-description"></textarea>
					<a class="ap-admin-button" style="float:left" onclick="AddField(); return false; " href = "">
					<span class="ap-admin-button-2">
						<span style=""> Add Field </span>
					</span>
					</a>
					<a class="ap-admin-button" style="float:right" onclick="AddField(true); return false; " href = "">
					<span class="ap-admin-button-2">
						<span style=""> Save and Add Another</span>
					</span>
					</a>
			</div>
			<div id="ap-type-adder" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:690px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;">User Type Creator</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field"> Singular Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="type-add-name" value="" /><br>
					<p class="ap-settings-field" > Plural Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="type-add-plurname" value="" /><br>
					<p class="ap-settings-field"> Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="type-add-slug" ><br>
					<p class="ap-settings-field"> AgilePress Identifier </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" value="" id="type-add-id" DISABLED /><br>
					<p class="ap-settings-field"> Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="type-add-description" ></textarea>
					<a class="ap-admin-button" style="float:left" onclick="AddType(); return false;">
					<span class="ap-admin-button-2">
						<span style=""> Add Type </span>
					</span>
					</a>
			</div>
			<div id="ap-global-adder" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:640px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;">Global User Field Creator</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field"> Field Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="global-add-name" value="" /><br>
					<p class="ap-settings-field"> Field Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="global-add-slug" ><br>
					<p class="ap-settings-field"> Control Type </p>
					<select id="global-add-ctrl" style="width:400px; border-radius:5px; text-align:center;">
						<option value="textbox"> Text Box </option>
						<option value="textarea"> Text Area </option>
						<option value="listbox"> List Box </option>
						<option value="radio"> Radio Button </option>
						<option value="checkbox"> Check Box </option><option value="checkboxlist"> Check Box List</option>
					</select>
					<p class="ap-settings-field"> Field Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="global-add-description" ></textarea>
					<a class="ap-admin-button" style="float:left" onclick="AddGlobal(); return false;">
					<span class="ap-admin-button-2">
						<span style=""> Add Field </span>
					</span>
					</a>
			</div>
			<div id="ap-role-adder" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:720px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;">Custom User Role Creator</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field"> Role Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="role-add-name" value="" /><br>
					<p class="ap-settings-field"> Role Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="role-add-slug" ><br>
					<p class="ap-settings-field"> Capabilities </p>
					<div style="width:90%; height:150px; background:white; overflow:auto;">
						<?php
							$objRole = get_role('administrator');
							$strCapabilityArray = array_keys($objRole->capabilities);
							asort($strCapabilityArray);
							foreach($strCapabilityArray as $key => $strCapability)
								echo "<input type='checkbox' class='role-capability' value='1' name='" . $strCapability . "' ><label for='" . $strCapability . "'>" . $strCapability . "</label><br>";
						?>
					</div>
					<p class="ap-settings-field"> Role Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="global-add-description" ></textarea>
					<a class="ap-admin-button" style="float:left" onclick="AddRole(); return false;">
					<span class="ap-admin-button-2">
						<span style=""> Add Role </span>
					</span>
					</a>
			</div>
			<?php $objData = AP_CustomUserTypeBase::GetConfigUserRoleArray(); foreach ($objData as $key => $value){?>
			<div id="ap-<?= $value['slug']?>-role-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:720px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;"><?= $value['name']?> Role Editor</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field"> Role Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="<?= $value['slug']?>-role-add-name" value="<?= $value['name']?>" /><br>
					<p class="ap-settings-field"> Role Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="<?= $value['slug']?>-role-add-slug" value="<?= $value['slug']?>" DISABLED><br>
					<p class="ap-settings-field"> Capabilities </p>
					<div style="width:100%; height:150px; background:white; overflow:auto;">
						<?php
							$objRole = get_role('administrator');
							$strCapabilityArray = array_keys($objRole->capabilities);
							asort($strCapabilityArray);
							foreach($strCapabilityArray as $key => $strCapability){
								$strChecked = "";
								foreach($value['capabilities'] as $capability){
									if($capability['name'] == $strCapability)
										$strChecked = 'checked="checked"';
								}
								echo "<input type='checkbox' " . $strChecked . " class='" . $value['slug'] . "-role-capability' value='1' name='" . $strCapability . "' ><label for='" . $strCapability . "'>" . $strCapability . "</label><br>";
							}
						?>
					</div>
					<p class="ap-settings-field"> Role Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="global-add-description" ><?= $objData['description']?></textarea>
					<a class="ap-admin-button" style="float:left" onclick="UpdateRole(); return false;">
					<span class="ap-admin-button-2">
						<span style=""> Update Role </span>
					</span>
					</a>
			</div>
			<?php } ?>
			<div id="temp-field-edit" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:640px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;">Global User Field Creator</p>
					<a class="temp-edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field"> Field Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="temp-edit-name" value="" /><br>
					<p class="ap-settings-field"> Field Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input" id="temp-edit-slug" DISABLED><br>
					<p class="ap-settings-field"> Control Type </p>
					<select id="temp-edit-ctrl" style="width:400px; border-radius:5px; text-align:center;">
						<option value="textbox"> Text Box </option>
						<option value="textarea"> Text Area </option>
						<option value="listbox"> List Box </option>
						<option value="radio"> Radio Button </option>
						<option value="checkbox"> Check Box </option><option value="checkboxlist"> Check Box List</option>
					</select>
					<p class="ap-settings-field"> Field Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="temp-edit-description" ></textarea>
					<a class="ap-admin-button" style="float:left" onclick="SaveField(); return false;">
					<span class="ap-admin-button-2">
						<span style=""> Save field </span>
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
			var CurGlobalFields = [];
			var CurUserTypes = [];
			var FieldNameArray = []; 
			var CurrentType;
			var CurrentField;
			var CurrentRole;

	
			$(document).ready(function(){
				$.post("", { "action" : "ajax", "CurrentGlobalFields" : true }, function(d){ $(d).find('item').each(function(index){ CurGlobalFields.push({ "singular_name" : $(this).attr("singular_name"), "slug" : $(this).attr("slug")}); }); console.log(CurGlobalFields);}, "xml") ;
				$.post("", { "action" : "ajax", "CurrentCustomTypes" : true }, function(d){ $(d).find('item').each(function(index){ var curFields = []; $(this).find('field').each(function(index){ var tempField = $.parseJSON($(this).text()); curFields.push(tempField);}); CurUserTypes.push({ "plural_name" : $(this).attr("plural_name"), "singular_name" : $(this).attr("singular_name"), "slug" : $(this).attr("slug"), "fields" : curFields}); }); console.log(CurUserTypes);}, "xml") ;

				$("#type-add-slug").keyup(function(e){$("#type-add-id").val($("#type-add-slug").val().replace("_","-")) });

				$("#ap-global-viewer").click(function(event){
					$("#ap-global-adder").css("display","block");
					$("#overlay").css("display","block");
				});
				
				$(".field-viewer").each(function(index){$(this).hover(function(){CurUserTypes.forEach(function(entry){ if (entry['slug'] == $(".field-viewer").eq(index).attr("data-type")){ $("#field-view").html(""); entry['fields'].forEach(function(lowerentry){$("#field-view").append("<p class='field-view-row'>" + lowerentry['name'] + "</p>");}); $("#field-view").css("top",$(".field-viewer").eq(index).offset().top - 30); $("#field-view").css("left",$(".field-viewer").eq(index).offset().left); $("#field-view").css("display","block");}})}, function(){$("#field-view").css("display","none");})});
				var intPopupOffset = $("#wpcontent").width() - 800;
				intPopupOffset = intPopupOffset / 2;
				intPopupOffset = intPopupOffset - 20;

				$(".field-holder").css("margin-left",intPopupOffset);
				$(".field-holder-bar").css("left",intPopupOffset+200);

				intPopupOffset = intPopupOffset + 100;
				$(".edit-holder").css("margin-left",intPopupOffset);
				$(".edit-holder-bar").css("left",intPopupOffset+200);

				$(".type-delete").click(function(event){
					if(confirm("Are you sure you want to delete this user type?")){
						$.post("",{"action":"ajax","UserTypeDelete" : $(event.target).parent().siblings("p#row-slug").text()},function(data){ document.location.reload();});
						}
					else{}
				});

				$(".global-field-delete").click(function(event){
					if(confirm("Are you sure you want to delete this global field?")){
						$.post("",{"action":"ajax","GlobalFieldDelete" : $(event.target).parent().siblings("p#row-slug").text()},function(data){ document.location.reload();});
						}
					else{}
				});
				
				$(".field-closer").click(function(){
					document.location.reload();
					$(".field-closer").parents(".field-holder").css("display","none"); 
					$("#overlay").css("display","none");
				});

				$(".field-viewer").click(function(){
					$("#ap-" + $(this).parent().siblings("#row-slug").text() + "-fields").css("display","block");
					$("#overlay").css("display","block");
					window.CurrentType = $(this).parent().siblings("#row-slug").text();
				});

				$(".role-viewer").click(function(){
					$("#ap-" + $(this).parent().siblings("#row-slug").text() + "-role-editor").css("display","block");
					$("#overlay").css("display","block");
					window.CurrentRole = $(this).attr("data-role");
					console.log(window.CurrentRole);
				});

				$(".edit-closer").click(function(){
					$(".edit-closer").parents(".edit-holder").css("display","none"); 
					$("#overlay").css("display","none");
				});

				$(".temp-edit-closer").click(function(){
					$("#temp-field-edit").css("display","none"); 
					$("#ap-" + CurrentType + "-fields").css("display","block");
				});

				$(".edit-viewer").click(function(){
					$("#ap-" + $(this).parent().siblings("#row-slug").text() + "-editor").css("display","block");
					$("#overlay").css("display","block");
					CurrentType = $(this).parent().siblings("#row-slug").text();
				});

				$(".field-edit-closer").click(function(){
					$("#ap-" + CurrentType + "-field-editor").css("display","none");
					$("#ap-field-adder").css("display","none");
					$("#ap-" + CurrentType + "-fields").css("display","block");
					$("#overlay").css("display","block");
				});

				$(".field-edit-viewer").click(function(){
					var objElement = this;
					var objField;
					objField = JSON.parse($(objElement).attr("data-type"));
					CurrentField = objField;
					$("#ap-" + CurrentType + "-field-editor").children("#field-plural").val(objField['name']);
					$("#ap-" + CurrentType + "-field-editor").children("#field-slug").val(objField['slug']);
					$("#ap-" + CurrentType + "-field-editor").children("#field-ctrl").val(objField['control_type']);
					$("#ap-" + CurrentType + "-field-editor").children("#field-description").val(objField['description']);
					$("#ap-" + CurrentType + "-field-editor").children("div").children("#field-header").html(CapitaliseFirstLetter(CurrentType) + " Type Field Editor");

					$("#ap-" + CurrentType + "-field-editor").css("display","block");
					$("#ap-" + CurrentType + "-fields").css("display","none");
				});

				$(".field-adder").click(function(){
					$("#field-header-add").html(CapitaliseFirstLetter(CurrentType) + " Type Field Creator");
					$("#ap-field-adder").css("display","block");
					$("#ap-" + CurrentType + "-fields").css("display","none");
				});
				
				$(".type-adder").click(function(){
					$("#ap-type-adder").css("display","block");
					$("#overlay").css("display","block");
				});
				
				$(".role-adder").click(function(){
					$("#ap-role-adder").css("display","block");
					$("#overlay").css("display","block");
				});

				});

			function CapitaliseFirstLetter(string)
			{
			    return string.charAt(0).toUpperCase() + string.slice(1);
			}

			
			function TypeFieldDelete(obj){
				var fieldSlug = $(obj).attr("data-slug");
				$.post("",
						{
					"action" : "ajax",
					"TypeFieldDelete" : fieldSlug,
					"type" : CurrentType

						}
				, function(data){




				});
				
				

			}
			
			function SaveLoadedField(){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#field-plural").val();
				if(typeof name == undefined || name == null){ alert("ERROR: Unable to save type, problem setting global field name"); return;}
				var slug = $("#field-slug").val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field slug"); return;}
				var ctrl = $("#field-ctrl").find('option:selected').val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field control type"); return;}
				var description = $("#field-description").val();

				
				if(name.length < 3 || name.length > 20){
					alert("Global user field names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("Global user field slugs must be 3 characters or longer and less than 20");
					return;
				}

				
				if(name.search(nameRegEx) !== -1){
					alert("Singular Names can only contain Alphanumeric, dash and space characters");
					return;
				}
				if(slug.search(slugRegEx) !== -1){
					alert("Slugs can only contain Alphanumeric, underscore and dash characters");
					return;
				}

				var TypeDataArray = 
					
					{
						"name" : name,
						"slug" : slug,
						"control_type" : ctrl,
						"description" : description
					}

				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"type" : CurrentType,
						"OldFieldData" : TypeDataArray
					},
				function(d){ 
						console.log(TypeDataArray);
						var $objError = $.parseJSON(d); 
						if($objError){
							alert("The slug or name entered already exists"); 
						}
						else{
							var intHeight = $("#" + CurrentField['slug']).css("top");
							$("#" + CurrentField['slug']).remove();
							id = "#ap-" + CurrentType + "-fields #field-row-holder";
							var dataArray = JSON.stringify(TypeDataArray);
							var EvenOddHeight = intHeight / 40
							var EvenOdd = EvenOddHeight%2;
							if(!Math.abs(EvenOdd))
								var RowColor = "light";
							else
								var RowColor = "dark";
							console.log( $(id).children().length);
							$(id).append("<div class='" + RowColor + "' id='" + TypeDataArray['slug'] + "' style='position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7);  top:" + intHeight + "; left:0; width:99%; height:36px;'><p class='ap-post-key-row'>" + name + "</p><p class='ap-post-key-row'>" + slug + "</p><p class='ap-post-key-row'><a class='field-type-edit-viewer' data-type='" + dataArray + "'>Edit</a>|<a>Delete</a></p></div>");

							$("#ap-" + CurrentType + "-field-editor").css("display","none");
							$("#ap-field-adder").css("display","none");
							$("#ap-" + CurrentType + "-fields").css("display","block");
							$("#overlay").css("display","block");
						}
				});
				 
				 return false;
			}

			function SaveField(){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#temp-edit-name").val();
				if(typeof name == undefined || name == null){ alert("ERROR: Unable to save type, problem setting global field name"); return;}
				var slug = $("#temp-edit-slug").val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field slug"); return;}
				var ctrl = $("#temp-edit-ctrl").find('option:selected').val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field control type"); return;}
				var description = $("#temp-edit-description").val();

				
				if(name.length < 3 || name.length > 20){
					alert("Global user field names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("Global user field slugs must be 3 characters or longer and less than 20");
					return;
				}

				
				if(name.search(nameRegEx) !== -1){
					alert("Singular Names can only contain Alphanumeric, dash and space characters");
					return;
				}
				if(slug.search(slugRegEx) !== -1){
					alert("Slugs can only contain Alphanumeric, underscore and dash characters");
					return;
				}

				var TypeDataArray = 
					
					{
						"name" : name,
						"slug" : slug,
						"control_type" : ctrl,
						"description" : description
					}

				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"type" : CurrentType,
						"OldFieldData" : TypeDataArray
					},
				function(d){ 
						var $objError = $.parseJSON(d); 
						if($objError){
							alert("The slug or name entered already exists"); 
						}
						else{
							var intHeight = $("#" + CurrentField['slug']).css("top");
							$("#" + CurrentField['slug']).remove();
							id = "#ap-" + CurrentType + "-fields #field-row-holder";
							var dataArray = JSON.stringify(TypeDataArray);
							var EvenOddHeight = intHeight / 40
							var EvenOdd = EvenOddHeight%2;
							if(!Math.abs(EvenOdd))
								var RowColor = "light";
							else
								var RowColor = "dark";
							console.log( $(id).children().length);
							$(id).append("<div class='" + RowColor + "' id='" + TypeDataArray['slug'] + "' style='position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7);  top:" + intHeight + "; left:0; width:99%; height:36px;'><p class='ap-post-key-row'>" + name + "</p><p class='ap-post-key-row'>" + slug + "</p><p class='ap-post-key-row'><a class='field-type-edit-viewer' data-type='" + dataArray + "'>Edit</a>|<a>Delete</a></p></div>");

							$(".field-type-edit-viewer").click(function(){
								var fieldDataArray = JSON.parse($(this).attr("data-type"));
								CurrentField = fieldDataArray;
								$("#temp-field-edit").css("display","block");
								$("#ap-" + CurrentType + "-fields").css("display","none");
								$("#temp-edit-name").val(fieldDataArray['name']);
								$("#temp-edit-slug").val(fieldDataArray['slug']);
								$("#temp-edit-ctrl").val(fieldDataArray['ctrl']);
								$("#temp-edit-description").val(fieldDataArray['description']);
								});

							$("#temp-field-edit").css("display","none"); 
							$("#ap-" + CurrentType + "-fields").css("display","block");
						}
				});
				 
				 return false;
			}
			
			function AddField(boolAddMore){
				if(!boolAddMore){
					boolAddMore = false;
				}
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#field-add-name").val();
				if(typeof name == undefined || name == null){ alert("ERROR: Unable to save type, problem setting global field name"); return;}
				var slug = $("#field-add-slug").val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field slug"); return;}
				var ctrl = $("#field-add-ctrl").find('option:selected').val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field control type"); return;}
				var description = $("#field-add-description").val();

				
				if(name.length < 3 || name.length > 20){
					alert("Global user field names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("Global user field slugs must be 3 characters or longer and less than 20");
					return;
				}

				
				if(name.search(nameRegEx) !== -1){
					alert("Singular Names can only contain Alphanumeric, dash and space characters");
					return;
				}
				if(slug.search(slugRegEx) !== -1){
					alert("Slugs can only contain Alphanumeric, underscore and dash characters");
					return;
				}

				var TypeDataArray = 
					
					{
						"name" : name,
						"slug" : slug,
						"control_type" : ctrl,
						"description" : description
					}

				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"type" : CurrentType,
						"NewFieldData" : TypeDataArray
					},
				function(d){ 
						var $objError = $.parseJSON(d); 
						if($objError){
							alert("The slug or name entered already exists"); 
						}
						else{
							id = "#ap-" + CurrentType + "-fields #field-row-holder";
							var intHeight =  $(id).children("div").length;
							if(intHeight != 0 ) intHeight = intHeight / 2;
							var dataArray = JSON.stringify(TypeDataArray);
							var EvenOdd = intHeight%2;
							if(!Math.abs(EvenOdd))
								var RowColor = "light";
							else
								var RowColor = "dark";
							intHeight = intHeight * 40;
							console.log( $(id).children().length);
							$(id).append("<div class='" + RowColor + "' id='" + TypeDataArray['slug'] + "' style='position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7);  top:" + intHeight + "px; left:0; width:99%; height:40px;'><p class='ap-post-key-row'>" + name + "</p><p class='ap-post-key-row'>" + slug + "</p><p class='ap-post-key-row'><a class='field-type-edit-viewer' data-type='" + dataArray + "'>Edit</a>|<a>Delete</a></p></div>");

							$(".field-type-edit-viewer").click(function(){
								var fieldDataArray = JSON.parse($(this).attr("data-type"));
								CurrentField = fieldDataArray;
								$("#temp-field-edit").css("display","block");
								$("#ap-" + CurrentType + "-fields").css("display","none");
								$("#temp-edit-name").val(fieldDataArray['name']);
								$("#temp-edit-slug").val(fieldDataArray['slug']);
								$("#temp-edit-ctrl").val(fieldDataArray['ctrl']);
								$("#temp-edit-description").val(fieldDataArray['description']);
								});
							
							if(boolAddMore){
								$("#field-add-name").val("");
								$("#field-add-slug").val("");
								$("#field-add-ctrl").find("option").attr("selected", false);
								$("#field-add-description").val("");
							}
							else{
								$("#ap-" + CurrentType + "-field-editor").css("display","none");
								$("#ap-field-adder").css("display","none");
								$("#ap-" + CurrentType + "-fields").css("display","block");
								$("#overlay").css("display","block");
							}

							
						}
				});
				 
				 return false;
			}

			function AddRole(){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#role-add-name").val();
				if(typeof name == undefined || name == null){ alert("ERROR: Unable to save role, problem setting role name"); return;}
				var slug = $("#role-add-slug").val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save role, problem setting role slug"); return;}
				var capabilities = [];
				$('.role-capability:checked').each(function(d){
					capabilities.push({ 'name' : $(this).attr('name'), 'value' : $(this).val()});
					console.log({ 'name' : $(this).attr('name'), 'value' : $(this).val()});
				});
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save role, problem setting role capabilities"); return;}
				var description = $("#role-add-description").val();

				
				if(name.length < 3 || name.length > 20){
					alert("User role names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("User role slugs must be 3 characters or longer and less than 20");
					return;
				}

				
				if(name.search(nameRegEx) !== -1){
					alert("Names can only contain Alphanumeric, dash and space characters");
					return;
				}
				if(slug.search(slugRegEx) !== -1){
					alert("Slugs can only contain Alphanumeric, underscore and dash characters");
					return;
				}

				var TypeDataArray = 
					
					{
						"name" : name,
						"slug" : slug,
						"capabilities" : capabilities,
						"description" : description
					}

				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"new-role-data" : TypeDataArray
					},
				function(d){ 
						var $objError = $.parseJSON(d); 
						if($objError){
							alert("A User Role with this slug has already been created"); 
						}
						else{
							document.location.reload();
						}
				});
				 
				 return false;
			}

			function UpdateRole(){
				console.log(window.CurrentRole);
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#" + window.CurrentRole + "-role-add-name").val();
				if(typeof name == undefined || name == null){ alert("ERROR: Unable to save role, problem setting role name"); return;}
				var slug = $("#" + window.CurrentRole + "-role-add-slug").val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save role, problem setting role slug"); return;}
				var capabilities = [];
				$('.' + window.CurrentRole + '-role-capability:checked').each(function(d){
					capabilities.push({ 'name' : $(this).attr('name'), 'value' : $(this).val()});
				});
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save role, problem setting role capabilities"); return;}
				var description = $("#role-add-description").val();

				
				if(name.length < 3 || name.length > 20){
					alert("User role names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("User role slugs must be 3 characters or longer and less than 20");
					return;
				}

				
				if(name.search(nameRegEx) !== -1){
					alert("Names can only contain Alphanumeric, dash and space characters");
					return;
				}
				if(slug.search(slugRegEx) !== -1){
					alert("Slugs can only contain Alphanumeric, underscore and dash characters");
					return;
				}

				var TypeDataArray = 
					
					{
						"name" : name,
						"slug" : slug,
						"capabilities" : capabilities,
						"description" : description
					}

				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"old-role-data" : TypeDataArray
					},
				function(d){ 
						var $objError = $.parseJSON(d); 
						if($objError){
							alert($objError); 
						}
						else{
							document.location.reload();
						}
				});
				 
				 return false;
			}
			
			function AddGlobal(){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#global-add-name").val();
				if(typeof name == undefined || name == null){ alert("ERROR: Unable to save type, problem setting global field name"); return;}
				var slug = $("#global-add-slug").val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field slug"); return;}
				var ctrl = $("#global-add-ctrl").val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field control type"); return;}
				var description = $("#global-add-description").val();

				
				if(name.length < 3 || name.length > 20){
					alert("Global user field names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("Global user field slugs must be 3 characters or longer and less than 20");
					return;
				}

				
				if(name.search(nameRegEx) !== -1){
					alert("Singular Names can only contain Alphanumeric, dash and space characters");
					return;
				}
				if(slug.search(slugRegEx) !== -1){
					alert("Slugs can only contain Alphanumeric, underscore and dash characters");
					return;
				}

				var TypeDataArray = 
					
					{
						"name" : name,
						"slug" : slug,
						"control_type" : ctrl,
						"description" : description
					}

				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"NewGlobalData" : TypeDataArray
					},
				function(d){ 
						var $objError = $.parseJSON(d); 
						if($objError){
							$objError.forEach(function(entry){ 
								strErrors = strErrors + entry.replace("_", " ") + " ";
							}); 
							alert("The following fields are duplicates of already existing values: " + strErrors); 
						}
						else{
							document.location.reload();
						}
				});
				 
				 return false;
			}
			
			function AddType(){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#type-add-name").val();
				
				if(typeof name == undefined || name == null){alert("ERROR: Unable to save type, problem setting user type name"); return;}
				
				var pluralName = $("#type-add-plurname").val();
				
				if(typeof pluralName == undefined || pluralName == null){ alert("ERROR: Unable to save type, problem setting user type plural name"); return;}
				
				var slug = $("#type-add-slug").val();
				
				if(typeof slug == undefined || slug == null){alert("ERROR: Unable to save type, problem setting user type slug"); return;}
				
				var id = $("#type-add-id").val();
				
				var description = $("#type-add-description").val();
				
				
				if(name.length < 3 || name.length > 20){
					alert("User singular names must be 3 characters or longer and less than 20");
					return;
				}
				if(pluralName.length < 3 || pluralName.length > 20){
					alert("User plural names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("User slugs must be 3 characters or longer and less than 20");
					return;
				}
				if(id.length < 3 || id.length > 20){
					alert("User slugs must be 3 characters or longer and less than 20");
					return;
				}

				
				if(name.search(nameRegEx) !== -1){
					alert("Singular Names can only contain Alphanumeric, dash and space characters");
					return;
				}
				if(pluralName.search(nameRegEx) !== -1){
					alert("Plural Names can only contain Alphanumeric, dash and space characters");
					return;
				}
				if(slug.search(slugRegEx) !== -1){
					alert("Slugs can only contain Alphanumeric, underscore and dash characters");
					return;
				}

				var TypeDataArray = 
					
					{
						"singular_name" : name,
						"name" : pluralName,
						"slug" : slug,
						"ap_id" : id,
						"description" : description
					}

				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"NewTypeData" : TypeDataArray
					},
				function(d){ 
						var $objError = $.parseJSON(d); 
						if($objError){
							$objError.forEach(function(entry){ 
								strErrors = strErrors + entry.replace("_", " ") + " ";
							}); 
							alert("The following fields are duplicates of already existing values: " + strErrors); 
						}
						else{
							document.location.reload();
						}
				});
				 
				 return false;
				}
			
			function UpdateGlobal(obj){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $(obj).siblings("#global-edit-name").val();
				if(typeof name == undefined || name == null){ alert("ERROR: Unable to save type, problem setting global field name"); return;}
				var description = $(obj).siblings("#global-edit-description").val();
				var slug = $(obj).siblings("#global-edit-slug").val();
				
				if(name.length < 3 || name.length > 20){
					alert("Global user field names must be 3 characters or longer and less than 20");
					return;
				}
				
				if(name.search(nameRegEx) !== -1){
					alert("Singular Names can only contain Alphanumeric, dash and space characters");
					return;
				}
				var TypeDataArray = 
					
					{
						"name" : name,
						"description" : description,
						"slug" : slug
					}

				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"OldGlobalData" : TypeDataArray
					},
				function(d){ 
						var $objError = $.parseJSON(d); 
						if($objError){
							alert("The name entered already exists for another global field"); 
						}
						else{
							document.location.reload();
						}
				});
				 return false;
			}
			
			function UpdateType(obj){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var slug = $(obj).siblings("#type-edit-slug").val();
				var ap_id = $(obj).siblings("#type-edit-apid").val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting user type slug"); return;}
				var id = $("#type-add-id").val();
				var description = $(obj).siblings("#type-edit-description").val();

				
				if(slug.length < 3 || slug.length > 20){
					alert("User slugs must be 3 characters or longer and less than 20");
					return;
				}
				
				if(slug.search(slugRegEx) !== -1){
					alert("Slugs can only contain Alphanumeric, underscore and dash characters");
					return;
				}

				var TypeDataArray = 
					
					{
						"slug" : slug,
						"description" : description,
						"ap_id" : ap_id
					}

				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"OldTypeData" : TypeDataArray
					},
				function(d){ 
						var $objError = $.parseJSON(d); 
						if($objError){
							alert("The slug entered already exists"); 
						}
						else{
							document.location.reload();
						}
				});
				 
				 return false;
				}
			</script>
			<?php 
		}
	
	
}