var sp_tickers = new Array();

// triggered by mouse movement, keypress etc.
// send an ajax notification that we're active in this thread
function sp_tick( action ) {
	var now = new Date();
	var last_tick = sp_tickers[ action ];
	if ( !last_tick ) last_tick = 0;

	var delta = now.getTime() - last_tick;
	
	// 30 seconds since the last ping?
	if ( delta > 30000 ) {
		sp_tickers[ action ] = now.getTime();
		var thread_id = $('#threadstatus input[name=thread_id]').val();
		
		// send a heartbeat ping, and store the result (a list of other users also reading this thread)
		$.getJSON('heartbeat.php', { thread_id: thread_id, delta: delta, action: action }, function(r) { if ( r.users ) $('#other-users').innerHTML = r.users.join(', '); } );
	}
	
}


$(document).ready(function(){
$('.inlinereply').click( function() { $('.reply').removeClass('replying'); $('#' + this.name ).toggle(); $('#' + this.name + ' .reply').addClass('replying').eq(0).focus(); } );
$('p').click( function() { $('.lastclicked').attr( 'value', this.parentNode.id ); } );
$('input.notify-checkbox').click( function() { if ( $('input.notify-checkbox:checked').length > 0 ) $('.notify-toggle').show(); else $('.notify-toggle').hide(); } );
	$('select.predefined_message').change( function() {
		if ( $(this).val() > 0 ) {
			$.getJSON('ajax-predefined.php', { id: $(this).val(), user_id: $('.user_id').val() }, function(data) { 
				$('textarea.replying').replaceSelection( $('<div/>').text(data.message).html() + '\n\n' );
				$('.title.replying').val( data.title );
				$('.tag.replying').val( data.tag );
			} );
		}
	} );

$('.note-button').click( function() { $('.note-toggle').toggle(); } );
$('.message-toggle').click( function() { $(this).parents('.message').find('.mainpart').toggle(); } );
$(document).mousemove( function(e) { sp_tick( 'read' ); } );
$(document).keyup( function(e ) { sp_tick( 'read' ); } );
$('textarea.reply').focus( function(e) { sp_tick( 'reply' ); } );
$('textarea.reply').keyup( function(e) { sp_tick( 'reply' ); } );
});

