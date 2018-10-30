<?php add_thickbox(); ?>
<div id="rse-overlay-editor-modal" style="display:none;">
  <h1>
    Create new overlay
  </h1>

  <div id="rse-overlay-editor" class="rse-overlay-editor"></div>

  <p>
    <button class="rse-image-selection-button button-primary">
      Select overlay image
    </button>
    <button class="rse-overlay-creation-button button-primary">
      Create overlay
    </button>
  </p>

  <input name="rse-overlay-image-id" type="hidden"/>
</div>

<a href="#TB_inline?width=600&height=550&inlineId=rse-overlay-editor-modal" class="button-secondary thickbox">
  Create new overlay
</a>

<script>
  jQuery(document).ready(function(e) {
    var editorContainer = jQuery('#rse-overlay-editor-modal');

    rseInitOverlayEditor(
      editorContainer,
      '<?php echo admin_url( 'admin-ajax.php', isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ) ?>',
      '<?php echo Resoc_Social_Editor::PLUGIN_SLUG . '_create_overlay' ?>'
    );
  });
</script>
