<?php

$js = '<script src="js/thread.js" type="text/javascript"></script>';

?>
<?php 
include( 'header.php' );
include( 'includes/mime.php' );
$thread_id = (int) $_GET['t'];
$thread = get_thread( $thread_id );
if ( ! $thread )
	die( 'Thread not found.' );
?>

<h2><?php echo mime_header_decode($thread->subject); ?></h2>

<form action="thread-tags.php" method="post" id="newtags" class="<?php if ( isset( $_GET['updated'] ) && 'tags' == $_GET['updated'] ) echo "fade"; ?>">
<p>Tags: 
<input type="text" size="50" name="tags" value="<?php echo thread_tags_form( $thread->thread_id ); ?>"  class="padme" /> 
<input type="submit" value="Update &raquo;" name="submit" />
<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" />
</p>
</form>

<form action="thread-status.php" method="post" id="threadstatus" class="<?php if ( isset( $_GET['updated'] ) && 'status' == $_GET['updated'] ) echo "fade"; ?>">
<p>Thread status: 
<select name="status">
	<option value="open" <?php if ( $thread->state == 'open' ) echo " selected='selected'"; ?>>Open</option>
	<option value="tickle" <?php if ( $thread->state == 'tickle' ) echo " selected='selected'"; ?>>Tickle me later</option>
	<option value="closed" <?php if ( $thread->state == 'closed' ) echo " selected='selected'"; ?>>Closed</option>
</select>
<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" />
<input type="submit" name="submit" value="Update &raquo;" /></p>
</form>

<form action="thread-notify.php" method="post">
<p>Notify:
<?php
if ( $user_ids = get_support_user_ids() ) {
	foreach ( $user_ids as $user_id ) {
		$user = get_user($user_id);
		echo '<label><input type="checkbox" name="notify[]" class="notify-checkbox" value="'.htmlspecialchars($user_id).'" />'.htmlspecialchars($user->nickname).'</label> ';
	}
}
?>
<div class="notify-toggle" style="display:none;">
<label for="notify-message">Message (seen by staff only):</label>
<textarea class="notify-message" name="notify-message" id="notify-message" style="width:100%;height:6em;"></textarea>
<input type="submit" name="notify-send" value="Notify &raquo;" style="float:right;" />
<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" />
</div>
</p>
<br style="clear:both;" />
</form>

<?php


$messages = $db->get_results( "SELECT * FROM $db->messages WHERE thread_id = $thread_id" );

do_action('thread-above', $thread_id, $messages);

$i = 0;
foreach ( $messages as $m ) {
	$i++;

	do_action('message-above', $m->message_id, $m);

	if ( !empty($_GET['qqq']) )
		var_dump(htmlspecialchars($m->content));
	if ( $parts = mime_split($m->content) ) {
		$part = find_first_part($parts, 'text/plain');
		$message_text = trim($part->content);
		// some autoresponders include a blank text/plain part
		if ( !$message_text ) {
			$part = find_first_part($parts, 'text/html');
			$message_text = trim( strip_tags($part->content) );
		}

	} else {
		$message_text = trim($m->content);
	}

	$html_message = htmlspecialchars( $message_text );
	$html_message = preg_replace( "|(-- \n.*)$|s", '<span class="sig">$1</span>', $html_message );
	$html_message = nl2br( $html_message );
	$html_message = make_clickable( $html_message );
	$html_message = preg_replace('|href="(http://[^"]+)"|', 'href="http://hiderefer.com/?$1"', $html_message);

	$meat = htmlspecialchars(message_meat( $message_text ));
	if ( $i == 1 ) {
		echo "<input class='lastclicked' value='$m->message_id' type='hidden' />";
		$the_user = $db->get_row("SELECT * FROM $db->users WHERE user_email='$m->email' LIMIT 1");
		if ( $the_user )
			echo "<input class='user_id' value='{$the_user->ID}' type='hidden' />";
	}

	if ( $m->from_user_id ) {
		$u = get_user($m->from_user_id);
		$avatar = get_avatar( $u->user_email, 48 );
		$staff_reply = "Staff reply by {$u->display_name}<br />";
	} else {
		$avatar = get_avatar( $m->email, 48 );
		$staff_reply = '';
	}

	$in = count($messages) - $i;

	$permalink = add_query_arg('message', $m->message_id).'#m'.$m->message_id;
	$plinked = ( @$_GET['message'] == $m->message_id ? ' highlight' : '' );

	echo "
<div class='message$plinked $m->message_type n$i n-$in' id='m$m->message_id'>
<p class='avatar message-toggle'>$avatar</p>
<p class='wrote message-toggle'>{$staff_reply}<a href='index.php?email=$m->email' class='email'>$m->email</a> wrote on <a href='$permalink'>$m->dt</a>: <span class='meat'>".htmlspecialchars($meat)." &hellip;</span></p>
<div class='mainpart'>
<p class='content' id='c$m->message_id'>$html_message</p>";

if ( $m->message_type == 'support' ) {
	echo "<p class='action'><input type='button' class='inlinereply' value='Reply' name='ir$m->message_id' /></p>";
}
?>

<form action="thread-reply.php" method="post" class="inlinereplyform" style="display: none;" id="<?php echo "ir$m->message_id"; ?>">
<p>
<?php
if ( $parts = mime_split($m->content) ) {
	$part = find_first_part($parts, 'text/plain');
	$reply_message = $part->content;
}
if ( !$reply_message )
	$reply_message = trim( $m->content );
$reply_message = htmlspecialchars( $reply_message );
$reply_message = preg_replace( "|(-- \n.*)$|s", '', $reply_message ); // strip the sig
$reply_message = wordwrap( $reply_message, 74, "\n" );
$reply_message = trim( $reply_message );
$reply_message = preg_replace( "/(\A|\n)/", "$1> ", $reply_message );
$reply_message = $reply_message . "\n\n";
?>
<select class="predefined_message">
<option value="">Predefined Reply</option>
<?php
	$predefined = get_predefined_names();
	foreach ( $predefined as $id => $name )
		echo html_message('<option value="%d">%s</option>', $id, $name);
?>
</select>
<label>Reply to: <strong class="reply-to"><?php echo htmlspecialchars( $thread->email ); ?></strong>
<textarea name="message_reply" class="widetext reply" style="height: <?php echo substr_count( $reply_message, "\n" ) + 8; ?>em ">
<?php
echo "$m->email wrote:\n";
echo $reply_message;
?>
</textarea></label></p>
<p class="addtags">
<label>Add tags:
<input type="text" size="50" name="tags" value=""  class="tag padme reply" /></label>
</p>
<p class="submit">
<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" />

<input type="submit" name="submit" value="Send" />
<input type="submit" name="sendtickle" value="Send and Tickle" />
<input type="submit" name="sendclose" value="Send and Close" />
<input type="hidden" name="from" value="<?php echo htmlspecialchars( $_SERVER['HTTP_REFERER'] ); ?>" />
</p>
</form>

<?php 

if ( $parts = mime_split($m->content) ) {
	foreach ( $parts as $pi => $part ) {
		echo '<div class="hidepart">';
		if ( $part->is_type('image') ) {
			echo '<img src="message-image.php?message_id='.intval($m->message_id).'&amp;i='.intval($pi).'" />';
		} elseif ( $pi > 0 && $part->is_type('text/plain') ) {
			echo '<pre>'; echo htmlspecialchars($part->content); echo '</pre>';
		} else {
			echo '<p>MIME part: <a href="message-attachment.php?message_id='.intval($m->message_id).'&amp;i='.intval($pi).'" target="_blank">'.htmlspecialchars($part->content_type).'</a></p>';
		}
		echo '</div>';
	}
}

echo '</div>'; // mainpart
echo '</div>'; // message

do_action('message-below', $m->message_id, $m);
}

?>
<input type="submit" name="note-button" class="note-button" value="Add Note" />
<form method="post" action="thread-addnote.php" class="note-toggle">
<textarea name="note" class="widetext" style="height: 12em;"></textarea>
<p class="submit">
<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" />

<input type="submit" name="submit" value="Add Note" />
<input type="submit" name="sendtickle" value="Add Note and Tickle" />
<input type="submit" name="sendclose" value="Add Note and Close" />
<input type="hidden" name="from" value="<?php echo htmlspecialchars( $_SERVER['HTTP_REFERER'] ); ?>" />
</p>
</form>

<?php

do_action('thread-below', $thread_id, $messages);

?>

<form action="thread-delete.php" method="post">
<p>
<input type="hidden" name="from" value="<?php echo htmlspecialchars( $_SERVER['HTTP_REFERER'] ); ?>" />
<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" />
<input type="submit" name="delete" value="Delete Thread &raquo;" />
</p>
</form>

<form action="thread-status.php" method="post" id="threadstatus" class="<?php if ( isset( $_GET['updated'] ) && 'status' == $_GET['updated'] ) echo "fade"; ?>">
<p>
<input type="hidden" name="status" value="closed" />
<input type="hidden" name="from" value="<?php echo htmlspecialchars( $_SERVER['HTTP_REFERER'] ); ?>" />
<input type="submit" name="submit" value="Close Thread" /></p>
<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" />
</p>
</form>


<?php include( 'footer.php' ); ?>
