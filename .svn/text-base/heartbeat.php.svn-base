<?php

include('init.php');
auth_redirect();

$thread_id = $_GET['thread_id'];
$action = $_GET['action'];

do_action( 'heartbeat', $thread_id, $action, $current_user->ID );

// FIXME: this should record the activity somehow, eg to track and display which users are currently active in a thread

// this should return an array of others users (if any) who are currently doing stuff in the same thread
die ( json_encode( array() ) );
?>
