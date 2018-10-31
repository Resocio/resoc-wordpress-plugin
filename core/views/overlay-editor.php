<?php add_thickbox(); ?>
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

      <div id="rse-overlay-editor" class="rse-overlay-editor"></div>

      <div class="form-table">
        <p>
          <button class="rse-image-selection-button button-primary">
            Select overlay image
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

          <button class="rse-overlay-creation-button button-primary">
            Create overlay
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<a href="#TB_inline?width=600&height=550&inlineId=rse-overlay-editor-modal" class="button-secondary thickbox">
  Create new overlay
</a>

<?php
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
