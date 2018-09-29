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

	private static function print_notice($message, $type = self::TYPE_INFO) {
		$allowed_types = [
			self::TYPE_SUCCESS,
			self::TYPE_INFO,
			self::TYPE_WARNING,
			self::TYPE_ERROR,
		];
		if ( ! in_array($type, $allowed_types) ) $type = self::TYPE_INFO;

		$args = [
			'type'      => $type,
			'message'   => $message
		];
		echo Wpb_Admin::render('notices/admin-notice', $args);
	}
}