<?php

	add_action( 'show_user_profile', array('AP_UserAdmin', 'AddCustomUserFields') );
	add_action( 'edit_user_profile', array('AP_UserAdmin', 'AddCustomUserFields') );
	
	add_action( 'personal_options_update', array('AP_UserAdmin', 'SaveCustomerUserFields') );
	add_action( 'edit_user_profile_update', array('AP_UserAdmin', 'SaveCustomerUserFields') );

	class AP_UserAdminBase {
		public static function AddCustomUserFields() {
			
		}
		
		public static function SaveCustomerUserFields() {
			
		}
	}
	
	