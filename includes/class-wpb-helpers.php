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
	 * Clean variables using sanitize_textarea_field. Arrays are cleaned recursively.
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
			return is_scalar( $var ) ? sanitize_textarea_field( $var ) : $var;
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
	 * @return bool
	 */
	public static function is_plugin_page($tab = null) {
		$is_plugin_page = false;

		if ( function_exists('get_current_screen') ) {
			$screen = get_current_screen();

			if ( $screen instanceof WP_Screen && isset($screen->id) ) {
				$is_plugin_page = $screen->id === ('tools_page_' . Wpb_Admin::PAGE_KEY);
			}
		} else {
			// If get_current_screen() is not available
			$parsed = @parse_url(self::server_var('REQUEST_URI'));

			if ( ! empty($parsed['path']) ) {
				if ( strpos($parsed['path'], 'wp-admin/tools.php') !== false ) {
					if ( self::get_var('page') === Wpb_Admin::PAGE_KEY ) {
						$is_plugin_page = true;
					}
				}
			}
		}

		if ( $is_plugin_page && ! is_null($tab) ) {
			$is_plugin_page = ($tab === self::get_var('tab', Wpb_Admin::TAB_GENERAL));
		}

		return $is_plugin_page;
	}

	/**
	 * @return bool
	 */
	public static function is_frontend_ajax()
	{
		//Try to figure out if frontend AJAX request... If we are DOING_AJAX; let's look closer
		if( defined('DOING_AJAX') && DOING_AJAX ) {
			//From wp-includes/functions.php, wp_get_referer() function.
			//Required to fix: https://core.trac.wordpress.org/ticket/25294
			$ref = '';
			$wp_http_ref = self::server_var('_wp_http_referer');
			if ( ! empty($wp_http_ref) ) {
				$ref = $wp_http_ref;
			} else {
				$http_ref = self::server_var('HTTP_REFERER');
				if ( ! empty($http_ref) ) {
					$ref = $http_ref;
				}
			}
			$ref = stripslashes((string) $ref);

			$script_filename = self::server_var('SCRIPT_FILENAME');

			//If referer does not contain admin URL and we are using the admin-ajax.php endpoint, this is likely a frontend AJAX request
			if( (strpos($ref, admin_url()) === false && basename($script_filename) === 'admin-ajax.php') )
				return true;
		}

		//If no checks triggered, we end up here - not an AJAX request.
		return false;
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

	/**
	 * @param $var
	 * @param $method
	 * @param string $default
	 * @return mixed
	 */
	private static function input_var($var, $method, $default = '') {

		$method = self::strtoupper($method);

		$supported = array(
			'GET',
			'POST',
			'COOKIE',
			'REQUEST',
			'SERVER',
			'ENV'
		);

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