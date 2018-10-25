
var rseInitOpenGraphEditor = function(
  editorContainer,
  title, description,
  imageId, imageSettings, imageUrl,
  siteUrl
) {
  var openGraphEditor;
  var fileFrame;

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
    }}), domContainer);

  initForm(editorContainer, title, description);
  initImageSelection(editorContainer);

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

        imageUrl = attachment.url;
        openGraphEditor.setImage(imageUrl);

        imageId = attachment.id;
        editorContainer.find('input[name="rse-og-image-id"]').val(imageId);
      });
  
      fileFrame.open();
    });
  }
  
  console.log("OPENGRAPH Editor Init Completed");
}

/*
var rseInitSocialEditor = function(editorContainer, imageId, imageUrl, settings, siteUrl) {
	var fileFrame;

	var editorContainer;
	var rseEditor;

	var rseScaleMin, rseScaleMax;

	var fb;

	initImageSelection();

	initFormSerialization();

	if (imageId && imageUrl) {
		// There is something to show right now
		initAndShowEditor();
	}
	else {
		// Nothing to do: image selection button is displayed by default
	}

	function initAndShowEditor() {
		initEditor();

		editorContainer.find('.rse-image-selection-container.rse-no-existing-image').hide();
		editorContainer.find('.rse-image-selection-container.rse-existing-image').show();

		editorContainer.find('.rse-editor-overall-container').fadeIn();
	}


	function initEditor() {
		fb = new RFGFacebook({
			apiKey: 'TODO',
			element: jQuery('#rse-editor .rse-facebook-editor'),
			master_image_src: imageUrl,
			format: 'wide',
			title: 'A title you should change',
			description: 'A description you should change, too',
			url: siteUrl,
			apiRequest: settings
		});

		if (settings) {
			// As there is en existing API request, initialize the form with the
			// editor's data
			editorContainer.find('input[name="rse-title"]').val(fb.getTitle());
			editorContainer.find('textarea[name="rse-description"]').val(fb.getDescription());
		}

		// Scale setting
		fb.on('scaleChange', function(e, currentScale, minScale, maxScale) {
			var range = editorContainer.find('input[name="rse-scale"]');
			currentScale = RFGComponent.transposeInterval(currentScale, minScale, maxScale,
				range.attr('min'), range.attr('max'));
			range.val(currentScale);
		});

		fb.render();

		// Init UI

		// Platform
		var platforms = fb.getAvailablePlatforms();
		editorContainer.find('.rse-platform-switcher-container').html('');
		Object.keys(platforms).forEach(function(platform) {
				var button = jQuery(
					'<button href="#" class="button-secondary" data-platform="' + platform + '" ' +
						((platform == fb.getPlatform()) ? ' disabled="disabled"' : '') + '>' +
					platforms[platform] + '</button>');
				button.click(function(e) {
					editorContainer.find('.rse-platform-switcher-container button').removeAttr('disabled');
					button.attr('disabled', 'disabled');
					fb.setPlatform(platform);
					e.preventDefault();
				});
				editorContainer.find('.rse-platform-switcher-container').append(button);
		});
		editorContainer.find('.rse-platform-switcher-container input[name="platform"]').change(function() {
			var newPlatform = editorContainer.find('.rse-platform-switcher-container input[name="platform"]:checked').val();
			fb.setPlatform(newPlatform);
		});

		// Format
		var formats = fb.getAvailableFormats();
		editorContainer.find('.format-radios-container').html('');
		var freeDuringTheBeta = ' <em>Free during the beta</em>';
		Object.keys(formats).forEach(function(format) {
			editorContainer.find('.format-radios-container').append('<label>'
					+ '<input type="radio" name="format" value="'
					+ format + '"'
					+ ((format == fb.getFormat()) ? ' checked' : '')
					+ '>' + formats[format]
					+ (format == 'wide' ? '' : freeDuringTheBeta)
				+ '</label>');
		});
		editorContainer.find('.format-radios-container input[name="format"]').change(function() {
			var newFormat = editorContainer.find('.format-radios-container input[name="format"]:checked').val();
			fb.setFormat(newFormat);
		});

		// Title
		editorContainer.find('input[name="rse-title"]').on('input', function(val) {
			fb.setTitle(jQuery('input[name="rse-title"]').val());
		});

		// Description
		editorContainer.find('textarea[name="rse-description"]').on('input propertychange', function(val) {
			fb.setDescription(jQuery('textarea[name="rse-description"]').val());
		});

		// Scale
		var is = editorContainer.find('input[name="rse-scale"]');
		is.bind('propertychange change click keyup input paste', function() {
			var range = jQuery(this);
			fb.setScale(range.val(), range.attr('min'), range.attr('max'));
		});
	}

	function initImageSelection() {
		jQuery('.rse-image-selection-button').live('click', function(event) {
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
				imageUrl = attachment.url;

				initAndShowEditor();
			});

			fileFrame.open();
		});
	}

	function initFormSerialization() {
		var postForm = jQuery.find('#post');
		jQuery(document).on('submit', postForm, function() {
			if (fb) {
				var data = {};
				data = fb.getApiFaviconDesign();
				editorContainer.find('input[name="rse-og-serialized-data"]').val(
					JSON.stringify(data));
				editorContainer.find('input[name="rse-og-image-id"]').val(imageId);
			}
		});
	}
*/
	/*
	function rseInitEditors(settings, imageId, imageUrl) {
		rseEditor.openGraphEditor();

		imageId = imageId;

		var allFormats = rseEditor.getAllFormats();
		var radioContainer = rseContainer.find('.format-radios-container');
		radioContainer.html('');
		jQuery.each(allFormats, function(format) {
			radioContainer.append('<p><input type="radio" name="rse-format" value="' +
				format + '"> ' + allFormats[format] + '</p>');
		});

		rseInitEditor();


		function rseInitEditor() {
			rseEditor.initComponent({
				master_img_src: imageUrl,
				serialized_data: settings.open_graph.facebook_open_graph,
				onScaleChange: function(e, min, max, current) {
					rseScaleMin = min;
					rseScaleMax = max;
					var range = rseEditor.addScaleAmplitude(100, rseScaleMin, rseScaleMax, current);
					rseContainer.find('input[name="rse-scale"]').attr('min', range[0]);
					rseContainer.find('input[name="rse-scale"]').attr('max', range[1]);
					rseContainer.find('input[name="rse-scale"]').val(range[2]);
				},
				onInit: function() {
					rseContainer.find('.rse-platform-switcher-container').html('');
					var platforms = rseEditor.getAllPlatforms();
					platforms.forEach(function(p) {
						var button = $(
							'<button href="#" class="button-secondary" data-platform="' + p + '">' +
							rseEditor.getPlatformName(p) +
							'</button>');
						button.click(function(e) {
							rseContainer.find('.rse-platform-switcher-container button').removeAttr('disabled');
							button.attr('disabled', 'disabled');
							rseEditor.setPlatform(p);
							e.preventDefault()
						});
						rseContainer.find('.rse-platform-switcher-container').append(button);
					});

					rseEditor.setUrl(location.protocol + "//" +  window.location.hostname);

					rseContainer.find('input[name="rse-title"]').val(rseEditor.getTitle());
					rseContainer.find('input[name="rse-title"]').bind('propertychange change click keyup input paste', function() {
				    rseEditor.setTitle($(this).val());
				  });

					rseContainer.find('textarea[name="rse-description"]').val(rseEditor.getDescription());
					rseContainer.find('textarea[name="rse-description"]').bind('propertychange change click keyup input paste', function() {
				    rseEditor.setDescription($(this).val());
				  });

					var format = rseEditor.getFormat();
					rseContainer.find('input[name="rse-format"][value="' + format + '"]').attr('checked', 'checked');
					rseContainer.find('input[name="rse-format"]').bind('propertychange change click keyup input paste', function() {
						rseEditor.setFormat(rseContainer.find('input[name="rse-format"]:checked').val());
					});

					rseContainer.find('input[name="rse-scale"]').bind('propertychange change click keyup input paste', function() {
						var scale = rseEditor.removeScaleAmplitude(100, rseScaleMin, rseScaleMax, $(this).val());
						rseEditor.setScale(scale);
					});


					objectTypeField = rseContainer.find('select[name="rse-object-type"]');
					allTypes = rseEditor.getAllObjectTypes();
					var currentType = rseEditor.getObjectType() || 'article';
					jQuery.each(allTypes, function(t) {
						var selected = (t == currentType) ? ' selected' : '';
						objectTypeField.append('<option value="' + t + '"' + selected + '>'
							+ allTypes[t] + '</option>');
					});
					objectTypeField.bind('propertychange change click keyup input paste', function() {
				    rseEditor.setObjectType($(this).find('option:selected').val());
				  });

					var postForm = jQuery.find('#post');
					$(document).on('submit', postForm, function() {
						var data = {};
						data.facebook_open_graph = rseEditor.serializeForAPIRequest();
						rseContainer.find('input[name="rse-og-serialized-data"]').val(
							JSON.stringify(data));
						rseContainer.find('input[name="rse-og-image-id"]').val(imageId);
					});
				}
			});
		}
	}

}
*/
