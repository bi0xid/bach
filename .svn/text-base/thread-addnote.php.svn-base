<?php

include( 'init.php' );

$user_ids = array_filter( (array)$_POST['notify'] );
$note = @$_POST['note'];
$thread_id = @intval($_POST['thread_id']);

$thread = get_thread( $thread_id );

if ( !$thread )
	die('Thread not found.');

$dt = gmdate( 'Y-m-d H:i:s' );

$hash = md5( $dt . $thread->email . $note );

$name = $current_user->display_name;

$message = preg_replace("/\n\n+/", "\n\n", $note); // take care of duplicates

$message .= "\n\n-- \n$name";


if ( $tags ) {
	$current_tags = get_thread_tags( $thread_id );

	foreach ( $user_tags as $tag ) {
		$tag = trim( strtolower($tag) );
		if ( $tag = sanitize_title( $tag ) ) {
			if ( !in_array( $tag, $current_tags ) )
				$db->query( $db->prepare("INSERT INTO $db->tags ( thread_id, tag_slug ) VALUES ( %d, %s )", $thread_id, $tag) );
		}
	}
}

$db->query( $db->prepare("INSERT INTO $db->messages ( hash, thread_id, dt, email, content, message_type ) VALUES ( %s, %d, %s, %s, %s, 'note' )", $hash, $thread_id, $dt, "support+$current_user->user_login@{$email_domain}", $message ) );

update_message_count( $thread_id );

if ( 'closed' == $thread->status )
	header( 'Location: ' . $_POST['from'] );
else
	header( 'Location: thread.php?t=' . $thread_id );

?>
