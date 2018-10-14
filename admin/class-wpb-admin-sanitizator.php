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

	public function sanitize_text_field($value) {
		return sanitize_text_field($value);
	}

	public function sanitize_textarea_field($value) {
		return sanitize_textarea_field($value);
	}

	public function sanitize_schedule_name($value) {
		$value = Wpb_Helpers::sanitize($value);

		$allowed_schedules = Wpb_Cron::get_schedules_list();
		if ( ! in_array($value, $allowed_schedules) ) {
			$value = 'wpb_monthly';
		}

		return $value;
	}

	/**
	 * @param $value
	 * @return string
	 */
	public function sanitize_email($value) {

		$value = Wpb_Helpers::sanitize($value);
		$value = sanitize_email($value);

		if ( ! is_email($value) ) {
			$user_email = Wpb_Helpers::get_user_email();

			return $user_email;
		}

		return $value;
	}

	/**
	 * @param string|null $value
	 * @return string Correct string: '' or 'true'
	 */
	public function sanitize_checkbox($value) {

		$value = Wpb_Helpers::sanitize($value);

		// Replace any non-empty string as 'true' (string)
		$value = ! empty($value) ? 'true' : '';

		return $value;
	}

}