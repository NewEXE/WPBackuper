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

	/**
	 * @param string $backuper_type
	 */
	public function send_backup_to_email($backuper_type) {
		$backuper_type = Wpb_Abstract_Backuper::get_backuper($backuper_type);
		$backuper_type->make_backup();
		$backuper_type->send_backup_to_email();
	}

	public function define_cron_hooks() {
		add_action(Wpb_Abstract_Backuper::FILES, [$this, 'send_backup_to_email']);
		add_action(Wpb_Abstract_Backuper::DB, [$this, 'send_backup_to_email']);
		add_filter('cron_schedules', [$this, 'add_schedules']);
	}

	public function schedule_cron_events() {

		$recurrence_files   = 'hourly';
		$recurrence_db      = 'hourly';

		if (
			get_option(Wpb_Abstract_Backuper::FILES, false) &&
		    ! wp_next_scheduled(Wpb_Abstract_Backuper::FILES, [Wpb_Abstract_Backuper::FILES])
		) {
			wp_schedule_event(
				time(),
				$recurrence_files,
				Wpb_Abstract_Backuper::FILES,
				[Wpb_Abstract_Backuper::FILES]
			);
		}

		if (
			get_option(Wpb_Abstract_Backuper::DB, false) &&
			! wp_next_scheduled(Wpb_Abstract_Backuper::DB, [Wpb_Abstract_Backuper::DB])
		) {
			wp_schedule_event(
				time(),
				$recurrence_db,
				Wpb_Abstract_Backuper::DB,
				[Wpb_Abstract_Backuper::DB]
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
				if ( in_array($name, [Wpb_Abstract_Backuper::FILES, Wpb_Abstract_Backuper::DB]) ) {
					$task = array_shift($tasks);
					$plugin_cron_tasks[$name]['name']           = $name;
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
			case Wpb_Abstract_Backuper::FILES:
				return __('Create files backup and send by email', 'wpb');
			case Wpb_Abstract_Backuper::DB:
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

	public function add_schedules($schedules) {
		$schedules['wpb_monthly'] = [
			'interval'  => MONTH_IN_SECONDS,
			'display'   => __('Once Monthly', 'wpb')
		];
		$schedules['wpb_twicemonthly'] = [
			'interval'  => WEEK_IN_SECONDS * 2,
			'display'   => __('Twice Monthly', 'wpb')
		];
		return $schedules;
	}
}