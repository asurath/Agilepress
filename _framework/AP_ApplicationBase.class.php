<?php
abstract class AP_ApplicationBase {
	
	/**
	 * Main function that runs the application.
	 */
	public function Run() {
		add_action( 'init', array($this, 'Init'), 6 );
		add_action( 'admin_init', array($this, 'AdminInit') );
		add_action( 'save_post', array($this, 'SavePost'), 1, 2);
		
		register_activation_hook(__FILE__,array($this, 'Install'));
	}
	
	// Loading functions
	abstract protected function SiteSettings();
	abstract protected function AdminSettings();
	abstract protected function SetCurrentUser();
	abstract protected function PreExitInit();
	
	// Install functions
	abstract public function Install();
	
	/**
	 * Runs on every page load
	 */
	public function Init() {
		$this->SiteSettings();
		
		// Register post types
		$this->RegisterPostTypes();
	
		// Register taxonomies
	
		// Handle custom functions before exiting
		$this->PreExitInit();
	}
	
	/**
	 * Dynamically runs through each custom post type and displays the metabox
	 */
	public function AdminInit() {
		$this->AdminSettings();
		
		if (defined('AP_ADMIN_CSS'))
			wp_enqueue_style('ap-admin-stylesheet', AP_ADMIN_CSS);
	
		// Run through each post type and see if there is a admin init
		$post_types = unserialize(AP_CUSTOM_POST_TYPES);
		foreach ($post_types as $post_type) {
				
			$o = new $post_type(); // create an instance of the custom post type
			$o->MetaBoxRun();
				
		}
	}
	
	/**
	 * Dynamically runs through each custom post type and calls the SavePost function
	 * @param integer $post_id
	 * @param WP_Post $post
	 */
	public function SavePost($post_id, $post) {
		$post_types = unserialize(AP_CUSTOM_POST_TYPES);
		foreach ($post_types as $post_type) {
			$object_post = new $post_type();
			$object_post->SavePost($post_id, $post);
		}
	}
	
	/**
	 * Loops through all custom post types and registers each one
	 */
	protected function RegisterPostTypes() {
		$post_types = unserialize(AP_CUSTOM_POST_TYPES);
		foreach ($post_types as $post_type) {
			$this->RegisterCustomPostType($post_type);
		}
	}
	
	/**
	 * Creates the NonceField so we make sure any post request comes from the site and not an
	 * outside source.
	 */
	public static function NonceField() {
		wp_nonce_field( plugin_basename( __FILE__ ), __NONCE_KEY__ );
	}
	
	/**
	 * Registers an individual post type
	 * @param string $custom_post_type
	 */
	protected function RegisterCustomPostType($custom_post_type) {
	
		if (!class_exists($custom_post_type))
			return;
	
		$o = new $custom_post_type();
	
		$name = $o->Name;
		$slug = $o->Slug;
		$label_name = $o->LabelName;
		$singular_name = $o->SingularName;
		$capability_type = $o->CapabilityType;
		$hierarchical = $o->Hierarchical;
		$supports = $o->Supports;
		$show_in_menu = $o->ShowInMenu;
		$map_meta_cap = $o->MapMetaCap;
	
	
		if (!isset($name))
			$slug = AP_CustomPostTypeBase::SetDefaultSlug($name);
	
		$post_type_setup = array(
				'labels' => array(
						'name' => __( $label_name ),
						'singular_name' => __( $singular_name ),
						'add_new_item' => __( "Add $singular_name" ),
						'edit_item' => __( "Edit $singular_name" ),
						'not_found' => __( 'No '. strtolower($label_name).' found.' ),
				),
				'public' => true,
				'show_ui' => true,
				'capability_type' => $capability_type,
				'hierarchical' => $hierarchical,
				'supports' => $supports,
				'rewrite' => array('slug' => $slug),
				'show_in_menu' => $show_in_menu,
				'map_meta_cap' => $map_meta_cap // typically when capability type is NOT post or page
		);
	
		register_post_type($name, $post_type_setup);
	}
}