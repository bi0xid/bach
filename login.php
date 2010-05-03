<?php

define( 'SP_IS_LOGIN', true );

include('init.php');
$action = @$_REQUEST['action'];
$error = '';

header('Content-Type: text/html; charset=utf-8');

switch($action) {

case 'logout':

	$wp_auth_object->clear_auth_cookie();
	nocache_headers();

	$redirect_to = "$site_url/" . 'login.php';
	if ( isset($_REQUEST['redirect_to']) )
		$redirect_to = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $_REQUEST['redirect_to']);
			
	wp_redirect($redirect_to);
	exit();

break;

case 'lostpassword':
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Helpdesk &raquo; <?php _e('Lost Password') ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript">
	function focusit() {
		// focus on first input field
		document.getElementById('user_login').focus();
	}
	window.onload = focusit;
	</script>
	<style type="text/css">
	#user_login, #email, #submit {
		font-size: 1.7em;
	}
	</style>
</head>
<body>
<div id="login">
<h1><a href="">Helpdesk</a></h1>
<p><?php _e('Please enter your information here. We will send you a new password.') ?></p>
<?php
if ($error)
	echo "<div id='login_error'>$error</div>";
?>

<form name="lostpass" action="login.php" method="post" id="lostpass">
<p>
<input type="hidden" name="action" value="retrievepassword" />
<label><?php _e('Username:') ?><br />
<input type="text" name="user_login" id="user_login" value="" size="20" tabindex="1" /></label></p>
<p><label><?php _e('E-mail:') ?><br />
<input type="text" name="email" id="email" value="" size="25" tabindex="2" /></label><br />
</p>
<p class="submit"><input type="submit" name="submit" id="submit" value="<?php _e('Retrieve Password'); ?> &raquo;" tabindex="3" /></p>
</form>

</div>
</body>
</html>
<?php
break;

case 'retrievepassword':
	$user_data = get_user( $_POST['user_login'] );
	// redefining user_login ensures we return the right case in the email
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;

	if (!$user_email || $user_email != $_POST['email'])
		die(sprintf(__('Sorry, that user does not seem to exist in our database. Perhaps you have the wrong username or e-mail address? <a href="%s">Try again</a>.'), 'login.php?action=lostpassword'));
	// Generate something random for a password... md5'ing current time with a rand salt
	$key = substr( md5( uniqid( microtime() ) ), 0, 50);
	// now insert the new pass md5'd into the db
 	$db->query("UPDATE $db->users SET user_activation_key = '$key' WHERE user_login = '$user_login'");
	$message = __('Someone has asked to reset the password for the following site and username.') . "\r\n\r\n";
	$message .= get_option('siteurl') . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= __('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.') . "\r\n\r\n";
	$message .= $site_url . "/login.php?action=resetpass&key=$key\r\n";

	$m = wp_mail($user_email, __('[Password Reset'), $message);

	if ($m == false) {
		 echo '<p>' . __('The e-mail could not be sent.') . "<br />\n";
         echo  __('Possible reason: your host may have disabled the mail() function...') . "</p>";
		die();
	} else {
		echo '<p>' .  sprintf(__("The e-mail was sent successfully to %s's e-mail address."), $user_login) . '<br />';
		echo  "<a href='login.php' title='" . __('Check your e-mail first, of course') . "'>" . __('Click here to login!') . '</a></p>';
		die();
	}

break;

case 'resetpass' :

	// Generate something random for a password... md5'ing current time with a rand salt
	$key = preg_replace('/a-z0-9/i', '', $_GET['key']);
	if ( empty($key) )
		die( __('Sorry, that key does not appear to be valid.') );
	$user = $db->get_row("SELECT * FROM $db->users WHERE user_activation_key = '$key'");
	if ( !$user )
		die( __('Sorry, that key does not appear to be valid.') );

	$new_pass = substr( md5( uniqid( microtime() ) ), 0, 7);
 	$db->query("UPDATE $db->users SET user_pass = MD5('$new_pass'), user_activation_key = '' WHERE user_login = '$user->user_login'");
	$message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
	$message .= sprintf(__('Password: %s'), $new_pass) . "\r\n";
	$message .= $site_url . "/login.php\r\n";

	$m = wp_mail($user->user_email, __('[%s] Your new password'), $message);

	if ($m == false) {
		echo '<p>' . __('The e-mail could not be sent.') . "<br />\n";
		echo  __('Possible reason: your host may have disabled the mail() function...') . '</p>';
		die();
	} else {
		echo '<p>' .  sprintf(__('Your new password is in the mail.'), $user_login) . '<br />';
        echo  "<a href='login.php' title='" . __('Check your e-mail first, of course') . "'>" . __('Click here to login!') . '</a></p>';
		// send a copy of password change notification to the admin
		$message = sprintf(__('Password Lost and Changed for user: %s'), $user->user_login) . "\r\n";
		die();
	}
break;

case 'login' : 
default:

	$user_login = '';
	$user_pass = '';
	$using_cookie = false;
	if ( !isset( $_REQUEST['redirect_to'] ) )
		$redirect_to = $site_url;
	else
		$redirect_to = $_REQUEST['redirect_to'];
	$redirect_to = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $redirect_to);

	if ( $_POST ) {
		$user_login = $_POST['log'];
		$user_login = sanitize_user( $user_login );
		$user_pass  = $_POST['pwd'];
		$rememberme = @$_POST['rememberme'];
	} elseif ( !empty($_COOKIE) ) {
		if ( !empty($_COOKIE[AUTH_COOKIE]) )
			$using_cookie = true;
	}

	if ( $user_login && $user_pass && wp_login($user_login, $user_pass) ) {
		$user = new WP_User( $user_login );
		$wp_auth_object->set_auth_cookie( $user->ID, $rememberme ? time() + 1209600 : 0, $rememberme ? time() + 1209600 : 0, 'auth' );
		$wp_auth_object->set_auth_cookie( $user->ID, $rememberme ? time() + 1209600 : 0, $rememberme ? time() + 1209600 : 0, 'logged_in' );
		wp_redirect($redirect_to);
		exit;
	}

	if ( $using_cookie )			
		$error = __('Your session has expired.');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Helpdesk &rsaquo; <?php _e('Login') ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript">
	function focusit() {
		document.getElementById('log').focus();
	}
	window.onload = focusit;
	</script>
</head>
<body>

<div id="login">
<h1><a href="">Helpdesk</a></h1>
<?php
if ( $error )
	echo "<div id='login_error'>$error</div>";
?>

<form name="loginform" id="loginform" action="login.php" method="post">
<p><label><?php _e('Username:') ?><br /><input type="text" name="log" id="log" value="<?php echo wp_specialchars(stripslashes($user_login), 1); ?>" size="20" tabindex="1" /></label></p>
<p><label><?php _e('Password:') ?><br /> <input type="password" name="pwd" id="pwd" value="" size="20" tabindex="2" /></label></p>
<p>
  <label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="3" /> 
  <?php _e('Remember me'); ?></label></p>
<p class="submit">
	<input type="submit" name="submit" id="submit" value="<?php _e('Login'); ?> &raquo;" tabindex="4" />
	<input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>" />
</p>
</form>

</div>

</body>
</html>
<?php

break;
} // end action switch
?>
