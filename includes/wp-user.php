<?php

// Backpress compat
class WP_User extends BP_User {
	function BP_User( $id, $name = '' ) {
		parent::WP_User( $id, $name );
	}
}

function get_user( $user_id, $args = null ) {
	global $wp_users_object;
	$user = $wp_users_object->get_user( $user_id, $args );
	if ( is_wp_error($user) )
		return false;
	if ( !empty($user->ID) )
		return new BP_User( $user->ID );
	return false;
}

// All the user crap imported from WP

function update_usermeta( $id, $meta_key, $meta_value ) {
	global $wp_users_object;
	$return = $wp_users_object->update_meta( compact( 'id', 'meta_key', 'meta_value' ) );
	if ( is_wp_error( $return ) )
		return false;
	return $return;
}

function delete_usermeta( $id, $meta_key, $meta_value = null ) {
	global $wp_users_object;
	$return = $wp_users_object->delete_meta( compact( 'id', 'meta_key', 'meta_value' ) );
	if ( is_wp_error( $return ) )
		return false;
	return $return;
}

if ( !function_exists('is_user_logged_in') ) :
function is_user_logged_in() {
	global $current_user;
	
	if ( $current_user->id == 0 )
		return false;
	return true;
}
endif;

if ( !function_exists('wp_login') ) :
function wp_login($username, $password, $already_md5 = false) {
	global $db, $error;

	if ( '' == $username )
		return false;

	if ( '' == $password ) {
		$error = __('<strong>Error</strong>: The password field is empty.');
		return false;
	}

	$user = new WP_User( $username );

	if (!$user || !$user->ID) {
		$error = __('<strong>Error</strong>: Wrong username.');
		return false;
	}

	if ( !WP_Pass::check_password( $password, $user->data->user_pass, $user->ID ) ) {
		$error = __('<strong>Error</strong>: Incorrect password.');
		$pwd = '';
		return false;
	}

	if ( !$user->has_cap( 'supporter' ) && !$user->has_cap( 'supportpressadmin' ) )
		return false;

	return true;
}
endif;

if ( !function_exists('wp_salt') ) :
function wp_salt() {
        if ( defined('SECRET_KEY') && ('' != SECRET_KEY) && ('put your unique phrase here' != BB_SECRET_KEY) )
                $secret_key = SECRET_KEY;
        
        if ( defined('SECRET_SALT') )
                $salt = SECRET_SALT;
        
        return apply_filters('salt', $secret_key . $salt);
}
endif;
       
if ( !function_exists('wp_hash') ) :
function wp_hash($data) {
        $salt = wp_salt();
        
        return hash_hmac('md5', $data, $salt);
}
endif;

function create_role( $name, $display_name, $capabilities = array() ) {
	global $wp_roles, $db;
	if ( ! isset( $wp_roles ) )
		$wp_roles = new BP_Roles( $db );
		
	$wp_roles->add_role( $name, $display_name, $capabilities );
}



/*

No longer used.  Use Backpress functions.

if ( !function_exists('get_userdatabylogin') ) :
function get_userdatabylogin($user_login) {
	global $db;
	$user_login = sanitize_user( $user_login );

	if ( empty( $user_login ) )
		return false;


	if ( !$user = $db->get_row("SELECT * FROM $db->users WHERE user_login = '$user_login'") )
		return false;

	$db->hide_errors();
	$metavalues = $db->get_results("SELECT meta_key, meta_value FROM $db->usermeta WHERE user_id = '$user->ID'");
	$db->show_errors();

	if ($metavalues) {
		foreach ( $metavalues as $meta ) {
			@ $value = unserialize($meta->meta_value);
			if ($value === FALSE)
				$value = $meta->meta_value;
			$user->{$meta->meta_key} = $value;

			// We need to set user_level from meta, not row
			if ( $db->prefix . 'user_level' == $meta->meta_key )
				$user->user_level = $meta->meta_value;
		}
	}

	return $user;

}
endif;

if ( !function_exists('set_current_user') ) :
function set_current_user($id, $name = '') {
	global $user_login, $userdata, $user_level, $user_ID, $user_email, $user_url, $user_pass_md5, $user_identity, $current_user;

	$current_user	= '';

	$current_user	= new WP_User($id, $name);

	$userdata	= get_userdatabylogin($user_login);

	$user_login	= $userdata->user_login;
	$user_level	= $userdata->user_level;
	$user_ID	= $userdata->ID;
	$user_email	= $userdata->user_email;
	$user_url	= $userdata->user_url;
	$user_pass_md5	= md5($userdata->user_pass);
	$user_identity	= $userdata->display_name;

	return $current_user;
}
endif;


if ( !function_exists('get_currentuserinfo') ) :
function get_currentuserinfo() {
	global $user_login, $userdata, $user_level, $user_ID, $user_email, $user_url, $user_pass_md5, $user_identity, $current_user;

	if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST )
		return false;
	
	if ( empty($_COOKIE[USER_COOKIE]) || empty($_COOKIE[PASS_COOKIE]) || 
		!wp_login($_COOKIE[USER_COOKIE], $_COOKIE[PASS_COOKIE], true) ) {
		$current_user = new WP_User(0);
		return false;
	}
	$user_login  = $_COOKIE[USER_COOKIE];
	
	$userdata    = get_userdatabylogin($user_login);
	$user_level  = $userdata->user_level;
	$user_ID     = $userdata->ID;
	$user_email  = $userdata->user_email;
	$user_url    = $userdata->user_url;
	$user_pass_md5 = md5($userdata->user_pass);
	$user_identity = $userdata->display_name;

	if ( empty($current_user) )
		$current_user = new WP_User($user_ID);
}
endif;

if ( !function_exists('get_userdata') ) :
function get_userdata( $user_id ) {
	global $db;
	$user_id = (int) $user_id;
	if ( $user_id == 0 )
		return false;

	if ( !$user = $db->get_row("SELECT * FROM $db->users WHERE ID = '$user_id' LIMIT 1") )
		return false;

	$db->hide_errors();
	$metavalues = $db->get_results("SELECT meta_key, meta_value FROM $db->usermeta WHERE user_id = '$user_id'");
	$db->show_errors();

	if ($metavalues) {
		foreach ( $metavalues as $meta ) {
			@ $value = unserialize($meta->meta_value);
			if ($value === FALSE)
				$value = $meta->meta_value;
			$user->{$meta->meta_key} = $value;

			// We need to set user_level from meta, not row
			if ( $db->prefix . 'user_level' == $meta->meta_key )
				$user->user_level = $meta->meta_value;
		} // end foreach
	} //end if
	
	return $user;
}
endif;

class WP_Roles {
	var $roles;

	var $role_objects = array();
	var $role_names = array();
	var $role_key;

	function WP_Roles() {
		global $table_prefix;

		$this->roles = array( 
		  "administrator" =>
		  array( 
				"name"=>"Administrator",
				"capabilities"=> array("everything"=>true)
			)
		);

		if ( empty($this->roles) )
			return;

		foreach ($this->roles as $role => $data) {
			$this->role_objects[$role] = new WP_Role($role, $this->roles[$role]['capabilities']);
			$this->role_names[$role] = $this->roles[$role]['name'];
		}
	}

	function add_role($role, $display_name, $capabilities = '') {
		if ( isset($this->roles[$role]) )
			return;

		$this->roles[$role] = array(
			'name' => $display_name,
			'capabilities' => $capabilities);
		update_option($this->role_key, $this->roles);
		$this->role_objects[$role] = new WP_Role($role, $capabilities);
		$this->role_names[$role] = $display_name;
		return $this->role_objects[$role];
	}
	
	function remove_role($role) {
		if ( ! isset($this->role_objects[$role]) )
			return;
		
		unset($this->role_objects[$role]);
		unset($this->role_names[$role]);
		unset($this->roles[$role]);
		
		update_option($this->role_key, $this->roles);
	}

	function add_cap($role, $cap, $grant = true) {
		$this->roles[$role]['capabilities'][$cap] = $grant;
		update_option($this->role_key, $this->roles);
	}

	function remove_cap($role, $cap) {
		unset($this->roles[$role]['capabilities'][$cap]);
		update_option($this->role_key, $this->roles);
	}

	function &get_role($role) {
		if ( isset($this->role_objects[$role]) )
			return $this->role_objects[$role];
		else
			return null;
	}

	function get_names() {
		return $this->role_names;
	}

	function is_role($role)
	{
		return isset($this->role_names[$role]);
	}	
}

class WP_Role {
	var $name;
	var $capabilities;

	function WP_Role($role, $capabilities) {
		$this->name = $role;
		$this->capabilities = $capabilities;
	}

	function add_cap($cap, $grant = true) {
		global $wp_roles;

		$this->capabilities[$cap] = $grant;
		$wp_roles->add_cap($this->name, $cap, $grant);
	}

	function remove_cap($cap) {
		global $wp_roles;

		unset($this->capabilities[$cap]);
		$wp_roles->remove_cap($this->name, $cap);
	}

	function has_cap($cap) {
		$capabilities = apply_filters('role_has_cap', $this->capabilities, $cap, $this->name);
		if ( !empty($capabilities[$cap]) )
			return $capabilities[$cap];
		else
			return false;
	}

}

// REG FUNCTIONS

function username_exists( $username ) {
	global $db;
	$username = sanitize_user( $username );
	$user = get_userdatabylogin($username);
	if ( $user )
		return $user->ID;

	return null;
}

function wp_insert_user($userdata) {
	global $db;

	extract($userdata);

	// Are we updating or creating?
	if ( !empty($ID) ) {
		$update = true;
	} else {
		$update = false;
		// Password is not hashed when creating new user.
		$user_pass = md5($user_pass);
	}
	
	if ( empty($user_nicename) )
		$user_nicename = sanitize_title( $user_login );

	if ( empty($display_name) )
		$display_name = $user_login;
		
	if ( empty($nickname) )
		$nickname = $user_login;
			
	if ( empty($user_registered) )
		$user_registered = gmdate('Y-m-d H:i:s');

	if ( $update ) {
		$query = "UPDATE $db->users SET user_pass='$user_pass', user_email='$user_email', user_url='$user_url', user_nicename = '$user_nicename', display_name = '$display_name' WHERE ID = '$ID'";
		$query = apply_filters('update_user_query', $query);
		$db->query( $query );
		$user_id = $ID;
	} else {
		$query = "INSERT INTO $db->users 
		(user_login, user_pass, user_email, user_url, user_registered, user_nicename, display_name)
	VALUES 
		('$user_login', '$user_pass', '$user_email', '$user_url', '$user_registered', '$user_nicename', '$display_name')";
		$query = apply_filters('create_user_query', $query);
		$db->query( $query );
		$user_id = $db->insert_id;
	}
	
	update_usermeta( $user_id, 'first_name', $first_name);
	update_usermeta( $user_id, 'last_name', $last_name);
	update_usermeta( $user_id, 'nickname', $nickname );
	update_usermeta( $user_id, 'description', $description );
	update_usermeta( $user_id, 'jabber', $jabber );
	update_usermeta( $user_id, 'aim', $aim );
	update_usermeta( $user_id, 'yim', $yim );

	if ($update && !empty($role)) {
		$user = new WP_User($user_id);
		$user->set_role($role);
	}

	if ( !$update ) {
		$user = new WP_User($user_id);
//		$user->set_role('Administrator');
	}
	
	if ( $update )
		do_action('profile_update', $user_id);
	else
		do_action('user_register', $user_id);
		
	return $user_id;	
}

function wp_update_user($userdata) {
	global $db, $current_user;

	$ID = (int) $userdata['ID'];
	
	// First, get all of the original fields
	$user = get_userdata($ID);	

	// Escape data pulled from DB.
	$user = add_magic_quotes(get_object_vars($user));

	// If password is changing, hash it now.
	if ( ! empty($userdata['user_pass']) ) {
		$plaintext_pass = $userdata['user_pass'];
		$userdata['user_pass'] = md5($userdata['user_pass']);
	}

	// Merge old and new fields with new fields overwriting old ones.
	$userdata = array_merge($user, $userdata);
	$user_id = wp_insert_user($userdata);

	// Update the cookies if the password changed.	
	if( $current_user->id == $ID ) {
		if ( isset($plaintext_pass) ) {
			wp_clearcookie();
			wp_setcookie($userdata['user_login'], $plaintext_pass);
		}
	}
	
	return $user_id;
}

function wp_create_user( $username, $password, $email = '') {
	global $db;
	
	$user_login = $db->escape( $username );
	$user_email = $db->escape( $email );
	$user_pass = $password;

	$userdata = compact('user_login', 'user_email', 'user_pass');
	return wp_insert_user($userdata);
}


function create_user( $username, $password, $email ) {
	return wp_create_user( $username, $password, $email );	
}

if ( !function_exists('wp_new_user_notification') ) :
function wp_new_user_notification($user_id, $plaintext_pass = '') {
	global $admin_email;
	$user = new WP_User($user_id);
	
	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);
	
	$message  = __('New user registration on your blog ') . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";
	
	@wp_mail($admin_email, __('[%s] New User Registration'), $message);

	if ( empty($plaintext_pass) )
		return;

	$message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
	$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";

	wp_mail($user_email, __('Your username and password'), $message);
	
}
endif;
*/

?>
