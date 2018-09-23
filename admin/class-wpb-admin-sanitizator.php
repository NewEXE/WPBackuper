<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 23.09.18
 * Time: 21:29
 */

class Wpb_Admin_Sanitizator {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	private static $instance = null;

	private function __construct() {  }

	/**
	 * Get the class instance.
	 *
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @static
	 * @return self
	 */
	public static function instance() {

		if ( is_null(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param $value
	 * @return string
	 */
	public function sanitize_email($value) {

		$value = Wpb_Helpers::sanitize($value);

		if ( ! is_email($value) ) {
			return get_option('admin_email');
		}

		return $value;
	}

}