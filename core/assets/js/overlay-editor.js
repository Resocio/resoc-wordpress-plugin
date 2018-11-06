
var rseInitOverlayEditor = function(
  editorContainer,
  ajaxUrl,
  ajaxActionName,
  overlayEditorCallback) {
  var overlayEditor;
  var imageSelectionFrame;
  var imageId;

  var selectImageButton = editorContainer.find('.rse-image-selection-button');
  var createOverlayButton = editorContainer.find('.rse-overlay-creation-button');
  var creationPanel = editorContainer.find('.rse-image-selected-panel');

  const e = React.createElement;
  const domContainer = editorContainer.find('.rse-overlay-editor')[0];
  ReactDOM.render(e(
    ResocSocialEditor.StandaloneOverlayEditor, {onCreated: function(obj) {
      overlayEditor = obj;
    }}
  ), domContainer);

  selectImageButton.on('click', function(event) {
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
      overlayEditor.setImage(imageUrl);

      imageId = attachment.id;

      creationPanel.show();
      selectImageButton.removeClass('button-primary');
      selectImageButton.addClass('button-secondary');
    });

    imageSelectionFrame.open();
  });

  createOverlayButton.on('click', function(event) {
    event.preventDefault();

    var request = JSON.stringify({
      image_id: imageId,
      image_settings: overlayEditor.getImageEditionState()
    });

    var data = {
      action: ajaxActionName,
      request: request
    };
    
    // Prevent the button to be clicked twice,
    // show feedback
    createOverlayButton.attr('disabled', 'disabled');
    jQuery.ajax({
      type: 'POST',
      data: data,
      dataType: 'json',
      url: ajaxUrl,
      success: function(response) {
        console.log("DONE", response);
        tb_remove();
        createOverlayButton.removeAttr('disabled');
        if (overlayEditorCallback) {
          overlayEditorCallback(response);
        }
      },
      error: function(e, f, g) {
        console.log("Cannot create overlay", e);
        createOverlayButton.removeAttr('disabled');
      }
    });
  });

  console.log("Overlay editor initialized");
}
