<?php
/**
 * Class for handling all Post Type UI output as well as AJAX and menu registeration
 *
 * @since 1.0
 * @package AgilePress
 * @subpackage Post Type Administrator Page
 * @author AgilePress Core Developement Team
 *
 */
class AP_PostAdminPage extends AP_AdminPageBase {

	/**
	 * Called on the wordpress hook admin_init, this method registers the sub_menu AgilePress item "Post Types" and redirects admin AJAX calls back to the page
	 *
	 * @since 1.0
	 * @return void
	 */
	public static function Init(){
		add_submenu_page('ap-settings-home', 'Agile Press Custom Post Types Manager', 'Posts', 'administrator', 'ap-post-types', array('AP_PostAdminPage', 'PageCreate'));
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
		if($_GET['page'] <> 'ap-post-types') return;
		$strClass = get_class();
		$objPage =  new $strClass;
		$objPage->Run();
	}

	/**
	 * Method for handling all AJAX requests to this administrator page
	 *
	 * @since 1.0
	 * @return void
	 */
	protected function DoAJAX(){
		global $wpdb;
		unset($_POST['action']);
		$objXMLHandler = new AP_CoreCodeGenerator;

		/////////////////////////////////////////////
		//AJAX logic for deleting a Post Type field//
		/////////////////////////////////////////////
		if(isset($_POST['type-field-delete'])){
			$intIndex = 0;
			
			//loop through post types
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $strPostTypeArray){
				$strPostTypeArray = unserialize($strPostTypeArray);
				
				//Check each config post-type against the  post type slug that was posted back to ajax
				if($_POST["type"] == $strPostTypeArray[0]['slug']){
					$intLowerIndex = 0;
					$strPostTypeArray[1] = array_merge($strPostTypeArray[1]);
					
					// if post-type matches then loop through the post-type fields to find the modified field
					foreach ($strPostTypeArray[1] as $FieldArray){
						if($FieldArray['slug'] == $_POST['type-field-delete']){
							
							//modify the field and save all required files/tables/data
							unset($strPostTypeArray[1][$intLowerIndex]);
							$objXMLHandler->objConfigData->posttypes->posttype[$intIndex] = serialize($strPostTypeArray);
							$objXMLHandler->SaveConfigurationData();
							$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "post_type_field";
							$strTableName2 = $wpdb->prefix . strtolower(AP_PREFIX) . "post_type";
							$strTableChecker = $_POST['type-field-delete'];
							$strTableChecker2 = $_POST['type'];
							$strSQLQuery1 = "SELECT id FROM $strTableName WHERE slug = '$strTableChecker';";
							$strSQLQuery2 = "SELECT id FROM $strTableName2 WHERE slug = '$strTableChecker2';";
							$intFieldID = $wpdb->get_results($strSQLQuery1);
							$intTypeID = $wpdb->get_results($strSQLQuery2);
							$wpdb->delete($wpdb->prefix . strtolower(AP_PREFIX) . "post_field", array("post_type_id" => $intTypeID[0]->id, "post_field_id" => $intFieldID[0]->id));
							$wpdb->delete($wpdb->prefix . strtolower(AP_PREFIX) . "post_type_field", array("id" => $intFieldID[0]->id, "slug" => $_POST['type-field-delete']));
							echo json_encode(true);
							return;
						}
						$intLowerIndex++;
					}
				}
				$intIndex++;
			}
		}
		
		///////////////////////////////////////
		//AJAX logic for deleting a Post Type//
		///////////////////////////////////////
		if(isset($_POST['post-type-delete'])){
			$intIndex = 0;
			//loop through configuration file post-types
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $strPostTypeArray){
				$strPostTypeArray = unserialize($strPostTypeArray);
				
				// if the current-post type matches the post-type we are trying to delete then delelete it
				if($_POST["post-type-delete"] == $strPostTypeArray[0]['slug']){
					unset($objXMLHandler->objConfigData->posttypes->posttype[$intIndex]);
					$objXMLHandler->SaveConfigurationData();
					$strSlug = $_POST["post-type-delete"];
					$strTableName = $wpdb->prefix . strtolower(AP_PREFIX) . "post_type";
					$strTableName2 = $wpdb->prefix . strtolower(AP_PREFIX) . "post_field";
					$strTableName3 = $wpdb->prefix . strtolower(AP_PREFIX) . "post_type_field";
					$intIDArray = $wpdb->get_results("SELECT id FROM $strTableName WHERE ap_id = '$strSlug';", ARRAY_A);
					$intID = $intIDArray[0]['id'];
					$intIDTempArray = array_merge($wpdb->get_results("SELECT post_field_id FROM $strTableName2 WHERE post_type_id = '$intID';", ARRAY_A));
					$intID2Array = array();
					foreach($intIDTempArray as $intIDArray){
						$intID2Array[] = array("id" => $intIDArray['post_field_id']);
					}
					$wpdb->delete($strTableName, array('slug' => $strSlug ));
					$wpdb->delete($strTableName2, array('post_type_id' => $intID ));
					foreach($intID2Array as $intIDArray)
						$wpdb->delete($strTableName3, $intIDArray);
					return;
				}
				$intIndex++;
			}
		}
		
		/////////////////////////////////////////////////
		//AJAX logic for updating an existing Post Type//
		/////////////////////////////////////////////////
		if(isset($_POST["old-type-update"])){
			$intIndex = 0;
			foreach($_POST["old-type-update"]['arguments']['labels'] as $key => $value)
				if($value === "")
					unset($_POST["old-type-update"]['arguments']['labels'][$key]);
			if($_POST["old-type-update"]['arguments']['menu_position'] === "")
				$_POST["old-type-update"]['arguments']['menu_position'] = "10";
			//Loop through configuration file post-types
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $strPostTypeArray){
				$strPostTypeArray = unserialize($strPostTypeArray);
				//if the user is trying to change a post-type slug to a slug that already exists then return and dislay error
				if($_POST["old-type-update"]['slug'] == $strPostTypeArray[0]['slug'] && $_POST["old-type-update"]['ap_id'] != $strPostTypeArray[0]['ap_id']){
					echo json_encode(true);
					return;
				}
			}
			//Loop through configuration files
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $strPostTypeArray){
				$strPostTypeArray = unserialize($strPostTypeArray);
				//if the current post matches the post we are trying to update then update all respective data and save tables/configuration file
				if($_POST["old-type-update"]['ap_id'] == $strPostTypeArray[0]['ap_id']){
					$strPostTypeArray[0]['slug'] = $_POST["old-type-update"]['slug'];
					$strPostTypeArray[0]['arguments'] = $_POST["old-type-update"]['arguments'];
					$strPostTypeArray[0]['description'] = $_POST["old-type-update"]['description'];
					$objXMLHandler->objConfigData->posttypes->posttype[$intIndex] = serialize($strPostTypeArray);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->update($wpdb->prefix . "ap_post_type", array('description' => $strPostTypeArray[0]['description'], 'slug' => $strPostTypeArray[0]['slug']), array('ap_id' => $strPostTypeArray[0]['ap_id']));
				}
				$intIndex++;
			}
			//Return with no errors
			echo json_encode(false);
			return;
		}
		
		////////////////////////////////////////////////////
		//AJAX logic for adding a new field to a post type//
		////////////////////////////////////////////////////
		if(isset($_POST['new-field-add'])){
			//if for any reason the post-type is not set return an error
			if(!isset($_POST['type'])){
				echo "Error No Post Type Selected for Field";
				return;
			}
			else{
				//loop through configuration global fields
				foreach ($objXMLHandler->objConfigData->postfields->postfield as $strPostTypeArray){
					$strPostTypeArray = unserialize($strPostTypeArray);
					//if any duplicate slugs are found return with an error
					if($_POST["new-field-add"]['slug'] == $strPostTypeArray['slug']){
						echo json_encode(true);
						return;
					}
				}
				//loop through post-types
				foreach ($objXMLHandler->objConfigData->posttypes->posttype as $strPostTypeArray){
					$strPostTypeArray = unserialize($strPostTypeArray);
					//for the individual post type, loop through unique fields and return an error if there is a duplicate name
					if($_POST["type"] == $strPostTypeArray[0]['slug']){
						foreach($strPostTypeArray[1] as $strPostTypeFieldArray){
							if($_POST["new-field-add"]['name'] == $strPostTypeFieldArray['name']){
								echo json_encode(true);
								return;
							}
						}
					}
					// loop through all fields and if any slug is duplicate then return an error
					foreach($strPostTypeArray[1] as $strPostTypeFieldArray){
						if($_POST["new-field-add"]['slug'] == $strPostTypeFieldArray['slug']){
							echo json_encode(true);
							return;
						}
					}
				}
				//if no error have returned yet then add the field data to table and configuration file
				$intIndex = 0;
				foreach ($objXMLHandler->objConfigData->posttypes->posttype as $strPostTypeArray){
					$strPostTypeArray = unserialize($strPostTypeArray);
					if($_POST["type"] == $strPostTypeArray[0]['slug']){
						if(!is_array($strPostTypeArray[1]))
							$strPostTypeArray[1] = array();
						else{
							$strPostTypeArray[1][] = $_POST["new-field-add"];
						}
						$objXMLHandler->objConfigData->posttypes->posttype[$intIndex] = serialize($strPostTypeArray);
						$objXMLHandler->SaveConfigurationData();
					}
					$intIndex++;
				}
				echo json_encode(false);
				return;
			}
		}
		
		///////////////////////////////////////////////////////
		//AJAX logic for updating an existing post type field//
		///////////////////////////////////////////////////////
		if(isset($_POST['old-field-update'])){
			//if for any reason the post-type for the field being updated is not set then return with an error
			if(!isset($_POST['type'])){
				echo "Error No Post Type Selected for Field";
				return;
			}
			else{
				//loop through the configuration post-types
				$intIndex = 0;
				foreach ($objXMLHandler->objConfigData->posttypes->posttype as $strPostTypeArray){
					$strPostTypeArray = unserialize($strPostTypeArray);
					//check current post-type against post-type posted back to ajax
					if($_POST["type"] == $strPostTypeArray[0]['slug']){
						//if the user is trying to change the name of a field to one that already exists in the post-type return an error
						foreach($strPostTypeArray[1] as $strPostTypeFieldArray){
							if($_POST["old-field-update"]['name'] == $strPostTypeFieldArray['name'] && $_POST["old-field-update"]['slug'] <> $strPostTypeFieldArray['slug']){
								echo json_encode(true);
								return;
							}
						}
						//Save the field data in the appropriate post-type and save the tables and configuration file
						$intLowerIndex = 0;
						foreach($strPostTypeArray[1] as $strPostTypeFieldArray){
							if($_POST["old-field-update"]['slug'] == $strPostTypeFieldArray['slug']){
								$strPostTypeArray[1][$intLowerIndex] =  $_POST["old-field-update"];
								$objXMLHandler->objConfigData->posttypes->posttype[$intIndex] = serialize($strPostTypeArray);
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
		
		////////////////////////////////////////////////////
		//AJAX logic for updating an existing global field//
		////////////////////////////////////////////////////
		if(isset($_POST["old-global-update"])){
			$intIndex = 0;
			//loop through existing global fields and return an error if the user is trying to change the name an already existing name
			foreach ($objXMLHandler->objConfigData->postfields->postfield as $strPostTypeArray){
				$strPostTypeArray = unserialize($strPostTypeArray);
				if($_POST["old-global-update"]['name'] == $strPostTypeArray['name'] && $_POST["old-global-update"]['slug'] != $strPostTypeArray['slug']){
					echo json_encode(true);
					return;
				}
			}
			//loop though global post fields
			foreach ($objXMLHandler->objConfigData->postfields->postfield as $strPostTypeArray){
				$strPostTypeArray = unserialize($strPostTypeArray);
				//if current global field is the correct field update necessary data in configuration file and tables
				if($_POST["old-global-update"]['slug'] == $strPostTypeArray['slug']){
					$strPostTypeArray['description'] = $_POST["old-global-update"]['description'];
					$strPostTypeArray['name'] = $_POST["old-global-update"]['name'];
					$objXMLHandler->objConfigData->postfields->postfield[$intIndex] = serialize($strPostTypeArray);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->update($wpdb->prefix . "ap_post_type_field", array('description' => $strPostTypeArray['description'], 'name' => $strPostTypeArray['name']), array('slug' => $strPostTypeArray['slug']));
				}
				$intIndex++;
			}
			echo json_encode(false);
			return;
		}
		
		//////////////////////////////////////////
		//AJAX logic for deleting a global field//
		//////////////////////////////////////////
		if(isset($_POST['global-field-delete'])){
			$intIndex = 0;
			//loop through configuration file post-types
			foreach ($objXMLHandler->objConfigData->postfields->postfield as $strPostTypeArray){
				$strPostTypeArray = unserialize($strPostTypeArray);
				//if current post-type matches post-type to delete then remove post-type from tables and configuration file
				if($_POST["global-field-delete"] == $strPostTypeArray['slug']){
					unset($objXMLHandler->objConfigData->postfields->postfield[$intIndex]);
					$objXMLHandler->SaveConfigurationData();
					$wpdb->delete($wpdb->prefix . "ap_post_type_field", array('slug' => $_POST["global-field-delete"]));
					return;
				}
				$intIndex++;
			}
		}
		
		///////////////////////////////////////////
		//AJAX logic for creating a new Post Type//
		///////////////////////////////////////////
		if(isset($_POST["new-type-add"])) {
			foreach($_POST["new-type-add"]['arguments']['labels'] as $key => $value)
				if($value === "")
					unset($_POST["new-type-add"]['arguments']['labels'][$key]);
			if($_POST["new-type-add"]['arguments']['menu_position'] === "" || $_POST["new-type-add"]['arguments']['menu_position'] == 0)
				$_POST["new-type-add"]['arguments']['menu_position'] = "10";
			$strErrorArray = array();
			$strNewTypeArray = $_POST['new-type-add'];
			//loop through configuration post-types
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $strPostTypeArray){
				$strPostTypeArray = unserialize($strPostTypeArray);
				// if their is a duplicate singular name add the appropriate error to $strErrorArray
				if(strtolower($strPostTypeArray[0]['arguments']['labels']["singular_name"]) == strtolower($strNewTypeArray['arguments']['labels']['singular_name']))
					$strErrorArray[] = "Singular Name";
				// if their is a duplicate plural name add the appropriate error to $strErrorArray
				if(strtolower($strPostTypeArray[0]['arguments']['labels']["name"]) == strtolower($strNewTypeArray['arguments']['labels']['name']))
					$strErrorArray[] = "Plural Name";
				// if their is a duplicate slug add the appropriate error to $strErrorArray
				if(strtolower($strPostTypeArray[0]["slug"]) == strtolower($strNewTypeArray['slug']))
					$strErrorArray[] = "Slug";
				// if their is a duplicate ap_id add the appropriate error to $strErrorArray
				if(strtolower($strPostTypeArray[0]["ap_id"]) == strtolower($strNewTypeArray['ap_id']))
					$strErrorArray[] = "AgilePress ID";
			}
			if(!empty($strErrorArray)){
				echo json_encode($strErrorArray);
				die;
			}
			else{
				// if there are no error with the post-type then add it to the configuration file
				$newPostType = $objXMLHandler->objConfigData->posttypes->addChild("posttype", serialize(array($strNewTypeArray,array())));
				$objXMLHandler->SaveConfigurationData();
				echo json_encode(false);
			}
			die;
		}
		
		//////////////////////////////////////////////
		//AJAX logic for creating a new global field//
		//////////////////////////////////////////////
		if(isset($_POST["new-global-add"])){
			$strErrorArray = array();
			$strNewTypeArray = $_POST['new-global-add'];
			//loop through configuration file global post fields, if there are any duplicate names or slugs push an error to $strErrorArray
			foreach ($objXMLHandler->objConfigData->postfields->postfield as $strPostTypeArray){
				$strPostTypeArray = unserialize($strPostTypeArray);
				if(strtolower($strPostTypeArray["name"]) == strtolower($strNewTypeArray['name']))
					$strErrorArray[] = "Name";
				if(strtolower($strPostTypeArray["slug"]) == strtolower($strNewTypeArray['slug']))
					$strErrorArray[] = "Slug";
			}
			//loop through configuration file post-type fields, if there are any duplicate names or slugs push an error to $strErrorArray
			$objCurrentFieldsArray = AP_CustomPostTypeBase::GetGlobalPostFieldCheckArray();
			foreach ($objXMLHandler->objConfigData->posttypes->posttype as $strPostTypeArray){
				$strPostTypeArray = unserialize($strPostTypeArray);
				foreach($strPostTypeArray[1] as $strPostTypeArray){
					if(strtolower($strPostTypeArray["name"]) == strtolower($strNewTypeArray['name']))
						$strErrorArray[] = "Name";
					if(strtolower($strPostTypeArray["slug"]) == strtolower($strNewTypeArray['slug']))
						$strErrorArray[] = "Slug";
				}
			}
			//if $strErrorArray is not empty post $strErrorArray back to javascript frontend
			if(!empty($strErrorArray)){
				echo json_encode(array_merge(array_unique($strErrorArray)));
				die;
			}
			//if there are no error then add the global field to the configuration file
			else{
				$newPostType = $objXMLHandler->objConfigData->postfields->addChild("postfield", serialize($strNewTypeArray));
				$objXMLHandler->SaveConfigurationData();
				echo json_encode(false);
			}
		}
		
		///////////////////////////////////////////////////
		//AJAX logic to get all current custom post types//
		///////////////////////////////////////////////////
		if(isset($_POST['current-custom-types'])){
			echo "<?xml version='1.0'?>";
			echo "<root>";
			//get all code generated post-types
			$strCustomPostTypeArray = AP_CustomPostTypeBase::GetPostTypeArray();
			//loop through $strCustomPostType array and build an XML file to return to javascript frontend
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
		
		///////////////////////////////////////////////////////////
		//AJAX logic to get all current custom global post fields//
		///////////////////////////////////////////////////////////
		if(isset($_POST['current-global-fields'])){
			echo "<?xml version='1.0'?>";
			echo "<root>";
			//get all global fields 
			$strCustomPostTypeArray = AP_CustomPostTypeBase::GetGlobalPostFieldCheckArray();
			//loop through $strCustomPostType array and build an XML file to return to javascript frontend
			foreach ($strCustomPostTypeArray as $strCustomPostType){
				echo "<item singular_name='"; echo $strCustomPostType['name']; echo "' slug='"; echo $strCustomPostType['slug']; echo "'></item>";
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
		<h1 style="margin-left:15px;">AgilePress Post Manager
			</h1>
			<div style="width:850px; margin-bottom:200px;">
			<?php $this->AdminNavBarRender('posts');?>
			<div class="ap-post-main">
				<p class="ap-settings-field" style="font-weight:bold; float:left; margin-top:15px; font-size:18px;">Post Types </p>
				<span class="type-adder" style="position:relative;  cursor:pointer !important; display:block; float:right; width:200px; height:50px; margin-top:20px; margin-right:0%; padding-right:0;">
					<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:130px; top:12px;">
						<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
					</span>	
					<span style="position:absolute; margin-top:15px;  right:0px; font-size:14px;">Add new post type</span>
				</span>
				<div style="clearfix:both; position:relative; float:left; border-style:solid; top:40px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:300px; overflow:auto; margin-bottom:60px;" >
					<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-o-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-webkit-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background: linear-gradient(rgb(45,114,206),rgb(2,74,171));">
						<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
					</div>					
					<?php $objData = AP_CustomPostTypeBase::GetPostTypeArray(); $objDataTrack = array(); $boolBackground = true; $intPixCount = 40; foreach($objData as $key => $value){ $objDataTrack[] = $value['slug'];?>
					<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
						<p class="ap-post-key-row"><?php _e($value['singular_name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="field-viewer" data-type="<?php _e($value['ap_id']);?>">View Fields</a>|<a class="edit-viewer" >Edit</a>|<a class="type-delete" >Delete</a></p>
					</div>
					<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } ?> 
					<?php $objNewData = AP_CustomPostTypeBase::GetConfigPostTypeArray(); $boolCodeGenCheck = false; foreach($objNewData as $key => $value){ if(!in_array($value['slug'],$objDataTrack)){ $boolCodeGenCheck = true;?>
					<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7); top:<?php _e($intPixCount);?>px; left:0; width:99%; height:36px; ">
						<p class="ap-post-key-row"><?php _e($value['singular_name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="field-viewer" data-type="<?php _e($value['ap_id']);?>">View Fields</a>|<a class="edit-viewer" >Edit</a>|<a class="type-delete" >Delete</a></p>
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
					<span style="position:absolute; right:0px; margin-top:15px; font-size:14px;">Add new global post field</span>
				</span>
				<div id="ap-global-holder" style="clearfix:both; position:relative; float:left; border-style:solid; top:40px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:300px;" >
					<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-o-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-webkit-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:linear-gradient(rgb(45,114,206),rgb(2,74,171));">
						<p class="ap-post-key">Name</p><p class="ap-post-key">Key</p><p class="ap-post-key">Action</p>
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
			<div id="field-view"></div>
			<div id="overlay"></div>
			<?php 
			$this->RenderPostTypesFieldDivs();
			
			
			$this->RenderPostTypeManager("editor", AP_CustomPostTypeBase::GetFullConfigPostTypeArray());
			$this->RenderPostTypeManager("adder");
			
			$strPostTypeArray = AP_CustomPostTypeBase::GetConfigPostTypeArray();
			foreach($strPostTypeArray as $intKey => $strValueArray){
				$strFieldArray = AP_CustomPostTypeBase::GetPostTypeFieldConfigArray($strValueArray['name']);
				foreach($strFieldArray as $strField){
					$strTempValueArray = array_merge(array( 'type' => $strValueArray['name']), $strField);
					if(is_array($strValueArray))
						$this->RenderPostFieldManager("editor", $strTempValueArray);
				}
			}

			$strGlobalFieldArray = AP_CustomPostTypeBase::GetGlobalPostFieldArray();

			foreach($strGlobalFieldArray as $strField){
				$this->RenderPostFieldManager("editor",  $strField, true);
			}
				
			$this->RenderPostFieldManager("adder");
			$this->RenderPostFieldManager("adder", null , true);
				
				
			?>
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

			//Global variable declarations 
			var CurGlobalFields = [];
			var CurPostTypes = [];
			var FieldNameArray = []; 
			var CurrentType;
			var CurrentField;
			
			$(document).ready(function(){

				$("input.all-lower").keyup(function(event){
					$(this).val($(this).val().toLowerCase());
					return;
				});
				
				// Get current global fields
				$.post("", { "action" : "ajax", "current-global-fields" : true }, function(d){ $(d).find('item').each(function(index){ CurGlobalFields.push({ "singular_name" : $(this).attr("singular_name"), "slug" : $(this).attr("slug")}); }); console.log(CurGlobalFields);}, "xml") ;

				// Get current post types
				$.post("", { "action" : "ajax", "current-custom-types" : true }, function(d){ $(d).find('item').each(function(index){ var curFields = []; $(this).find('field').each(function(index){ var tempField = $.parseJSON($(this).text()); curFields.push(tempField);}); CurPostTypes.push({ "plural_name" : $(this).attr("plural_name"), "singular_name" : $(this).attr("singular_name"), "slug" : $(this).attr("slug"), "fields" : curFields}); }); console.log(CurPostTypes);}, "xml") ;

				// In Post Type Adder when a user enters text into the slug field, automatically build the AP ID field
				$("#type-add-slug").keyup(function(e){$("#type-add-id").val($("#type-add-slug").val().replace("_","-")) });

				// Event handler for viewing the global field adder
				$("#ap-global-viewer").click(function(event){
					$("#ap-global-adder").css("display","block");
					$("#overlay").css("display","block");
				});

				// Event handler for hover effect on field viewer
				$(".field-viewer").each(function(index){
					$(this).hover(function(){
						CurPostTypes.forEach(function(entry){ 
							if (entry['slug'] == $(".field-viewer").eq(index).attr("data-type")){ 
							$("#field-view").html("");
							entry['fields'].forEach(function(lowerentry){
								$("#field-view").append("<p class='field-view-row'>" + lowerentry['name'] + "</p>");
							}); 
							$("#field-view").css("top",$(".field-viewer").eq(index).offset().top - 30);
							$("#field-view").css("left",$(".field-viewer").eq(index).offset().left);
							$("#field-view").css("display","block");
						}})
					},function(){
						$("#field-view").css("display","none");
					});
				});

				// Logic for centering all popup divs
				var intPopupOffset = $("#wpcontent").width() - 900;
				intPopupOffset = intPopupOffset / 2;
				intPopupOffset = intPopupOffset - 20;
				if(intPopupOffset < 1 )
					intPopupOffset = 0;
				$(".field-holder").css("margin-left",intPopupOffset);
				intPopupOffset = intPopupOffset;
				$(".edit-holder").css("margin-left",intPopupOffset);
				$(".edit-holder-bar").css("left",intPopupOffset + 200);

				// Event handler for deleting post types
				$(".type-delete").click(function(event){
					if(confirm("Are you sure you want to delete this post type?")){
						$.post("",{"action":"ajax","post-type-delete" : $(event.target).parent().siblings("p#row-slug").text()},function(data){ document.location.reload();});
						}
					else{}
				});

				// Event handler for deleting global fields
				$(".global-field-delete").click(function(event){
					if(confirm("Are you sure you want to delete this global field?")){
						$.post("",{"action":"ajax","global-field-delete" : $(event.target).parent().siblings("p#row-slug").text()},function(data){ document.location.reload();});
						}
					else{}
				});

				// Event handler for closing a post type field holder
				$(".field-closer").click(function(){
					document.location.reload();
					$(".field-closer").parents(".field-holder").css("display","none"); 
					$("#overlay").css("display","none");
				});

				// Event handler for viewing a post type field holder
				$(".field-viewer").click(function(){
					$("#ap-" + $(this).parent().siblings("#row-slug").text() + "-fields").css("display","block");
					$("#overlay").css("display","block");
					CurrentType = $(this).parent().siblings("#row-slug").text();
				});

				// Event handler for closing post type editors
				$(".edit-closer").click(function(){
					$(".edit-closer").parents(".edit-holder").css("display","none"); 
					$("#overlay").css("display","none");
				});

				// Event handler for closing temporary field editors
				$(".temp-edit-closer").click(function(){
					$("#temp-field-edit").css("display","none"); 
					$("#ap-" + CurrentType + "-fields").css("display","block");
				});

				// Event handler for opening post type editors
				$(".edit-viewer").click(function(){
					$("#ap-" + $(this).parent().siblings("#row-slug").text() + "-editor").css("display","block");
					$("#overlay").css("display","block");
					CurrentType = $(this).parent().siblings("#row-slug").text();
				});

				// Event handler for closing field editors
				$(".field-edit-closer").click(function(){
					$("#ap-" + CurrentType + "-" + CurrentField + "-editor").css("display","none");
					$("#ap-field-adder").css("display","none");
					$("#ap-" + CurrentType + "-fields").css("display","block");
					$("#overlay").css("display","block");
				});

				// Event handler for opening temporary field editors
				$(".field-edit-viewer").click(function(){
					var objElement = this;
					CurrentField = JSON.parse($(objElement).attr("data-type"));
					console.log("#ap-" + CurrentType + "-" + CurrentField + "-editor");
					$("#ap-" + CurrentType + "-" + CurrentField + "-editor").css("display","block");
					$("#ap-" + CurrentType + "-fields").css("display","none");
				});

				// Event handler for opening post type field creator
				$(".field-adder").click(function(){
					$(".field-title-filler").html(CapitaliseFirstLetter(CurrentType + " Type "));
					$("#ap-field-adder").css("display","block");
					$("#ap-" + CurrentType + "-fields").css("display","none");
				});

				// Event handler for opening post type creator
				$(".type-adder").click(function(){
					$("#ap-type-adder").css("display","block");
					$("#overlay").css("display","block");
				});
				
				});

				// Miscellaneous function used to capitalize first letter of a string
				function CapitaliseFirstLetter(string)
				{
				    return string.charAt(0).toUpperCase() + string.slice(1);
				}

			// AJAX function for deleting a post type
			function TypeFieldDelete(obj){
				var fieldSlug = $(obj).attr("data-slug");
				$.post("",
						{
					"action" : "ajax",
					"type-field-delete" : fieldSlug,
					"type" : CurrentType

						}
				, function(data){
					data = JSON.parse(data);
					if(data)
						document.location.reload();
					else
						alert("Uh-oh AgilePress encountered an error while trying to delete the post type field");
				});
				
				

			}

			// AJAX function for saving a existing field
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
					alert("Global post field names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("Global post field slugs must be 3 characters or longer and less than 20");
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
						"old-field-update" : TypeDataArray
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

			//AJAX function for saving an existing post type field
			function UpdateField(){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#" + CurrentField + "-edit-name").val();
				if(typeof name == undefined || name == null){ alert("ERROR: Unable to save type, problem setting global field name"); return;}
				var slug = $("#" + CurrentField + "-edit-slug").val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field slug"); return;}
				var ctrl = $("#" + CurrentField + "-edit-ctrl").find('option:selected').val();
				if(typeof slug == undefined || slug == null){ alert("ERROR: Unable to save type, problem setting global field control type"); return;}
				var description = $("#" + CurrentField + "-edit-description").val();

				
				if(name.length < 3 || name.length > 20){
					alert("Global post field names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("Global post field slugs must be 3 characters or longer and less than 20");
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
				console.log(TypeDataArray);
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"type" : CurrentType,
						"old-field-update" : TypeDataArray
					},
				function(d){ 
						console.log(d);
						console.log(CurrentField);
						if(d)
							var $objError = $.parseJSON(d); 
						if($objError){
							alert("The slug or name entered already exists"); 
						}
						else{
							var intHeight = $("#" + CurrentField).css("top");
							$("#" + CurrentField['slug']).remove();
							id = "#ap-" + CurrentType + "-fields #field-row-holder";
							var dataArray = JSON.stringify(TypeDataArray);
							var EvenOddHeight = intHeight / 40
							var EvenOdd = EvenOddHeight%2;
							if(!Math.abs(EvenOdd))
								var RowColor = "light";
							else
								var RowColor = "dark";
							$(id).append("<div class='" + RowColor + "' id='" + TypeDataArray['slug'] + "' style='position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7);  top:" + intHeight + "; left:0; width:99%; height:36px;'><p class='ap-post-key-row'>" + name + "</p><p class='ap-post-key-row'>" + slug + "</p><p class='ap-post-key-row'><a class='field-edit-viewer' data-type='" + JSON.stringify(slug) + "'>Edit</a>|<a>Delete</a></p></div>");

							// Event handler for opening temporary field editors
							$(".field-edit-viewer").click(function(){
								var objElement = this;
								CurrentField = JSON.parse($(objElement).attr("data-type"));
								$("#ap-" + CurrentType + "-" + CurrentField + "-editor").css("display","block");
								$("#ap-" + CurrentType + "-fields").css("display","none");
							});
							
							$("#ap-" + CurrentType + "-" + CurrentField + "-editor").css("display","none"); 
							$("#ap-" + CurrentType + "-fields").css("display","block");
						}
				});
				 
				 return false;
			}

			// AJAX function for adding a field to a post type
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
					alert("Post field names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("Post field slugs must be 3 characters or longer and less than 20");
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
						"new-field-add" : TypeDataArray
					},
				function(d){ 
						var $objError = $.parseJSON(d); 
						if($objError){
							alert("The slug or name entered already exists"); 
						}
						else{
							id = "#ap-" + CurrentType + "-fields #field-row-holder";
							var intHeight =  $(id).children("div").length;
							var dataArray = JSON.stringify(TypeDataArray);
							var EvenOdd = intHeight%2;
							if(!Math.abs(EvenOdd))
								var RowColor = "light";
							else
								var RowColor = "dark";
							intHeight = intHeight * 40;
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

			// AJAX function for adding a global field
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
				console.log(name);
				
				if(name.length < 3 || name.length > 20){
					alert("Global post field names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("Global post field slugs must be 3 characters or longer and less than 20");
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
						"new-global-add" : TypeDataArray
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

			// AJAX function for adding a new post type
			function AddType(){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#post-add-name").val();
				
				if(typeof name == undefined || name == null){alert("ERROR: Unable to save type, problem setting taxonomy name"); return;}
				
				var pluralName = $("#post-add-plurname").val();
				
				if(typeof pluralName == undefined || pluralName == null){ alert("ERROR: Unable to save type, problem setting taxonomy plural name"); return;}
				
				var slug = $("#post-add-slug").val();
				
				if(typeof slug == undefined || slug == null){alert("ERROR: Unable to save type, problem setting taxonomy slug"); return;}

				var AP_ID = $("#post-add-ap-id").val();
				var menuName = $("#post-add-menu-name").val();
				var nameAdminBar = $("#post-add-admin-bar").val();
				var addNew = $("#post-add-add-new").val();
				var searchItems = $("#post-add-search-item").val();
				var notFound = $("#post-add-not-found").val();
				var notFoundInTrash = $("#post-add-not-found-trash").val();
				var allItems = $("#post-add-all-items").val();
				var editItem = $("#post-add-edit-item").val();
				var viewItem = $("#post-add-view-item").val();
				var updateItem = $("#post-add-update-item").val();
				var newItem = $("#post-add-new-item").val();
				var newItemName = $("#post-add-new-item-name").val();
				var parentItem = $("#post-add-parent-item").val();
				var taxonomies = [];

				$(".post-taxonomies").each(function(){
					if(this.checked)
						taxonomies.push($(this).val());
				});
				
				var boolExcludeFromSearch = $("#post-add-exclude-search:checked").val();
				var boolQueryVar = $("#post-add-query-var:checked").val();
				var boolHasArchive = $("#post-add-has-archive:checked").val();
				var boolPublicQuery = $("#post-add-public-query:checked").val();
				var boolHeirarchical = $("#post-add-hier:checked").val();
				var boolPublic = $("#post-add-public:checked").val();
				var boolShowUI = $("#post-add-show-ui:checked").val();
				var boolInNav = $("#post-add-nav-show:checked").val();
				var intMenuPosition = $("#post-add-menu-position").val();
				var strSupportsArray = [];
				
				$(".post-supports").each(function(){
					if(this.checked)
						strSupportsArray.push($(this).val());
				});
				var description = $("#post-add-description").val();
				
				
				if(name.length < 3 || name.length > 20){
					alert("Taxonomy singular names must be 3 characters or longer and less than 20");
					return;
				}
				if(pluralName.length < 3 || pluralName.length > 20){
					alert("Taxonomy plural names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("Taxonomy slugs must be 3 characters or longer and less than 20");
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

				var postDataArray = 
					
					{
						"slug" : slug,
						"ap_id" : AP_ID,
						"taxonomies" : taxonomies,
						"arguments" : {
							"hierarchical" : boolHeirarchical,
							"public" : boolPublic,
							"show_ui" : boolShowUI,
							"has_archive" : boolHasArchive,
							"exclude_from_search" : boolExcludeFromSearch,
							"publicly_queryable" : boolPublicQuery,
							"query_var" : boolQueryVar,
							"show_in_menu" : boolInNav,
							"menu_position" : intMenuPosition,
							"supports" : strSupportsArray,
							"labels" : {
								"name" : pluralName,
								"singular_name" : name,
								"menu_name" : menuName,
								"name_admin_bar" : nameAdminBar,
								"all_items" : allItems,
								"edit_item" : editItem,
								"view_item" : viewItem,
								"update_item" : updateItem,
								"add_new" : addNew,
								"add_new_item" : newItem,
								"new_item" : newItemName,
								"search_items" : searchItems,
								"not_found" : notFound,
								"not_found_in_trash" : notFoundInTrash,
								"parent_item_colon" : parentItem,
							}
						},
						"description" : description					
					}
				console.log(postDataArray);
				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"new-type-add" : postDataArray
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

			//AJAX function for saving an existing global field
			function UpdateGlobal(obj){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				console.log("#" + CurrentType + "-edit-name");
				var name = $(obj).siblings("#" + CurrentType + "-edit-name").val();
				if(typeof name == undefined || name == null){ alert("ERROR: Unable to save type, problem setting global field name"); return;}
				var description = $(obj).siblings("#" + CurrentType + "-edit-description").val();
				var slug = $(obj).siblings("#" + CurrentType + "-edit-slug").val();
				
				if(name.length < 3 || name.length > 20){
					alert("Global post field names must be 3 characters or longer and less than 20");
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
						"old-global-update" : TypeDataArray
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

			// AJAX function for saving an existing post type
			function UpdateType(obj){

				console.log('hello');
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#post-" + CurrentType + "-name").val();
				
				if(typeof name == undefined || name == null){alert("ERROR: Unable to save type, problem setting taxonomy name"); return;}
				
				var pluralName = $("#post-" + CurrentType + "-plurname").val();
				
				if(typeof pluralName == undefined || pluralName == null){ alert("ERROR: Unable to save type, problem setting taxonomy plural name"); return;}
				
				var slug = $("#post-" + CurrentType + "-slug").val();
				
				if(typeof slug == undefined || slug == null){alert("ERROR: Unable to save type, problem setting taxonomy slug"); return;}

				var AP_ID = $("#post-" + CurrentType + "-ap-id").val();
				var menuName = $("#post-" + CurrentType + "-menu-name").val();
				var nameAdminBar = $("#post-" + CurrentType + "-admin-bar").val();
				var addNew = $("#post-" + CurrentType + "-add-new").val();
				var searchItems = $("#post-" + CurrentType + "-search-item").val();
				var notFound = $("#post-" + CurrentType + "-not-found").val();
				var notFoundInTrash = $("#post-" + CurrentType + "-not-found-trash").val();
				var allItems = $("#post-" + CurrentType + "-all-items").val();
				var editItem = $("#post-" + CurrentType + "-edit-item").val();
				var viewItem = $("#post-" + CurrentType + "-view-item").val();
				var updateItem = $("#post-" + CurrentType + "-update-item").val();
				var newItem = $("#post-" + CurrentType + "-new-item").val();
				var newItemName = $("#post-" + CurrentType + "-new-item-name").val();
				var parentItem = $("#post-" + CurrentType + "-parent-item").val();
				var taxonomies = [];

				$(".post-" + CurrentType + "-taxonomies").each(function(){
					if(this.checked)
						taxonomies.push($(this).val());
				});
				
				var boolExcludeFromSearch = $("#post-" + CurrentType + "-exclude-search:checked").val();
				var boolQueryVar = $("#post-" + CurrentType + "-query-var:checked").val();
				var boolHasArchive = $("#post-" + CurrentType + "-has-archive:checked").val();
				var boolPublicQuery = $("#post-" + CurrentType + "-public-query:checked").val();
				var boolHeirarchical = $("#post-" + CurrentType + "-hier:checked").val();
				var boolPublic = $("#post-" + CurrentType + "-public:checked").val();
				var boolShowUI = $("#post-" + CurrentType + "-show-ui:checked").val();
				var boolInNav = $("#post-" + CurrentType + "-nav-show:checked").val();
				var intMenuPosition = $("#post-" + CurrentType + "-menu-position").val();
				var strSupportsArray = [];

				$(".post-" + CurrentType + "-supports").each(function(){
					if(this.checked)
						strSupportsArray.push($(this).val());
				});
				var description = $("#post-" + CurrentType + "-description").val();
				
				
				if(name.length < 3 || name.length > 20){
					alert("Taxonomy singular names must be 3 characters or longer and less than 20");
					return;
				}
				if(pluralName.length < 3 || pluralName.length > 20){
					alert("Taxonomy plural names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 20){
					alert("Taxonomy slugs must be 3 characters or longer and less than 20");
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

				var postDataArray = 
					
					{
						"slug" : slug,
						"ap_id" : AP_ID,
						"taxonomies" : taxonomies,
						"arguments" : {
							"hierarchical" : boolHeirarchical,
							"public" : boolPublic,
							"show_ui" : boolShowUI,
							"has_archive" : boolHasArchive,
							"exclude_from_search" : boolExcludeFromSearch,
							"publicly_queryable" : boolPublicQuery,
							"query_var" : boolQueryVar,
							"show_in_menu" : boolInNav,
							"menu_position" : intMenuPosition,
							"supports" : strSupportsArray,
							"labels" : {
								"name" : pluralName,
								"singular_name" : name,
								"menu_name" : menuName,
								"name_admin_bar" : nameAdminBar,
								"all_items" : allItems,
								"edit_item" : editItem,
								"view_item" : viewItem,
								"update_item" : updateItem,
								"add_new" : addNew,
								"add_new_item" : newItem,
								"new_item" : newItemName,
								"search_items" : searchItems,
								"not_found" : notFound,
								"not_found_in_trash" : notFoundInTrash,
								"parent_item_colon" : parentItem,
							}
						},
						"description" : description					
					}
				console.log(postDataArray.arguments.supports);
				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"old-type-update" : postDataArray
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
			</script>
			<?php 
		}
		
		
		
	/**
	 * Method for rendering the Post Type field holder divs
	 * 
	 * @since 1.0
	 * @return void
	 */	
	protected function RenderPostTypesFieldDivs(){
		$objData = AP_CustomPostTypeBase::GetConfigPostTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomPostTypeBase::GetPostTypeFieldArray($value['name']); $objNewFields = AP_CustomPostTypeBase::GetPostTypeFieldConfigArray($value['name']);?>
		<div id="ap-<?php _e($value['slug']);?>-fields" class="field-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:850px; background:white; width:800px; ">
				<div style="height:40px;  width:840px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px;" >
				<p class="ap-post-key" style="margin-top:13px; position:absolute; left:-20px;">Custom <?php _e($value['name']);?> Fields</p>
				<a class="field-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
				</div>
				<span  class="field-adder" style="position:absolute; display:block; float:left; width:200px; height:50px; margin-top:20px; left:20px;">
					<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:175px; top:12px;">
						<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
					</span>	
					<p style="position:absolute; left:45px; font-size:14px;">Add new <?php _e(strtolower($value['singular_name']));?> field</p>
				</span>
				<div class="field-holder-bar" style="position:absolute; margin:70px 0; width:802px; height:40px; margin-left:0px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-webkit-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-o-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:linear-gradient(rgb(45,114,206),rgb(2,74,171));">
						<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
				</div>
				<div id="field-row-holder" style="clearfix:both; position:absolute; border-style:solid; top:150px; margin-left:0px; border-width:2px; border-top-width:0; border-color:rgb(179,179,179); background:white; z-index: 2; width:798px; height:670px; overflow:auto; margin-bottom:100px;" >
					<?php $boolPreviousHasBorder = false; $boolBackground = true; $intPixCount = 0; foreach($objNewFields as $key => $value[0]){$boolIsIn = false; foreach($objFields as $objField) if($objField['slug'] == $value[0]['slug'] && $objField['name'] == $value[0]['name']) $boolIsIn = true; if($boolIsIn){?>
					<div class="<?= ($boolBackground) ? "light" : "dark"?>" id="<?php _e($value[0]['slug']);?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
						<p class="ap-post-key-row"><?php _e($value[0]['name']);?></p><p class="ap-post-key-row"><?php _e($value[0]['slug']);?></p><p class="ap-post-key-row"><a class="field-edit-viewer" data-type='<?php _e(json_encode($value[0]['slug']));?>'>Edit</a>|<a data-slug='<?php _e($value[0]['slug']);?>' onclick="TypeFieldDelete(this); return false;">Delete</a></p>
					</div>
					<?php } else { ?>
					<div class="<?= ($boolBackground) ? "light" : "dark"?>"  id="<?php _e($value[0]['slug']);?>" style="position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7); top:<?php _e($intPixCount);?>px; left:0; width:99%; height:32px; <?= ($boolPreviousHasBorder) ? "border-top-width:0;" : "" ?>" >
						<p class="ap-post-key-row"><?php _e($value[0]['name']);?></p><p class="ap-post-key-row"><?php _e($value[0]['slug']);?></p><p class="ap-post-key-row"><a class="field-edit-viewer" data-type='<?php _e(json_encode($value[0]['slug']));?>'>Edit</a>|<a data-slug='<?php _e($value[0]['slug']);?>' onclick="TypeFieldDelete(this); return false;">Delete</a></p>
					</div>
					<?php $boolPreviousHasBorder = true; } $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } if(empty($objData)){?>
					<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
						<p class="ap-post-key-row">None</p>
					</div>
					<?php }?>
				</div>
		</div>
		<?php }
	}
		
		
	protected function RenderPostFieldManager($strType, $mixFieldArray = null, $boolIsGlobal = false){
		if($strType == "editor" && !is_array($mixFieldArray))
			return $this->error("ERROR:Invalid data-type passed to RenderPostFieldManager()");
 
		?>
			<div id="ap-<?php if($boolIsGlobal) echo ($strType == "adder") ? "global" : $mixFieldArray['slug']; else echo ($strType == "adder") ? "field" : strtolower($mixFieldArray['type']) . "-" . strtolower($mixFieldArray['slug']); ?>-<?= ($strType == "adder") ? "adder" : "editor" ?>" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:620px; background:white; width:405px; ">
				<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
				<p class="ap-post-key" id="field-header-add" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left !important; width:80%;" id="field-header"><?= ($boolIsGlobal) ? "Global " : "<span class='field-title-filler'></span>" ?>Field <?= ($strType == "adder") ? "Creator" : "Editor" ?></p>
				<a class="<?= ($boolIsGlobal) ? "" : "field-" ?>edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
				</div>
				<p class="ap-settings-field" > Field Name </p>
				<input autocomplete='off' type="text" class="ap-settings-field-input" id="<?php if(!$boolIsGlobal){ echo ($strType == "adder") ? "field" : $mixFieldArray['slug'] ; } else { echo ($strType == "adder") ? "global" : $mixFieldArray['slug'] ; } ?>-<?= ($strType == "adder") ? "add" : "edit" ?>-name" <?= ($strType == "editor") ? 'value=' . $mixFieldArray['name'] : "" ?> /><br>
				<p class="ap-settings-field"> Field Slug </p>
				<input autocomplete='off' <?= ($strType == "editor") ? 'DISABLED' : "" ; ?> type="text" class="ap-settings-field-input all-lower" id="<?php if(!$boolIsGlobal){ echo ($strType == "adder") ? "field" : $mixFieldArray['slug'] ; } else { echo ($strType == "adder") ? "global" : $mixFieldArray['slug'] ; } ?>-<?= ($strType == "adder") ? "add" : "edit" ?>-slug"  value="<?= ($strType == "editor") ? $mixFieldArray['slug'] : "" ?>" /><br>
				<p class="ap-settings-field"> Control Type </p>
				<select id="<?php if(!$boolIsGlobal){ echo ($strType == "adder") ? "field" : $mixFieldArray['slug'] ; } else { echo ($strType == "adder") ? "global" : $mixFieldArray['slug'] ; } ?>-<?= ($strType == "adder") ? "add" : "edit" ?>-ctrl" style="width:400px; border-radius:5px; text-align:center;" <?= ($strType == "editor") ? "DISABLED" : "" ?> >
					<option value="textbox" <?= ($mixFieldArray['slug'] == 'textbox') ? "SELECTED" : "" ?> > Text Box </option>
					<option value="textarea" <?= ($mixFieldArray['slug'] == 'textarea') ? "SELECTED" : "" ?> > Text Area </option>
					<option value="listbox" <?= ($mixFieldArray['slug'] == 'listbox') ? "SELECTED" : "" ?> > List Box </option>
					<option value="radio" <?= ($mixFieldArray['slug'] == 'radio') ? "SELECTED" : "" ?> > Radio Button </option>
					<option value="checkbox" <?= ($mixFieldArray['slug'] == 'checkbox') ? "SELECTED" : "" ?> > Check Box </option><option value="checkboxlist"> Check Box List</option>
				</select>
				<p class="ap-settings-field"> Field Description </p>
				<textarea  autocomplete="off"  style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="<?php if(!$boolIsGlobal){ echo ($strType == "adder") ? "field" : $mixFieldArray['slug'] ; } else { echo ($strType == "adder") ? "global" : $mixFieldArray['slug'] ; } ?>-<?= ($strType == "adder") ? "add" : "edit" ?>-description"><?= $mixFieldArray['description'] ?></textarea>
				<a class="ap-admin-button" style="float:left" onclick="<?= ($strType == "adder") ? "Add" : "Update" ?><?php if($boolIsGlobal) echo "Global"; else echo "Field"; ?>(this); return false; " href = "">
				<span class="ap-admin-button-2">
					<span style=""><?= ($strType == "adder") ? "Add" : "Update" ?> Field </span>
				</span>
				</a>
			</div>
		<?php 
	}
		
		
	protected function RenderPostTypeManager($strType, $mixTypeArray = null){
		$strIDArray = array();
		if(isset($mixTypeArray) && !is_array($mixTypeArray))
			return $this->error("invalid data type passed to RenderPostTypeManager");
		if($strType == "adder"){
			$intLoopLimit = 1;
			$boolIsEditor = false;
			$strIDArray[] = "type";
		} elseif ($strType == "editor"){
			$boolIsEditor = true;
			foreach ($mixTypeArray as $intKey => $mixType){
				if(isset($mixType['arguments']['labels']['singular_name']))
					$strIDArray[] = $mixType['arguments']['labels']['singular_name'];
				else
					return $this->error("invalid data-type given to RenderPostTypeManager");
			}
			if(isset($mixTypeArray) && is_array($mixTypeArray))
				$intLoopLimit = count($mixTypeArray);
			else
				return $this->error("invalid type given to RenderPostTypeManager");
		} else
			return $this->error("invalid type given to RenderPostTypeManager");
		
		$intLooper = 0;
		while($intLooper < $intLoopLimit){
				$strDataArray = $mixTypeArray[$intLooper];
		?>
			<script type="text/javascript">
			$(document).ready(function(){			
				$("#post-add-slug").keyup(function(){
					$("#post-add-slug").val($("#post-add-slug").val().toLowerCase());
					$("#post-add-ap-id").val($("#post-add-slug").val());
				});
			});
			</script>
			<div id="ap-<?= ($strDataArray['slug']) ? $strDataArray['slug'] : $strIDArray[0] ?>-<?= $strType ?>" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:1065px; background:white; width:870px;">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;"><?= ($strType == "adder") ? "Post Type Creator" : $strDataArray['singular_name'] . " Type Editor" ?></p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<div style="float:left; margin-left:15px;">
					<p class="ap-settings-field" > Singular Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-name" value="<?= $strDataArray['arguments']['labels']['singular_name'] ?>" /><br>
					<p class="ap-settings-field"  > Plural Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-plurname" value="<?= $strDataArray['arguments']['labels']['name'] ?>" /><br>
					<p class="ap-settings-field" > Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-slug" value="<?= $strDataArray['slug'] ?>"><br>
					<p class="ap-settings-field" > AP Identifier </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-ap-id" value="<?= $strDataArray['ap_id'] ?>"  DISABLED><br>
					<h2 style="margin-bottom:0; float:left;"> Labels </h2><p class="clearfix" style=" margin-left:65px; position:relative; top:3px; color:#CCC;"> (optional)</p>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Menu Name </span>
					<input autocomplete='off' type="text" class="ap-settings-field-input clearfix" style="width:250px;" value="<?= $strDataArray['arguments']['labels']['menu_name'] ?>" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-menu-name" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> All Items</span>
					<input autocomplete='off' type="text" placeholder="All Tags" class="ap-settings-field-input clearfix" value="<?= $strDataArray['arguments']['labels']['all_items'] ?>" style="width:250px;" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-all-items" value="" /></p><br>
					<p class="ap-settings-field"  style="float:left;" ><span style="width:150px; display:inline-block;"> Edit Item</span>
					<input autocomplete='off' type="text" placeholder="Edit Tag" class="ap-settings-field-input clearfix" value="<?= $strDataArray['arguments']['labels']['edit_item'] ?>" style="width:250px;" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-edit-item" value="" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Add New</span>
					<input autocomplete='off' type="text" placeholder="Edit Tag" class="ap-settings-field-input clearfix" value="<?= $strDataArray['arguments']['labels']['add_new'] ?>" style="width:250px;" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-add-new" value="" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;">  View Item</span>
					<input autocomplete='off' type="text" placeholder="View Tag" class="ap-settings-field-input clearfix" value="<?= $strDataArray['arguments']['labels']['view_item'] ?>" style="width:250px;" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-view-item" ></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;">  Admin Bar Name</span>
					<input autocomplete='off' type="text" placeholder="View Tag" class="ap-settings-field-input clearfix" value="<?= $strDataArray['arguments']['labels']['name_admin_bar'] ?>" style="width:250px;" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-admin-bar" ></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Update Item</span>
					<input autocomplete='off' type="text" placeholder="Update Tag" class="ap-settings-field-input clearfix"  value="<?= $strDataArray['arguments']['labels']['update_item'] ?>" style="width:250px;" value="" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-update-item" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Add New Item</span>
					<input autocomplete='off' type="text" placeholder="Add New Tag" class="ap-settings-field-input clearfix" value="<?= $strDataArray['arguments']['labels']['add_new_item'] ?>" style="width:250px;" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-new-item" value="" /></p><br>
					<p class="ap-settings-field" style="float:left;"  ><span style="width:150px; display:inline-block;">New Item Name</span>
					<input autocomplete='off' type="text" placeholder="New Tag Name" class="ap-settings-field-input clearfix" value="<?= $strDataArray['arguments']['labels']['new_item'] ?>" style="width:250px;" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-new-item-name" value="" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Parent Item</span>
					<input autocomplete='off' type="text" placeholder="Parent Category" class="ap-settings-field-input clearfix" value="<?= $strDataArray['arguments']['labels']['parent_item_colon'] ?>" style="width:250px;" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-parent-item" ></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Menu Position</span>
					<input autocomplete='off' type="text" placeholder="INTEGERS ONLY" class="ap-settings-field-input clearfix" value="<?= $strDataArray['arguments']['menu_position'] ?>" style="width:250px;" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-menu-position" ></p><br>
					</div>
					<div class="clearfix" style="margin-left:450px;">
					<p class="ap-settings-field" > Supports </p>
					<div style="background:#F4F4F4; width:400px; height:100px; overflow:auto;">
					<?php
					foreach(AP_CodeGenConstants::$PostSupports as $strSupportType){
						?>
							<label style="padding:0; margin:5px;"><input type="checkbox" value="<?= $strSupportType ?>" <?= ($strType == "adder") ? 'checked="checked"' : (is_array($strDataArray['arguments']['supports']) && in_array($strSupportType, $strDataArray['arguments']['supports'])) ? 'checked="checked"' : "" ?> class="post<?= (strlen($strDataArray['slug']) > 1) ? "-" . $strDataArray['slug'] : "" ?>-supports"> <?= ucfirst($strSupportType) ?></label><br>
							<?php 
						}
						?>
					</div>
					<p class="ap-settings-field" > Exclude From Search </p>
					<label> True <input <?= ($strDataArray['arguments']['exclude_from_search']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-exclude-search" name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>exclude-search" value="1" /></label><label> False <input  <?= (!$strDataArray['arguments']['exclude_from_search']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-exclude-search" name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>exclude-search" value="0" /></label><br>
					<p class="ap-settings-field"  > Query Variable </p>
					<label> True <input  <?= ($strDataArray['arguments']['query_var']) ? 'checked="CHECKED"' : "" ?> autocomplete='off'  type="radio" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-query-var" name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>query-var" value="1" /></label><label> False <input <?= (!$strDataArray['arguments']['query_var']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-query-var" name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>query-var" value="0" /></label><br>
					<p class="ap-settings-field" > Has Archive </p>
					<label> True <input <?= ($strDataArray['arguments']['has_archive']) ? 'checked="CHECKED"' : "" ?> autocomplete='off'  type="radio" class="ap-settings-field-input " name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>has-archive" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-has-archive" value="1" /></label><label> False <input <?= (!$strDataArray['arguments']['has_archive']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>has-archive" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-has-archive" value="0"  ></label><br>
					<p class="ap-settings-field" > Hierarchical </p>
					<label> True <input <?= ($strDataArray['arguments']['hierarchical']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-hier" name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>heir" value="1" /></label><label> False <input <?= (!$strDataArray['arguments']['hierarchical']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-hier" name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>heir" value="0" /></label><br>
					<p class="ap-settings-field"  > Public </p>
					<label> True <input <?= ($strDataArray['arguments']['public']) ? 'checked="CHECKED"' : "" ?> autocomplete='off'  type="radio" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-public" name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>public" value="1" /></label><label> False <input <?= (!$strDataArray['arguments']['public']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-public" name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>public" value="0" /></label><br>
					<p class="ap-settings-field" > Show UI </p>
					<label> True <input <?= ($strDataArray['arguments']['show_ui']) ? 'checked="CHECKED"' : "" ?> autocomplete='off'  type="radio" class="ap-settings-field-input " name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>show-ui" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-show-ui" value="1" /></label><label> False <input <?= (!$strDataArray['arguments']['show_ui']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>show-ui" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-show-ui" value="0"  ></label><br>
					<p class="ap-settings-field" > Show in Navigation </p>
					<label> True <input <?= ($strDataArray['arguments']['show_in_menu']) ? 'checked="CHECKED"' : "" ?> autocomplete='off'  type="radio" class="ap-settings-field-input " name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>nav-show" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-nav-show" value="1" /></label><label> False <input <?= (!$strDataArray['arguments']['show_in_menu']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>nav-show" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-nav-show" value="0"  ></label><br>
					<p class="ap-settings-field" > Publicly Queryable </p>
					<label> True <input <?= ($strDataArray['arguments']['publicly_queryable']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>public-query" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-public-query" value="1" /></label><label> False <input <?= (!$strDataArray['arguments']['publicly_queryable']) ? 'checked="CHECKED"' : "" ?> autocomplete='off' type="radio" class="ap-settings-field-input " name="<?= ($strType == "adder") ? "" : $strDataArray['slug'] . "-"; ?>public-query" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-public-query" value="0"  ></label><br>
					<p class="ap-settings-field"> Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="post-<?= ($strType == "adder") ? "add" : $strDataArray['ap_id'] ?>-description" ><?= $strDataArray['description'] ?></textarea>
					<a class="ap-admin-button" style="float:left" onclick="<?= ($strType == "adder") ? "AddType();" : "UpdateType();" ?> return false;">
					<span class="ap-admin-button-2">
						<span style=""> <?= ($strType == "adder") ? "Add" : "Update" ?> Post Type </span>
					</span>
					</a>
					</div>
			</div>

		<?php 
		$intLooper++;
		}


	}
		
}


