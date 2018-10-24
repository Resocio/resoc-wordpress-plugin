<?php

class Social_by_RealFaviconGenerator_Facebook_Editor {

	public static function facebook_editor( $post ) {

    $title =
      get_post_meta( $post->ID,
        Social_by_RealFaviconGenerator::OG_TITLE, true );
    $description =
      get_post_meta( $post->ID,
        Social_by_RealFaviconGenerator::OG_DESCRIPTION, true );

		$imageSettings =
			get_post_meta( $post->ID,
        Social_by_RealFaviconGenerator::OG_MASTER_IMAGE_SETTINGS, true );

		$imageId =
			get_post_meta( $post->ID,
				Social_by_RealFaviconGenerator::OG_MASTER_IMAGE_ID, true );
		if ($imageId) {
			$imageUrl = wp_get_attachment_url( $imageId );
    }

		ob_start();
?>
	<div class="social-by-rfg-wrap custom-field-panel sbrfg-editor" id="sbrfg-editor">
		<div>
			<h3>By <a href="https://realfavicongenerator.net/social" target="_blank">RealFaviconGenerator</a></h3>
		</div>

		<div class="sbrfg-editor-overall-container">
      <div class="sbrfg-preview-container">
				<div class="open-graph-editor-container"></div>
			</div>

			<div class="sbrfg-fields">
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="sbrfg-title">Title</label></th>
						<td><input type="text" name="sbrfg-title" placeholder="A title you should change"></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="sbrfg-description">Description</label></th>
						<td><textarea rows="3" name="sbrfg-description" placeholder="A description you should change, too"></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="sbrfg-image">Image</label></th>
						<td>
              <button class="sbrfg-image-selection-button button-primary">Select Facebook image</button>
            </td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="sbrfg-view">View</label></th>
						<td>
              <label for="sbrfg-view-facebook">
                <input type="radio" name="sbrfg-view" value="facebook" id="sbrfg-view-facebook" checked="checked" />
                Facebook
              </label>
              <label for="sbrfg-view-facebook-caption">
                <input type="radio" name="sbrfg-view" value="facebook_caption" id="sbrfg-view-facebook-caption" />
                Facebook - Captions
              </label>
              <label for="sbrfg-view-linkedin">
                <input type="radio" name="sbrfg-view" value="linkedin" id="sbrfg-view-linkedin" />
                LinkedIn
              </label>
              <label for="sbrfg-view-linkedin-caption">
                <input type="radio" name="sbrfg-view" value="linkedin_caption" id="sbrfg-view-linkedin-caption" />
                LinkedIn - Captions
              </label>
            </td>
					</tr>
				</table>
			</div>

			<div class="sbrfg-clear-fix"></div>
      <input
        type="hidden"
        name="sbrfg-og-image-settings"
      >
      <input
        type="hidden"
        name="sbrfg-og-image-id"
        value="<?php echo $imageId ?>"
      >
		</div>
	</div>

	<div id="sbrfg-upgrade-notice" style="display:none">
		Your version of the plugin is outdated.
		Please <a href="<?php echo get_site_url( null, '/wp-admin/plugins.php' ) ?>" target="_blank">
			visit your plugins page</a> and update <strong>Social by RealFaviconGenerator</strong>.
	</div>

	<script>
  /*
		jQuery(document).ready(function(e) {
			var imageId = <?php echo $imageId ? $imageId : 'undefined' ?>;
			var imageUrl = <?php echo $imageUrl ? '"' . $imageUrl . '"' : 'undefined' ?>;
			sbrfgInitSocialEditor(
				jQuery('#sbrfg-editor'),
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
      var editorContainer = jQuery('#sbrfg-editor');

      sbrfgInitOpenGraphEditor(
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
