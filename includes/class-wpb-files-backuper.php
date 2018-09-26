<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 22.09.18
 * Time: 19:02
 */

class Wpb_Files_Backuper {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	private static $instance = null;

	private $wp_dir;

	private $backup_dir;

	private $backup_file_path = null;

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
		$this->backup_dir = get_temp_dir();
		$this->wp_dir = Wpb_Helpers::is_bedrock() ? dirname(get_home_path()) : get_home_path();
	}

	public function get_backup_file_path() {
		return $this->backup_file_path;
	}

	/**
	 * @return true|WP_Error
	 */
	public function make_backup() {

		if ( is_wp_error($maybe_error = Wpb_Helpers::connect_to_fs()) ) {
			return $maybe_error;
		}

		$list_files = list_files($this->wp_dir);
		if ( ! $list_files ) {
			return new WP_Error(
				'files_backuper_list_files_error',
				__('Something went wrong while getting list files for backup', 'wpb')
			);
		}

		if ( Wpb_Helpers::is_zip_archive_available() ) {
			return $this->create_archive_via_zip_archive($list_files);
		} else {
			return $this->create_archive_via_pclzip($list_files);
		}
	}

	public function send_backup_to_browser_and_exit() {

		if ( is_wp_error($maybe_error = Wpb_Helpers::connect_to_fs()) ) {
			return $maybe_error;
		}

		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */
		global $wp_filesystem;

		$file_name = basename($this->backup_file_path);

		header('Content-Type: application/zip');
		header("Content-Disposition: attachment; filename=$file_name");
		header('Content-Length: ' . $wp_filesystem->size($this->backup_file_path));

		echo $wp_filesystem->get_contents($this->backup_file_path);
		exit;
	}

	/**
	 * @param null $subject
	 * @param null $message
	 * @param null $headers
	 *
	 * @return true|WP_Error
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
			$this->backup_file_path
		];

		if ( ! wp_mail($to, $subject, $message, $headers, $attachments) ) {
			return new WP_Error('files_backuper_wp_mail_error', __('Something went wrong while sending email via wp_mail', 'wpb'));
		}

		return true;
	}

	/**
	 * @param $files
	 *
	 * @return true|WP_Error
	 */
	private function create_archive_via_zip_archive($files) {
		$zip = new ZipArchive();

		$zip_file_path = $this->backup_dir . 'wpb_files_backup_' . date('Y-m-d_H-i-s') . '.zip';
		if ( $zip->open($zip_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) ) {

			if ( is_wp_error($maybe_error = Wpb_Helpers::connect_to_fs()) ) {
				return $maybe_error;
			}

			/**
			 * @var WP_Filesystem_Base $wp_filesystem
			 */
			global $wp_filesystem;

			foreach ( $files as $file ) {

				$file_without_base = $file;
				if ( substr($file, 0, strlen($this->wp_dir)) === $this->wp_dir ) {
					$file_without_base = substr($file, strlen($this->wp_dir));
				}

				if ( $wp_filesystem->is_dir($file) ) {
					$zip->addEmptyDir($file_without_base);
				} else {
					$zip->addFile($file, $file_without_base);
				}
			}

			if ( $zip->close() ) {
				$this->backup_file_path = $zip_file_path;
				return true;
			}
		}

		return new WP_Error('files_backuper_za_error', __('Something went wrong while archivation via ZipArchive', 'wpb'));
	}

	/**
	 * @param $files
	 *
	 * @return WP_Error
	 */
	private function create_archive_via_pclzip($files) {

		return new WP_Error('files_backuper_pclzip_stub', __('Archivation via PclZip not supported for now... ', 'wpb'));

//		require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
//		$pclzip = new PclZip();
	}
}