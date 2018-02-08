<?php

/**
 * Class for registering and maintaining AgilePress custom post-types 
 * 
 * @author AgilePress Core Developement Team
 *
 *@property string Name
 *@property string Slug
 *@property string LabelName
 *@property string SingularName
 *@property string CapabilityType
 *@property boolean Hierarchical
 *@property array Supports
 *@property boolean ShowInMenu
 *@property boolean MapMetaCap
 *@property boolean ShowMetaBox
 */

class AP_CustomPostTypeBase extends AP_Base {
	
	// SETUP
	protected $strName = 'post';
	protected $strSlug = '';
	protected $strDescription;
	protected $strLabelName = 'Posts';
	protected $strSingularName = 'Post';
	protected $strControlArray = array();
	protected $capability_type = 'page';
	protected $hierarchical = false;
	protected $supports = array('title', 'editor', 'revisions', 'comments', 'author', 'excerpt', 'page-attributes');
	protected $show_in_menu = true;
	protected $map_meta_cap = true;
	protected $show_meta_box = true;
	protected $metabox_array = array();
	
	public function __construct() {
	}
	
	public static function GetConfigPostTypeArray(){
		$objConfigClass = new AP_CoreCodeGenerator;
		if(!isset($objConfigClass->objConfigData))
			return array();
		$arrReturnArray = array();
		foreach($objConfigClass->objConfigData->posttypes->posttype as $value){
			 $value = unserialize($value);
			 $value = $value[0];
			 $arrReturnArray[] = array('slug' => $value['slug'], 'name' => $value['arguments']['labels']['name'], 'singular_name' => $value['arguments']['labels']['singular_name'], 'ap_id' => $value['ap_id'], 'description' => $value['description']);
		}
		return $arrReturnArray;
	}
	
public static function GetFullConfigPostTypeArray(){
		$objConfigClass = new AP_CoreCodeGenerator;
		if(!isset($objConfigClass->objConfigData))
			return array();
		$arrReturnArray = array();
		foreach($objConfigClass->objConfigData->posttypes->posttype as $value){
			 $value = unserialize($value);
			 $value = $value[0];
			 $arrReturnArray[] = $value;
		}
		return $arrReturnArray;
	}
	
	
	
	public static function GetConfigFieldArray(){
		$objConfigClass = new AP_CoreCodeGenerator;
		if(!isset($objConfigClass->objConfigData))
			return array();
		$arrReturnArray = array();
		foreach($objConfigClass->objConfigData->postfields->postfield as $value){
			$arrReturnArray[] = unserialize($value);
		}
		return $arrReturnArray;
	}
	
	/**
	 * Inserts a new AgilePress custom Posttype into the WPDB based on the data in the currently instantiated object
	 */
	
	public function Insert(){
		$arrPostTypesArray = AP_CustomPostTypeBase::GetPostTypeArray();
		$boolDuplicateTest = false;
		foreach($arrPostTypesArray as $PostTypeArray)
		if(in_array($this->strName, $PostTypeArray) || in_array($this->strSingName, $PostTypeArray))
			$boolDuplicateTest = true;
		if($boolDuplicateTest){
			echo "Cannot Register Post Type with Duplicate Singluar or Plural Names";
			die;
		}
		$intControlIDArray = array();
		global $wpdb;
		$wpdb->insert($wpdb->base_prefix . "ap_post_type", array(
				'singular_name' => $this->SingularName,
				'description' => $this->strDescription,
				'name' => $this->strName,
				'slug' => $this->strSlug
		));
		$intNewTypeID = $wpdb->insert_id;
		foreach($this->strControlArray as $IndividualControlArray){
			print_r($IndividualControlArray);
			$wpdb->insert($wpdb->base_prefix . "ap_post_type_field", array(
					'name' => $IndividualControlArray["type-field-key"],
					'description' => $IndividualControlArray["type-field-description"],
					'control_type' => $IndividualControlArray["type-field-control-type"],
					'slug' => "ap-" . AP_Base::SanitizeString($IndividualControlArray["type-field-slug"])
			));
			$intControlIDArray[] = $wpdb->insert_id;
		}
		foreach($intControlIDArray as $intControlID)
			$wpdb->insert($wpdb->base_prefix . "ap_post_field", array(
					'post_type_id' => $intNewTypeID,
					'post_field_id' => $intControlID
			));
	
	}
	
	
	/**
	 * Called by AP_Application::AdminInit()
	 * 
	 * This function sets up the standard metaboxes that will render on the admin page of a post type.
	 * Function can be overwritten to add additional metaboxes
	 */
	public function MetaBoxRun() {
		$this->AdminLoad();
		$this->AdminLoadCustom();
		$this->MetaBoxInit();
	}
	
	protected function AdminLoad() {}
	protected function AdminLoadCustom() {}
	protected function MetaBoxInit() {
		$this->AddMetaBox("add-" . $this->Name . "-meta-normal-high", "Additional Fields");
	}
	
	/**
	 * Adds a metabox to the admin screen
	 * @param string $metabox_id
	 * @param string $title
	 */
	protected function AddMetaBox($metabox_id, $title) {
		// store the metabox in an array so we know which metaboxes to render later
		$size = array_push($this->metabox_array, $metabox_id);
		// call the WordPress function to add the metabox
		add_meta_box($metabox_id, $title,
			array($this, 'MetaBoxCallback'), ($this->Name == "Posts") ? $this->Slug : $this->Name, "normal", "core", 
			array('id'=>$metabox_id, 'metabox_index' => $size-1)
		);
	}
	
	/**
	 * Takes an AgilePress custom post type plural name ($this->Name) and returns all of its custom data fields
	 * 
	 * @param string $strPostTypePluralName
	 * @return array $mixPostTypeFieldArray
	 */
	public static function GetPostTypeFieldArray($strPostTypePluralName){
		global $wpdb;
		$strTableName1 = $wpdb->base_prefix . "ap_post_type";
		$strTableName2 = $wpdb->base_prefix . "ap_post_type_field";
		$strTableName3 = $wpdb->base_prefix . "ap_post_field";
		$sql = "
		SELECT r.name, r.description, r.control_type, r.slug, s.singular_name FROM $strTableName1 s INNER JOIN $strTableName3 d on s.id = d.post_type_id INNER JOIN $strTableName2 r on d.post_field_id = r.id WHERE s.name = '$strPostTypePluralName'
		";
		$i = true;
		$n = 0;
		$mixPostTypeFieldArray = array();
		while($i <> null){
		$i = $wpdb->get_row($sql, ARRAY_A, $n);
		if($i <> null) $mixPostTypeFieldArray[] =  $i;
		$n++;
		}
		return $mixPostTypeFieldArray;
	
	}
	
	/**
	 * Takes an AgilePress custom post type plural name ($this->Name) and returns the custom data fields that have not been codegened
	 *
	 * @param string $strPostTypePluralName
	 * @return array $mixPostTypeFieldArray
	 */
	public static function GetPostTypeFieldConfigArray($strPostTypePluralName){
		$mixPostTypeFieldConfigArray = array();
		$objXMLHandler = new AP_CoreCodeGenerator;
		foreach($objXMLHandler->objConfigData->posttypes->posttype as $PostType){
			$PostType = unserialize($PostType);
			if($PostType[0]['arguments']['labels']['name'] == $strPostTypePluralName){
				foreach($PostType[1] as $PostTypeField){
						$mixPostTypeFieldConfigArray[] = $PostTypeField;
					}
				}				
			}
		
		return $mixPostTypeFieldConfigArray;
	
	}
	
	/**
	 * Returns an array of all the AgilePress custom post data fields
	 *
	 * @return array $mixGlobalPostTypeFieldArray
	 */
	
	public static function GetGlobalPostFieldCheckArray(){
		global $wpdb;
		$strTableName = $wpdb->base_prefix . "ap_post_type_field";
		$sql = "SELECT * FROM $strTableName";
		$i = true;
		$n = 0;
		$mixGlobalPostTypeFieldArray = array();
		while($i <> null){
			$i = $wpdb->get_row($sql, ARRAY_A, $n);
			if($i <> null) $mixGlobalPostTypeFieldArray[] =  $i;
			$n++;
		}
		return $mixGlobalPostTypeFieldArray;
	}
	
	
	
	/**
	* Returns an array of all the AgilePress custom global post data fields
	*
	* @return array $mixGlobalPostTypeFieldArray
	*/
	
	public static function GetGlobalPostFieldArray(){
	global $wpdb;
	$strTableName = $wpdb->base_prefix . "ap_post_type_field";
	$sql = "SELECT * FROM $strTableName WHERE global = 1";
	$i = true;
	$n = 0;
	$mixGlobalPostTypeFieldArray = array();
	while($i <> null){
	$i = $wpdb->get_row($sql, ARRAY_A, $n);
		if($i <> null) $mixGlobalPostTypeFieldArray[] =  $i;
		$n++;
	}
	return $mixGlobalPostTypeFieldArray;
	}
	
	
	/**
	 * Returns an array of all currently registered AgilePress custom post types
	 *
	 * @return array $mixPostTypeArray
	 */
	
	public static function GetPostTypeArray(){
		global $wpdb;
		$mixPostTypeArray = array();
		$i = true;
		$n = 0;
		$strTableName =  $wpdb->base_prefix . "ap_post_type";
		while($i <> null){
			$i = $wpdb->get_row("SELECT * FROM $strTableName", ARRAY_A, $n);
			if($i <> null) $mixPostTypeArray[] =  $i;
			$n++;
		}
		return $mixPostTypeArray;
	}
	
	
	/**
	 * Called every time a metabox is added
	 * @param WP_Post $post
	 * @param array $metabox - arguments from the add_meta_box() parameter $callback_args
	 */
	public function MetaBoxCallback($post, $metabox) {
		global $post;
		$custom = get_post_custom($post->ID); // get the postmeta values for a post
		$metabox_id = $metabox['args']['id'];
		$metabox_index = $metabox['args']['metabox_index'];
		
		AP_Application::NonceField();

		foreach (get_object_vars($this) as $param=>$object) {
			if ($this->{$param} instanceof AP_Control)
				$this->{$param}->Value = $custom[$this->{$param}->ID][0];
		}
		
		$this->RenderMetaBoxes($metabox_id, $metabox_index);
	}
	
	/**
	 * Used to render the Additional Fields of a post type
	 * This method is intended to be overriden in child classes.
	 */
	protected function RenderMetaBoxes($metabox_id, $metabox_index) {}
	
	/**
	 * Saves Additional Field values.
	 * This method is intended to be overriden in child classes.
	 */
	protected function SaveCustomPost() {}
	
	/**
	 * Allows custom post types to hook in and save additional fields.
	 * Runs when the save_post() method is run. 
	 * 
	 * @param integer $post_id
	 * @param WP_Post $post
	 */
	public function SavePost($post_id, $post) {
		
		$this->AdminLoad();
		$this->AdminLoadCustom();
		
		// prevent auto save for custom fields
		if(defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return $post->ID;
		if( ! ( wp_is_post_revision( $post_id) && wp_is_post_autosave( $post_id ) ) ) {
			$i = 0;
			foreach (get_object_vars($this) as $param=>$object) {
				
				if ($this->{$param} instanceof AP_Control)
					//Save logic for AP_CheckBoxList	
					if($this->{$param} instanceof AP_CheckBoxList){
						foreach($this->{$param}->ItemArray as $LowerParam => $lowerObject)
							delete_post_meta($post_id, $this->{$param}->ID . "[$LowerParam]");
						foreach($_POST[$this->{$param}->ID] as $LowerParam => $lowerObject){
							update_post_meta($post_id, $this->{$param}->ID . "[$LowerParam]", $lowerObject);
						}
					}
					//Save logic for all other controls
					update_post_meta($post_id, $this->{$param}->ID, $_POST[$this->{$param}->ID]);
			}
			// call the custom save function for each post type 
			$this->SaveCustomPost();
		}
		
	}
	
	// ==================================== //
	// ======= [STATIC FUNCTIONS] ========= //
	// ==================================== //
	public static function SetDefaultSlug($post_type) {
		return str_replace('ap_', '', $post_type);
	}
	
	// ==================================== //
	// ============ [GETTERS] ============= //
	// ==================================== //
	public function __get($name) {
		switch ($name) {
			case "Name": 
				return $this->strName;
			case "Slug": 
				return $this->strSlug;
			case "LabelName": 
				return $this->strLabelName;
			case "SingularName": 
				return $this->strSingularName;
			case "CapabilityType": 
				return $this->capability_type;
			case "Hierarchical": 
				return $this->hierarchical;
			case "Supports": 
				return $this->supports;
			case "ShowInMenu": 
				return $this->show_in_menu;
			case "MapMetaCap": 
				return $this->map_meta_cap;
			case "ShowMetaBox": 
				return $this->show_meta_box;
		}
	}
	
	// ==================================== //
	// ============ [SETTERS] ============= //
	// ==================================== //
	public function __set($name, $value) {
		switch ($name) {
			case "Name":
				return ($this->strName = $value);
			case "Slug":
				return ($this->strSlug = $value);
			case "LabelName":
				return ($this->strLabelName = $value);
			case "SingularName":
				return ($this->strSingularName = $value);
			case "CapabilityType":
				return ($this->capability_type = $value);
			case "Hierarchical":
				return ($this->hierarchical = $value);
			case "Supports":
				return ($this->supports = $value);
			case "ShowInMenu":
				return ($this->show_in_menu = $value);
			case "MapMetaCap":
				return ($this->map_meta_cap = $value);
			case "ShowMetaBox":
				return ($this->show_meta_box = $value);
		}
	}
}