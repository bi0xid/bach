<?php

// Update the tags for a thread

include( 'init.php' );

$thread_id = (int) $_POST['thread_id'];

$thread = get_thread( $thread_id );

if ( !$thread )
	die('Thread not found.');

$tags = $_POST['tags'];

update_tags( $thread_id, $tags );

header( 'Location: thread.php?updated=tags&t=' . $thread_id );

?>
