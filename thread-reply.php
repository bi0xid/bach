<?php

include( 'init.php' );

$thread_id = (int) $_POST['thread_id'];

$thread = get_thread( $thread_id );

if ( !$thread )
	die('Thread not found.');

$reply = $_POST['message_reply'];

$dt = gmdate( 'Y-m-d H:i:s' );

$hash = md5( $dt . $thread->email . $reply );

$reply_db = addslashes( $reply );

$db->query( $db->prepare("INSERT INTO $db->messages ( hash, thread_id, dt, email, from_user_id, content ) VALUES ( %s, %d, %s, %s, %d, %s )", $hash, $thread_id, $dt, "support+{$current_user->user_login}@{$email_domain}", $current_user->ID, $reply) );

// Status stuff
$status = 'open';
if ( isset( $_POST['sendtickle'] ) )
	$status = 'tickle';
if ( isset( $_POST['sendclose'] ) )
	$status = 'closed';

$tags = @trim($_POST['tags']);
if ( $tags ) {
	add_tags( $thread_id, $tags );
}

$count = $db->get_var( "SELECT COUNT(*) FROM $db->messages WHERE thread_id = $thread_id" );

$db->query( "UPDATE $db->threads SET messages = $count, state = '$status' WHERE thread_id = $thread_id" );

$name = "$current_user->first_name $current_user->last_name";
if ( !trim($name) )
	$name = $current_user->user_login;

$reply = str_replace(array("\r\n", "\r"), "\n", $reply); // cross-platform newlines
$reply = preg_replace("/\n\n+/", "\n\n", $reply); // take care of duplicates

$reply .= "\n\n-- \n$name";

wp_mail( $thread->email, 'Re: ' . $thread->subject, $reply,  "From: $name <$support_email>\nReply-to: $support_email\nMessage-Id: <$hash@{$email_domain}>"); 

if ( 'closed' == $status )
	header( 'Location: ' . $_POST['from'] );
else
	header( 'Location: thread.php?t=' . $thread_id );

?>
