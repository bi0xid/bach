<?php

include( 'init.php' );

$tags = @trim($_POST['tags']);
$to_email = @trim($_POST['to_email']);
$to_name = @trim($_POST['to_name']);
$subject = @trim($_POST['subject']);
$message = @trim($_POST['message']);

$dt = gmdate( 'Y-m-d H:i:s' );

$hash = md5( $dt . $to_email . $message );

$hash = md5( $dt . $email . $message );

$db->query( $db->prepare("INSERT INTO $db->threads ( hash, dt, email, subject ) VALUES ( %s, %s, %s, %s )", $hash, $dt, $to_email, $subject ) );

$thread_id = $db->insert_id;

$db->query( $db->prepare("INSERT INTO $db->messages ( hash, thread_id, dt, email, content ) VALUES ( %s, %s, %s, %s, %s )", $hash, $thread_id, $dt, "support+$current_user->user_login@{$email_domain}", $message ) );

// Status stuff
$status = 'open';
if ( isset( $_POST['sendtickle'] ) )
	$status = 'tickle';
if ( isset( $_POST['sendclose'] ) )
	$status = 'closed';

if ( $tags )
	add_tags( $thread_id, $tags );

$count = $db->get_var( "SELECT COUNT(*) FROM $db->messages WHERE thread_id = $thread_id" );

$db->query( "UPDATE $db->threads SET messages = $count, state = '$status' WHERE thread_id = $thread_id" );

$name = "$current_user->first_name $current_user->last_name";

$reply = str_replace(array("\r\n", "\r"), "\n", $reply); // cross-platform newlines
$reply = preg_replace("/\n\n+/", "\n\n", $reply); // take care of duplicates

$reply .= "\n\n-- \n$name";

wp_mail( $to_email, $subject, $message,  "From: $name <$support_email>\nReply-to: $name <$support_email>\nMessage-Id: <$hash@{$email_domain}>"); 

if ( 'closed' == $status )
	header( 'Location: ' . $site_url );
else
	header( 'Location: thread.php?t=' . $thread_id );

?>
