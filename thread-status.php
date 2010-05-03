<?php

// Update the status for a thread

include( 'init.php' );

$thread_id = (int) $_POST['thread_id'];

$thread = get_thread( $thread_id );

if ( !$thread )
	die('Thread not found.');

$status = $_POST['status'];

$status = preg_replace( '|[^a-z]|', '', $status );

$db->query( "UPDATE $db->threads SET state = '$status' WHERE thread_id = '$thread_id'" );

if ( isset( $_POST['from'] ) )
	header( 'Location: ' . $_POST['from'] );
else
	header( 'Location: thread.php?updated=status&t=' . $thread_id );

?>