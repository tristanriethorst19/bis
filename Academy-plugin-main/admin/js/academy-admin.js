jQuery(document).ready(function($) {
    var mediaUploader;

    /**
     * Handle image upload button click
     */
    $('#upload_image_button').click(function(e) {
        e.preventDefault();

        // If media uploader instance already exists, reopen it
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Create new media uploader frame
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image', // Media window title
            button: {
                text: 'Choose Image' // Button text inside modal
            },
            multiple: false // Only allow single image selection
        });

        // When image is selected
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();

            // Set preview and hidden input fields
            $('#cpt_image').val(attachment.url);           // (Optional, legacy fallback — not used here)
            $('#cpt_image_id').val(attachment.id);          // Save image ID
            $('#image-preview').attr('src', attachment.url).show(); // Show preview
            $('#remove_image_button').show();               // Reveal remove button
        });

        // Open the media uploader
        mediaUploader.open();
    });

    /**
     * Handle remove image button click
     */
    $('#remove_image_button').click(function(e) {
        e.preventDefault();

        // Clear image preview and stored ID
        $('#cpt_image').val('');
        $('#cpt_image_id').val('');
        $('#image-preview').attr('src', '').hide();
        $(this).hide();
    });

    /**
     * Initial state check — hide preview/remove if no image is selected
     */
    if ($('#image-preview').attr('src') === '') {
        $('#image-preview').hide();
        $('#remove_image_button').hide();
    }

    /**
     * Keep menu item highlighted for dynamically generated CPT edit pages
     */
    var page = window.location.search.substring(1); // e.g., "page=edit_cpt_test"
    if (page.indexOf('edit_cpt_') !== -1) {
        $('#toplevel_page_academy').addClass('current');          // Highlight menu wrapper
        $('#toplevel_page_academy > a').addClass('current');      // Highlight menu link
    }
});
