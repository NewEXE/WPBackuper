<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 23.09.18
 * Time: 2:04
 */

class Wpb_Db_Backuper extends Wpb_Abstract_Backuper {

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
			'wpb_sql_backup_' . date('Y-m-d_H-i-s') . '.zip'
		);
		$this->errors   = new WP_Error();
	}

	/**
	 * @return true|WP_Error
	 */
	public function make_backup() {

		if ( ! Wpb_Helpers::is_fs_connected() ) {
			$this->errors->add('fs_not_connected', __('FS not connected', 'wpb'));
			return false;
		}

		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */
		global $wp_filesystem;

		// Backup is created, so bail.
		if ( $wp_filesystem->is_file($this->archiver->get_archive_fullpath()) ) {
			return;
		}

		if ( Wpb_Helpers::is_exec_available() ) {
			return $this->create_archive_via_mysqldump();
		} else {
			return $this->create_archive_via_wpdb();
		}
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

		header('Content-Type: application/x-gzip');
		header('Content-Disposition: attachment; filename=' . $this->archiver->get_archive_filename());
		header('Content-Length: ' . $wp_filesystem->size($this->archiver->get_archive_fullpath()));

		echo $wp_filesystem->get_contents($this->archiver->get_archive_fullpath());
		exit(0);
	}

	/**
	 * @param string $subject
	 * @param string $message
	 * @param string $headers
	 *
	 * @return bool
	 */
	public function send_backup_to_email($subject = null, $message = null, $headers = null) {
		$to = get_option(Wpb_Admin::OPTION_BACKUP_EMAIL, Wpb_Helpers::get_user_email());

		if ( is_null($subject) ) {
			$subject = __('WPBackup: your WP DB backup', 'wpb');
		}

		if ( is_null($message) ) {
			$message = __('Howdy! Here your backup of WordPress Database.', 'wpb');
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

	/**
	 * @return true|WP_Error
	 */
	private function create_archive_via_mysqldump() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$this->archiver->set_archive_filename( 'wpb_sql_backup_' . date('Y-m-d_H-i-s') . '.sql.gz');
		$zip_file_path = $this->archiver->get_archive_fullpath();

		$cmd = "mysqldump --user={$wpdb->dbuser} --password={$wpdb->dbpassword} {$wpdb->dbname} | gzip --best > $zip_file_path";
		$exec_output = [];

		exec($cmd, $exec_output);
		if ( ! empty($exec_output) ) {
			$this->errors->add('exec_error', __('Something went wrong while executing "mysqldump"', 'wpb'), $exec_output);
			return false;
		}

		return true;
	}

	/**
	 * Backup the whole database or just some tables.
	 * Use '*' for whole database or array with tables names.
	 *
	 * Source:
	 * @see https://github.com/daniloaz/myphp-backup/blob/master/myphp-backup.php
	 *
	 * @param string|array $tables
	 * @return bool
	 */
	private function create_archive_via_wpdb($tables = '*') {

		if ( ! Wpb_Helpers::is_fs_connected() ) {
			$this->errors->add('fs_not_connected', __('FS not connected', 'wpb'));
			return false;
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		$batchSize = 1000;

		// Tables to export
		if( $tables === '*' || empty($tables) ) {
			$tables = [];
			$result = $wpdb->get_results('SHOW TABLES', ARRAY_N);

			foreach ( $result as $table ) {
				$tables[] = $table[0];
			}
		} else {
			$tables = is_array($tables) ? $tables : explode(',', str_replace(' ', '', $tables));
		}
		$sql = 'CREATE DATABASE IF NOT EXISTS `'.$wpdb->dbname."`;\n\n";
		$sql .= 'USE `'.$wpdb->dbname."`;\n\n";

		// Iterate tables
		foreach($tables as $table) {

			// CREATE TABLE
			$sql .= 'DROP TABLE IF EXISTS `'.$table.'`;';
			$row = $wpdb->get_row('SHOW CREATE TABLE `'.$table.'`', ARRAY_N);

			$sql .= "\n\n".$row[1].";\n\n";


			$row = $wpdb->get_row('SELECT COUNT(*) FROM `'.$table.'`', ARRAY_N);
			$numRows = (int) $row[0];

			if ( $numRows === 0 ) {
				continue; // We don't need insert into, because table is empty
			}

			// INSERT INTO

			// Split table in batches in order to not exhaust system memory
			$numBatches = intval($numRows / $batchSize) + 1; // Number of for-loop calls to perform

			for ($b = 1; $b <= $numBatches; $b++) {

				$query = 'SELECT * FROM `' . $table . '` LIMIT ' . ($b * $batchSize - $batchSize) . ',' . $batchSize;
				$result = $wpdb->get_results($query, ARRAY_N);

				$realBatchSize = count($result); // Last batch size can be different from $batchSize
				$numFields = count($result[0]);
				if ($realBatchSize !== 0) {
					$sql .= 'INSERT INTO `'.$table.'` VALUES ' . "\n";

					$rowCount = 1;
					foreach ($result as $row) {
						$sql.='(';
						for($i = 0; $i < $numFields; $i++) {
							if ( isset($row[$i]) ) {
								$row[$i] = addslashes($row[$i]);
								$row[$i] = str_replace("\n","\\n",$row[$i]);
								$sql .= '"'.$row[$i].'"' ;
							} else {
								$sql.= 'NULL';
							}

							if ($i < ($numFields-1)) {
								$sql .= ',';
							}
						}

						if ( $rowCount === $realBatchSize ) {
							$rowCount = 0;
							$sql.= ");\n"; //close the insert statement
						} else {
							$sql.= "),\n"; //close the row
						}
						$rowCount++;
					}
				}
			}

			$sql.="\n\n";
		}

		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */
		global $wp_filesystem;

		$sql_tmp_file = get_temp_dir() . 'wpb_sql_backup_' . date('Y-m-d_H-i-s') . '.sql';

		if ( ! $wp_filesystem->touch($sql_tmp_file, $sql) ) {
			$this->errors->add('touch_tmp_file_error', __('Something went wrong while touch temp file', 'wpb'), $sql_tmp_file);
			return false;
		}

		if ( ! $wp_filesystem->put_contents($sql_tmp_file, $sql) ) {
			$this->errors->add('put_sql_to_tmp_file_error', __('Something went wrong while put sql content to temp file', 'wpb'), $sql_tmp_file);
			$wp_filesystem->delete($sql_tmp_file);
			return false;
		}

		$this->archiver->set_archive_filename( 'wpb_sql_backup_' . date('Y-m-d_H-i-s') . '.zip');

		$this->archiver->push_file($sql_tmp_file);

		if ( ! $this->archiver->create_archive(get_temp_dir()) ) {
			$wp_filesystem->delete($sql_tmp_file);
			// todo find way for sharing errors from zipper
			return false;
		}

		$wp_filesystem->delete($sql_tmp_file);
		return true;
	}

}