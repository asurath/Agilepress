<?php
/**
 * Class for handling all Taxonomy UI output as well as AJAX and menu registeration
 *
 * @since 1.0
 * @package AgilePress
 * @subpackage Taxonomy Administrator Page
 * @author AgilePress Core Developement Team
 *
 */
class AP_TaxonomyAdminPage extends AP_AdminPageBase {

	/**
	 * Called on the wordpress hook admin_init, this method registers the sub_menu AgilePress item "Taxonomy" and redirects admin AJAX calls back to the page
	 *
	 * @since 1.0
	 * @return void
	 */
	public static function Init(){
		add_submenu_page('ap-settings-home', 'Agile Press Custom Taxonomy Manager', 'Taxonomies', 'administrator', 'ap-taxonomies', array('AP_TaxonomyAdminPage', 'PageCreate'));
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
		if($_GET['page'] <> 'ap-taxonomies') return;
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
		if(isset($_POST['NewTaxData'])){
			global $wpdb;
			$objTaxonomyArray = AP_CustomTaxonomyTypeBase::GetTaxonomyTypeArray();
			foreach($objXMLHandler->objConfigData->taxonomies->taxonomy as $objTaxonomy){
				$objTaxonomy = unserialize($objTaxonomy);
				if(strtolower($objTaxonomy['slug']) == strtolower($_POST['NewTaxData']['slug'])){
					echo json_encode(array("Slug"));
					die;
				}
			}
			if(is_array($_POST['NewTaxData']['types']))
			$_POST['NewTaxData']['types'] = array_filter($_POST['NewTaxData']['types']);
			$arguments = $_POST['NewTaxData']['arguments'];
			$arguments['labels'] = array_filter($_POST['NewTaxData']['arguments']['labels']);
			$objXMLHandler->objConfigData->taxonomies->AddChild("taxonomy",serialize(array('slug' => $_POST['NewTaxData']['slug'], 'ap_id' => $_POST['NewTaxData']['slug'], 'post_types' => serialize($_POST['NewTaxData']['types']), "arguments" => serialize($arguments),"description" => $_POST['NewTaxData']['description'])));
			$objXMLHandler->SaveConfigurationData();
			echo json_encode(false);
			die;
		}
		if (isset($_POST['OldTaxData'])) {
			global $wpdb;
			$arguments = $_POST['OldTaxData']['arguments'];
			$arguments['labels'] = array_filter($_POST['OldTaxData']['arguments']['labels']);
			$i = 0;
			foreach($objXMLHandler->objConfigData->taxonomies->taxonomy as $objTaxonomy){
				$objTaxonomy = unserialize($objTaxonomy);
				if($objTaxonomy['ap_id'] == $_POST['OldTaxData']['ap_id']){
					$objXMLHandler->objConfigData->taxonomies->taxonomy[$i] = serialize(array('slug' => $_POST['OldTaxData']['slug'],  'ap_id' => $objTaxonomy['ap_id'], 'post_types' => serialize($_POST['OldTaxData']['types']), "arguments" => serialize($arguments),"description" => $_POST['OldTaxData']['description']));
					$objXMLHandler->SaveConfigurationData();
					echo json_encode(false);
					die;
				}
				$i++;
			}
		}
		if(isset($_POST['TaxDelete'])){
				$wpdb->delete($wpdb->prefix . "ap_taxonomies", array("ap_id" => $_POST['TaxDelete']));
				$i = 0;
				foreach($objXMLHandler->objConfigData->taxonomies->taxonomy as $objTaxonomy){
					$objTaxonomy = unserialize($objTaxonomy);
					if($objTaxonomy["ap_id"] == $_POST['TaxDelete'])
						unset($objXMLHandler->objConfigData->taxonomies->taxonomy[$i]);
					$i++;
				}
				$objXMLHandler->SaveConfigurationData();
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
		<h1 style="margin-left:15px;">AgilePress Taxonomy Manager
			</h1>
			<div style="width:850px;">
			<?php $this->AdminNavBarRender("tax");?>
			<div style="padding: 1px 20px; margin: 5px 15px 2px; margin-top:0; padding-top:0; height:850px; background:white; width:100%; ">
				<p class="ap-settings-field" style="font-weight:bold; float:left; margin-top:15px; font-size:18px;">Taxonomies </p>
					<span class="type-adder" style="position:relative;  cursor:pointer !important; display:block; float:right; width:200px; height:50px; margin-top:20px; margin-right:0%; padding-right:0;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:135px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; right:0px; font-size:14px;">Add new taxonomy</p>
					</span>
					<div style="clearfix:both; position:relative; float:left; border-style:solid; top:40px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:715px; overflow:auto; margin-bottom:60px;" >
						<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-o-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background:-webkit-linear-gradient(rgb(45,114,206),rgb(2,74,171)); background: linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-post-key">Name</p><p class="ap-post-key">Slug</p><p class="ap-post-key">Action</p>
						</div>
						<?php $objData = AP_CustomTaxonomyTypeBase::GetTaxonomyTypeArray(); $objDataTrack = array(); $boolBackground = true; $intPixCount = 40; foreach($objData as $value){ $objDataTrack[] = $value['ap_id'];?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row"><?php $objArguments = unserialize($value['arguments']); _e($objArguments['labels']['singular_name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="edit-viewer row-data" data-tax='<?php _e(json_encode(array('ID' => $value['id'], 'AP_ID' => $value['ap_id'], 'slug' => $value['slug'], 'arguments' => $objArguments,'post_types' =>  unserialize($value['post_types']))));?>' >Edit</a>|<a class="tax-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } ?> 
						<?php $objData = AP_CustomTaxonomyTypeBase::GetConfigTaxonomyTypeArray(); foreach($objData as $value){ if(!in_array($value['ap_id'], $objDataTrack)){ $objDataTrack[] = $value['ap_id']; ?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; border-style: solid; border-width:4px; border-color:rgb(241, 146, 7); top:<?php _e($intPixCount);?>px; left:0; width:99%; height:36px; " >
							<p class="ap-post-key-row"><?php $objArguments = unserialize($value['arguments']); _e($objArguments['labels']['singular_name']);?></p><p class="ap-post-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-post-key-row"><a class="edit-viewer row-data" data-tax='<?php _e(json_encode(array('ID' => $value['id'], 'AP_ID' => $value['ap_id'], 'slug' => $value['slug'], 'arguments' => $objArguments,'post_types' =>  unserialize($value['post_types']))));?>' >Edit</a>|<a class="tax-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; }} ?> 
						<?php if(empty($objDataTrack)){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-post-key-row">None</p>
						</div>
						<?php }?>
					</div>
					<?php if($boolCodeGenCheck){?>
						<p class="ap-settings-field-note" style="position:relative; top:425px; color:rgb(241, 146, 7); left:-80px;padding:0; margin:0;">*Taxonomy Types bordered in orange have not been had their code generated yet</p>
					<?php }?>
					</div>
					
				</div>
					<div id="field-view">
					</div>
					<div id="overlay">
					</div>
				<div id="ap-tax-adder" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:900px; background:white; width:870px;">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;">Taxonomy Creator</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<div style="float:left; margin-left:15px;">
					<p class="ap-settings-field" > Singular Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input " id="tax-add-name" value="" /><br>
					<p class="ap-settings-field"  > Plural Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input " id="tax-add-plurname" value="" /><br>
					<p class="ap-settings-field" > Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input " id="tax-add-slug" ><br>
					<h2 style="margin-bottom:0; float:left;"> Labels </h2><p class="clearfix" style=" margin-left:65px; position:relative; top:3px; color:#CCC;"> (optional)</p>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Menu Name </span>
					<input autocomplete='off' type="text" class="ap-settings-field-input clearfix" style="width:250px;" value="" id="tax-add-menu-name" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> All Items</span>  
					<input autocomplete='off' type="text" placeholder="All Tags" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-add-all-items" value="" /></p><br>
					<p class="ap-settings-field"  style="float:left;" ><span style="width:150px; display:inline-block;"> Edit Item</span>  
					<input autocomplete='off' type="text" placeholder="Edit Tag" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-add-edit-item" value="" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> View Item</span>  
					<input autocomplete='off' type="text" placeholder="View Tag" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-add-view-item" ></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Update Item</span>  
					<input autocomplete='off' type="text" placeholder="Update Tag" class="ap-settings-field-input clearfix" style="width:250px;" value="" id="tax-add-update-item" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Add New Item</span>  
					<input autocomplete='off' type="text" placeholder="Add New Tag" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-add-new-item" value="" /></p><br>
					<p class="ap-settings-field" style="float:left;"  ><span style="width:150px; display:inline-block;">New Item Name</span>
					<input autocomplete='off' type="text" placeholder="New Tag Name" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-add-new-item-name" value="" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Parent Item</span>  
					<input autocomplete='off' type="text" placeholder="Parent Category" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-add-parent-item" >
					</div>
					<div class="clearfix" style="margin-left:450px;">
					<p class="ap-settings-field" > Post-Types </p>
					<div style="background:#F4F4F4; width:400px; height:100px; overflow:auto;">
						<?php 
						$objPostTypeArray = get_post_types(array('public'=>true));
						foreach($objPostTypeArray as $strPostType){
							$objPostType = get_post_type_object($strPostType);
							?>
							<label style="padding:0; margin:5px;"><input type="checkbox" value="<?= $objPostType->name ?>" class="tax-post-type"> <?= $objPostType->label ?></label><br>
							<?php 
						}
						?>
					</div>
					<p class="ap-settings-field" > Hierarchical </p>
					<label> True <input autocomplete='off' type="radio" class="ap-settings-field-input " id="tax-add-hier" name="heir" value="1" /></label><label> False <input CHECKED autocomplete='off' type="radio" class="ap-settings-field-input " id="tax-add-hier" name="heir" value="0" /></label><br>
					<p class="ap-settings-field"  > Public </p>
					<label> True <input autocomplete='off' CHECKED type="radio" class="ap-settings-field-input " id="tax-add-public" name="public" value="1" /></label><label> False <input autocomplete='off' type="radio" class="ap-settings-field-input " id="tax-add-public" name="public" value="0" /></label><br>
					<p class="ap-settings-field" > Show UI </p>
					<label> True <input autocomplete='off' CHECKED type="radio" class="ap-settings-field-input " name="show-ui" id="tax-add-show-ui" value="1" /></label><label> False <input autocomplete='off' type="radio" class="ap-settings-field-input " name="show-ui" id="tax-add-show-ui" value="0"  ></label><br>
					<p class="ap-settings-field" > Show in Navigation </p>
					<label> True <input autocomplete='off' CHECKED type="radio" class="ap-settings-field-input " name="nav-show" id="tax-add-nav-show" value="1" /></label><label> False <input autocomplete='off' type="radio" class="ap-settings-field-input " name="nav-show" id="tax-add-nav-show" value="0"  ></label><br>
					<p class="ap-settings-field" > Show in TagCloud </p>
					<label> True <input autocomplete='off' CHECKED type="radio" class="ap-settings-field-input " name="tag-cloud" id="tax-add-tag-cloud" value="1" /></label><label> False <input autocomplete='off' type="radio" class="ap-settings-field-input " name="tag-cloud" id="tax-add-tag-cloud" value="0" ></label><br>
					<p class="ap-settings-field" > Show in Administrator Column </p>
					<label> True <input autocomplete='off' type="radio" class="ap-settings-field-input " name="admin-column" id="tax-add-admin-column" value="1" /></label><label> False <input CHECKED autocomplete='off' type="radio" class="ap-settings-field-input " name="admin-column" id="tax-add-admin-column" value="0"  ></label><br>
					<p class="ap-settings-field"> Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="tax-add-description" ></textarea>
					<a class="ap-admin-button" style="float:left" onclick="AddType(); return false;">
					<span class="ap-admin-button-2">
						<span style=""> Add Taxonomy </span>
					</span>
					</a>
					</div>
			</div>
			<div id="ap-tax-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:900px; background:white; width:870px;">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;">Taxonomy Creator</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
					</div>
					<div style="float:left; margin-left:15px;">
					<p class="ap-settings-field" > Singular Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input " id="tax-edit-name" value="" /><br>
					<p class="ap-settings-field"  > Plural Name </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input " id="tax-edit-plurname" value="" /><br>
					<p class="ap-settings-field" > Slug </p>
					<input autocomplete='off' type="text" class="ap-settings-field-input " id="tax-edit-slug" ><br>
					<h2 style="margin-bottom:0; float:left;"> Labels </h2><p class="clearfix" style=" margin-left:65px; position:relative; top:3px; color:#CCC;"> (optional)</p>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Menu Name </span>
					<input autocomplete='off' type="text" class="ap-settings-field-input clearfix" style="width:250px;" value="" id="tax-edit-menu-name" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> All Items</span>  
					<input autocomplete='off' type="text" placeholder="All Tags" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-edit-all-items" value="" /></p><br>
					<p class="ap-settings-field"  style="float:left;" ><span style="width:150px; display:inline-block;"> Edit Item</span>  
					<input autocomplete='off' type="text" placeholder="Edit Tag" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-edit-edit-item" value="" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> View Item</span>  
					<input autocomplete='off' type="text" placeholder="View Tag" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-edit-view-item" ></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Update Item</span>  
					<input autocomplete='off' type="text" placeholder="Update Tag" class="ap-settings-field-input clearfix" style="width:250px;" value="" id="tax-edit-update-item" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Add New Item</span>  
					<input autocomplete='off' type="text" placeholder="Add New Tag" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-edit-new-item" value="" /></p><br>
					<p class="ap-settings-field" style="float:left;"  ><span style="width:150px; display:inline-block;">New Item Name</span>
					<input autocomplete='off' type="text" placeholder="New Tag Name" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-edit-new-item-name" value="" /></p><br>
					<p class="ap-settings-field" style="float:left;" ><span style="width:150px; display:inline-block;"> Parent Item</span>  
					<input autocomplete='off' type="text" placeholder="Parent Category" class="ap-settings-field-input clearfix" style="width:250px;" id="tax-edit-parent-item" >
					</div>
					<div class="clearfix" style="margin-left:450px;">
					<p class="ap-settings-field" > Post-Types </p>
					<div style="background:#F4F4F4; width:400px; height:100px; overflow:auto;">
						<?php 
						$objPostTypeArray = get_post_types(array('public'=>true));
						foreach($objPostTypeArray as $strPostType){
							$objPostType = get_post_type_object($strPostType);
							?>
							<label style="padding:0; margin:5px;"><input type="checkbox" value="<?= $objPostType->name ?>" class="tax-edit-post-type"> <?= $objPostType->label ?></label><br>
							<?php 
						}
						?>
					</div>
					<p class="ap-settings-field" > Hierarchical </p>
					<label> True <input autocomplete='off' type="radio" class="ap-settings-field-input " id="tax-edit-hier" name="heir" value="1" /></label><label> False <input  autocomplete='off' type="radio" class="ap-settings-field-input " id="tax-edit-hier" name="heir" value="0" /></label><br>
					<p class="ap-settings-field"  > Public </p>
					<label> True <input autocomplete='off'  type="radio" class="ap-settings-field-input " id="tax-edit-public" name="public" value="1" /></label><label> False <input autocomplete='off' type="radio" class="ap-settings-field-input " id="tax-edit-public" name="public" value="0" /></label><br>
					<p class="ap-settings-field" > Show UI </p>
					<label> True <input autocomplete='off'  type="radio" class="ap-settings-field-input " name="show-ui" id="tax-edit-show-ui" value="1" /></label><label> False <input autocomplete='off' type="radio" class="ap-settings-field-input " name="show-ui" id="tax-edit-show-ui" value="0"  ></label><br>
					<p class="ap-settings-field" > Show in Navigation </p>
					<label> True <input autocomplete='off'  type="radio" class="ap-settings-field-input " name="nav-show" id="tax-edit-nav-show" value="1" /></label><label> False <input autocomplete='off' type="radio" class="ap-settings-field-input " name="nav-show" id="tax-edit-nav-show" value="0"  ></label><br>
					<p class="ap-settings-field" > Show in TagCloud </p>
					<label> True <input autocomplete='off'  type="radio" class="ap-settings-field-input " name="tag-cloud" id="tax-edit-tag-cloud" value="1" /></label><label> False <input autocomplete='off' type="radio" class="ap-settings-field-input " name="tag-cloud" id="tax-edit-tag-cloud" value="0" ></label><br>
					<p class="ap-settings-field" > Show in Administrator Column </p>
					<label> True <input autocomplete='off' type="radio" class="ap-settings-field-input " name="admin-column" id="tax-edit-admin-column" value="1" /></label><label> False <input  autocomplete='off' type="radio" class="ap-settings-field-input " name="admin-column" id="tax-edit-admin-column" value="0"  ></label><br>
					<p class="ap-settings-field"> Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="tax-edit-description" ></textarea>
					<a class="ap-admin-button" style="float:left" onclick="UpdateType(); return false;">
					<span class="ap-admin-button-2">
						<span style=""> Update Taxonomy </span>
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
			var CurrentType;

	
			$(document).ready(function(){
				$("#tax-add-slug").keyup(function(e){$("#tax-add-menu-name").val($("#tax-add-slug").val().replace("_","-")) });
				
				var intPopupOffset = $("#wpcontent").width() - 800;
				intPopupOffset = intPopupOffset / 2;
				intPopupOffset = intPopupOffset - 20;

				
				
				$(".field-holder").css("margin-left",intPopupOffset);
				$(".field-holder-bar").css("left",intPopupOffset+100);

				intPopupOffset = intPopupOffset - 50;
				$(".edit-holder").css("margin-left",intPopupOffset);
				$(".edit-holder-bar").css("left",intPopupOffset+100);

				
				$(".edit-closer").click(function(){
					$(".edit-closer").parents(".edit-holder").css("display","none"); 
					$("#overlay").css("display","none");
				});

				$(".edit-viewer").click(function(){
					$("#ap-tax-editor").css("display","block");
					$("#overlay").css("display","block");
					CurrentType = JSON.parse($(this).attr("data-tax"));
					
					$("#tax-edit-name").val("");
					$("#tax-edit-plurname").val("");
					$("#tax-edit-slug").val("");
					$("#tax-edit-menu-name").val("");
					$("#tax-edit-all-items").val("");
					$("#tax-edit-edit-item").val("");
					$("#tax-edit-view-item").val("");
					$("#tax-edit-update-item").val("");
					$("#tax-edit-new-item").val("");
					$("#tax-edit-new-item-name").val("");
					$("#tax-edit-parent-item").val("");

					$("#tax-edit-name").val(CurrentType.arguments.labels.singular_name);
					$("#tax-edit-plurname").val(CurrentType.arguments.labels.name);
					$("#tax-edit-slug").val(CurrentType.slug);
					$("#tax-edit-menu-name").val(CurrentType.arguments.labels.menu_name);
					$("#tax-edit-all-items").val(CurrentType.arguments.labels.all_items);
					$("#tax-edit-edit-item").val(CurrentType.arguments.labels.edit_item);
					$("#tax-edit-view-item").val(CurrentType.arguments.labels.view_item);
					$("#tax-edit-update-item").val(CurrentType.arguments.labels.update_item);
					$("#tax-edit-new-item").val(CurrentType.arguments.labels.add_new_item);
					$("#tax-edit-new-item-name").val(CurrentType.arguments.labels.new_item_name);
					$("#tax-edit-parent-item").val(CurrentType.arguments.labels.parent_item);


					$(".tax-edit-post-type").each(function(){ $(this).prop("checked",false); });
					if( Object.prototype.toString.call(CurrentType.post_types) === '[object Array]'){
					$(".tax-edit-post-type").each(function(){
						 	var objSlug = $(this);
							$.each(CurrentType.post_types, function(){
								if(this == objSlug.val())
									objSlug.prop("checked",true);
							});
					});
					}
					
					console.log(CurrentType.arguments);
					if(CurrentType.arguments.hierarchical == 1)
						$("#tax-edit-hier[value='1']").prop("checked",true);
					else
						$("#tax-edit-hier[value='0']").prop("checked",true);

					if(CurrentType.arguments['public'] == 1)
						$("#tax-edit-public[value='1']").prop("checked",true);
					else
						$("#tax-edit-public[value='0']").prop("checked",true);

					if(CurrentType.arguments.show_ui == 1)
						$("#tax-edit-show-ui[value='1']").prop("checked",true);
					else
						$("#tax-edit-show-ui[value='0']").prop("checked",true);

					if(CurrentType.arguments.show_in_nav_menus == 1)
						$("#tax-edit-nav-show[value='1']").prop("checked",true);
					else
						$("#tax-edit-nav-show[value='0']").prop("checked",true);

					if(CurrentType.arguments.show_tagcloud == 1)
						$("#tax-edit-tag-cloud[value='1']").prop("checked",true);
					else
						$("#tax-edit-tag-cloud[value='0']").prop("checked",true);

					if(CurrentType.arguments.show_admin_column == 1)
						$("#tax-edit-admin-column[value='1']").prop("checked",true);
					else
						$("#tax-edit-admin-column[value='0']").prop("checked",true);

					$("#tax-add-description").val(CurrentType.description);
					
				});

				
				$(".type-adder").click(function(){
					$("#ap-tax-adder").css("display","block");
					$("#overlay").css("display","block");
				});
				
				});

			$(".tax-delete").click(function(event){
				if(confirm("Are you sure you want to delete this taxonomy?")){
					CurrentType = JSON.parse($(this).siblings("a.row-data").attr("data-tax"));
					$.post("",{"action":"ajax","TaxDelete" : CurrentType.AP_ID},function(data){ document.location.reload();});
					}
				else{}
			});

			function CapitaliseFirstLetter(string)
			{
			    return string.charAt(0).toUpperCase() + string.slice(1);
			}

			
			function AddType(){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#tax-add-name").val();
				
				if(typeof name == undefined || name == null){alert("ERROR: Unable to save type, problem setting taxonomy name"); return;}
				
				var pluralName = $("#tax-add-plurname").val();
				
				if(typeof pluralName == undefined || pluralName == null){ alert("ERROR: Unable to save type, problem setting taxonomy plural name"); return;}
				
				var slug = $("#tax-add-slug").val();
				
				if(typeof slug == undefined || slug == null){alert("ERROR: Unable to save type, problem setting taxonomy slug"); return;}
				
				var menuName = $("#tax-add-menu-name").val();
				var allItems = $("#tax-add-all-items").val();
				var editItem = $("#tax-add-edit-item").val();
				var viewItem = $("#tax-add-view-item").val();
				var updateItem = $("#tax-add-update-item").val();
				var newItem = $("#tax-add-new-item").val();
				var newItemName = $("#tax-add-new-item-name").val();
				var parentItem = $("#tax-add-parent-item").val();
				var postTypes = [];

				$(".tax-post-type").each(function(){
					if(this.checked)
						postTypes.push($(this).val());
				});

				console.log(postTypes);
				var boolHeirarchical = $("#tax-add-hier:checked").val();
				var boolPublic = $("#tax-add-public:checked").val();
				var boolShowUI = $("#tax-add-show-ui:checked").val();
				var boolInNav = $("#tax-add-nav-show:checked").val();
				var boolTagCloud = $("#tax-add-tag-cloud:checked").val();
				var boolAdminColumn = $("#tax-add-admin-column:checked").val();
				var description = $("#tax-add-description").val();
				
				
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

				var TaxDataArray = 
					
					{
						"slug" : slug,
						"types" : postTypes,
						"arguments" : {
							"hierarchical" : boolHeirarchical,
							"public" : boolPublic,
							"show_ui" : boolShowUI,
							"show_in_nav_menus" : boolInNav,
							"show_tagcloud" : boolTagCloud,
							"show_admin_column" : boolAdminColumn,
							"labels" : {
								"name" : pluralName,
								"singular_name" : name,
								"menu_name" : menuName,
								"all_items" : allItems,
								"edit_item" : editItem,
								"view_item" : viewItem,
								"update_item" : updateItem,
								"add_new_item" : newItem,
								"new_item_name" : newItemName,
								"parent_item" : parentItem,
							}
						},
						"description" : description					
					}
				console.log(TaxDataArray);
				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"NewTaxData" : TaxDataArray
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

			
			function UpdateType(){
				var nameRegEx = /[^0-9a-zA-Z- ]/;
				var slugRegEx = /[^0-9a-zA-Z-_]/;
				var name = $("#tax-edit-name").val();
				
				if(typeof name == undefined || name == null){alert("ERROR: Unable to save type, problem setting user type name"); return;}
				
				var pluralName = $("#tax-edit-plurname").val();
				
				if(typeof pluralName == undefined || pluralName == null){ alert("ERROR: Unable to save type, problem setting user type plural name"); return;}
				
				var slug = $("#tax-edit-slug").val();
				
				if(typeof slug == undefined || slug == null){alert("ERROR: Unable to save type, problem setting user type slug"); return;}
				
				var menuName = $("#tax-edit-menu-name").val();
				var allItems = $("#tax-edit-all-items").val();
				var editItem = $("#tax-edit-edit-item").val();
				var viewItem = $("#tax-edit-view-item").val();
				var updateItem = $("#tax-edit-update-item").val();
				var newItem = $("#tax-edit-new-item").val();
				var newItemName = $("#tax-edit-new-item-name").val();
				var parentItem = $("#tax-edit-parent-item").val();
				var postTypes = [];

				$(".tax-edit-post-type").each(function(){
					if(this.checked)
						postTypes.push($(this).val());
				});

				var boolHeirarchical = $("#tax-edit-hier:checked").val();
				var boolPublic = $("#tax-edit-public:checked").val();
				var boolShowUI = $("#tax-edit-show-ui:checked").val();
				var boolInNav = $("#tax-edit-nav-show:checked").val();
				var boolTagCloud = $("#tax-edit-tag-cloud:checked").val();
				var boolAdminColumn = $("#tax-edit-admin-column:checked").val();
				var description = $("#tax-edit-description").val();
				
				
				if(name.length < 3 || name.length > 20){
					alert("Taxonomy singular names must be 3 characters or longer and less than 20");
					return;
				}
				if(pluralName.length < 3 || pluralName.length > 20){
					alert("Taxonomy plural names must be 3 characters or longer and less than 20");
					return;
				}
				if(slug.length < 3 || slug.length > 24){
					alert("Taxonomy slugs must be 3 characters or longer and less than 24");
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

				var TaxDataArray = 
					
					{
						"ID" : CurrentType.ID,
						"ap_id" : CurrentType.AP_ID,
						"slug" : slug,
						"types" : postTypes,
						"arguments" : {
							"hierarchical" : boolHeirarchical,
							"public" : boolPublic,
							"show_ui" : boolShowUI,
							"show_in_nav_menus" : boolInNav,
							"show_tagcloud" : boolTagCloud,
							"show_admin_column" : boolAdminColumn,
							"labels" : {
								"name" : pluralName,
								"singular_name" : name,
								"menu_name" : menuName,
								"all_items" : allItems,
								"edit_item" : editItem,
								"view_item" : viewItem,
								"update_item" : updateItem,
								"add_new_item" : newItem,
								"new_item_name" : newItemName,
								"parent_item" : parentItem,
							}
						},
						"description" : description					
					}
				console.log(TaxDataArray);
				var strErrors = "";
				$.post(window.location, 
					{ 
						"action" : "ajax" ,
						"OldTaxData" : TaxDataArray
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
	
	
}