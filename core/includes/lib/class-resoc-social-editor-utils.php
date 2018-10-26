<?php
class Resoc_Social_Editor_Utils {
  public static function is_yoast_seo_active() {
		return is_plugin_active( 'wordpress-seo/wp-seo.php' );
  }
}
