<?php

$js = '<script src="js/thread.js" type="text/javascript"></script>';

?>
<?php 
include( 'header.php' );

$tags = @trim($_POST['tags']);
$to_email = @trim($_POST['to_email']);
$to_name = @trim($_POST['to_name']);
$subject = @trim($_POST['subject']);
$message = @trim($_POST['message']);

if ( empty($_POST) ) {
	// defaults for a blank form
	$tags = empty($tags) ? 'support' : '';
	$subject = empty($subject) ? 'Support' : '';
}

?>
<h2>Create a new support thread</h2>

<div class='message'>
<form action="thread-create.php" method="post">

<p>Send to email address:<br />
<input type="text" size="100" name="to_email" value="<?php echo htmlspecialchars($to_email); ?>" /></p>
<p>Subject:<br />
<input type="text" size="100" name="subject" value="<?php echo htmlspecialchars($subject); ?>" class="title reply replying" /></p>

<select class="predefined_message">
<option value="">Predefined Reply</option>
<?php
	$predefined = get_predefined_names();
	foreach ( $predefined as $id => $name )
		echo html_message('<option value="%d">%s</option>', $id, $name);
?>
</select>
<textarea name="message" class="widetext reply replying" id="message" style="height: 20em;">
<?php
echo htmlspecialchars($message);
?>
</textarea></p>
<p>Tags: 
<input type="text" size="50" name="tags" value="<?php echo htmlspecialchars($tags); ?>"  class="tag padme reply replying" /> 
</p>
<p class="submit">

<input type="submit" name="submit" value="Send" />
<input type="submit" name="sendtickle" value="Send and Tickle" />
<input type="submit" name="sendclose" value="Send and Close" />
<input type="hidden" name="from" value="<?php echo htmlspecialchars( $_SERVER['HTTP_REFERER'] ); ?>" />
</p>
</form>

</div>

<?php include( 'footer.php' ); ?>
