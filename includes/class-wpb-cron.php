<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 30.09.18
 * Time: 19:02
 */

/**
 * Class Wpb_Cron
 */
class Wpb_Cron {

	const EVENT_BACKUP_FILES    = 'wpb_cron_backup_files';
	const EVENT_BACKUP_DB       = 'wpb_cron_backup_db';

	/**
	 * @param Wpb_Abstract_Backuper $backuper
	 */
	public function send_backup_to_email($backuper) {
		$backuper->make_backup();
		$backuper->send_backup_to_email();
	}

	public function define_cron_hooks() {
		add_action(self::EVENT_BACKUP_FILES, [$this, 'send_backup_to_email']);
		add_action(self::EVENT_BACKUP_DB, [$this, 'send_backup_to_email']);
	}

	public function schedule_cron_events() {

		$recurrence_files   = 'hourly';
		$recurrence_db      = 'hourly';

		if( ! wp_next_scheduled(self::EVENT_BACKUP_FILES, [Wpb_Abstract_Backuper::TYPE_FILES] ) ) {
			wp_schedule_event(
				time(),
				$recurrence_files,
				self::EVENT_BACKUP_FILES,
				[Wpb_Abstract_Backuper::get_backuper(Wpb_Abstract_Backuper::TYPE_FILES)]
			);
		}

		if( ! wp_next_scheduled(self::EVENT_BACKUP_DB, [Wpb_Abstract_Backuper::TYPE_DB] ) ) {
			wp_schedule_event(
				time(),
				$recurrence_db,
				self::EVENT_BACKUP_DB,
				[Wpb_Abstract_Backuper::get_backuper(Wpb_Abstract_Backuper::TYPE_DB)]
			);
		}
	}

	/**
	 * todo phpDoc
	 * @return array
	 */
	public static function get_plugin_cron_tasks() {
		$cron_option = get_option('cron', []);

		// Last $cron_option element is 'version' (integer), so miss him.
		unset($cron_option['version']);

		$plugin_cron_tasks = [];

		foreach ( $cron_option as $time => $tasks_by_time ) {
			foreach ( $tasks_by_time as $name => $tasks ) {
				// It's plugin's task.
				if ( in_array($name, [self::EVENT_BACKUP_FILES, self::EVENT_BACKUP_DB]) ) {
					$task = array_shift($tasks);
					$plugin_cron_tasks[$name]['schedule']       = $task['schedule'];
					$plugin_cron_tasks[$name]['args']           = $task['args'];
					$plugin_cron_tasks[$name]['interval']       = $task['interval'];
					$plugin_cron_tasks[$name]['next_execution'] = $time;
				}
			}
		}

		return $plugin_cron_tasks;
	}

	/**
	 * @param $event
	 *
	 * @return string
	 */
	public static function get_readable_name($event) {
		switch ($event) {
			case self::EVENT_BACKUP_FILES:
				return __('Create files backup and send by email', 'wpb');
			case self::EVENT_BACKUP_DB:
				return __('Create database backup and send by email', 'wpb');
			default:
				return __('Unknown', 'wpb');
		}
	}

	/**
	 * @param $schedule
	 *
	 * @return string
	 */
	public static function get_readable_schedule($schedule) {
		static $schedules = null;

		if ( is_null($schedules) ) {
			$schedules = wp_get_schedules();
		}

		if ( in_array($schedule, array_keys($schedules)) ) {
			return $schedules[$schedule]['display'];
		}
	}
}