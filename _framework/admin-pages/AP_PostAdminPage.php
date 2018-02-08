<?php

class AP_PostAdminPage extends AP_AdminPageBase {


	public static function Init(){
		add_submenu_page('ap-settings-home', 'Agile Press Custom Post Types Manager', 'Post Type Manager', 'administrator', 'ap-post-types', array('AP_PostAdminPage', 'PageCreate'));
		if(!empty($_POST) && $_POST['action'] == "ajax")
			self::PageCreate();
			
	}

	public static function PageCreate(){
		if($_GET['page'] <> 'ap-post-types') return;
		$strClass = get_class();
		$objPage =  new $strClass;
		$objPage->Run();
	}



	protected function DoAJAX(){
		global $wpdb;
		unset($_POST['action']);
		$objXMLHandler = new AP_CoreCodeGenerator;
		if(isset($_POST['CodeGen'])){
			$obj = new AP_CoreCodeGenerator;
			$obj->CodeGenRun();
			die;
		}
		if(isset($_POST['TypeFieldDelete'])){
			$intIndex = 0;
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $PostType){
				$PostType = unserialize($PostType);
				if($_POST["type"] == $PostType[0]['slug']){
					echo "hello";
					$intLowerIndex = 0;
					foreach ($PostType[1] as $FieldArray){
						if($FieldArray['slug'] == $_POST['TypeFieldDelete']){
							print_r($PostType[1][$intLowerIndex]);
							unset($PostType[1][$intLowerIndex]);
							$objXMLHandler->objConfigData->posttypes->posttype[$intIndex] = serialize($PostType);
							$objXMLHandler->SaveConfigurationData();
							$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "post_type_field";
							$strTableName2 = $wpdb->prefix . strtolower(AP_PREFIX) . "post_type";
							$strTableChecker = $_POST['TypeFieldDelete'];
							$strTableChecker2 = $_POST['type'];
							$intFieldID = $wpdb->get_results("SELECT id FROM $strTableName WHERE slug = '$strTableChecker';");
							$intTypeID = $wpdb->get_results("SELECT id FROM $strTableName2 WHERE slug = '$strTableChecker2';");
							$wpdb->delete($wpdb->prefix . strtolower(AP_PREFIX) . "post_field", array("post_type_id" => $intTypeID[0]->id, "post_field_id" => $intFieldID[0]->id));
							$wpdb->delete($wpdb->prefix . strtolower(AP_PREFIX) . "post_type_field", array("id" => $intFieldID[0]->id, "slug" => $_POST['TypeFieldDelete']));
							echo json_encode(false);
							return;
						}
						$intLowerIndex++;
					}
				}
				$intIndex++;
			}
		}
		if(isset($_POST['PostTypeDelete'])){
			$intIndex = 0;
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $PostType){
				$PostType = unserialize($PostType);
				if($_POST["PostTypeDelete"] == $PostType[0]['slug']){
					unset($objXMLHandler->objConfigData->posttypes->posttype[$intIndex]);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->delete($wpdb->prefix . "ap_post_type", array('slug' => $_POST["PostTypeDelete"]));
					return;
				}
				$intIndex++;
			}
		}
		if(isset($_POST["OldTypeData"])){
			$intIndex = 0;
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $PostType){
				$PostType = unserialize($PostType);
				if($_POST["OldTypeData"]['slug'] == $PostType[0]['slug'] && $_POST["OldTypeData"]['ap_id'] != $PostType[0]['ap_id']){
					echo json_encode(true);
					return;
				}
			}
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $PostType){
				$PostType = unserialize($PostType);
				if($_POST["OldTypeData"]['ap_id'] == $PostType[0]['ap_id']){
					$PostType[0]['slug'] = $_POST["OldTypeData"]['slug'];
					$PostType[0]['description'] = $_POST["OldTypeData"]['description'];
					$objXMLHandler->objConfigData->posttypes->posttype[$intIndex] = serialize($PostType);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->update($wpdb->prefix . "ap_post_type", array('description' => $PostType[0]['description'], 'slug' => $PostType[0]['slug']), array('ap_id' => $PostType[0]['ap_id']));
				}
				$intIndex++;
			}
			echo json_encode(false);
			return;
		}
		if(isset($_POST['NewFieldData'])){
			if(!isset($_POST['type'])){
				echo "Error No Post Type Selected for Field";
				return;
			}
			else{
				foreach ($objXMLHandler->objConfigData->postfields->postfield as $PostType){
					$PostType = unserialize($PostType);
					if($_POST["NewFieldData"]['slug'] == $PostType['slug']){
						echo json_encode(true);
						return;
					}
				}
				foreach ($objXMLHandler->objConfigData->posttypes->posttype as $PostType){
					$PostType = unserialize($PostType);
					if($_POST["type"] == $PostType[0]['slug']){
						foreach($PostType[1] as $PostTypeField){
							if($_POST["NewFieldData"]['name'] == $PostTypeField['name']){
								echo json_encode(true);
								return;
							}
						}
					}
						
					foreach($PostType[1] as $PostTypeField){
						if($_POST["NewFieldData"]['slug'] == $PostTypeField['slug']){
							echo json_encode(true);
							return;
						}
					}
				}
				$intIndex = 0;
				foreach ($objXMLHandler->objConfigData->posttypes->posttype as $PostType){
					$PostType = unserialize($PostType);
					if($_POST["type"] == $PostType[0]['slug']){
						if(!is_array($PostType[1]))
							$PostType[1] = array();
						else{
							$PostType[1][] = $_POST["NewFieldData"];
						}
						$objXMLHandler->objConfigData->posttypes->posttype[$intIndex] = serialize($PostType);
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
				echo "Error No Post Type Selected for Field";
				return;
			}
			else{
				$intIndex = 0;
				foreach ($objXMLHandler->objConfigData->posttypes->posttype as $PostType){
					$PostType = unserialize($PostType);
					if($_POST["type"] == $PostType[0]['slug']){
						foreach($PostType[1] as $PostTypeField){
							if($_POST["OldFieldData"]['name'] == $PostTypeField['name'] && $_POST["OldFieldData"]['slug'] <> $PostTypeField['slug']){
								echo json_encode(true);
								return;
							}
						}
						$intLowerIndex = 0;
						foreach($PostType[1] as $PostTypeField){
							if($_POST["OldFieldData"]['slug'] == $PostTypeField['slug']){
								$PostType[1][$intLowerIndex] =  $_POST["OldFieldData"];
								$objXMLHandler->objConfigData->posttypes->posttype[$intIndex] = serialize($PostType);
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
			foreach ($objXMLHandler->objConfigData->postfields->postfield as $PostType){
				$PostType = unserialize($PostType);
				if($_POST["OldGlobalData"]['name'] == $PostType['name'] && $_POST["OldGlobalData"]['slug'] != $PostType['slug']){
					echo json_encode(true);
					return;
				}
			}
			foreach ($objXMLHandler->objConfigData->postfields->postfield as $PostType){
				$PostType = unserialize($PostType);
				if($_POST["OldGlobalData"]['slug'] == $PostType['slug']){
					$PostType['description'] = $_POST["OldGlobalData"]['description'];
					$PostType['name'] = $_POST["OldGlobalData"]['name'];
					$objXMLHandler->objConfigData->postfields->postfield[$intIndex] = serialize($PostType);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->update($wpdb->prefix . "ap_post_type_field", array('description' => $PostType['description'], 'name' => $PostType['name']), array('slug' => $PostType['slug']));
				}
				$intIndex++;
			}
			echo json_encode(false);
			return;
		}
		if(isset($_POST['GlobalFieldDelete'])){
			$intIndex = 0;
			foreach ($objXMLHandler->objConfigData->postfields->postfield as $PostType){
				$PostType = unserialize($PostType);
				print_r(json_encode($PostType));
				if($_POST["GlobalFieldDelete"] == $PostType['slug']){
					unset($objXMLHandler->objConfigData->postfields->postfield[$intIndex]);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->delete($wpdb->prefix . "ap_post_type_field", array('slug' => $_POST["GlobalFieldDelete"]));
					return;
				}
				$intIndex++;
			}
		}
		if(isset($_POST["NewTypeData"])) {
			$strErrorArray = array();
			$strNewTypeArray = $_POST['NewTypeData'];
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $PostType){
				$PostTypeArray = unserialize($PostType);
				if(strtolower($PostTypeArray[0]["singular_name"]) == strtolower($strNewTypeArray['singular_name']))
					$strErrorArray[] = "Singular Name";
				if(strtolower($PostTypeArray[0]["name"]) == strtolower($strNewTypeArray['plural_name']))
					$strErrorArray[] = "Plural Name";
				if(strtolower($PostTypeArray[0]["slug"]) == strtolower($strNewTypeArray['slug']))
					$strErrorArray[] = "Slug";
				if(strtolower($PostTypeArray[0]["ap_id"]) == strtolower($strNewTypeArray['ap_id']))
					$strErrorArray[] = "AgilePress ID";
			}
			if(!empty($strErrorArray)){
				echo json_encode($strErrorArray);
				die;
			}
			else{
				$newPostType = $objXMLHandler->objConfigData->posttypes->addChild("posttype", serialize(array($strNewTypeArray,array())));
				$objXMLHandler->SaveConfigurationData();
				echo json_encode(false);
			}
			die;
		}
		if(isset($_POST["NewGlobalData"])){
			$strErrorArray = array();
			$strNewTypeArray = $_POST['NewGlobalData'];
			foreach ($objXMLHandler->objConfigData->postfields->postfield as $PostType){
				$PostTypeArray = unserialize($PostType);
				if(strtolower($PostTypeArray["name"]) == strtolower($strNewTypeArray['name']))
					$strErrorArray[] = "Name";
				if(strtolower($PostTypeArray["slug"]) == strtolower($strNewTypeArray['slug']))
					$strErrorArray[] = "Slug";
			}
			if(!empty($strErrorArray)){
				echo json_encode($strErrorArray);
				die;
			}
			else{
				$newPostType = $objXMLHandler->objConfigData->postfields->addChild("postfield", serialize($strNewTypeArray));
				$objXMLHandler->SaveConfigurationData();
				echo json_encode(false);
			}
		}
		if(isset($_POST['CurrentCustomTypes'])){
			echo "<?xml version='1.0'?>";
			echo "<root>";
			$strCustomPostTypeArray = AP_CustomPostTypeBase::GetPostTypeArray();
			foreach ($strCustomPostTypeArray as $strCustomPostType){
				$strFieldArray = AP_CustomPostTypeBase::GetPostTypeFieldArray($strCustomPostType['name']);
				echo "<item plural_name='"; echo $strCustomPostType['name']; echo "' singular_name='"; echo $strCustomPostType['singular_name']; echo "' "; echo "slug='"; echo $strCustomPostType['slug'] . "'>";
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
			$strCustomPostTypeArray = AP_CustomPostTypeBase::GetGlobalPostFieldArray();
			foreach ($strCustomPostTypeArray as $strCustomPostType){
				echo "<item singular_name='"; echo $strCustomPostType['name']; echo "' slug='"; echo $strCustomPostType['slug']; echo "'></item>";
			}
			echo "</root>";
			die;
		}
	}


	protected function Content(){
		?>
			<style type="text/css">
			.ap-admin-nav a { display:block; height:100%; width:100px; text-align:center; float:left; color:white; font-size:14px; font-weight:bold; line-height:35px; text-decoration:none;}
			#ap-nav-home {width:50px !important; margin-left:10px; margin-right:10px;}
			.ap-settings-field {margin:0; padding:0; padding-top:20px; padding-bottom:7px; font-size:16px; line-height:16px;}
			.ap-settings-field-input {border-radius:5px; width:400px; font-size:16px; line-height:16px;}
			.ap-admin-button { text-decoration:none !important;  }
			.ap-admin-button .ap-admin-button-2 { text-decoration:none; cursor:pointer;  display:block; width:150px; height:40px; border-radius:5px; border-style:solid; border-width:1px; border-color: rgb(9, 128, 171); margin-bottom: 20px; background: -moz-linear-gradient(rgb(54,183,231),rgb(7,148,198)); text-align:center; }
			.ap-admin-button .ap-admin-button-2 span {text-decoration:none; position:relative; top:10px; color:white; font-weight:bold;}
			.ap-settings-field-note { font-size:11px; color:rgb(113,113,113); margin:0; padding:3px; }
			.ap-post-key {display:block; font-size:14px; float:left; height:100%; color:white; font-weight:bold; text-align:center; line-height:100%; width:250px;}
			.ap-post-key-row {display:block; font-size:14px; float:left; height:100%; color:black;  text-align:center; line-height:100%; width:250px;}
			.light {background:rgb(243,243,243);}
			.dark {background:rgb(226,226,226);}
			#field-view { width:150px; position:absolute; overflow:hidden; display:none; background:white; min-height:10px; border-style:solid; border-width:1px; border-color:rgb(179,179,179); padding-top:7px; padding-bottom:7px; -webkit-box-shadow: 5px 5px 5px 0px rgba(50, 50, 50, 0.55); -moz-box-shadow: 5px 5px 5px 0px rgba(50, 50, 50, 0.55); box-shadow: 5px 5px 5px 0px rgba(50, 50, 50, 0.55);} 
			.field-view-row {width:100%; font-size:11px; line-height:15px; padding:0; margin:0; padding-left:7px;}
			#overlay{position:fixed;  width:100%;  height:100%;  top:0;  left:0;  opacity:0.6; background:black; z-index:1; display:none;/* see below for cross-browser opacity */}
			a {cursor:pointer;}
		</style>
		<h1 style="margin-left:15px;">AgilePress Post Type Manager
			</h1>
			<?php $objCodeCheck = new AP_CoreCodeGenerator; if(intval(get_option("AP_CodeGen_Version"))<>intval($objCodeCheck->objConfigData->title->version)) $this->AdminCodeGenError();?>
			<div style="width:800px;">
			<div class="ap-admin-nav" style=" padding: 1px 20px; margin: 5px 15px 2px; background: -moz-linear-gradient(rgb(115,115,115),rgb(76,76,76)); height:35px; margin-top:40px; width:100%;">
				<a id="ap-nav-home" href="/wp-admin/admin.php?page=ap-settings-home"><i class="fa fa-home fa-2x" style="margin-top:5px;"></i></a>
				<a href="/wp-admin/admin.php?page=ap-site-settings"  >Settings</a>
				<a href="/wp-admin/admin.php?page=ap-post-types" style="background:rgb(52,52,52);">Posts</a>
				<a href="/wp-admin/admin.php?page=ap-user-types">Users</a>
				<a href="/wp-admin/admin.php?page=ap-code-gen-settings">CodeGen</a>
			</div>
			<div style="padding: 1px 20px; margin: 5px 15px 2px; margin-top:0; padding-top:0; height:850px; background:white; width:100%; ">
				<p class="ap-settings-field" style="font-weight:bold; float:left; margin-top:15px; font-size:18px;">Post Types </p>
					<span class="type-adder" style="position:relative;  cursor:pointer !important; display:block; float:right; width:200px; height:50px; margin-top:20px; margin-right:0%; padding-right:0;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:130px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; right:0px; font-size:14px;">Add new post type</p>
					</span>
					<div style="clearfix:both; position:relative; float:left; border-style:solid; top:40px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:300px; overflow:auto; margin-bottom:60px;" >
						<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
						</div>
						<?php $objData = AP_CustomPostTypeBase::GetPostTypeArray(); $objDataTrack = array(); $boolBackground = true; $intPixCount = 40; foreach($objData as $key => $value){ $objDataTrack[] = $value['slug'];?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row"><?php _e($value['singular_name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="field-viewer" data-type="<?php _e($value['slug']);?>">View Fields</a>|<a class="edit-viewer" >Edit</a>|<a class="type-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } ?> 
						<?php $objNewData = AP_CustomPostTypeBase::GetConfigPostTypeArray(); $boolCodeGenCheck = false; foreach($objNewData as $key => $value){ if(!in_array($value[0]['slug'],$objDataTrack)){ $boolCodeGenCheck = true;?>
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
						<p class="ap-settings-field-note" style="position:relative; top:425px; color:rgb(241, 146, 7); left:-80px;padding:0; margin:0;">*Post Types bordered in orange have not been had their code generated yet</p>
					<?php }?>
				<p class="ap-settings-field" style="font-weight:bold; float:left; margin-top:0px; font-size:18px;">Global Post Fields </p>
					<span id="ap-global-viewer" style="position:relative; cursor:pointer !important; display:block; float:right; width:200px; height:50px; margin-top:5px; margin-right:0%; padding-right:0;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:175px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; right:0px; font-size:14px;">Add new global post field</p>
					</span>
					<div id="ap-global-holder" style="clearfix:both; position:relative; float:left; border-style:solid; top:40px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:300px;" >
						<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
						</div>
						<?php $objDataTrack = array(); $objData = AP_CustomPostTypeBase::GetGlobalPostFieldArray(); $boolBackground = true; $intPixCount = 40; foreach($objData as $key => $value){ $objDataTrack[] = $value['slug'];?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row"><?php _e($value['name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="edit-viewer">Edit</a>|<a class="global-field-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; }  ?> 
						<?php $objNewData = AP_CustomPostTypeBase::GetConfigFieldArray(); $boolCodeGenCheck = false; foreach($objNewData as $key => $value){ if(!in_array($value['slug'],$objDataTrack)){ $boolCodeGenCheck = true;?>
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
			<?php $objData = AP_CustomPostTypeBase::GetPostTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomPostTypeBase::GetPostTypeFieldArray($value['name']); $objNewFields = AP_CustomPostTypeBase::GetPostTypeFieldConfigArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-fields" class="field-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:850px; background:white; width:800px; ">
					<div style="height:40px;  width:840px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:-20px;">Custom <?php _e($value['name']);?> Fields</p>
					<a class="field-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
					</div>
					<span  class="field-adder" style="position:absolute; display:block; float:left; width:200px; height:50px; margin-top:20px; left:20px;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:175px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; left:45px; font-size:14px;">Add new <?php _e(strtolower($value['singular_name']));?> field</p>
					</span>
					<div class="field-holder-bar" style="position:fixed; top:0; left:0; width:802px; height:40px; top:200px; margin-left:0px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
					</div>
					<div id="field-row-holder" style="clearfix:both; position:fixed; border-style:solid; top:240px; margin-left:0px; border-width:2px; border-top-width:0; border-color:rgb(179,179,179); background:white; z-index: 2; width:798px; height:670px; overflow:auto; margin-bottom:100px;" >
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
			<?php $objData = AP_CustomPostTypeBase::GetPostTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomPostTypeBase::GetPostTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:690px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:-30px; height:20px;"><?php _e($value['singular_name']);?> Type Editor</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
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
			<?php $objData = AP_CustomPostTypeBase::GetConfigPostTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomPostTypeBase::GetPostTypeFieldArray($value[0]['name']); $objNewFields = AP_CustomPostTypeBase::GetPostTypeFieldConfigArray($value[0]['name']);?>
			<div id="ap-<?php _e($value[0]['slug']);?>-fields" class="field-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:850px; background:white; width:800px; ">
					<div style="height:40px;  width:840px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:-20px;">Custom <?php _e($value[0]['name']);?> Fields</p>
					<a class="field-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
					</div>
					<span  class="field-adder" style="position:absolute; display:block; float:left; width:200px; height:50px; margin-top:20px; left:20px;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:175px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; left:45px; font-size:14px;">Add new <?php _e(strtolower($value[0]['singular_name']));?> field</p>
					</span>
					<div class="field-holder-bar" style="position:fixed; top:0; left:0; width:802px; height:40px; top:200px; margin-left:0px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
					</div>
					<div id="field-row-holder"  style="clearfix:both; position:fixed; border-style:solid; top:240px; margin-left:0px; border-width:2px; border-top-width:0; border-color:rgb(179,179,179); background:white; z-index: 2; width:798px; height:670px; overflow:auto; margin-bottom:100px;" >
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
			<?php $objData = AP_CustomPostTypeBase::GetConfigPostTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomPostTypeBase::GetPostTypeFieldArray($value[0]['name']);?>
			<div id="ap-<?php _e($value[0]['slug']);?>-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:690px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:-30px; height:20px;"><?php _e($value[0]['singular_name']);?> Type Editor</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
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
			<?php $objData = AP_CustomPostTypeBase::GetGlobalPostFieldArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomPostTypeBase::GetPostTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:690px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:20px; text-align:left; height:20px;"><?php _e($value['name']);?> Global Field Editor</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
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
			<?php $objData = AP_CustomPostTypeBase::GetConfigFieldArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomPostTypeBase::GetPostTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:640px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px;  margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:20px; text-align:left; height:20px;"><?php _e($value['name']);?> Global Field Editor</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
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
			<?php $objData = AP_CustomPostTypeBase::GetPostTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomPostTypeBase::GetPostTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-field-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:620px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left !important; width:80%;" id="field-header"></p>
					<a class="field-edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
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
						<option value="checkbox"> Check Box </option>
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
			<div id="ap-field-adder" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:620px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" id="field-header-add" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left !important; width:80%;" id="field-header"></p>
					<a class="field-edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
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
						<option value="checkbox"> Check Box </option>
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
			<div id="ap-type-adder" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:690px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;">Post Type Creator</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
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
			<div id="ap-global-adder" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:640px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;">Global Post Field Creator</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
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
						<option value="checkbox"> Check Box </option>
					</select>
					<p class="ap-settings-field"> Field Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="global-add-description" ></textarea>
					<a class="ap-admin-button" style="float:left" onclick="AddGlobal(); return false;">
					<span class="ap-admin-button-2">
						<span style=""> Add Field </span>
					</span>
					</a>
			</div>
			<div id="temp-field-edit" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:640px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;">Global Post Field Creator</p>
					<a class="temp-edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
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
						<option value="checkbox"> Check Box </option>
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
			<?php 
		}
	
	protected function Scripts(){
		?>
			<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
			<script type="text/javascript" src="/wp-content/plugins/agilepress/_framework/js/jquery-1.10.2.js"></script>
			<script type="text/javascript">
			var CurGlobalFields = [];
			var CurPostTypes = [];
			var FieldNameArray = []; 
			var CurrentType;
			var CurrentField;

	
			$(document).ready(function(){
				$.post("", { "action" : "ajax", "CurrentGlobalFields" : true }, function(d){ $(d).find('item').each(function(index){ CurGlobalFields.push({ "singular_name" : $(this).attr("singular_name"), "slug" : $(this).attr("slug")}); }); console.log(CurGlobalFields);}, "xml") ;
				$.post("", { "action" : "ajax", "CurrentCustomTypes" : true }, function(d){ $(d).find('item').each(function(index){ var curFields = []; $(this).find('field').each(function(index){ var tempField = $.parseJSON($(this).text()); curFields.push(tempField);}); CurPostTypes.push({ "plural_name" : $(this).attr("plural_name"), "singular_name" : $(this).attr("singular_name"), "slug" : $(this).attr("slug"), "fields" : curFields}); }); console.log(CurPostTypes);}, "xml") ;

				$("#type-add-slug").keyup(function(e){$("#type-add-id").val($("#type-add-slug").val().replace("_","-")) });

				$("#ap-global-viewer").click(function(event){
					$("#ap-global-adder").css("display","block");
					$("#overlay").css("display","block");
				});
				
				$(".field-viewer").each(function(index){$(this).hover(function(){CurPostTypes.forEach(function(entry){ if (entry['slug'] == $(".field-viewer").eq(index).attr("data-type")){ $("#field-view").html(""); entry['fields'].forEach(function(lowerentry){$("#field-view").append("<p class='field-view-row'>" + lowerentry['name'] + "</p>");}); $("#field-view").css("top",$(".field-viewer").eq(index).offset().top - 30); $("#field-view").css("left",$(".field-viewer").eq(index).offset().left - 130); $("#field-view").css("display","block");}})}, function(){$("#field-view").css("display","none");})});
				var intPopupOffset = $("#wpcontent").width() - 800;
				intPopupOffset = intPopupOffset / 2;
				intPopupOffset = intPopupOffset - 20;

				
				
				$(".field-holder").css("margin-left",intPopupOffset);
				$(".field-holder-bar").css("left",intPopupOffset+200);

				intPopupOffset = intPopupOffset + 100;
				$(".edit-holder").css("margin-left",intPopupOffset);
				$(".edit-holder-bar").css("left",intPopupOffset+200);

				$(".type-delete").click(function(event){
					if(confirm("Are you sure you want to delete this post type?")){
						$.post("",{"action":"ajax","PostTypeDelete" : $(event.target).parent().siblings("p#row-slug").text()},function(data){ document.location.reload();});
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
					CurrentType = $(this).parent().siblings("#row-slug").text();
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
					$("#field-plural").val(objField['name']);
					$("#field-slug").val(objField['slug']);
					$("#field-ctrl").val(objField['control_type']);
					$("#field-description").val(objField['description']);
					$("#field-header").html(CapitaliseFirstLetter(CurrentType) + " Type Field Editor");

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

				
				if(name.length < 3){
					alert("Global post field names must be longer than 2 characters");
					return;
				}
				if(slug.length < 3){
					alert("Global post field slugs must be longer than 2 characters");
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
						"ctrl" : ctrl,
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

				
				if(name.length < 3){
					alert("Global post field names must be longer than 2 characters");
					return;
				}
				if(slug.length < 3){
					alert("Global post field slugs must be longer than 2 characters");
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
						"ctrl" : ctrl,
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
			
			function AddField(boolAddMore = false){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#field-add-name").val();
				if(typeof name == undefined || name == null){ alert("ERROR: Unable to save type, problem setting global field name"); return;}
				var slug = $("#field-add-slug").val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field slug"); return;}
				var ctrl = $("#field-add-ctrl").find('option:selected').val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field control type"); return;}
				var description = $("#field-add-description").val();

				
				if(name.length < 3){
					alert("Global post field names must be longer than 2 characters");
					return;
				}
				if(slug.length < 3){
					alert("Global post field slugs must be longer than 2 characters");
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
						"ctrl" : ctrl,
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

				
				if(name.length < 3){
					alert("Global post field names must be longer than 2 characters");
					return;
				}
				if(slug.length < 3){
					alert("Global post field slugs must be longer than 2 characters");
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
						"ctrl" : ctrl,
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
				
				if(typeof name == undefined || name == null){alert("ERROR: Unable to save type, problem setting post type name"); return;}
				
				var pluralName = $("#type-add-plurname").val();
				
				if(typeof pluralName == undefined || pluralName == null){ alert("ERROR: Unable to save type, problem setting post type plural name"); return;}
				
				var slug = $("#type-add-slug").val();
				
				if(typeof slug == undefined || slug == null){alert("ERROR: Unable to save type, problem setting post type slug"); return;}
				
				var id = $("#type-add-id").val();
				
				var description = $("#type-add-description").val();
				
				
				if(name.length < 3){
					alert("Post singular names must be longer than 2 characters");
					return;
				}
				if(pluralName.length < 3){
					alert("Post plural names must be longer than 2 characters");
					return;
				}
				if(slug.length < 3){
					alert("Post slugs must be longer than 2 characters");
					return;
				}
				if(id.length < 3){
					alert("Post slugs must be longer than 2 characters");
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
				
				if(name.length < 3){
					alert("Global post field names must be longer than 2 characters");
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
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting post type slug"); return;}
				var id = $("#type-add-id").val();
				var description = $(obj).siblings("#type-edit-description").val();

				
				if(slug.length < 3){
					alert("Post slugs must be longer than 2 characters");
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