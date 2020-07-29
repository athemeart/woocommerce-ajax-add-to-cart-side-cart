jQuery(document).ready(function () {
	
	
	if(  jQuery('.wcspc_color_picker').length ){
	
    jQuery('.wcspc_color_picker').wpColorPicker();
	}
    
    // choose background image
	if( jQuery('#wcspc_upload_image_button').length ){
    var wcspc_file_frame;
    jQuery('#wcspc_upload_image_button').on('click', function (event) {
        event.preventDefault();
        // If the media frame already exists, reopen it.
        if (wcspc_file_frame) {
            // Open frame
            wcspc_file_frame.open();
            return;
        } else {
        }
        // Create the media frame.
        wcspc_file_frame = wp.media.frames.wcspc_file_frame = wp.media({
            title: 'Select a image to upload',
            button: {
                text: 'Use this image',
            },
            multiple: false	// Set to true to allow multiple files to be selected
        });
        // When an image is selected, run a callback.
        wcspc_file_frame.on('select', function () {
            // We set multiple to false so only get one image from the uploader
            attachment = wcspc_file_frame.state().get('selection').first().toJSON();
            // Do something with attachment.id and/or attachment.url here
            jQuery('#wcspc_image_preview').attr('src', attachment.url).css('width', 'auto');
            jQuery('#wcspc_image_attachment_url').val(attachment.id);
        });
        // Finally, open the modal
        wcspc_file_frame.open();
    });
	}
	
	if( jQuery('#wcspc_image_attachment_url').length ){
	jQuery('#wcspc_remove_image_button').on('click', function (event) {
		event.preventDefault();	
		jQuery('#wcspc_image_attachment_url').val( '' );
		jQuery('#wcspc_image_preview').attr( 'src', wcspc.placeholder );
	} );
	}
	
	
});

