<?php

function get_support_user_ids() {
	global $db;

	$user_ids = wp_cache_get('support_user_ids', 'supportpress');
	if ( $user_ids !== false )
		return $user_ids;

	$user_ids = $db->get_col("SELECT user_id FROM {$db->usermeta} WHERE meta_key='{$db->prefix}capabilities'");
	wp_cache_set('support_user_ids', $user_ids, 'supportpress');
	return $user_ids;
}

function update_message_count($thread_id) {
	global $db;

	$count = $db->get_var( $db->prepare("SELECT count(*) FROM $db->messages WHERE thread_id = %d", $thread_id) );
	$db->query( $db->prepare("UPDATE $db->threads SET messages = %d WHERE thread_id = %d", $count, $thread_id) );
}

// return an absolute URL for viewing the given thread.
// this doesn't verify that $thread_id exists, merely that it is a number.
function get_thread_url($thread_id) {
	global $site_url;

	if ( $id = intval($thread_id) )
		return $site_url . '/thread.php?t=' . $id;

	return false;
}

?>
