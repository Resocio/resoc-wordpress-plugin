<?php
  add_thickbox();

  $highlight_creation_button = ! get_option( Resoc_Social_Editor::OPTION_SKIP_OVERLAY_CREATION_SUGGESTION );
?>
<div id="rse-overlay-editor-modal" style="display:none;">
  <div class="rse-overlay-editor-container">
    <div class="rse-overlay-editor-inner-container">
      <h1>
        Create new overlay
      </h1>

      <p>
        An overlay can be applied to all or some or the images
        that illustrate your posts when they are shared on social networks.
        A typical overlay contains your logo, so it is visible
        by your visitors's friends and followers.
      </p>

      <p>
        <strong>Rather design an overlay your own way?</strong>
        Create a 1200x630 PNG image. Use transparency to let the underlying image appear.
      </p>

      <div id="rse-overlay-editor" class="rse-overlay-editor"></div>

      <div class="form-table">
        <p>
          <button class="rse-image-for-overlay-selection-button button-primary">
            Select image for overlay
          </button>
          <span class="description">
            You probably want to select your logo and/or company name.
          </span>
        </p>

        <div class="rse-image-selected-panel" style="display:none">
          <p>
            <strong>
              Not perfect?
            </strong>
            Move the image by dragging it with your mouse.
            Zoom in/out with your mouse wheel.
          </p>

          <div class="rse-overlay-creation-button-container">
            <button class="rse-overlay-creation-button button-primary">
              Create new overlay
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<a
  href="#TB_inline?width=600&height=650&inlineId=rse-overlay-editor-modal"
  class="<?php echo $highlight_creation_button ? 'button-primary' : 'button-secondary' ?> thickbox"
>
  Create new overlay
</a>

<?php
if ( ! function_exists( 'init_rse_overlay_editor' ) ) {
  function init_rse_overlay_editor( $overlay_created_callback ) {
?>
  var editorContainer = jQuery('#rse-overlay-editor-modal');

  rseInitOverlayEditor(
    editorContainer,
    '<?php echo admin_url( 'admin-ajax.php', isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ) ?>',
    '<?php echo Resoc_Social_Editor::PLUGIN_SLUG . '_create_overlay' ?>',
    <?php echo $overlay_created_callback ? $overlay_created_callback : undefined ?>
  );
<?php
  }
}
