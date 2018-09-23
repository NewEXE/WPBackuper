<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 23.09.18
 * Time: 2:04
 */

class Wpb_Db_Backuper {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	private static $instance = null;

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
	}

	public function make_backup() {

		if ( Wpb_Helpers::is_exec_available() ) {
			$this->create_archive_via_exec();
		} else {
			$this->create_archive_via_wpdb();
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

		header('Content-Type: application/x-gzip');
		header("Content-Disposition: attachment; filename=$file_name");
		header('Content-Length: ' . $wp_filesystem->size($this->backup_file_path));

		echo $wp_filesystem->get_contents($this->backup_file_path);
		exit;
	}

	public function send_backup_to_email() {

	}

	private function create_archive_via_exec() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$zip_file_path = $this->backup_dir . 'wpb_sql_backup_' . date('Y-m-d_H-i-s') . '.sql.gz';
		$cmd = "mysqldump --user={$wpdb->dbuser} --password={$wpdb->dbpassword} {$wpdb->dbname} | gzip --best > $zip_file_path";
		$exec_output = [];

		exec($cmd, $exec_output);
		if ( ! empty($exec_output) ) {
			return false;
		} else {
			$this->backup_file_path = $zip_file_path;
		}

		return true;
	}

	private function create_archive_via_wpdb() {

	}
}