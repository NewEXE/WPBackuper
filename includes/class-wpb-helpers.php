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
			$query_args = self::array_wrap($query_args);
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
	 *
	 *
	 * @param bool $with_query_args
	 * @param bool $save_flash
	 *
	 * @return string
	 */
	public static function current_url($with_query_args = true, $save_flash = true) {
		if ( $with_query_args ) {
			$url = home_url(add_query_arg(null, null));
		} else {
			$url = home_url(parse_url(self::server_var('REQUEST_URI'), PHP_URL_PATH));
			if ( self::is_plugin_page() ) {
				// Restore 'page' and 'tab' query vars.
				$url = add_query_arg([
					'page' => Wpb_Admin::PAGE_KEY,
					'tab' => self::get_var('tab', Wpb_Admin::TAB_GENERAL)
				], $url);
			}
		}

		if ( $save_flash ) {
			$flash = Wpb_Admin_Notices::flash();
			if ( $flash ) {
				$url = self::add_query_arg(['wpb_flash' => $flash], $url);
			}
		}

		return $url;
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
			$sep = (strtolower($parsed['scheme']) === 'mailto' ? ':' : '://');
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
	 * Based on add_query_arg(), but with http_build_query() instead of build_query().
	 *
	 * @see add_query_arg()
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $key   Either a query variable key, or an associative array of query variables.
	 * @param string       $value Optional. Either a query variable value, or a URL to act upon.
	 * @param string       $url   Optional. A URL to act upon.
	 * @return string New URL query string (unescaped).
	 */
	public static function add_query_arg() {
		$args = func_get_args();
		if ( is_array( $args[0] ) ) {
			if ( count( $args ) < 2 || false === $args[1] )
				$uri = $_SERVER['REQUEST_URI'];
			else
				$uri = $args[1];
		} else {
			if ( count( $args ) < 3 || false === $args[2] )
				$uri = $_SERVER['REQUEST_URI'];
			else
				$uri = $args[2];
		}

		if ( $frag = strstr( $uri, '#' ) )
			$uri = substr( $uri, 0, -strlen( $frag ) );
		else
			$frag = '';

		if ( 0 === stripos( $uri, 'http://' ) ) {
			$protocol = 'http://';
			$uri = substr( $uri, 7 );
		} elseif ( 0 === stripos( $uri, 'https://' ) ) {
			$protocol = 'https://';
			$uri = substr( $uri, 8 );
		} else {
			$protocol = '';
		}

		if ( strpos( $uri, '?' ) !== false ) {
			list( $base, $query ) = explode( '?', $uri, 2 );
			$base .= '?';
		} elseif ( $protocol || strpos( $uri, '=' ) === false ) {
			$base = $uri . '?';
			$query = '';
		} else {
			$base = '';
			$query = $uri;
		}

		wp_parse_str( $query, $qs );
		$qs = urlencode_deep( $qs ); // this re-URL-encodes things that were already in the query string
		if ( is_array( $args[0] ) ) {
			foreach ( $args[0] as $k => $v ) {
				$qs[ $k ] = $v;
			}
		} else {
			$qs[ $args[0] ] = $args[1];
		}

		foreach ( $qs as $k => $v ) {
			if ( $v === false )
				unset( $qs[$k] );
		}

		$ret = http_build_query( $qs );
		$ret = trim( $ret, '?' );
		$ret = preg_replace( '#=(&|$)#', '$1', $ret );
		$ret = $protocol . $base . $ret . $frag;
		$ret = rtrim( $ret, '?' );
		return $ret;
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
	 * Get the input SESSION var with sanitization.
	 *
	 * @param $key
	 * @param string $default
	 * @return array|string
	 */
	public static function session_var($key, $default = '') {

		$value = self::input_var($key, 'SESSION', $default);

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

		if ( ! Wpb_Helpers::is_fs_connected() ) {
			return null;
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

	public static function get_filesystem_credentials_from_constants() {
		$credentials = [];

		$credentials['hostname']    = defined('FTP_HOST') ? FTP_HOST : '';
		$credentials['username']    = defined('FTP_USER') ? FTP_USER : '';
		$credentials['password']    = defined('FTP_PASS') ? FTP_PASS : '';
		$credentials['public_key']  = defined('FTP_PUBKEY') ? FTP_PUBKEY : '';
		$credentials['private_key'] = defined('FTP_PRIKEY') ? FTP_PRIKEY : '';

		// Sanitize the hostname, some people might pass in odd-data.
		// Strip any schemes off.
		$credentials['hostname'] = preg_replace('|\w+://|', '', $credentials['hostname']);

		if ( strpos($credentials['hostname'], ':') ) {
			list( $credentials['hostname'], $credentials['port'] ) = explode(':', $credentials['hostname'], 2);
			if ( ! is_numeric($credentials['port']) )
				unset($credentials['port']);
		} else {
			unset($credentials['port']);
		}

		if ( function_exists('get_filesystem_method') ) {
			$fs_method = get_filesystem_method();
		} else {
			$fs_method = defined('FS_METHOD') ? FS_METHOD : false;
		}

		if ( ( defined( 'FTP_SSH' ) && FTP_SSH ) || ( defined( 'FS_METHOD' ) && 'ssh2' === FS_METHOD ) ) {
			$credentials['connection_type'] = 'ssh';
		} elseif ( ( defined( 'FTP_SSL' ) && FTP_SSL ) && 'ftpext' === $fs_method ) {
			// Only the FTP Extension understands SSL.
			$credentials['connection_type'] = 'ftps';
		} elseif ( ! isset( $credentials['connection_type'] ) ) {
			// All else fails, default to FTP.
			$credentials['connection_type'] = 'ftp';
		}

		return $credentials;
	}

	/**
	 * Wrapper for request_filesystem_credentials.
	 *
	 * @param string $form_post
	 *
	 * @return bool
	 */
	public static function connect_to_fs($form_post = '')  {
		if ( self::is_fs_connected() ) {
			return true;
		}

		if ( empty($form_post) ) {
			// Trying to use current url with GET params (if present)
			$form_post = self::current_url();
		}

		$credentials = request_filesystem_credentials($form_post);

		if( ! $credentials ) {
			return false;
		}

		if( ! WP_Filesystem($credentials) ) {
			request_filesystem_credentials($form_post, '', true);
			return false;
		}

		return true;
	}

	/**
	 * Performs filesystem connection (direct/ftp/ssh)
	 * check without requesting credentials.
	 *
	 * @return bool
	 */
	public static function is_fs_connected() {
		global $wp_filesystem;

		if ( $wp_filesystem instanceof WP_Filesystem_Base) {
			if ( $wp_filesystem->connect() ) {
				return true;
			}
		}

		if ( function_exists('WP_Filesystem') ) {
			return (bool) WP_Filesystem(self::get_filesystem_credentials_from_constants());
		}

		if ( function_exists('get_filesystem_method') ) {
			return get_filesystem_method() === 'direct';
		}

		$method = defined('FS_METHOD') ? FS_METHOD : false;

		if ( $method === 'direct' ) {
			return true;
		}

		// Let's perform FS writing test from get_filesystem_method().

		$context = WP_CONTENT_DIR;
		if ( WP_LANG_DIR === $context && ! is_dir( $context ) ) {
			$context = dirname( $context );
		}
		$context = trailingslashit( $context );

		$temp_file_name = $context . 'temp-write-test-' . time();
		$temp_handle = @fopen($temp_file_name, 'w');
		if ( $temp_handle ) {

			$wp_file_owner = $temp_file_owner = false;
			if ( function_exists('fileowner') ) {
				$wp_file_owner = @fileowner( __FILE__ );
				$temp_file_owner = @fileowner( $temp_file_name );
			}

			if ( $wp_file_owner !== false && $wp_file_owner === $temp_file_owner ) {
				$method = 'direct';
			}

			@fclose($temp_handle);
			@unlink($temp_file_name);
		}

		if ( $method === 'direct' ) {
			return true;
		}

		return false;
	}

	public static function get_wp_dir() {
		return self::is_bedrock() ? dirname(get_home_path()) : get_home_path();
	}

	public static function is_zip_archive_available() {
		return class_exists( 'ZipArchive', false );
	}

	public static function get_file_ext($path) {
		$ext = @pathinfo($path, PATHINFO_EXTENSION);

		return empty($ext) ? false : $ext;
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
		if ( ! Wpb_Helpers::is_fs_connected() ) {
			return false;
		}

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		return $wp_filesystem->is_writable(get_temp_dir());
	}

	public static function clean_temp_dir() {
		if ( ! Wpb_Helpers::is_fs_connected() ) {
			return false;
		}

		/** @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		$files = list_files(get_temp_dir());

		foreach ($files as $file) {
			$wp_filesystem->delete($file, true);
		}

		return true;
	}

	/**
	 * Returns a listing of all files in the specified folder and all subdirectories up to 100 levels deep.
	 * The depth of the recursiveness can be controlled by the $levels param.
	 *
	 * @param string $folder Optional. Full path to folder. Default empty.
	 * @param int    $levels Optional. Levels of folders to follow, Default 100 (PHP Loop limit).
	 * @return bool|array False on failure, Else array of files
	 */
	public static function list_files( $folder = '', $levels = 100 ) {
		if ( empty( $folder ) ) {
			return false;
		}

		$folder = trailingslashit( $folder );

		if ( ! $levels ) {
			return false;
		}

		$files = [];

		$dir = @opendir( $folder );
		if ( $dir ) {
			while ( ( $file = readdir( $dir ) ) !== false ) {
				// Skip current and parent folder links.
				if ( in_array( $file, [ '.', '..' ], true ) ) {
					continue;
				}

				if ( is_dir( $folder . $file ) ) {
					$files2 = self::list_files( $folder . $file, $levels - 1 );
					if ( $files2 ) {
						$files = array_merge($files, $files2 );
					} else {
						$files[] = $folder . $file . '/';
					}
				} else {
					$files[] = $folder . $file;
				}
			}
		}
		@closedir( $dir );

		return $files;
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
		return self::safe_wp_get_current_user('user_email', $default);
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
			'REQUEST',
			'COOKIE',
			'SESSION',
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

	/**
	 * If the given value is not an array and not null, wrap it in one.
	 *
	 * @param  mixed  $value
	 * @return array
	 */
	public static function array_wrap($value)
	{
		if ( is_null($value) ) {
			return [];
		}

		return ! is_array($value) ? [$value] : $value;
	}

	public static function wrap_code_tag($array, $delimiter = ' ') {
		$html = '';
		foreach ( $array as $item ) {
			$html .= '<code>' . $item . '</code>' . $delimiter;
		}
		return $html;
	}

	public static function is_debug() {
		return defined('WP_DEBUG') && WP_DEBUG;
	}

	public static function is_debug_log() {
		return defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
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