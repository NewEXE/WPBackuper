<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 22.09.18
 * Time: 19:02
 */

class Wpb_Files_Backuper extends Wpb_Abstract_Backuper {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * @var Wpb_Archiver
	 */
	private $archiver;

	/**
	 * @var WP_Error
	 */
	private $errors;

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

	private function __construct() {
		$this->archiver = new Wpb_Zipper(
			get_temp_dir(),
			'wpb_files_backup_' . date('Y-m-d_H-i-s') . '.zip'
		);
		$this->errors   = new WP_Error();
	}

	public function get_backup_file_path() {
		return $this->archiver->get_archive_fullpath();
	}

	/**
	 * @return bool
	 */
	public function make_backup() {

		if ( ! Wpb_Helpers::is_fs_connected() ) {
			$this->errors->add('fs_not_connected', __('FS not connected', 'wpb'));
			return false;
		}

		$wp_dir = Wpb_Helpers::get_wp_dir();

		//todo escape from list_files() that working correctly only with 'direct' FS
		$list_files = Wpb_Helpers::list_files($wp_dir);

		if ( ! $list_files ) {
			$this->errors->add(
				'list_files_error',
				__('Something went wrong while getting list files for backup', 'wpb'),
				$list_files
			);
			return false;
		}

		$this->archiver->push_files($list_files);

		if ( ! $this->archiver->create_archive($wp_dir) ) {
			// todo find way for sharing errors from zipper
			return false;
		}

		return true;
	}

	public function send_backup_to_browser_and_exit() {

		if ( ! Wpb_Helpers::is_fs_connected() ) {
			$this->errors->add('fs_not_connected', __('FS not connected', 'wpb'));
			return false;
		}

		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */
		global $wp_filesystem;

		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename=' . $this->archiver->get_archive_filename());
		header('Content-Length: ' . $wp_filesystem->size($this->archiver->get_archive_fullpath()));

		echo $wp_filesystem->get_contents($this->archiver->get_archive_fullpath());
		exit(0);
	}

	/**
	 * @param null $subject
	 * @param null $message
	 * @param null $headers
	 *
	 * @return bool
	 */
	public function send_backup_to_email($subject = null, $message = null, $headers = null) {
		$to = get_option(Wpb_Admin::OPTION_BACKUP_EMAIL, Wpb_Helpers::get_user_email());

		if ( is_null($subject) ) {
			$subject = __('WPBackup: your WP files backup', 'wpb');
		}

		if ( is_null($message) ) {
			$message = __('Howdy! Here your backup of WordPress files.', 'wpb');
		}

		if ( is_null($headers) ) {
			$headers = '';
		}

		$attachments = [
			$this->archiver->get_archive_fullpath(),
		];

		if ( ! wp_mail($to, $subject, $message, $headers, $attachments) ) {
			$this->errors->add('wp_mail_error', __('Something went wrong while sending email via wp_mail', 'wpb'));
			return false;
		}

		return true;
	}

	public function get_errors() {
		return $this->errors;
	}
}