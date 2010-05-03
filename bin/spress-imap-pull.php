#!/usr/local/bin/php -d display_errors=true
<?php

define( 'SP_DIR', dirname( dirname(__FILE__) ) );
require_once( SP_DIR . '/config.php' );


require_once( SP_DIR . '/includes/wp-functions.php' );
require_once( SP_DIR . '/includes/backpress/functions.compat.php' );
require_once( SP_DIR . '/includes/backpress/functions.formatting.php' );
require_once( SP_DIR . '/includes/backpress/functions.core.php' );
require_once( SP_DIR . '/includes/backpress/functions.kses.php' );
require_once( SP_DIR . '/includes/backpress/functions.plugin-api.php' );

require_once( SP_DIR . '/includes/backpress/class.bpdb-multi.php' );

if ( defined('DB_HOST') ) {
	$db = new BPDB_Multi( array(
		'name' => DB_NAME,
		'host' => DB_HOST,
		'user' => DB_USER,
		'password' => DB_PASSWORD,
		'errors' => 'suppress',
	) );
	// standard prefix for SupportPress tables
	$db->set_prefix($table_prefix, array('users', 'usermeta', 'threads', 'messages', 'tags', 'predefined_messages') );
	// use wp_ for user tables
	if ( isset($user_table_prefix) )
		$db->set_prefix( $user_table_prefix, array('users', 'usermeta') );
}

if ( !defined('IMAP_PORT') )
	define('IMAP_PORT', '143');
	
define( 'IMAP_ACCOUNT_STRING', '{'.IMAP_HOST.':'.IMAP_PORT.'/imap/ssl/novalidate-cert}' . IMAP_MAILBOX );

$mbox = imap_open( IMAP_ACCOUNT_STRING, IMAP_USER, IMAP_PASSWORD );
if ( !$mbox )
		die( "COULD NOT OPEN MAILBOX!\r\n" );
		
$boxinfo = imap_check( $mbox );

if ( !is_object( $boxinfo ) || ! isset( $boxinfo->Nmsgs ) )
		die( "COULD NOT GET MAILBOX INFO\r\n" );

if ( $boxinfo->Driver != 'imap' )
		die( "THIS SCRIPT HAS ONLY BEEN TESTED WITH IMAP MAILBOXES\r\n" );

if ( $boxinfo->Nmsgs < 1 )
		die( "NO NEW MESSAGES\r\n" );
		
echo "Fetching {$boxinfo->Mailbox}\r\n";
echo "{$boxinfo->Nmsgs} messages, {$boxinfo->Recent} recent\r\n";

// Helps clean up the mailbox, especially if a desktop client is interracting with it also
imap_expunge( $mbox );

foreach( (array)imap_sort( $mbox, SORTARRIVAL, 1 ) as $message_id ) {
		// Detect a disconnect...
		if ( !imap_ping( $mbox ) )
				die( "REMOTE IMAP SERVER HAS GONE AWAY\r\n" );
		// Fetch this message, fix the line endings, this makes a 1:1 copy of the message in ram
		// that, in my tests, matches the filesystem copy of the message on the imap server 
		// ( tested with debian / postfix / dovecot )
		$r = processmail( 
				str_replace( 
						"\r\n",
						"\n",
						imap_fetchheader( $mbox, $message_id, FT_INTERNAL ) . imap_body( $mbox, $message_id )
				)
		);
		// stop when we reach the first duplicate
		if ( !$r )
			break;
		echo '.';
		imap_setflag_full( $mbox, $message_id, '\SEEN' );
		continue;
		#imap_delete( $mbox, $message_id );
}
echo "\r\n";

#imap_expunge( $mbox );

function processmail( $email ) {
		global $db;
		if ( trim( substr( $email, 0, 100 ) ) == '' )
				die( "GOT EMPTY MESSAGE?!\r\n" );
		$emaillines = explode("\n", $email);
		$is_message = $spam = false;
		$headers = $other_headers = $message = '';
		$subject = $date = $from = $address = $name = $spamhits = $message_hash = '';
		foreach ($emaillines as $line) {
				if ( !$is_message ) {
								$headers .= "$line\n";
				} else {
								$message .= "$line\n";
								continue;
				}
				if(preg_match('/^Subject: (.*)/i', $line, $data) ) {
								$subject = $data[1];
								continue;
				} elseif ( preg_match('/^Date: (.*)/i', $line, $data) ) {
								$date = $data[1];
								continue;
				} elseif ( preg_match('/^From: (.*)/i', $line, $data) ) {
								$from = $data[1];
								preg_match( "/([a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,})/i" , $from, $data );
								$address = $data[1];
								$name = trim( preg_replace( '|(.+)<.*>|', '$1', $from ) );
								$name = trim( $name, '"' );
								continue;
				} elseif ( preg_match('/^X-Spam-Status: .*? hits=([^ ]*)/i', $line, $data) ) {
								$spamhits = $data[1];
								$other_headers .= $line . "\n";
				} elseif ( preg_match('/^In-Reply-To: <([a-z0-9]{32})/i', $line, $data) ) {
								$message_hash = $data[1];
								$other_headers .= $line . "\n";
				} elseif ( preg_match('/^X-Spam-Flag: YES/i', $line, $data) ) {
								$spam = true;
				} else {
								$other_headers .= $line . "\n";
				}
				if ('' == $line) { // first blank line
								$is_message = true;
				}
		}

		$email_length = strlen($email);
		$real_date = gmdate('Y-m-d H:i:s', time() - ( 7 * 3600 ));
		$claimed_date = gmdate( 'Y-m-d H:i:s', strtotime($date) );
		$today = gmdate( 'Y-m-d', time() - ( 7 * 3600 ) );

		$subject = addslashes($subject);
		$other_headers = addslashes($other_headers);
		$db_message = trim( addslashes($message) );
		$sender = addslashes($from);

		$name = addslashes( $name );
		$address = addslashes( $address );

		$dt = gmdate( 'Y-m-d H:i:s' );

		// $claimed_date is used in the hash to prevent duplicates. $dt is stored in the table as the received date.
		$hash = md5( $claimed_date . $email . $message );

		// First we need to find the thread
		$thread_id = 0;
		if ( $message_hash ) {
				$row = $db->get_row( $db->prepare("SELECT * FROM $db->messages WHERE hash = %s", $message_hash) );
				if ( $row )
						$thread_id = $row->thread_id;
		}

		if ( !$thread_id ) {
				$clean_sub = preg_replace( '#\b[a-z]{2,3}:\s*#i', '', $subject );
				$row = $db->get_row( $db->prepare("SELECT * FROM $db->threads WHERE email = %s AND subject LIKE (%s) ORDER BY dt DESC", $address, '%' . $clean_sub . '%') );
				if ( $row )
						$thread_id = $row->thread_id;
		}

		if ( !$thread_id ) { 
				// we can't find it, so make a new thread
				$result = $db->query( $db->prepare("INSERT INTO $db->threads ( hash, dt, email, subject ) VALUES ( %s, %s, %s, %s )", $hash, $dt, $address, $subject ) );
				$thread_id = $db->insert_id;
		}

		// Message already exists?
		if ( $db->get_var( $db->prepare("SELECT message_id FROM $db->messages WHERE hash = %s", $hash) ) )
			return false;

		if ( $db->query( $db->prepare("INSERT INTO $db->messages ( hash, thread_id, dt, email, content ) VALUES ( %s, %d, %s, %s, %s )", $hash, $thread_id, $dt, $address, $message ) ) ) {
			$count = $db->get_var( $db->prepare("SELECT count(*) FROM $db->messages WHERE thread_id = %d", $thread_id) );
			$db->query( $db->prepare("UPDATE $db->threads SET messages = %d, state = 'open' WHERE thread_id = %d", $count, $thread_id) );
			return true;
		}
}