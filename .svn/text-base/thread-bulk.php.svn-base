<?php

// Update the tags for a thread

include( 'init.php' );

$thread_ids = array_filter($_POST['thread_ids']);

if ( $_POST['status-close'] ) {
	foreach ( $thread_ids as $thread_id ) {
		$r = $db->query( $db->prepare("UPDATE $db->threads SET state = 'closed' WHERE thread_id = %d", $thread_id) );
	}
}

if ( isset( $_POST['from'] ) )
	header( 'Location: ' . $_POST['from'] );
else
	header( 'Location: ' . $_SERVER['HTTP_REFERER'] );

?>
