<?php

abstract class Wpb_Abstract_Backuper {

	const FILES = 'wpb_backup_files';
	const DB    = 'wpb_backup_db';

	/**
	 * @param $type
	 *
	 * @return Wpb_Abstract_Backuper
	 */
	public static function get_backuper($type) {
		switch ($type) {
			case self::FILES:
				return Wpb_Files_Backuper::instance();
			case self::DB:
				return Wpb_Db_Backuper::instance();
		}
	}

	abstract public function make_backup();

	/**
	 * @return void
	 */
	abstract public function send_backup_to_browser_and_exit();

	/**
	 * @return bool
	 */
	abstract public function send_backup_to_email();

	/**
	 * @return WP_Error
	 */
	abstract public function get_errors();

	public function download_backup() {

		if ( ! $this->make_backup() ) {
			wp_die($this->get_errors(), '', ['back_link' => true]);
		}

		$this->send_backup_to_browser_and_exit();
	}
}