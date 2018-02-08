<?php

/**
 * Class for the Admin page to create and maintain the Custom AgilePress Post Types and Global AgilePress Post Fields
 *
 * @author AgilePress Core Developement Team
 *
 */
class AP_CustomPostTypeAdmin extends AP_CustomPostTypeBase{

	public function __construct() {
		return true;
	}

	/**
	 * Outputs the MetaBox html for Custom UserTypes and GlobalFields on individual user pages
	 */

	public function AddMetaBox() {
		$mixGlobalFieldArray= AP_CustomPostTypeBase::GetGlobalPostFieldArray();
		$PostTypeArray = AP_CustomPostTypeBase::GetPostTypeArray();
		?>
	
	<h3><?php _e( 'Global User Fields' ); ?></h3>
	
	<?php foreach($mixGlobalFieldArray as $mixGlobalField){?>
	<table class="form-table">
		<tr>
			<th><label><?php _e( $mixGlobalField['name'] ); ?></label></th>

			<td>
				<label><?php _e( $mixGlobalField['description'] ); ?></label><br>
				<?php 
				$objNewControl = null;
				global $profileuser;
				$strCurUserType = get_user_meta($profileuser->data->ID,$mixGlobalField['slug'], true);
				echo AP_Control::GetControl($mixGlobalField['control_type'], $mixGlobalField['slug'], $strCurUserType)->Render();
				?>
			
			</td>
		</tr>

	</table>
	<?php } ?>
	
	<h3><?php _e( 'User Type' ); ?></h3>

	<table class="form-table">
		
		<tr>
			<th><label for="user-type"><?php _e( 'Select User Type' ); ?></label></th>

			<td><?php
			global $profileuser;
			$strCurUserType = get_user_meta($profileuser->data->ID,'custom-user-type');
			//print_r(get_user_meta($profileuser->data->ID,'custom-user-type'));
			if ( !empty( $UserTypeArray ) ) {

				foreach ( $UserTypeArray as $UserType ) { ?>
					<input type="radio" name="user-type"  value="<?php echo esc_attr( $UserType['name'] ); ?>" <?php checked( $UserType['name'], $strCurUserType[0]);  ?> /> <label for="user-type-<?php echo esc_attr( $UserType['name'] ); ?>"><?php echo $UserType['singular_name'] . " - " . $UserType['description']; ?></label> <br />
				<?php }
			}
			else {
				_e( 'There are no custom user types available.' );
			}

			?></td>
		</tr>

	</table>
	
	<?php if(isset($strCurUserType[0])){
		$mixUserTypeFieldArray= AP_CustomUserTypeBase::GetUserTypeFieldArray($strCurUserType[0]);
		?>
	<h3><?php _e( $mixUserTypeFieldArray[0]['singular_name'] . " User Fields" ); ?></h3>
	
	<?php foreach($mixUserTypeFieldArray as $mixUserTypeField){
		?>
	<table class="form-table">
		<tr>
			<th><label><?php _e( $mixUserTypeField['name'] ); ?></label></th>

			<td>
				<label><?php _e( $mixUserTypeField['description'] ); ?></label><br>
				<?php 
				$objNewControl = null;
				global $profileuser;
				$strCurrentUserTypeFieldValue = get_user_meta($profileuser->data->ID,$mixUserTypeField['slug'], true);
				echo AP_Control::GetControl($mixUserTypeField['control_type'], $mixUserTypeField['slug'], $strCurrentUserTypeFieldValue)->Render();
				?>
			
			</td>
		</tr>

	</table>
	<?php } 
	}
	?>
	
	
	
	
<?php
	}

	/**
	 * Saves the information entered into the Custom MetaBoxes when a user is updated in admin
	 * 
	 * @param integer $user_id
	 */
	
	public static function save($user_id) {
		$type = esc_attr( $_POST['user-type'] );
		update_user_meta($user_id,'custom-user-type',$type);
		$customfields = AP_CustomUserTypeBase::GetUserTypeFieldArray($type);
		foreach($customfields as $customfield){
			$name = $customfield['name'];
			$strDescription = $customfield['description'];
			$strSlug =  $customfield['slug'];
			update_user_meta($user_id, $strSlug, $_POST[$strSlug]);
		}
		
		$globalfields = AP_CustomUserTypeBase::GetGlobalUserFieldArray();
		foreach($globalfields as $globalfield){
			$name = $globalfield['name'];
			$strDescription = $globalfield['description'];
			$strSlug =  $globalfield['slug'];
			update_user_meta($user_id, $strSlug, $_POST[$strSlug]);
		}
		
	}

}