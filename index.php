<?php

$js = "
<script type='text/javascript'>
$(document).ready(function(){
$('.mcheck').change( function() { 
	if( this.checked )  {
		$('#tr' + this.value + '').addClass('highlight');
	} else {
		$('#tr' + this.value + '').removeClass('highlight');
	}
} );

$('.mcheck').each( function() { 
	if( this.checked ) $('#tr' + this.value + '').toggleClass('highlight'); 
} );

$('#checkall').click( function() { $('.mcheck').each( function(){
if ( this.checked ) {
	$('#' + this.id).removeAttr( 'checked' );
} else { $('#' + this.id).attr( 'checked', 'checked' ); }
$('#' + this.id).change();
}) } );

});
</script>
";

?>
<?php include( 'header.php' ); ?>
<?php include_once( 'includes/mime.php' ); ?>

<?php
if ( isset( $_GET['status'] ) )
	$status = preg_replace( '|[^a-z]|', '', $_GET['status'] );
else
	$status = 'open';
?>

<p>Status: <?php echo $status; ?></p>
<form action="thread-bulk.php" method="post">
<table cellpadding="10">
<tr class="tablehead">
<th><input type="checkbox" name="checkall" value="none" id="checkall" /></th>
<th>From</th>
<th>Subject</th>
<th>Tags</th>
<th>Age</th>
<th>#</th>
</tr>
<?php

if ( isset( $_GET['apage'] ) )
	$page = (int) $_GET['apage'];
else
	$page = 1;
$start = $offset = ( $page - 1 ) * 20;

$where = ' WHERE 1 = 1 ';

if ( isset( $_GET['tag'] ) ) {
	$tag = sanitize_title( $_GET['tag'] );
	$ids = $db->get_col( "SELECT thread_id FROM $db->tags WHERE tag_slug = '$tag'" );
	$ids = join( ', ', $ids );
	$where .= " AND $db->threads.thread_id IN ( $ids ) ";
}

if ( isset($_GET['email']) ) {
	$where .= $db->prepare(" AND $db->threads.email = %s ", $_GET['email']);
}


if ( $status && $status != 'all' ) {
	$where .= " AND state = '$status' "; 
}

if ( isset( $_GET['todo'] ) ) {
	if ( !empty( $_GET['subject'] ) ) {
		$search = addslashes( $_GET['subject'] );
		$where .= " AND subject LIKE ('%$search%') ";
	}

	if ( !empty( $_GET['sender'] ) ) {
		$search = addslashes( $_GET['sender'] );
		$where .= " AND $db->messages.email LIKE ('%$search%') ";
	}

	if ( !empty( $_GET['q'] ) )
		$where .= $db->prepare(" AND ( $db->messages.email RLIKE %s OR $db->messages.content RLIKE %s )", $_GET['q'], $_GET['q']);
}

#$recent = $db->get_results( "SELECT * FROM support_threads $where ORDER BY dt DESC LIMIT $offset, 20" );
// this query is uuugly but it works - it's called a group-wise maximum query.
// it finds the threads with the highest message_ids (i.e. the threads that have most recently been replied to)
$query= "FROM $db->threads INNER JOIN $db->messages ON $db->messages.thread_id=$db->threads.thread_id $where AND $db->messages.message_id=(SELECT MAX(m2.message_id) FROM $db->messages m2 WHERE m2.thread_id=$db->threads.thread_id) ORDER BY $db->messages.dt DESC";
$recent = $db->get_results( "SELECT *, $db->threads.email as t_email $query LIMIT $offset, 20" );
if ( !empty($db->last_error) )
	var_dump($db->last_error, $db->last_query);
$i = 0;
$total = $db->get_var( "SELECT COUNT(*) $query" );
foreach ( (array)$recent as $t ) {
	$class = ( $i % 2 ) ? ' class="alt"' : ''; 
	$excerpt = '';
	$parts = mime_split($t->content);
	if ( $part = find_first_part($parts) ) {
		$excerpt = substr( message_meat($part->content), 0, 300);
	}
	echo "<tr $class id='tr$t->thread_id'>
	<td><input type='checkbox' name='thread_ids[]' value='$t->thread_id' class='mcheck' id='mcheck$t->thread_id' /></td>
	<td><a href='index.php?email=$t->email'>$t->t_email</a></td><td><a title='".htmlspecialchars($excerpt, ENT_QUOTES)."' href='thread.php?t=$t->thread_id&amp;replies={$t->messages}'>".htmlspecialchars($t->subject ? mime_header_decode($t->subject) : '-')."</a></td>
	<td>" . thread_tags( $t->thread_id ) . "</td>
	<td>".short_time_diff($t->dt)."</td>
	<td>$t->messages</td>
	</tr>";
	++$i;
}

?>

<tr>
<td colspan="2">
<label>With checked:</label>
<input type="submit" name="status-close" value="Close" class="enablewhenselected" />
</td>
<td colspan="3" align="right">
<?php

$page_links = paginate_links( array(
	'base' => add_query_arg('apage', '%#%'),
	'total' => ceil($total / 20),
	'current' => $page
));

if ( $page_links )
	echo "<p class='pagenav'>$page_links</p>";
?>
</td>
</tr>

</table>
</form>


<?php include( 'footer.php' ); ?>
