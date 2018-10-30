
var rseInitOpenGraphEditor = function(
  editorContainer,
  title, description,
  imageId, imageSettings, imageUrl,
  overlayImageSrc,
  siteUrl
) {
  var openGraphEditor;
  var imageSelectionFrame;
  var overlaySelectionFrame;

  const e = React.createElement;
  console.log(RFGSocialEditor);
  const domContainer = editorContainer.find('.open-graph-editor-container')[0];
  ReactDOM.render(e(
    RFGSocialEditor.StandaloneOpenGraphEditor, {onCreated: function(obj) {
      openGraphEditor = obj;

      openGraphEditor.setImage(imageUrl, imageSettings);
      openGraphEditor.setView('facebook');
      openGraphEditor.setUrl(siteUrl);
      openGraphEditor.setTitle(title);
      openGraphEditor.setDescription(description);
      openGraphEditor.setOverlayImageSrc(overlayImageSrc);
    }}), domContainer);

  initForm(editorContainer, title, description);
  initImageSelection(editorContainer);
  initOverlayImageSelection(editorContainer);

  var postForm = jQuery.find('#post');
  jQuery(document).on('submit', postForm, function() {
    editorContainer.find('input[name="rse-og-image-settings"]').val(
      JSON.stringify(openGraphEditor.getImageEditionState())
    );
  });

  function initForm(editorContainer, title, description) {
    var titleField = editorContainer.find('input[name=rse-title]');
    titleField.val(title);
    titleField.on('input', function() {
      openGraphEditor.setTitle(this.value);
    });

    var descriptionField = editorContainer.find('textarea[name=rse-description]');
    descriptionField.val(description);
    descriptionField.on('input propertychange', function() {
      openGraphEditor.setDescription(this.value);
    });

    editorContainer.find('input[name=rse-view]').change(function() {
      openGraphEditor.setView(this.value);
    });
  }

  function initImageSelection(editorContainer) {
    editorContainer.find('.rse-image-selection-button').live('click', function(event) {
      event.preventDefault();
  
      if (imageSelectionFrame) {
        imageSelectionFrame.open();
        return;
      }
  
      // Create the media frame.
      imageSelectionFrame = wp.media.frames.file_frame = wp.media({
        title: jQuery(this).data('uploader_title'),
        button: {
          text: jQuery(this).data('uploader_button_text'),
        },
        multiple: false
      });
  
      imageSelectionFrame.on('select', function() {
        attachment = imageSelectionFrame.state().get('selection').first().toJSON();

        imageUrl = attachment.url;
        openGraphEditor.setImage(imageUrl);

        imageId = attachment.id;
        editorContainer.find('input[name="rse-og-image-id"]').val(imageId);
      });
  
      imageSelectionFrame.open();
    });
  }

  function initOverlayImageSelection(editorContainer) {
    editorContainer.find('.rse-overlay-image-reset-button').live('click', function(event) {
      event.preventDefault();

      editorContainer.find('input[name="rse-og-overlay-image-id"]').val(undefined);
      openGraphEditor.setOverlayImageSrc(undefined);
    });

    editorContainer.find('.rse-overlay-image-selection-button').live('click', function(event) {
      event.preventDefault();

      if (overlaySelectionFrame) {
        overlaySelectionFrame.open();
        return;
      }

      // Create the media frame.
      overlaySelectionFrame = wp.media.frames.file_frame = wp.media({
        title: jQuery(this).data('uploader_title'),
        button: {
          text: jQuery(this).data('uploader_button_text'),
        },
        multiple: false
      });

      overlaySelectionFrame.on('select', function() {
        attachment = overlaySelectionFrame.state().get('selection').first().toJSON();

        openGraphEditor.setOverlayImageSrc(attachment.url);
        editorContainer.find('input[name="rse-og-overlay-image-id"]').val(attachment.id);
      });

      overlaySelectionFrame.open();
    });
  }
  
  console.log("OPENGRAPH Editor Init Completed");
}
