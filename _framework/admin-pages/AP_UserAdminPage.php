<?php

class AP_UserAdminPage extends AP_AdminPageBase {
	

	public static function Init(){
		add_submenu_page('ap-settings-home', 'Agile Press Custom User Types Manager', 'User Type Manager', 'administrator', 'ap-user-types', array('AP_UserAdminPage', 'PageCreate'));
		if(!empty($_POST) && $_POST['action'] == "ajax")
			self::PageCreate();
			
	}
	
	public static function PageCreate(){
		if($_GET['page'] <> 'ap-user-types') return;
		$strClass = get_class();
		$objPage =  new $strClass;
		$objPage->Run();
	}
	
	
	
	protected function DoAJAX(){
		unset($_POST['action']);
		if(isset($_POST["NewTypeData"])) {
			$objNewUserType = New AP_CustomUserTypeBase;
			$objNewUserType->SetFromAdmin($_POST);
			$objNewUserType->Insert();
			die;
		}
		if(isset($_POST['global-field-name'])){
			$GlobalFieldArray = AP_CustomUserTypeBase::GetGlobalUserFieldArray();
			if(!in_array($_POST['global-field-name'], $GlobalFieldArray) || !in_array($_POST['global-field-slug'], $GlobalFieldArray))
				AP_CustomUserTypeBase::SetGlobalFromAdmin($_POST);
			die;
		}
		if(isset($_POST['CurrentCustomTypes'])){
			echo "<?xml version='1.0'?>";
			echo "<root>";
			$strCustomUserTypeArray = AP_CustomUserTypeBase::GetUserTypeArray();
			foreach ($strCustomUserTypeArray as $strCustomUserType){
				echo "<item plural_name='"; echo $strCustomUserType['name']; echo "' singular_name='"; echo $strCustomUserType['singular_name']; echo "'></item>";}
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
	
	
	protected function Content(){
		?>
			<style type="text/css">
			.ap-admin-nav a { display:block; height:100%; width:100px; text-align:center; float:left; color:white; font-size:14px; font-weight:bold; line-height:35px; text-decoration:none;}
			#ap-nav-home {width:50px !important; margin-left:10px; margin-right:10px;}
			.ap-settings-field {margin:0; padding:0; padding-top:20px; padding-bottom:7px; font-size:16px; line-height:16px;}
			.ap-settings-field-input {border-radius:5px; width:400px; font-size:16px; line-height:16px;}
			.ap-admin-button { text-decoration:none !important; }
			.ap-admin-button .ap-admin-button-2 { text-decoration:none; display:block; width:150px; height:40px; border-radius:5px; border-style:solid; border-width:1px; border-color: rgb(9, 128, 171); margin-bottom: 20px; background: -moz-linear-gradient(rgb(54,183,231),rgb(7,148,198)); text-align:center; }
			.ap-admin-button .ap-admin-button-2 span {text-decoration:none; position:relative; top:10px; color:white; font-weight:bold;}
			.ap-settings-field-note { font-size:11px; color:rgb(113,113,113); margin:0; padding:3px; }
			.ap-user-key {display:block; font-size:14px; float:left; height:100%; color:white; font-weight:bold; text-align:center; line-height:100%; width:250px;}
			.ap-user-key-row {display:block; font-size:14px; float:left; height:100%; color:black;  text-align:center; line-height:100%; width:250px;}
			.light {background:rgb(243,243,243);}
			.dark {background:rgb(226,226,226);}
			#field-view { width:150px; position:absolute; display:none; background:white; min-height:10px; border-style:solid; border-width:1px; border-color:rgb(179,179,179); padding-top:7px; padding-bottom:7px; -webkit-box-shadow: 5px 5px 5px 0px rgba(50, 50, 50, 0.55); -moz-box-shadow: 5px 5px 5px 0px rgba(50, 50, 50, 0.55); box-shadow: 5px 5px 5px 0px rgba(50, 50, 50, 0.55);} 
			.field-view-row {width:100%; font-size:11px; line-height:15px; padding:0; margin:0; padding-left:7px;}
			#overlay{position:fixed;  width:100%;  height:100%;  top:0;  left:0;  opacity:0.6; background:black; z-index:1; display:none;/* see below for cross-browser opacity */}
			a {cursor:pointer;}
		</style>
		<h1 style="margin-left:15px;">AgilePress User Type Manager
			</h1>
			<?php $objCodeCheck = new AP_CoreCodeGenerator; if(intval(get_option("AP_CodeGen_Version"))<>intval($objCodeCheck->objConfigData->title->version)) $this->AdminCodeGenError();?>
			<div style="width:800px;">
			<div class="ap-admin-nav" style=" padding: 1px 20px; margin: 5px 15px 2px; background: -moz-linear-gradient(rgb(115,115,115),rgb(76,76,76)); height:35px; margin-top:40px; width:100%;">
				<a id="ap-nav-home" href="/wp-admin/admin.php?page=ap-settings-home"><i class="fa fa-home fa-2x" style="margin-top:5px;"></i></a>
				<a href="/wp-admin/admin.php?page=ap-site-settings"  >Settings</a>
				<a href="/wp-admin/admin.php?page=ap-post-types" >Posts</a>
				<a href="/wp-admin/admin.php?page=ap-user-types" style="background:rgb(52,52,52);">Users</a>
				<a href="/wp-admin/admin.php?page=ap-code-gen-settings">CodeGen</a>
			</div>
			<div style="padding: 1px 20px; margin: 5px 15px 2px; margin-top:0; padding-top:0; height:850px; background:white; width:100%; ">
				<p class="ap-settings-field" style="font-weight:bold; float:left; margin-top:15px; font-size:18px;">User Types </p>
					<span style="position:relative; display:block; float:right; width:200px; height:50px; margin-top:20px; margin-right:0%; padding-right:0;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:130px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; right:0px; font-size:14px;">Add new user type</p>
					</span>
					<div style="clearfix:both; position:relative; border-style:solid; top:100px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:300px; margin-bottom:100px;" >
						<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-user-key">Name</p><p class="ap-user-key">Slug</p><p class="ap-user-key">Action</p>
						</div>
						<?php $objData = AP_CustomUserTypeBase::GetUserTypeArray(); $boolBackground = true; $intPixCount = 40; foreach($objData as $key => $value){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-user-key-row"><?php _e($value['singular_name']);?></p><p class="ap-user-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-user-key-row"><a class="field-viewer" data-type="<?php _e($value['slug']);?>">View Fields</a>|<a class="edit-viewer" >Edit</a>|<a class="type-delete" >Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } if(empty($objData)){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-user-key-row">None</p>
						</div>
						<?php }?>
					</div>
				<p class="ap-settings-field" style="font-weight:bold; float:left; margin-top:15px; font-size:18px;">Global User Fields </p>
					<span style="position:relative; display:block; float:right; width:200px; height:50px; margin-top:20px; margin-right:0%; padding-right:0;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:175px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; right:0px; font-size:14px;">Add new global user field</p>
					</span>
					<div style="clearfix:both; position:relative; border-style:solid; top:100px; border-width:1px; border-color:rgb(179,179,179); width:100%; height:300px;" >
						<div style="position:absolute; top:0; left:0; width:100%; height:40px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-user-key">Name</p><p class="ap-user-key">Slug</p><p class="ap-user-key">Action</p>
						</div>
						<?php $objData = AP_CustomUserTypeBase::GetGlobalUserFieldArray(); $boolBackground = true; $intPixCount = 40; foreach($objData as $key => $value){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-user-key-row"><?php _e($value['singular_name']);?></p><p class="ap-user-key-row" id="row-slug"><?php _e($value['slug']);?></p><p class="ap-user-key-row"><a class="edit-viewer">Edit</a>|<a>Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } if(empty($objData)){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-user-key-row">None</p>
						</div>
						<?php }?>
					</div>
				</div>
					<div id="field-view">
					</div>
					<div id="overlay">
					</div>
			<?php $objData = AP_CustomUserTypeBase::GetUserTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-fields" class="field-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:850px; background:white; width:800px; ">
					<div style="height:40px;  width:840px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:-30px;">Custom <?php _e($value['name']);?> Fields</p>
					<a class="field-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
					</div>
					<span style="position:absolute; display:block; float:left; width:200px; height:50px; margin-top:20px; left:20px;">
						<span style="position:absolute; height:25.5px; width:25.5px; background:rgb(252,113,0); border-radius:17.5px; right:175px; top:12px;">
							<i class="fa fa-plus fa-lg" style="margin-top:7px; margin-left:6.5px; color:white;"></i>
						</span>	
						<p style="position:absolute; left:45px; font-size:14px;">Add new <?php _e(strtolower($value['singular_name']));?> field</p>
					</span>
					<div class="field-holder-bar" style="position:fixed; top:0; left:0; width:802px; height:40px; top:200px; margin-left:0px; background:-moz-linear-gradient(rgb(45,114,206),rgb(2,74,171));">
							<p class="ap-user-key">Name</p><p class="ap-user-key">Slug</p><p class="ap-user-key">Action</p>
					</div>
					<div style="clearfix:both; position:fixed; border-style:solid; top:240px; margin-left:0px; border-width:2px; border-top-width:0; border-color:rgb(179,179,179); background:white; z-index: 2; width:798px; height:670px; overflow:auto; margin-bottom:100px;" >
						<?php $boolBackground = true; $intPixCount = 0; foreach($objFields as $key => $value){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-user-key-row"><?php _e($value['name']);?></p><p class="ap-user-key-row"><?php _e($value['slug']);?></p><p class="ap-user-key-row"><a class="field-viewer" data-type="<?php _e($value['slug']);?>"><a class="field-edit-viewer" >Edit</a>|<a>Delete</a></p>
						</div>
						<?php $boolBackground = !$boolBackground; $intPixCount = $intPixCount + 40; } if(empty($objData)){?>
						<div class="<?= ($boolBackground) ? "light" : "dark"?>" style="position:absolute; top:<?php _e($intPixCount);?>px; left:0; width:100%; height:40px; ">
							<p class="ap-user-key-row">None</p>
						</div>
						<?php }?>
					</div>
			</div>
			<?php }?>
			<?php $objData = AP_CustomUserTypeBase::GetUserTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:690px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:-30px;"><?php _e($value['singular_name']);?> Type Editor</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field"> Singular Name </p>
					<input type="text" class="ap-settings-field-input" value="<?php _e($value['singular_name']);?>" DISABLED><br>
					<p class="ap-settings-field" > Plural Name </p>
					<input type="text" class="ap-settings-field-input" value="<?php _e($value['name']);?>" DISABLED /><br>
					<p class="ap-settings-field"> Slug </p>
					<input type="text" class="ap-settings-field-input" ><br>
					<p class="ap-settings-field"> AgilePress Identifier </p>
					<input type="text" class="ap-settings-field-input" value="<?php _e($value['name']);?>" DISABLED ><br>
					<p class="ap-settings-field"> Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;">
					</textarea>
					<a class="ap-admin-button" style="float:left" href = "">
					<span class="ap-admin-button-2">
						<span style=""> Continue (Add Fields) </span>
					</span>
					</a>
					<a class="ap-admin-button" style="float:right;" href = "">
					<span class="ap-admin-button-2">
						<span style=""> Save </span>
					</span>
					</a>
			</div>
			<?php }?>
			<?php $objData = AP_CustomUserTypeBase::GetUserTypeArray(); foreach ($objData as $key => $value){ $objFields = AP_CustomUserTypeBase::GetUserTypeFieldArray($value['name']);?>
			<div id="ap-<?php _e($value['slug']);?>-field-editor" class="edit-holder" style="display:none; padding: 1px 20px; position:fixed; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:690px; background:white; width:405px; ">
					<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-user-key" style="margin-top:13px; position:absolute; left:-30px;"><?php _e($value['singular_name']);?> Type Editor</p>
					<a class="field-edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-chevron-circle-up fa-2x" style="color:white;"></i></a>
					</div>
					<p class="ap-settings-field"> Singular Name </p>
					<input type="text" class="ap-settings-field-input" value="<?php _e($value['singular_name']);?>" DISABLED><br>
					<p class="ap-settings-field" > Plural Name </p>
					<input type="text" class="ap-settings-field-input" value="<?php _e($value['name']);?>" DISABLED /><br>
					<p class="ap-settings-field"> Slug </p>
					<input type="text" class="ap-settings-field-input" ><br>
					<p class="ap-settings-field"> AgilePress Identifier </p>
					<input type="text" class="ap-settings-field-input" value="<?php _e($value['name']);?>" DISABLED ><br>
					<p class="ap-settings-field"> Description </p>
					<textarea style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;">
					</textarea>
					<a class="ap-admin-button" style="float:left" href = "">
					<span class="ap-admin-button-2">
						<span style=""> Save Settings </span>
					</span>
					</a>
					<a class="ap-admin-button" style="float:right;" href = "">
					<span class="ap-admin-button-2">
						<span style=""> Save Settings </span>
					</span>
					</a>
			</div>
			<?php }?>
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
	
			$(document).ready(function(){
				$.post("", { "action" : "ajax", "CurrentGlobalFields" : true }, function(d){ $(d).find('item').each(function(index){ CurGlobalFields.push({ "singular_name" : $(this).attr("singular_name"), "slug" : $(this).attr("slug")}); }); console.log(CurGlobalFields);}, "xml") ;
				$.post("", { "action" : "ajax", "CurrentCustomTypes" : true }, function(d){ $(d).find('item').each(function(index){ var curFields = []; $(this).find('field').each(function(index){ var tempField = $.parseJSON($(this).text()); curFields.push(tempField);}); CurPostTypes.push({ "plural_name" : $(this).attr("plural_name"), "singular_name" : $(this).attr("singular_name"), "slug" : $(this).attr("slug"), "fields" : curFields}); }); console.log(CurPostTypes);}, "xml") ;

				$(".field-viewer").each(function(index){$(this).hover(function(){CurPostTypes.forEach(function(entry){ if (entry['slug'] == $(".field-viewer").eq(index).attr("data-type")){ $("#field-view").html(""); entry['fields'].forEach(function(lowerentry){$("#field-view").append("<p class='field-view-row'>" + lowerentry['name'] + "</p>");}); $("#field-view").css("top",$(".field-viewer").eq(index).offset().top - 30); $("#field-view").css("left",$(".field-viewer").eq(index).offset().left - 130); $("#field-view").css("display","block");}})}, function(){$("#field-view").css("display","none");})});
				var intPopupOffset = $("#wpcontent").width() - 800;
				intPopupOffset = intPopupOffset / 2;
				intPopupOffset = intPopupOffset - 20;
				
				$(".field-holder").css("margin-left",intPopupOffset);
				$(".field-holder-bar").css("left",intPopupOffset+200);

				intPopupOffset = intPopupOffset + 100;
				$(".edit-holder").css("margin-left",intPopupOffset);
				$(".edit-holder-bar").css("left",intPopupOffset+200);

				$(".type-delete").click(function(){
					if(confirm("Are you sure you want to delete this post type?")){}
					else{}
				});
				
				$(".field-closer").click(function(){
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

				$(".edit-viewer").click(function(){
					$("#ap-" + $(this).parent().siblings("#row-slug").text() + "-editor").css("display","block");
					$("#overlay").css("display","block");
					CurrentType = $(this).parent().siblings("#row-slug").text();
				});

				$(".field-edit-closer").click(function(){
					$("#ap-" + CurrentType + "-field-editor").css("display","none");
					$("#ap-" + CurrentType + "-fields").css("display","block");
				});

				$(".field-edit-viewer").click(function(){
					$("#ap-" + CurrentType + "-field-editor").css("display","block");
					$("#ap-" + CurrentType + "-fields").css("display","none");
				});

				});
	
			function InJSON1(strValue1, strValue2, objJSONArray){
				var found = false;
				for (var i=0; i<objJSONArray.length; i++) {
				    if (objJSONArray[i].slug.toLowerCase() == strValue1.toLowerCase() || objJSONArray[i]['singular_name'].toLowerCase() == strValue1.toLowerCase() || objJSONArray[i].slug.toLowerCase() == strValue2.toLowerCase() || objJSONArray[i]['singular_name'].toLowerCase() == strValue2.toLowerCase()) {
				    	console.log(objJSONArray[i].slug);
					    found = true;
				        break;
				    }
				}
				return found; 
			}

			function InJSON2(strValue1, strValue2, objJSONArray){
				var found = false;
				for (var i=0; i<objJSONArray.length; i++) {
				    if (objJSONArray[i]['singular_name'].toLowerCase() == strValue1.toLowerCase() || objJSONArray[i]['plural_name'].toLowerCase() == strValue1.toLowerCase() || objJSONArray[i]['singular_name'].toLowerCase() == strValue2.toLowerCase() || objJSONArray[i]['plural_name'].toLowerCase() == strValue2.toLowerCase()) {
				        found = true;
				        break;
				    }
				}
				return found; 
			}
			
			function SaveType(){
				if(document.getElementById('type-name').value.length < 2 || document.getElementById('type-sing-name').value.length < 2){
					window.alert("User Type Names must be at least 2 characters long"); 
					return; }
				if(escape(document.getElementById('type-slug').value).length > 1) var strSlug = escape(document.getElementById('type-slug').value.toLowerCase()); else var strSlug = escape(document.getElementById('type-sing-name').value.toLowerCase());
				if(InJSON2(document.getElementById('type-name').value, strSlug, CurUserTypes)){
					window.alert("You cannot create multiple Custom User Types with the same Name or Slug"); 
					return; }
				var TypeFieldDataArray = [{ 'type-name' : document.getElementById('type-name').value, 'type-sing-name' : document.getElementById('type-sing-name').value, 'type-description': document.getElementById('type-description').value, 'type-slug' : strSlug.toLowerCase()}];
				$.post("", { "action" : "ajax" , "NewTypeData" : TypeFieldDataArray, "NewFieldData" : FieldNameArray }, function(d){ document.location.reload(true); }, null);
			}
	
			function SaveGlobalField(){
				if(document.getElementById('glob-field-name').value.match(/[\W_]/) || document.getElementById('glob-type-slug').value.match(/[\W_]/)){
					window.alert("User Type Names can only contain Alpha-numeric characters and underscores");
					return; }
				if(document.getElementById('glob-field-name').value.length < 2 || document.getElementById('glob-type-slug').value.length < 2){
					window.alert("User Type Names must be at least 2 characters long"); 
					return; }
				if(InJSON1(document.getElementById('glob-field-name').value, document.getElementById('glob-type-slug').value, CurGlobalFields)){
					window.alert("You cannot create multiple Custom User Types with the same Name or Slug"); 
					return; }
				var strFieldArray = { "action" : "ajax", 'global-field-name': document.getElementById('glob-field-name').value, "global-control-type" : document.getElementById('glob-control-type').value, "global-field-description": document.getElementById('glob-type-metavalue').value, "global-field-slug" : document.getElementById('glob-type-slug').value.toLowerCase()};
				$.post("", strFieldArray, function(d){ document.location.reload(true); }, null);
				console.log(strFieldArray);
			}
			
			function MetaPairAdd(){
				var boolTest = false;
				jQuery.each( FieldNameArray, function(key, value){if(value["type-field-key"]  == document.getElementById('type-metakey').value) boolTest = true; });
				if(!boolTest){
					if(document.getElementById('type-metakey').value.length < 2){
						window.alert("Field Name must be at least 2 characters long"); 
						return; }
					var strNewFieldSlug = escape(document.getElementById('type-metakey').value);
					if(document.getElementById("no-pair-holder")!= null){
						var holder = document.getElementById("meta-pair-holder");
						holder.removeChild(document.getElementById("no-pair-holder"));
						holder.innerHTML = "<table id='temp-meta' border='1' style='width:100%;'>" + 
						"<tr>" + 
						"<th>Field Name</th>" +
						"<th>Control Type</th>" +
						"<th> Default Value </th>" +
						"</tr>" +
						"</table>";
						var holder = document.getElementById('temp-meta');
						holder.innerHTML = holder.innerHTML +"<tr><td>" + document.getElementById('type-metakey').value +  "</td><td>" + document.getElementById('control-type').value + "</td><td>" + document.getElementById('type-metavalue').value + "</td></tr>";
					}
					else{
						var holder = document.getElementById('temp-meta');
						holder.innerHTML = holder.innerHTML +"<tr><td>" + document.getElementById('type-metakey').value + "</td><td>" + document.getElementById('control-type').value + "</td><td>" + document.getElementById('type-metavalue').value + "</td></tr>";				}
						FieldNameArray.push({ 'type-field-key' : document.getElementById('type-metakey').value, 'type-field-control-type' : document.getElementById('control-type').value, 'type-field-description' : document.getElementById('type-metavalue').value, 'type-field-slug' : strNewFieldSlug.toLowerCase()});
						return;
				}
				else{
					window.alert("Field Name already exists for this User Type"); 
					return;}
			}
	
			</script>
			<?php 
		}
	
	
}