$(document).ready(function(){
	// show/hide text tip contents
	$('input.tiptext').addClass('has-tiptext');
	$('input.tiptext').focus( function() { if (!this.cleared) { this.cleared=true; this.tip=this.value; $(this).val('').removeClass('has-tiptext'); } } );
	$('input.tiptext').blur( function() { if (!this.value) { this.cleared=false; $(this).val(this.tip).addClass('has-tiptext'); } } );

	// remove tip contents from inputs when form is submitted
	$('form').submit( function() { $('input.has-tiptext').val(''); } );

	$('.whenselected').toggle( $('.mcheck:checked').size() > 0 );
	$('.mcheck').change( function() { $('.whenselected').toggle( $('.mcheck:checked').size() > 0 ); } );

	$('.enablewhenselected').attr( 'disabled', $('.mcheck:checked').size() > 0 ? false : 'disabled' );
	$('.mcheck').change( function() { $('.enablewhenselected').attr( 'disabled', $('.mcheck:checked').size() > 0 ? false : 'disabled' ); } );
});
