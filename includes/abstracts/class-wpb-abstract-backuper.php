<?php

abstract class Wpb_Abstract_Backuper {

	const TYPE_FILES    = 'files';
	const TYPE_DB       = 'db';

	/**
	 * @param $type
	 *
	 * @return Wpb_Abstract_Backuper
	 */
	public static function get_backuper($type) {
		switch ($type) {
			case self::TYPE_FILES:
				return Wpb_Files_Backuper::instance();
			case self::TYPE_DB:
				return Wpb_Db_Backuper::instance();
		}
	}

	abstract public function make_backup();

	abstract public function send_backup_to_browser_and_exit();

	abstract public function send_backup_to_email();

	abstract public function get_errors();

	public function download_backup() {

		if ( ! $this->make_backup() ) {
			wp_die($this->get_errors(), '', ['back_link' => true]);
		}

		$this->send_backup_to_browser_and_exit();
	}
}