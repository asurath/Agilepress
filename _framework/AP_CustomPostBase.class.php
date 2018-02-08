<?php
class AP_CustomPostTypeBase {
	
	// SETUP
	protected $name = 'post';
	protected $slug = '';
	protected $label_name = 'Posts';
	protected $singular_name = 'Post';
	protected $capability_type = 'page';
	protected $hierarchical = false;
	protected $supports = array('title', 'editor', 'revisions', 'comments', 'author', 'excerpt', 'page-attributes');
	protected $show_in_menu = true;
	protected $map_meta_cap = true;
	protected $show_meta_box = true;
	protected $metabox_array = array();
	
	public function __construct() {
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
		$this->AddMetaBox("add-" . $this->name . "-meta-normal-high", "Additional Fields");
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
			array($this, 'MetaBoxCallback'), $this->name, "normal", "high", 
			array('id'=>$metabox_id, 'metabox_index' => $size-1)
		);
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
			
			foreach (get_object_vars($this) as $param=>$object) {
				if ($this->{$param} instanceof AP_Control)
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
			case "Name": return $this->name;
			case "Slug": return $this->slug;
			case "LabelName": return $this->label_name;
			case "SingularName": return $this->singular_name;
			case "CapabilityType": return $this->capability_type;
			case "Hierarchical": return $this->hierarchical;
			case "Supports": return $this->supports;
			case "ShowInMenu": return $this->show_in_menu;
			case "MapMetaCap": return $this->map_meta_cap;
			case "ShowMetaBox": return $this->show_meta_box;
		}
	}
	
	// ==================================== //
	// ============ [SETTERS] ============= //
	// ==================================== //
	public function __set($name, $value) {
		switch ($name) {
			case "Name":
				return ($this->name = $value);
			case "Slug":
				return ($this->slug = $value);
			case "LabelName":
				return ($this->label_name = $value);
			case "SingularName":
				return ($this->singular_name = $value);
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