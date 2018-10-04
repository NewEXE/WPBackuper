<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 23.09.18
 * Time: 2:06
 */

class Wpb_Zipper implements Wpb_Archiver {

	/**
	 * Directory for storing archives, with '/'.
	 *
	 * @var string
	 */
	private $archives_dir;

	/**
	 * Filename for building fullpath to archive.
	 *
	 * @var string
	 */
	private $archive_filename;

	/**
	 * @var bool
	 */
	private $is_archive_created = false;

	/**
	 * @var string[]
	 */
	private $files = [];

	/**
	 * @var WP_Error
	 */
	private $errors;

	/**
	 * Wpb_Zipper constructor.
	 *
	 * @param string $archives_dir
	 * @param string $archive_filename
	 */
	public function __construct($archives_dir, $archive_filename) {
		$this->set_archives_dir($archives_dir);
		$this->set_archive_filename($archive_filename);
		$this->errors = new WP_Error();
	}

	/**
	 * @return string
	 */
	public function get_archives_dir() {
		return $this->archives_dir;
	}

	/**
	 * @param string $archives_dir
	 */
	public function set_archives_dir($archives_dir) {
		$this->archives_dir = trailingslashit($archives_dir);
	}

	/**
	 * @return string
	 */
	public function get_archive_filename() {
		return $this->archive_filename;
	}

	/**
	 * @param string $archive_filename
	 */
	public function set_archive_filename($archive_filename) {
		$archive_filename = trim($archive_filename, '/\\.');
		if ( ! Wpb_Helpers::get_file_ext($archive_filename) ) {
			$archive_filename .= '.zip';
		}

		$this->archive_filename = $archive_filename;
	}

	public function get_archive_fullpath() {
		return $this->archives_dir . $this->archive_filename;
	}

	public function get_errors() {
		return $this->errors;
	}

	public function is_archive_created() {
		return $this->is_archive_created;
	}

	public function push_file($file_path) {
		$this->files[] = $file_path;
	}

	public function push_files($file_paths) {
		foreach ( $file_paths as $file_path ) {
			$this->push_file($file_path);
		}
	}

	/**
	 * @param string $remove_prefix
	 *
	 * @return bool
	 */
	public function create_archive($remove_prefix = '') {
		if ( Wpb_Helpers::is_zip_archive_available() ) {
			return $this->create_archive_via_zip_archive($remove_prefix);
		} else {
			return $this->create_archive_via_pclzip($remove_prefix);
		}
	}

	/**
	 * @param string $remove_prefix Remove this substring from left part of each file/dir.
	 *
	 * @return bool
	 */
	private function create_archive_via_zip_archive($remove_prefix) {
		$zip = new ZipArchive();

		if ( $zip->open($this->archives_dir . $this->archive_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) ) {

			if ( ! Wpb_Helpers::is_fs_connected() ) {
				$this->errors->add('fs_not_connected', __('FS not connected', 'wpb'));
				return false;
			}

			/**
			 * @var WP_Filesystem_Base $wp_filesystem
			 */
			global $wp_filesystem;

			foreach ( $this->files as $file ) {

				$filepath_without_prefix = $file;
				if ( ! empty($remove_prefix) && substr($file, 0, strlen($remove_prefix)) === $remove_prefix ) {
					$filepath_without_prefix = substr($file, strlen($remove_prefix));
				}

				if ( $wp_filesystem->is_dir($file) ) {
					$zip->addEmptyDir($filepath_without_prefix);
				} else {
					$zip->addFile($file, $filepath_without_prefix);
				}
			}

			if ( $zip->close() ) {
				$this->is_archive_created = true;
				return true;
			} else {
				$this->errors->add('za_closing_error', __('ZipArchive: Something went wrong while closing archive', 'wpb'), $zip);
				return false;
			}
		}

		$this->errors->add('za_creation_error', __('Something went wrong while archivation via ZipArchive', 'wpb'), $zip);
		return false;
	}

	/**
	 * @return bool
	 */
	private function create_archive_via_pclzip($remove_prefix) {
		$this->errors->add('pclzip_stub', __('Archivation via PclZip not supported for now... ', 'wpb'));
		return false;

//		require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
//		$pclzip = new PclZip();
	}

}