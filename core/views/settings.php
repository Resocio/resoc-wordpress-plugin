<div class="wrap">

	<?php screen_icon() ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form action="<?php echo $social_editor_admin_url ?>" method="post" id="rse-settings-form">

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">Default overlay</th>
					<td>
            <div id="overlay-preview" style="display:none">
              <img
                class="rse-overlay-preview-img"
                style="background-image: url('<?php echo plugin_dir_url( __FILE__ ) . '../assets/images/overlay-background.png' ?>')"
              />
            </div>
            <div id="no-overlay" style="display:none">
              <p>No default overlay</p>
            </div>

            <button class="rse-image-selection-button button-secondary">Select overlay image</button>
            <button class="rse-image-reset-button button-secondary">Reset overlay image</button>
					</td>
				</tr>
			</tbody>
		</table>

    <input type="hidden" name="<?php echo Resoc_Social_Editor::SETTINGS_FORM ?>" value="1">
		<input
      type="hidden"
      name="<?php echo Resoc_Social_Editor::OPTION_DEFAULT_OVERLAY_ID ?>"
      value="<?php echo default_overlay_id ?>"
    >

		<input name="Submit" type="submit" class="button-primary" value="Save changes">
	</form>

  <script>
    jQuery(document).ready(function() {
      var form = jQuery('#rse-settings-form');
      var fileFrame;

      function showOverlayPreview(overlayId, overlayUrl) {
        if (overlayId) {
          form.find('#overlay-preview img').attr('src', overlayUrl);
          form.find('#no-overlay').hide();
          form.find('#overlay-preview').show();
        }
        else {
          form.find('#no-overlay').show();
          form.find('#overlay-preview').hide();
        }
      }

<?php
        if ( $default_overlay_id) {
?>
          showOverlayPreview(<?php echo $default_overlay_id ?>, "<?php echo $default_overlay_url ?>");
<?php
        }
        else {
?>
          showOverlayPreview(undefined, undefined);
<?php
        }
?>
      form.find('.rse-image-reset-button').live('click', function(event) {
        event.preventDefault();
        form.find(
          'input[name="<?php echo Resoc_Social_Editor::OPTION_DEFAULT_OVERLAY_ID ?>"]'
        ).val(undefined);
        showOverlayPreview(undefined, undefined);
      });

      form.find('.rse-image-selection-button').live('click', function(event) {
        event.preventDefault();
    
        if (fileFrame) {
          fileFrame.open();
          return;
        }
    
        // Create the media frame.
        fileFrame = wp.media.frames.file_frame = wp.media({
          title: jQuery(this).data('uploader_title'),
          button: {
            text: jQuery(this).data('uploader_button_text'),
          },
          multiple: false
        });
    
        fileFrame.on('select', function() {
          attachment = fileFrame.state().get('selection').first().toJSON();

          imageId = attachment.id;
          form.find(
            'input[name="<?php echo Resoc_Social_Editor::OPTION_DEFAULT_OVERLAY_ID ?>"]'
          ).val(imageId);
          showOverlayPreview(imageId, attachment.url);
        });
    
        fileFrame.open();
      });
    });
  </script>
</div>
