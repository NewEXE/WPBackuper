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

	public function make_backup() {

		if ( ! Wpb_Helpers::connect_to_fs()) {
			return false;
		}

		$list_files = list_files($this->wp_dir);

		if ( Wpb_Helpers::is_zip_archive_available() ) {
			$this->create_archive_via_zip_archive($list_files);
		} else {
			$this->create_archive_via_pclzip($list_files);
		}
	}

	public function send_backup_to_browser_and_exit() {

		if ( ! Wpb_Helpers::connect_to_fs() ) {
			return false;
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

	public function send_backup_email() {

	}

	private function create_archive_via_zip_archive($files) {
		$zip = new ZipArchive();

		$zip_file_path = $this->backup_dir . 'wpb_backup_' . date('Y-m-d_H-i-s') . '.zip';
		if ( $zip->open($zip_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) ) {

			if ( ! Wpb_Helpers::connect_to_fs() ) {
				return false;
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

		return false;
	}

	private function create_archive_via_pclzip($files) {
		require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');

//		$pclzip = new PclZip();
	}
}