<?php

require_once plugin_dir_path( __FILE__ ) . 'class-resoc-social-editor-api-response.php';
require_once plugin_dir_path( __FILE__ ) . 'class-resoc-social-editor-facebook-editor.php';


require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( ! defined( 'ABSPATH' ) ) exit;

class Resoc_Social_Editor_Admin_API {

	/**
	 * Constructor function
	 */
	public function __construct () {
		add_action( 'save_post',
			array( $this, 'save_social_data' ) );
		// Make sure to run this action just before Yoast SEO
		// (Yoast is using the default priority, which is 10)
		add_action( 'add_meta_boxes',
			array( $this, 'save_meta_boxes' ), 9 );
	}

	public function patch_yoast_seo_meta_box() {
		global $GLOBALS;

		// Useless if Yoast is not even installed and active
		if (! is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) return false;

		// This global is always supposed to be available, but if that's not the
		// case, just stop here
		if ( ! isset( $GLOBALS['wpseo_metabox'] ) ) return false;

		// At this point, Yoast SEO code is available
		require_once plugin_dir_path( __FILE__ ) . 'class-yoast-seo-enhanced-meta.php';

		$GLOBALS['wpseo_metabox'] = new RFG_WPSEO_Enhanced_Metabox( $GLOBALS['wpseo_metabox'] );

		return true;
	}

	public function save_social_data ( $post_id ) {
    // Title and description
    $title = $_POST['rse-title'];
    $description = $_POST['rse-description'];

    update_post_meta( $post_id,
      Resoc_Social_Editor::OG_TITLE, $title );
    update_post_meta( $post_id,
      Resoc_Social_Editor::OG_DESCRIPTION, $description );
/*
    WPSEO_Meta::set_value( 'opengraph-title', $title, $post_id );
    WPSEO_Meta::set_value( 'opengraph-description', $description, $post_id );
*/
    // Image

		$imageSettings = $_POST['rse-og-image-settings'];
		// See http://stackoverflow.com/questions/2496455/why-are-post-variables-getting-escaped-in-php
    $imageSettings = stripslashes( $imageSettings );

    error_log("DATA=" . $imageSettings);

    $imageId = $_POST['rse-og-image-id'];
    error_log("IMAGE ID " . $imageId);  

		// Check if the data have changed
		$existingImageSettings = get_post_meta( $post_id,
			Resoc_Social_Editor::OG_MASTER_IMAGE_SETTINGS, true );
		$existingImageId = get_post_meta( $post_id,
			Resoc_Social_Editor::OG_MASTER_IMAGE_ID, true );
		if ( $existingImageSettings && $existingImageSettings == $imageSettings && $existingImageId == $imageId ) {
      // No change in the data: nothing to do
      error_log("No change, nothing to do");
			return true;
		}

		update_post_meta( $post_id,
			Resoc_Social_Editor::OG_MASTER_IMAGE_SETTINGS, $imageSettings );
		update_post_meta( $post_id,
			Resoc_Social_Editor::OG_MASTER_IMAGE_ID, $imageId );

    $imageSettings = json_decode( $imageSettings, true );
		$faviconDesign = $imageSettings;

		$pic_path = $this->get_picture_url( $post_id );

		$masterImageUrl = wp_get_attachment_url( $imageId );
		$masterImageResult = wp_remote_get( $masterImageUrl );
		if (is_wp_error( $masterImageResult )) {
			// TODO
			error_log( "Cannot download master image: " . $masterImageResult->get_error_message() );
			return;
		}

		$masterImage = wp_remote_retrieve_body( $masterImageResult );

		$request = json_encode(array(
			'favicon_generation' => array(
				"api_key" => "87d5cd739b05c00416c4a19cd14a8bb5632ea563",
				"master_picture" => array(
					"type" => "inline",
					"content" => base64_encode( $masterImage )
				),
				"files_location" => array(
					"type" => "path",
					"path" => $pic_path
				),
				"favicon_design" => array(
					"resoc_open_graph" => array(
            "image" => array(
              "center_x" => $faviconDesign['imageCenterX'],
              "center_y" => $faviconDesign['imageCenterY'],
              "scale" => $faviconDesign['imageContainerWidthRatio']
            )
          )
				)
			)
    ));
    
    error_log("POSTED REQUEST = " . 
      json_encode( $request )
    );

		// Generate the Open Graph data
		$response = wp_remote_post('https://resoc.io/api/og-image', array(
      'body' => $request,
      'timeout' => 20
		));

		if ( is_wp_error( $response ) ) {
      error_log("We get NO answer");
			error_log($response->get_error_message());
		}
		else {
      error_log("We get an answer");
      error_log("Anwer is: " . $response['body']);

      $response = json_decode( $response['body'], true );

      // Very brittle
      $image_url = $response['favicon_generation_result']['favicon']['files_urls'][0];
      error_log( "Image URL " . $image_url );
      $og_image_id = $this->add_image_to_media_library( $image_url, $post_id );
/*
      $ogImageUrl = wp_get_attachment_url( $og_image_id );
      error_log( "OG Image URL=" . $ogImageUrl);
      
      WPSEO_Meta::set_value( 'opengraph-image', $ogImageUrl, $post_id );
*/
      update_post_meta( $post_id,
        Resoc_Social_Editor::OG_IMAGE_ID, $og_image_id );

      // Save these information internally, when WP SEO is not present

      /*

			$response = new Resoc_Social_Editor_Api_Response($response['body']);

			$zip_path = Resoc_Social_Editor::get_tmp_dir();
			if ( ! file_exists( $zip_path ) ) {
				if ( mkdir( $zip_path, 0755, true ) !== true ) {
					throw new InvalidArgumentException( sprintf( __( 'Cannot create directory %s to store the favicon package', FBRFG_PLUGIN_SLUG), $zip_path ) );
				}
			}
			$response->downloadAndUnpack( $zip_path );

			$this->store_pictures( $post_id, $response );

			Resoc_Social_Editor::remove_directory( $zip_path );

			update_post_meta( $post_id,
				Resoc_Social_Editor::OPTION_HTML_CODE,
        $response->getHtmlCode() );
      */
		}

		return true;
  }
  
  public function add_image_to_media_library( $image_url, $post_id ) {
    $upload_dir = wp_upload_dir();
    
    $image_data = file_get_contents($image_url);
  
    $filename = basename($image_url);
  
    if (wp_mkdir_p($upload_dir['path'])) {
      $file = $upload_dir['path'] . '/' . $filename;
    }
    else {
      $file = $upload_dir['basedir'] . '/' . $filename;
    }
  
    file_put_contents($file, $image_data);
    
    $wp_filetype = wp_check_filetype($filename, null);
    
    $attachment = array(
      'post_mime_type' => $wp_filetype['type'],
      'post_title' => sanitize_file_name($filename),
      'post_content' => '',
      'post_status' => 'inherit'
    );
    
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
  }


	public function get_picture_dir( $post_id ) {
		return Resoc_Social_Editor::get_files_dir( $post_id );
	}

	/**
	 * Returns http//somesite.com/blog/wp-content/upload/fbrfg/
	 */
	public function get_picture_url( $post_id ) {
		return Resoc_Social_Editor::get_files_url( $post_id );
	}

/*
	public function store_pictures( $post_id, $rfg_response ) {
		$working_dir = $this->get_picture_dir( $post_id );

		// Create destination directory if it doesn't exist
		if ( ! file_exists( $working_dir ) ) {
			mkdir( $working_dir, 0777, true );
		}

		// Move pictures to production directory
		$files = glob( $working_dir . '*' );
		foreach( $files as $file ) {
			if ( is_file( $file ) ) {
			    unlink( $file );
			}
    }
    error_log("PACKAGE DIR IS " . $rfg_response->getProductionPackagePath());
		$files = glob( $rfg_response->getProductionPackagePath() . '/*' );
		foreach( $files as $file ) {
      error_log("MOVE " . $file);
			if ( is_file( $file ) ) {
			    rename( $file, $working_dir . basename( $file ) );
			}
    }
    error_log("PICS moved to " . $working_dir);
    WPSEO_Meta::set_value( 'opengraph-image', $pic, $post_id );
  }
*/
	/**
	 * Generate HTML for displaying fields
	 * @param  array   $field Field data
	 * @param  boolean $echo  Whether to echo the field HTML or return it
	 * @return void
	 */
	public function display_field ( $data = array(), $post = false, $echo = true ) {

		// Get field info
		if ( isset( $data['field'] ) ) {
			$field = $data['field'];
		} else {
			$field = $data;
		}

		// Check for prefix on option name
		$option_name = '';
		if ( isset( $data['prefix'] ) ) {
			$option_name = $data['prefix'];
		}

		// Get saved data
		$data = '';
		if ( $post ) {

			// Get saved field data
			$option_name .= $field['id'];
			$option = get_post_meta( $post->ID, $field['id'], true );

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}

		} else {

			// Get saved option
			$option_name .= $field['id'];
			$option = get_option( $option_name );

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}

		}

		// Show default data if no option saved and default is supplied
		if ( $data === false && isset( $field['default'] ) ) {
			$data = $field['default'];
		} elseif ( $data === false ) {
			$data = '';
		}

		$html = '';

		switch( $field['type'] ) {

			case 'text':
			case 'url':
			case 'email':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" />' . "\n";
			break;

			case 'password':
			case 'number':
			case 'hidden':
				$min = '';
				if ( isset( $field['min'] ) ) {
					$min = ' min="' . esc_attr( $field['min'] ) . '"';
				}

				$max = '';
				if ( isset( $field['max'] ) ) {
					$max = ' max="' . esc_attr( $field['max'] ) . '"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '"' . $min . '' . $max . '/>' . "\n";
			break;

			case 'text_secret':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" />' . "\n";
			break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . $data . '</textarea><br/>'. "\n";
			break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' == $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . '/>' . "\n";
			break;

			case 'checkbox_multi':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( in_array( $k, (array) $data ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;

			case 'radio':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( $k == $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
			break;

			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( $k == $data ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;

			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;

			case 'image':
				$image_thumb = '';
				if ( $data ) {
					$image_thumb = wp_get_attachment_thumb_url( $data );
				}
				$html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
				$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'resoc-social-editor' ) . '" data-uploader_button_text="' . __( 'Use image' , 'resoc-social-editor' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'resoc-social-editor' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'resoc-social-editor' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
			break;

			case 'color':
				?><div class="color-picker" style="position:relative;">
			        <input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
			        <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
			    </div>
			    <?php
			break;

		}

		switch( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
			break;

			default:
				if ( ! $post ) {
					$html .= '<label for="' . esc_attr( $field['id'] ) . '">' . "\n";
				}

				$html .= '<span class="description">' . $field['description'] . '</span>' . "\n";

				if ( ! $post ) {
					$html .= '</label>' . "\n";
				}
			break;
		}

		if ( ! $echo ) {
			return $html;
		}

		echo $html;

	}

	/**
	 * Validate form field
	 * @param  string $data Submitted value
	 * @param  string $type Type of field to validate
	 * @return string       Validated value
	 */
	public function validate_field ( $data = '', $type = 'text' ) {

		switch( $type ) {
			case 'text': $data = esc_attr( $data ); break;
			case 'url': $data = esc_url( $data ); break;
			case 'email': $data = is_email( $data ); break;
		}

		return $data;
	}

	/**
	 * Add meta box to the dashboard
	 * @param string $id            Unique ID for metabox
	 * @param string $title         Display title of metabox
	 * @param array  $post_types    Post types to which this metabox applies
	 * @param string $context       Context in which to display this metabox ('advanced' or 'side')
	 * @param string $priority      Priority of this metabox ('default', 'low' or 'high')
	 * @param array  $callback_args Any axtra arguments that will be passed to the display function for this metabox
	 * @return void
	 */
	public function add_meta_box ( $id = '', $title = '', $post_types = array(), $context = 'advanced', $priority = 'default', $callback_args = null ) {
		// Get post type(s)
		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}

		// Generate each metabox
		foreach ( $post_types as $post_type ) {
			add_meta_box( $id, $title, array( $this, 'meta_box_content' ), $post_type, $context, $priority, $callback_args );
		}
	}

	/**
	 * Display metabox content
	 * @param  object $post Post object
	 * @param  array  $args Arguments unique to this metabox
	 * @return void
	 */
	public function meta_box_content ( $post, $args ) {
		echo Resoc_Social_Editor_Facebook_Editor::facebook_editor( $post );
	}

	/**
	 * Dispay field in metabox
	 * @param  array  $field Field data
	 * @param  object $post  Post object
	 * @return void
	 */
	public function display_meta_box_field ( $field = array(), $post ) {

		if ( ! is_array( $field ) || 0 == count( $field ) ) return;

		$field = '<p class="form-field"><label for="' . $field['id'] . '">' . $field['label'] . '</label>' . $this->display_field( $field, $post, false ) . '</p>' . "\n";

		echo $field;
	}

	/**
	 * Save metabox fields
	 * @param  integer $post_id Post ID
	 * @return void
	 */
	public function save_meta_boxes ( $post_id = 0 ) {
		// Try to patch Yoast SEO. If that works, there is nothing more to do
		if ( $this->patch_yoast_seo_meta_box() ) return;

		$this->add_meta_box('rse-meta-facebook', 'Share on Facebook',
			get_post_types( array( 'public' => true ) ) );

		if ( ! $post_id ) return;

		$post_type = get_post_type( $post_id );

		$fields = apply_filters( $post_type . '_custom_fields', array(), $post_type );

		if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

		foreach ( $fields as $field ) {
			if ( isset( $_REQUEST[ $field['id'] ] ) ) {
				update_post_meta( $post_id, $field['id'], $this->validate_field( $_REQUEST[ $field['id'] ], $field['type'] ) );
			} else {
				update_post_meta( $post_id, $field['id'], '' );
			}
		}
	}

}
