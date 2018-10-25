<?php

class Resoc_Social_Editor_Facebook_Editor {

	public static function facebook_editor( $post ) {

    $title =
      get_post_meta( $post->ID,
        Resoc_Social_Editor::OG_TITLE, true );
    $description =
      get_post_meta( $post->ID,
        Resoc_Social_Editor::OG_DESCRIPTION, true );

		$imageSettings =
			get_post_meta( $post->ID,
        Resoc_Social_Editor::OG_MASTER_IMAGE_SETTINGS, true );

		$imageId =
			get_post_meta( $post->ID,
				Resoc_Social_Editor::OG_MASTER_IMAGE_ID, true );
		if ($imageId) {
			$imageUrl = wp_get_attachment_url( $imageId );
    }

		ob_start();
?>
	<div class="custom-field-panel rse-editor" id="rse-editor">
		<div>
			<h3>By <a href="https://resoc.io" target="_blank">Resoc</a></h3>
		</div>

		<div class="rse-editor-overall-container">
      <div class="rse-preview-container">
				<div class="open-graph-editor-container"></div>
			</div>

			<div class="rse-fields">
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="rse-title">Title</label></th>
						<td><input type="text" name="rse-title" placeholder="A title you should change"></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rse-description">Description</label></th>
						<td><textarea rows="3" name="rse-description" placeholder="A description you should change, too"></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rse-image">Image</label></th>
						<td>
              <button class="rse-image-selection-button button-primary">Select Facebook image</button>
            </td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rse-view">View</label></th>
						<td>
              <label for="rse-view-facebook">
                <input type="radio" name="rse-view" value="facebook" id="rse-view-facebook" checked="checked" />
                Facebook
              </label>
              <label for="rse-view-facebook-caption">
                <input type="radio" name="rse-view" value="facebook_caption" id="rse-view-facebook-caption" />
                Facebook - Captions
              </label>
              <label for="rse-view-linkedin">
                <input type="radio" name="rse-view" value="linkedin" id="rse-view-linkedin" />
                LinkedIn
              </label>
              <label for="rse-view-linkedin-caption">
                <input type="radio" name="rse-view" value="linkedin_caption" id="rse-view-linkedin-caption" />
                LinkedIn - Captions
              </label>
            </td>
					</tr>
				</table>
			</div>

			<div class="rse-clear-fix"></div>
      <input
        type="hidden"
        name="rse-og-image-settings"
      >
      <input
        type="hidden"
        name="rse-og-image-id"
        value="<?php echo $imageId ?>"
      >
		</div>
	</div>

	<div id="rse-upgrade-notice" style="display:none">
		Your version of the plugin is outdated.
		Please <a href="<?php echo get_site_url( null, '/wp-admin/plugins.php' ) ?>" target="_blank">
			visit your plugins page</a> and update <strong>Social by RealFaviconGenerator</strong>.
	</div>

	<script>
  /*
		jQuery(document).ready(function(e) {
			var imageId = <?php echo $imageId ? $imageId : 'undefined' ?>;
			var imageUrl = <?php echo $imageUrl ? '"' . $imageUrl . '"' : 'undefined' ?>;
			rseInitSocialEditor(
				jQuery('#rse-editor'),
				imageId, imageUrl,
				<?php echo $openGraphSerializedData ? $openGraphSerializedData : 'undefined' ?>,
				'<?php echo get_site_url() ?>');
    });
    */
  </script>

  <script>
		jQuery(document).ready(function(e) {
      var title = <?php echo $title ? json_encode( $title ) : 'undefined' ?>;
      var description = <?php echo $description ? json_encode( $description ) : 'undefined' ?>;

      var imageId = <?php echo $imageId ? $imageId : 'undefined' ?>;
      var imageSettings = <?php echo $imageSettings ? $imageSettings : 'undefined' ?>;
      var imageUrl = <?php echo $imageUrl ? '"' . $imageUrl . '"' : 'undefined' ?>;
      console.log("IMAGE URL=" + imageUrl);
      var editorContainer = jQuery('#rse-editor');

      rseInitOpenGraphEditor(
        editorContainer,
        title,
        description,
        imageId,
        imageSettings,
        imageUrl,
        '<?php echo get_site_url() ?>'
      );
    });
  </script>
  
<?php
		return ob_get_clean();
	}
}
