<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 19.09.18
 * Time: 22:54
 */

class Wpb_Helpers
{
	private function __construct() { }

	public static function path($path = '') {

		$path = ltrim($path, '/');

		return WPB_PLUGIN_MAIN_DIR . $path;
	}

	public static function plugin_url($tab = null, $query_args = null) {

		$url = admin_url(Wpb_Admin::PLUGIN_URL_BASE . '?page=' . Wpb_Admin::PAGE_KEY);

		$q = [];
		if ( ! is_null($tab) ) {
			$q['tab'] = $tab;
		}

		if ( ! is_null($query_args) ) {
			if ( ! is_array($query_args) ) {
				$query_args = [$query_args];
			}
			$q = array_merge($query_args, $q);
		}

		if ( ! empty($q) ) {
			$url = add_query_arg($q, $url);
		}

		return $url;
	}

	public static function asset_url($path = '') {

		$path = ltrim($path, '/');

		return plugin_dir_url(WPB_PLUGIN_MAIN_FILE) . $path;
	}

	/**
	 * Inverse function to parse_url.
	 *
	 * Source: https://github.com/igorsimdyanov/php7/blob/master/www/lib/http_build_url.php
	 *
	 * @param array $parsed Result of parse_url()
	 * @return bool|string URL or false if incorrect param passed
	 */
	public static function http_build_url($parsed)
	{
		if ( ! is_array($parsed) ) return false;

		$url = '';

		if ( ! empty($parsed['scheme']) ) {
			$sep = (strtolower($parsed['scheme']) == 'mailto' ? ':' : '://');
			$url .= $parsed['scheme'] . $sep;
		}

		if ( ! empty($parsed['pass']) ) {
			$url .= "{$parsed['user']}:{$parsed['pass']}@";
		} elseif ( ! empty($parsed['user']) ) {
			$url .= "{$parsed['user']}@";
		}

		if ( ! empty($parsed['query']) && ! is_scalar($parsed['query']) ) {
			$parsed['query'] = http_build_query($parsed['query']);
		}

		if ( ! empty($parsed['host']) )     $url .= $parsed['host'];
		if ( ! empty($parsed['port']) )     $url .= ":".$parsed['port'];
		if ( ! empty($parsed['path']) )     $url .= $parsed['path'];
		if ( ! empty($parsed['query']) )    $url .= "?".$parsed['query'];
		if ( ! empty($parsed['fragment']) ) $url .= "#".$parsed['fragment'];

		return $url;
	}

	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $var Data to sanitize.
	 * @return string|array
	 * @since 1.0.0
	 */
	public static function sanitize( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'self::sanitize', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}

	/**
	 * Get the input GET var with sanitization.
	 *
	 * @param $key
	 * @param string $default
	 * @return array|string
	 */
	public static function get_var($key, $default = '') {

		$value = self::input_var($key, 'GET', $default);

		return $value;
	}

	/**
	 * Get the input POST var with sanitization.
	 *
	 * @param $key
	 * @param string $default
	 * @return array|string
	 */
	public static function post_var($key, $default = '') {

		$value = self::input_var($key, 'POST', $default);

		return $value;
	}

	/**
	 * Get the input SERVER var with sanitization.
	 *
	 * @param $key
	 * @param string $default
	 * @return array|string
	 */
	public static function server_var($key, $default = '') {

		$value = self::input_var($key, 'SERVER', $default);

		return $value;
	}

	/**
	 * Get the input REQUEST var with sanitization.
	 *
	 * @param $key
	 * @param string $default
	 * @return array|string
	 */
	public static function request_var($key, $default = '') {

		$value = self::input_var($key, 'REQUEST', $default);

		return $value;
	}

	/**
	 * @param null|string $tab
	 * @param array $query_args
	 * @return bool
	 */
	public static function is_plugin_page($tab = null, $query_args = null) {
		$is_plugin_page = false;

		if ( function_exists('get_current_screen') ) {
			$screen = get_current_screen();
			$is_plugin_page = $screen->id === ('tools_page_' . Wpb_Admin::PAGE_KEY);
		} else {
			// If get_current_screen() is not available
			$parsed = @parse_url(self::server_var('REQUEST_URI'));

			if ( ! empty($parsed['path']) ) {
				if ( strpos($parsed['path'], 'wp-admin/' . Wpb_Admin::PLUGIN_URL_BASE) !== false ) {
					if ( self::get_var('page') === Wpb_Admin::PAGE_KEY ) {
						$is_plugin_page = true;
					}
				}
			}
		}

		if ( $is_plugin_page && ! is_null($tab) ) {
			$is_plugin_page = ($tab === self::get_var('tab', Wpb_Admin::TAB_GENERAL));
		}

		if ( $is_plugin_page && is_array($query_args) ) {
			$get = Wpb_Helpers::sanitize($_GET);
			$intersected = array_intersect_assoc($get, $query_args);
			$is_plugin_page = count($intersected) === count($query_args);
		}

		return $is_plugin_page;
	}

	/**
	 * Wrapper for mb_strtoupper which see's if supported first.
	 *
	 * @since  1.0.0
	 * @param  string $string String to format.
	 * @return string
	 */
	public static function strtoupper( $string ) {
		return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $string ) : strtoupper( $string );
	}

	public static function is_bedrock() {
		static $is_bedrock = null;

		if ( ! is_null($is_bedrock) ) {
			return $is_bedrock;
		}

		if ( is_wp_error($maybe_error = Wpb_Helpers::connect_to_fs()) ) {
			return $maybe_error;
		}

		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */
		global $wp_filesystem;

		$wp_path = get_home_path();

		if ( empty($wp_path) ) {
			$is_bedrock = false;
			return $is_bedrock;
		}

		$is_bedrock = ( $wp_filesystem->is_dir("$wp_path/app/mu-plugins") && $wp_filesystem->is_dir("$wp_path/wp/wp-includes") );

		return $is_bedrock;
	}

	/**
	 * @return bool|WP_Error
	 */
	public static function connect_to_fs()  {

		$credentials = request_filesystem_credentials('');

		if( ! $credentials ) {
			return new WP_Error('wpb_fs_credentials_fail', __('Please provide filesystem credentials', 'wpb'));
		}

		if( ! WP_Filesystem($credentials) ) {
			request_filesystem_credentials('', '', true);
			return new WP_Error('wpb_fs_credentials_fail', __('Filesystem credentials are incorrect', 'wpb'));
		}

		return true;
	}

	public static function is_fs_connected() {
		return function_exists( 'WP_Filesystem' ) ? WP_Filesystem() : @is_writable(get_temp_dir());
	}

	public static function is_zip_archive_available() {
		return class_exists( 'ZipArchive', false );
	}

	public static function is_exec_available() {
		static $exec_enabled = null;

		if ( ! is_null($exec_enabled) ) {
			return $exec_enabled;
		}

		if ( ! function_exists('exec') || in_array(strtolower(ini_get('safe_mode')), [ 'on', '1' ], true) ) {
			$exec_enabled = false;
			return $exec_enabled;
		}

		$disabled_functions = explode(',', ini_get('disable_functions'));
		if ( ! in_array('exec', $disabled_functions) ) {
			if ( ! @exec('echo WORKS') === 'WORKS' ) {
				$exec_enabled = false;
				return $exec_enabled;
			}
		}

		$exec_enabled = true;
		return $exec_enabled;
	}

	public static function is_temp_dir_writable() {
		if ( is_wp_error(Wpb_Helpers::connect_to_fs()) ) {
			return false;
		}

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		return $wp_filesystem->is_writable(get_temp_dir());
	}

	/**
	 * @param string $property
	 * @param mixed $default
	 *
	 * @return mixed|WP_User
	 */
	public static function safe_wp_get_current_user($property = null, $default = false) {
		if (
			function_exists('wp_get_current_user') &&
			($user = wp_get_current_user()) instanceof WP_User
		) {
			if ( is_null($property) ) {
				return $user;
			} elseif ( ! empty($user->$property) ) {
				return $user->$property;
			} else {
				return $default;
			}
		}

		return $default;
	}

	public static function get_user_email($default = 'dummy@example.com') {
		return Wpb_Helpers::safe_wp_get_current_user('user_email', $default);
	}

	public static function is_admin() {
		return current_user_can('manage_options');
	}

	/**
	 * @param $var
	 * @param $method
	 * @param string $default
	 * @return mixed
	 */
	private static function input_var($var, $method, $default = '') {

		$method = self::strtoupper($method);

		$supported = [
			'GET',
			'POST',
			'COOKIE',
			'REQUEST',
			'SERVER',
			'ENV'
		];

		if ( ! in_array($method, $supported) ) return false;

		$superglobalName = "_$method";

		global $$superglobalName;           // this is needed even for superglobals
		$superglobal = $$superglobalName;   // without this on some environments doesn't work

		if ( isset($superglobal[$var]) ) {
			$value = $superglobal[$var];
		} else {
			return $default;
		}

		$value = self::sanitize($value);

		return $value;
	}

}

/**
 * Dump variable.
 *
 * @param mixed $data Any data
 */
function wpb_dump($data = null) {
	echo '<pre>';
	var_dump($data);
	echo '</pre>';
}

/**
 * Dump variable and die.
 *
 * @param mixed $data Any data
 * @param bool $wp_die
 */
function wpb_dd($data = null, $wp_die = false) {
	wpb_dump($data);
	if ( $wp_die ) {
		wp_die();
	}
	exit(0);
}