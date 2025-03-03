jQuery(document).ready(function($){

	// Handle insertable tags
	var insert_tags = function( text, type ){
		
		if ( 'subject' === type ) {
			var $obj = $("input.bbpnns-message-subject");
			var val = $obj.val();
			
			if (typeof val === 'undefined' )
				val = '';
			
			val += text;
			
			$obj.val(val);
			$obj.focus();
		}
		else {
            if ( ( typeof window.tinyMCE != 'undefined' ) && ( window.tinyMCE.activeEditor ) && ( !tinyMCE.activeEditor.isHidden() ) ) {
            	tinyMCE.execCommand( 'mceInsertContent', false, text + '</p>' );
            } 
            else {
                edInsertContent( document.getElementById('content'), text );
            }
		}
	    return false;
	};
	
	$("span.bbpnns-subject-line a.bbpnns_tinymce_tag").on( 'click', function(){
		var text = $(this).data('insert-tag');
		return insert_tags( text, 'subject' );
	});
	
	$("span.bbpnns-message-body a.bbpnns_tinymce_tag").on( 'click', function(){
		var text = $(this).data('insert-tag');
		return insert_tags( text, 'tinymce' );
	});

	// Tie action scheduler click with background notifications
	$('#use_action_scheduler').on('click', function(){
		var checked = $(this).is(':checked');
		if ( checked ) {
			$('#background_notifications').prop('checked', true);
		}
	});

	$('#background_notifications').on('click', function(){
		var checked = $(this).is(':checked');
		if ( ! checked ) {
			$('#use_action_scheduler').prop('checked', false);
		}
	});
});