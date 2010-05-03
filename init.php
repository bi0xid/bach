<?php

if ( !empty($_GET['queries']) )
	define('SAVEQUERIES', true);
require_once( 'includes/misc.php' );
time_since(true);

if ( !@include_once( 'config.php' ) )
	define('IS_INSTALLING', true);
require_once( 'includes/constants.php' );

if ( !defined('ABSPATH') )
	define( 'ABSPATH', dirname(__FILE__) );
if ( !defined('BACKPRESS_PATH') )
	define( 'BACKPRESS_PATH', ABSPATH . '/includes/backpress/' );

require_once( 'includes/wp-functions.php' );
require_once( 'includes/backpress/functions.compat.php' );
require_once( 'includes/backpress/functions.formatting.php' );
require_once( 'includes/backpress/functions.core.php' );
require_once( 'includes/backpress/functions.kses.php' );
require_once( 'includes/backpress/functions.plugin-api.php' );

require_once( 'includes/backpress/class.wp-error.php' );
require_once( 'includes/backpress/class.bpdb-multi.php' );

if ( defined('DB_HOST') ) {
	$db = new BPDB_Multi( array(
		'name' => DB_NAME,
		'host' => DB_HOST,
		'user' => DB_USER,
		'password' => DB_PASSWORD,
		'errors' => 'suppress',
	) );
	
	
	$db->set_prefix($table_prefix);
	// standard prefix for SupportPress tables
	$db->set_prefix($table_prefix, array('users', 'usermeta', 'threads', 'messages', 'tags', 'predefined_messages') );
	// use wp_ for user tables
	if ( isset($user_table_prefix) )
		$db->set_prefix( $user_table_prefix, array('users', 'usermeta') );

	// optional separate config for user db
	if ( defined('USER_DB_HOST') ) {
		$db->add_db_server( 'user', array(
			'name' => USER_DB_NAME,
			'host' => USER_DB_HOST,
			'user' => USER_DB_USER,
			'password' => USER_DB_PASSWORD,
			'errors' => 'suppress',
		));
		
		// these two tables are in the user db
		$db->add_db_table( 'user', $db->users );
		$db->add_db_table( 'user', $db->usermeta );
	}
}

require_once( 'includes/backpress/functions.bp-options.php' );
require_once( 'includes/class.bp-options.php' );
require_once( 'includes/backpress/class.wp-object-cache.php' );
require_once( 'includes/backpress/functions.wp-object-cache.php' );
require_once( 'includes/backpress/class.wp-pass.php' );
require_once( 'includes/backpress/class.bp-roles.php' );
require_once( 'includes/backpress/class.bp-user.php' );
require_once( 'includes/backpress/class.wp-users.php' );
require_once( 'includes/backpress/class.wp-auth.php' );
require_once( 'includes/wp-user.php' );

require_once( 'includes/crud.php' );
require_once( 'includes/form.php' );
require_once( 'includes/support-functions.php' );
require_once( 'includes/upgrade.php' );

// Upgrade the db?
if ( !defined('IS_INSTALLING') )
	sp_upgrade();

wp_cache_init();

// the installer kicks off here, so anything needed for installation has to happen above
if ( defined('IS_INSTALLING') && IS_INSTALLING )
	require_once('installer.php');

add_filter( 'sanitize_title', 'sanitize_title_with_dashes' );

// default roles
create_role( 'supportpressadmin', 'Administrator' );
create_role( 'supporter', 'Supporter' );

class BP_Options extends BP_Options_Stub {
}

// auth functions die if this isn't set
backpress_add_option( 'hash_function_name', 'sp_hash' );


function __( $str ) {
	return $str;
}

function _e( $str ) {
	echo $str;
}

function sp_hash( $s ) {
	return WP_Pass::hash_password( $s );
}


if ( !defined('AUTH_COOKIE') )
	define('AUTH_COOKIE', 'sp');
if ( !defined('LOGGED_IN_COOKIE') )
	define('LOGGED_IN_COOKIE', 'sp_logged_in');
if ( !defined('COOKIEPATH') )
	define('COOKIEPATH', '/' );
if ( !defined('COOKIE_DOMAIN') )
	define('COOKIE_DOMAIN', false);

if ( !defined( 'WP_AUTH_COOKIE_VERSION' ) )
	define( 'WP_AUTH_COOKIE_VERSION', 1 ); // change to 2 for wp 2.8

$wp_users_object = new WP_Users( $db );

$cookies['auth'][] = array(
	'domain' => COOKIE_DOMAIN,
	'path' => COOKIEPATH,
	'name' => AUTH_COOKIE
);
$cookies['logged_in'][] = array(
	'domain' => COOKIE_DOMAIN,
	'path' => COOKIEPATH,
	'name' => LOGGED_IN_COOKIE
);

$wp_auth_object = new WP_Auth( $db, $wp_users_object, $cookies );

$current_user = $wp_auth_object->get_current_user();

if ( !@constant( 'SP_IS_LOGIN' ) && ( !$current_user || (!$current_user->has_cap( 'supporter' ) && !$current_user->has_cap('supportpressadmin')) ) ) {
	$path = $_SERVER['REQUEST_URI'];
	wp_redirect("$site_url/login.php?redirect_to=" . urlencode($site_domain . $path ) );
	exit();
}

if ( !is_ssl() && substr($site_url, 0, 5) == 'https' ) {
	wp_redirect( $site_url );
	exit;
}

// It's safe to show errors now that we're logged in
$db->show_errors();

require_once('includes/plugin.php');
sp_load_plugins('plugins/');
