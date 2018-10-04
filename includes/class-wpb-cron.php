<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 30.09.18
 * Time: 19:02
 */

class Wpb_Cron {

	const EVENT_BACKUP_FILES    = 'wpb_cron_backup_files';
	const EVENT_BACKUP_DB       = 'wpb_cron_backup_db';
	const EVENT_TEST            = 'wpb_cron_test';

	public function test($p1, $p2, $p3) {
		file_put_contents(__FILE__.'.log', $p1 . $p2 . $p3 . PHP_EOL, FILE_APPEND);
	}

	public function send_backup_to_email() {

	}

	public function define_cron_hooks() {
		add_action(self::EVENT_TEST, [$this, 'test'], 10, 3);
	}

	public function schedule_cron_events() {
		$args = ['v1', 'v2', 'v3'];

		if( ! wp_next_scheduled('wpb_cron_test', $args ) ) {
			wp_schedule_event(time()+15, 'hourly', 'wpb_cron_test', $args);
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
				return __('Create backup files and send by email', 'wpb');
			case self::EVENT_BACKUP_DB:
				return __('Create backup database and send by email', 'wpb');
			case self::EVENT_TEST:
				return __('Test event', 'wpb');
			default:
				return __('Unknown');
		}
	}
}