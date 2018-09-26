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

	/**
	 * @return true|WP_Error
	 */
	public function make_backup() {

		if ( Wpb_Helpers::is_exec_available() ) {
			return $this->create_archive_via_exec();
		} else {
			return $this->create_archive_via_wpdb();
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

	/**
	 * @return true|WP_Error
	 */
	private function create_archive_via_exec() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$zip_file_path = $this->backup_dir . 'wpb_sql_backup_' . date('Y-m-d_H-i-s') . '.sql.gz';
		$cmd = "mysqldump --user={$wpdb->dbuser} --password={$wpdb->dbpassword} {$wpdb->dbname} | gzip --best > $zip_file_path";
		$exec_output = [];

		exec($cmd, $exec_output);
		if ( ! empty($exec_output) ) {
			return new WP_Error('db_backuper_exec_error', __('Something went wrong while executing "mysqldump"', 'wpb'));
		} else {
			$this->backup_file_path = $zip_file_path;
		}

		return true;
	}

	/**
	 * Backup the whole database or just some tables
	 * Use '*' for whole database or 'table1 table2 table3...'
	 * @param string $tables
	 */
	private function create_archive_via_wpdb($tables = '*') {
		/** @var wpdb $wpdb */
		global $wpdb;

		$batchSize = 1000;

		// Tables to export
		if( $tables == '*' ) {
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

		wpb_dd($sql);
	}

}