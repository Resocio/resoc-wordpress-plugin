
var rseInitOpenGraphEditor = function(
  editorContainer,
  title, description,
  imageId, imageSettings, imageUrl,
  overlayImageSrc, overlayImageId,
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

      setImage(imageId, imageUrl, imageSettings);
      openGraphEditor.setView('facebook');
      openGraphEditor.setUrl(siteUrl);
      openGraphEditor.setTitle(title);
      openGraphEditor.setDescription(description);
      setOverlay(overlayImageId, overlayImageSrc);
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
        setImage(attachment.id, attachment.url, undefined);
      });
  
      imageSelectionFrame.open();
    });
  }

  function initOverlayImageSelection(editorContainer) {
    editorContainer.find('.rse-overlay-image-reset-button').live('click', function(event) {
      event.preventDefault();

      setOverlay(undefined, undefined);
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

        setOverlay(attachment.id, attachment.url);
      });

      overlaySelectionFrame.open();
    });
  }

  function setImage(imageId, imageUrl, imageSettings) {
    openGraphEditor.setImage(imageUrl, imageSettings);
    editorContainer.find('input[name="rse-og-image-id"]').val(imageId);
    if (imageId) {
      editorContainer.find('.rse-image-selection-button')
      .removeClass('button-primary')
      .addClass('button-secondary');
    }
    else {
      editorContainer.find('.rse-image-selection-button')
        .removeClass('button-secondary')
        .addClass('button-primary');
    }
  }

  function setOverlay(overlayId, overlayUrl) {
    openGraphEditor.setOverlayImageSrc(overlayUrl);
    editorContainer.find('input[name="rse-og-overlay-image-id"]').val(overlayId);

    if (overlayId) {
      editorContainer.find('.rse-overlay-image-selection-button')
        .removeClass('button-primary')
        .addClass('button-secondary');
      editorContainer.find('.rse-overlay-image-reset-button').removeAttr('disabled');
    }
    else {
      editorContainer.find('.rse-overlay-image-selection-button')
        .removeClass('button-secondary')
        .addClass('button-primary');
      editorContainer.find('.rse-overlay-image-reset-button').attr('disabled', 'disabled');
    }
  }

  console.log("OPENGRAPH Editor Init Completed");
}
