<?php

// Update the tags for a thread

include( 'init.php' );

$thread_id = (int) $_POST['thread_id'];

$thread = get_thread( $thread_id );

if ( !$thread )
	die('Thread not found.');

$db->query( "DELETE FROM $db->tags WHERE thread_id = $thread_id" );
$db->query( "DELETE FROM $db->messages WHERE thread_id = $thread_id" );
$db->query( "DELETE FROM $db->threads WHERE thread_id = $thread_id" );

if ( isset( $_POST['from'] ) )
	header( 'Location: ' . $_POST['from'] );

?>