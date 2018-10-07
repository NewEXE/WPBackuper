<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 28.09.18
 * Time: 0:02
 */

/**
 * Class for printing formatted notices messages.
 */
class Wpb_Admin_Notices {

	const TYPE_SUCCESS  = 'success';
	const TYPE_INFO     = 'info';
	const TYPE_WARNING  = 'warning';
	const TYPE_ERROR    = 'error';

	public function maybe_add_fs_credentials_notice() {
		// If FS is connected, show nothing.
		if ( Wpb_Helpers::is_fs_connected() ) return;

		// If we on plugin's status tab, showing notice is not necessary.
		if ( Wpb_Helpers::is_plugin_page(Wpb_Admin::TAB_STATUS) ) return;

		$needs_notice =
			( Wpb_Helpers::is_plugin_page() || get_option(Wpb_Admin::OPTION_IS_CRON_SET, false) );

		if ( $needs_notice ) {
			$msg = sprintf(
			/* translators: 1: Admin URL to plugin status page */
				__( 'WPBackuper Filesystem connection issue. See <a href="%1$s">plugin status page</a> for details.' ),
				Wpb_Helpers::plugin_url(Wpb_Admin::TAB_STATUS)
			);
			self::print_notice($msg, self::TYPE_ERROR);
		}
	}

	public function maybe_add_settings_updated_notice() {
		if ( Wpb_Helpers::get_var('settings-updated', false) && Wpb_Helpers::is_plugin_page() ) {
			$msg = __('Settings saved.', 'wpb');
			self::print_notice($msg, self::TYPE_SUCCESS);
		}
	}

	public function maybe_add_flash_notice() {

		$flash = self::flash('wpb_flash');

		if ( $flash && Wpb_Helpers::is_plugin_page() ) {
			foreach ($flash as $f) {
				self::print_notice($f['message'], $f['type']);
			}
		}
	}

	public static function flash($message, $type = null) {

		if ( ! is_null($type) ) { // 'Set' mode
			$type = self::get_correct_type($type);

			$_POST['wpb_flash'][] = compact('message', 'type');
		} else { // 'Get' mode
			return Wpb_Helpers::post_var('wpb_flash', false);
		}
	}

	private static function print_notice($message, $type = self::TYPE_INFO) {
		$type = self::get_correct_type($type);

		$args = [
			'type'      => $type,
			'message'   => $message
		];
		echo Wpb_Admin::render('notices/admin-notice', $args);
	}

	private static function get_correct_type($type) {
		$allowed_types = [
			self::TYPE_SUCCESS,
			self::TYPE_INFO,
			self::TYPE_WARNING,
			self::TYPE_ERROR,
		];
		if ( ! in_array($type, $allowed_types) ) $type = self::TYPE_INFO;

		return $type;
	}
}