<?php
class Resoc_Social_Editor_Utils {
  /**
   * Returns the name of the plugin which might cause a conflict.
   * Returns NULL if there is no such conflicting plugin.
   */
  public static function conflicting_plugin() {
    if ( is_plugin_active( 'business-directory-plugin/business-directory-plugin.php' ) ) {
      return "Business Directory Plugin";
    }
    if ( defined( 'AIOSEOP_VERSION' ) ) {
      return "All in One SEO Pack";
    }
    // TODO: Add additional conflicting plugins

    return NULL;
  }

  public static function is_yoast_seo_active() {
		return is_plugin_active( 'wordpress-seo/wp-seo.php' );
  }

  public static function add_image_to_media_library( $image_data, $post_id, $filename = 'og-image.jpg', $attach_id = NULL ) {
    $upload_dir = wp_upload_dir();

    if (wp_mkdir_p($upload_dir['path'])) {
      $file = $upload_dir['path'] . '/' . $filename;
    }
    else {
      $file = $upload_dir['basedir'] . '/' . $filename;
    }

    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null);

    if (! $attach_id) {
      // Create new attachement if there is none
      // (else, the image is attached to the existing attachement)
      $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
      );

      $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    }

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
  }

  public static function generate_resoc_image($api_entry_point_url, $request, $filename = NULL, $attach_id = NULL) {
		$response = wp_remote_post($api_entry_point_url, array(
      'body' => json_encode( $request ),
      'timeout' => 10
		));

		if ( is_wp_error( $response ) ) {
      error_log( "Error while generating: " . $response->get_error_message() );
			throw new Exception( $response->get_error_message() );
    }

    return Resoc_Social_Editor_Utils::add_image_to_media_library( $response['body'], $post_id, $filename, $attach_id );
  }

  public static function get_image_content_by_id( $image_id ) {
		$image_url = wp_get_attachment_url( $image_id );
		$result = wp_remote_get( $image_url );
		if (is_wp_error( $result )) {
			error_log( "Cannot download image: " . $result->get_error_message() );
			throw new Exception( $result->get_error_message() );
		}
		return wp_remote_retrieve_body( $result );
  }

  // Returns '20181030-114327'
  public static function time_to_filename_fragment() {
    return date('Ymd-his');
  }

  // Analytics / anonymization

  public static function generate_salt() {
    return hash('sha256', strval( rand() ) );
  }

  public static function get_salt() {
    $salt = get_option( Resoc_Social_Editor::OPTION_SALT );
    if ( ! $salt ) {
      $salt = Resoc_Social_Editor_Utils::generate_salt();
      update_option( Resoc_Social_Editor::OPTION_SALT, $salt );
    }
    return $salt;
  }

  public static function anonymize_data( $data ) {
    return hash('sha256',
    Resoc_Social_Editor_Utils::get_salt() . $data
    );
  }

  public static function add_analytics_data( $api_request ) {
    if ( get_option( Resoc_Social_Editor::OPTION_SEND_ANONYMOUS_DATA, false ) ) {
      $api_request['analytics'] = array(
        'hashed_site_url' => Resoc_Social_Editor_Utils::anonymize_data( get_site_url() ),
        'hashed_post_id' => Resoc_Social_Editor_Utils::anonymize_data( get_the_ID() ),
        'hashed_user_id' => Resoc_Social_Editor_Utils::anonymize_data( get_current_user_id() )
      );
    }
    return $api_request;
  }
}
