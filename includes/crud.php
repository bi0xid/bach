<?php

function get_thread( $id ) {
	global $db;
	$id = (int) $id;
	return $db->get_row( "SELECT * FROM $db->threads WHERE thread_id = $id" );
}

function thread_tags( $id ) {
	$id = (int) $id;
	$tags = get_thread_tags( $id );
	if ( !$tags )
		return;

	$out = array();
	foreach ( $tags as $tag )
		$out[] = "<a href='index.php?tag=$tag'>$tag</a>";

	$out = join( ', ', $out );
	return $out;
}

function thread_tags_form( $id ) {
	$id = (int) $id;
	$tags = get_thread_tags( $id );
	if ( !$tags )
		return;

	$out = join( ', ', $tags );
	return $out;
}

function get_thread_tags( $id ) {
	global $db;
	
	$tags = $db->get_col( "SELECT tag_slug FROM $db->tags WHERE thread_id = $id" );
	return $tags;
}

function add_tags( $id, $str ) {
	global $db;

	$id = (int) $id;

	$current_tags = get_thread_tags( $id );

	$new_tags = explode(',', $str);
	$clean_new = array();
	foreach ( $new_tags as $tag ) {
		$tag = trim( $tag );
		if ( !$tag = sanitize_title( $tag ) )
			die('funky');
		if ( !in_array( $tag, $current_tags ) )
			$db->query( "INSERT INTO $db->tags ( thread_id, tag_slug ) VALUES ( $id, '$tag' )" );
		$clean_new[] = $tag;
	}
}

function update_tags( $id, $str ) {
	global $db;

	$id = (int) $id;

	$current_tags = get_thread_tags( $id );

	$new_tags = explode(',', $str);
	$clean_new = array();
	foreach ( $new_tags as $tag ) {
		$tag = trim( $tag );
		if ( !$tag = sanitize_title( $tag ) )
			die('funky');
		if ( !in_array( $tag, $current_tags ) )
			$db->query( "INSERT INTO $db->tags ( thread_id, tag_slug ) VALUES ( $id, '$tag' )" );
		$clean_new[] = $tag;
	}

	// Axe what was removed
	$removed = array_diff( $current_tags, $clean_new );
	foreach ( $removed as $tag )
		$db->query( "DELETE FROM $db->tags WHERE thread_id = $id AND tag_slug = '$tag'" );
}

function message_meat( $m ) {
	$lines = explode( "\n", $m );

	$no_quotes = '';
	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( empty( $line ) )
			continue;
		if ( $line{0} == '>' )
			continue;
		if ( strpos( $line, 'wrote:' ) )
			continue;
		$no_quotes .= "$line ";
	}
	$no_quotes = wordwrap( $no_quotes, 100, "---split---" );
	$no_quotes = explode( '---split---', $no_quotes );
	return $no_quotes[0];
}

function get_predefined_names( ) {	
	global $db;

	$rows = $db->get_results( "SELECT id, name FROM $db->predefined_messages" );
	$out = array();
	foreach ( (array)$rows as $row )
		$out[ $row->id ] = $row->name;
	return $out;
}

// add or update a predefined message. will update if $id is set, insert otherwise.
// returns the ID of the message or false on error.
function store_predefined_message( $name, $message, $tags, $id=null ) {
	global $db;
	if ( $id > 0 ) {
		if ( $db->query( $db->prepare("UPDATE $db->predefined_messages SET name=%s, message=%s, tag=%s WHERE id=%d", $name, $message, $tags, $id) ) )
			return $id;
		else
			return false;
	} else {
		if ( $db->query( $db->prepare("INSERT INTO $db->predefined_messages (name, message, tag) VALUES (%s, %s, %s)", $name, $message, $tags) ) )
			return $db->insert_id;
		else
			return false;
	}
}

?>
