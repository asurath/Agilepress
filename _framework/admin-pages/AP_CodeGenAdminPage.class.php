<?php
/**
 * Class for handling all Code Generation UI output as well as AJAX and menu registeration
 * 
 * @since 1.0
 * @package AgilePress
 * @subpackage Code Generation Administrator Page
 * @author AgilePress Core Developement Team
 *
 */
class AP_CodeGenAdminPage extends AP_AdminPageBase {
	
	/**
	 * Called on the wordpress hook admin_init, this method registers the sub_menu AgilePress item "CodeGen" and redirects admin AJAX calls back to the page
	 * 
	 * @since 1.0
	 * @return void
	 */
	public static function Init(){
		add_submenu_page('ap-settings-home', 'Agile Press CodeGen Settings', 'CodeGen', 'administrator', 'ap-code-gen-settings', array('AP_CodeGenAdminPage', 'PageCreate'));
		if(!empty($_POST) && $_POST['action'] == "ajax"){
			self::PageCreate();
		}
	}
	
	/**
	 * Static method for creating and running this page class
	 * 
	 * @since 1.0
	 * @return void
	 */
	public static function PageCreate(){
		if($_GET['page'] <> 'ap-code-gen-settings') return;
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
		$objXMLHandler = new AP_CoreCodeGenerator;
		if(isset($_POST['CodeGen'])){
			$objXMLHandler->CodeGenRun();
			die;
		}
		if(isset($_POST['ConfigBackup'])){
			$objXMLHandler->SaveConfigurationDataAsBackup();
			echo json_encode(true);
			die;
		}
		if(isset($_POST['CreatePage'])){
			$objNewClass = new AP_ClassHolder();
			$strPageID = preg_replace("/[^a-z0-9]/i" , "", strtolower($_POST['CreatePage']['class_name']));
			$strPageClassName = "AP_" . preg_replace("/[^a-z0-9]/i" , "", $_POST['CreatePage']['class_name']) . "Page";
			$strPageClassDocBlock = $_POST['CreatePage']['description'] . "\n";
			ob_start();
			$strFile = include( AP_TEMPLATE_PATH . "files/PageTemplate.tpl.php");
			$strContents = ob_get_contents();
			ob_end_clean();
			echo json_encode(AP_IO::WriteFile(AP_PLUGIN_PATH . $objXMLHandler->strSiteSlug . "/pages/" . $strPageClassName . ".class.php", "<?php \n" . $strContents));
			die;
		}
		if(isset($_POST['CleanupTables'])){
			global $wpdb;
			$strSQL = "
				DELETE pm.* from wp_postmeta pm left join wp_posts p on p.ID = pm.post_id 
				left join wp_ap_post_type pt on LOWER(pt.name) = LOWER(p.post_type) 
				left join wp_ap_post_field pf on pf.post_type_id = pt.id
				left join wp_ap_post_type_field ptf on ptf.id = pf.post_field_id 
				where meta_key REGEXP '^ap-' AND (ISNULL(pf.post_field_id) OR LOWER(CONCAT('^ap-' , ptf.slug)) != pm.meta_key)
			";
			$wpdb->$query($strSQL);
			echo json_encode(true);
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
		<h1 style="margin-left:15px;">AgilePress Code Generation Manager
			</h1>
			<div style="width:850px;">
			<?php $this->AdminNavBarRender('codegen');?>
				<div style="padding: 1px 20px; margin: 5px 15px 2px; margin-top:0; padding-top:0; height:750px; background:white; width:100%; ">
					<div style="height:40px;"></div>
					<a class="ap-admin-button type-adder" href="" onclick="return false;" style="float:left;">
						<span class="ap-admin-button-2">
							<span> Create page class </span>
						</span>
					</a>
					<a class="ap-admin-button" style="float:left;  margin-left:20px;" onclick="CodeGenOnPage(); return false;" href = "">
						<span class="ap-admin-button-2">
							<span> Update Code </span>
						</span>
					</a>
					<a class="ap-admin-button" style="float:left; margin-left:20px;" onclick="CleanupTables(); return false;" href = "">
						<span class="ap-admin-button-2">
							<span style="top:1px;"> Cleanup Wordpress Meta Tables </span>
						</span>
					</a>
					<a class="ap-admin-button" href="" onclick="ConfigBackup(); return false;" style="float:left; margin-left:20px;">
						<span class="ap-admin-button-2">
							<span> Create Config Backup </span>
						</span>
					</a>
					<a class="ap-admin-button" href="<?php _e(plugins_url() . '/agilepress/agilepress-codegen-log.txt');?>" style="float:left; margin-left:20px; " download>
						<span class="ap-admin-button-2">
							<span> Download Log </span>
						</span>
					</a>
					<br>
					<p class="ap-settings-field"> CodeGen Log: </p>
					<div style="border-style:solid; border-width:1px; width:100%; height:585px; overflow:auto; border-color:rgb(179,179,179);">
						<pre><?php ob_start(); if(file_exists(AP_PATH . "agilepress-codegen-log.txt")){ $strFile = include( AP_PATH . "agilepress-codegen-log.txt" ); $strContents = ob_get_contents(); ob_end_clean();} else $strContents = "				AgilePress CodeGen log does not exist"; echo $strContents;?></pre>	
					</div>
				</div>
			</div>
			<div id="field-view"></div>
			<div id="overlay"></div>
			<div id="ap-type-adder" class="edit-holder" style="display:none; padding: 1px 20px; position:absolute; margin: 5px 15px 2px; margin-top:0; top:80px; z-index:5; padding-top:0; height:450px; background:white; width:405px; ">
				<div style="height:40px; background:rgb(58,58,58); padding:0; margin:0; margin-left:-20px; margin-right:-20px;" >
					<p class="ap-post-key" style="margin-top:13px; position:absolute; left:20px; height:20px; text-align:left;">Page Class Creator</p>
					<a class="edit-closer" style="margin-top:7px; position:absolute; right:20px; cursor:pointer;"><i class="fa fa-times-circle fa-2x" style="color:white;"></i></a>
				</div>
				<p class="ap-settings-field" > Class Name </p>
				<input autocomplete='off' type="text" class="ap-settings-field-input" id="page-create-class-name" value="" /><br>
				<p class="ap-settings-field"> Description (Class document block) </p>
				<textarea  autocomplete="off"  style="width:400px; height:200px; border-radius:5px; margin-bottom:30px;" id="page-create-description" ></textarea>
				<a class="ap-admin-button" style="float:left" onclick="CreatePage(); return false;">
				<span class="ap-admin-button-2">
					<span style=""> Create Page </span>
				</span>
				</a>
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

			var intPopupOffset = $("#wpcontent").width() - 800;
			intPopupOffset = intPopupOffset / 2;
			intPopupOffset = intPopupOffset - 20;
			intPopupOffset = intPopupOffset + 100;
			$(".edit-holder").css("margin-left",intPopupOffset);
			$(".edit-holder-bar").css("left",intPopupOffset+200);

			$(".edit-closer").click(function(){
				$(".edit-closer").parents(".edit-holder").css("display","none"); 
				$("#overlay").css("display","none");
			});
			
			$(".type-adder").click(function(){
				$("#ap-type-adder").css("display","block");
				$("#overlay").css("display","block");
			});
			
			function CodeGenOnPage(){
				$.post("",{ "action" : "ajax" , "CodeGen" : true }, function(data){ document.location.reload(); });
			}
			
			function ConfigBackup() {
				$.post("",{"action":"ajax","ConfigBackup" : true }, function(data){ if(JSON.parse(data))alert("Configuration file successfully backed up"); });
			}

			function CleanupTables(){
				var booConfirmCheck = confirm("//// DANGER //// Are you sure you want to do this? This command will remove all meta key-value rows in the wp_postmeta and wp_usermeta tables whose key begins with 'ap-' and is not currently associated with AgilePress data.");
				if (booConfirmCheck == true)
				    $.post("",{"action":"ajax","Cleanup Tables": true }, function(data){ if(JSON.parse(data))alert("Configuration file successfully backed up"); });
			}

			function CreatePage() {
				var PageRegEx = /[^0-9a-zA-Z-_]/;
				
				var strPageData = {
					"class_name" : $("#page-create-class-name").val(),
					"description" : $("#page-create-description").val()
				}

				if(strPageData.class_name.length < 3 || strPageData.class_name.length > 20){
					alert("Post plural names must be 3 characters or longer and less than 20");
					return;
				}
				
				if(strPageData.class_name.search(PageRegEx) !== -1){
					alert("Class Names can only contain Alphanumeric, dash and underscore characters");
					return;
				}

				$.post("",{"action":"ajax", "CreatePage" : strPageData},function(data){
					console.log(data);
					$intReturnValue = JSON.parse(data);
					if($intReturnValue == 2)
						alert("Error: a file with this name already exists in the extension pages directory");
					if($intReturnValue == 1){
						alert("Page class created successfully");
						$("#page-create-class-name").val("");
						$("#page-create-description").val("");
						$(".edit-closer").trigger("click");
					}
					if($intReturnValue == 0)
						alert("Error: bad return value");
				});
			}
			</script>
			
				<?php 
			}
		
		
	}
	
	