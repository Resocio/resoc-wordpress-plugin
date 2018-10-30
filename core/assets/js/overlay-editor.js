
var rseInitOverlayEditor = function(editorContainer, ajaxUrl, ajaxActionName) {
  var overlayEditor;
  var imageSelectionFrame;
  var imageId;

  const e = React.createElement;
  const domContainer = editorContainer.find('.rse-overlay-editor')[0];
  ReactDOM.render(e(
    RFGSocialEditor.StandaloneOverlayEditor, {onCreated: function(obj) {
      overlayEditor = obj;
    }}
  ), domContainer);

  editorContainer.find('.rse-image-selection-button').on('click', function(event) {
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
    });

    imageSelectionFrame.open();
  });

  editorContainer.find('.rse-overlay-creation-button').on('click', function(event) {
    event.preventDefault();

    var request = JSON.stringify({
      image_id: imageId,
      image_settings: overlayEditor.getImageEditionState()
    });

    var data = {
      action: ajaxActionName,
      request: request
    };
    
    jQuery.ajax({
      type: 'POST',
      data: data,
      dataType: 'application/json',
      url: ajaxUrl,
      success: function(response) {
        console.log("DONE", response);
      },
      failure: function(e) {
        console.log("Fail!", e);
      }
    });
  });

  console.log("Overlay editor initialized");
}
