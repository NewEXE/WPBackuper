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

	const DEFAULT_SCHEDULE = 'wpb_monthly';

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
		add_action('updated_option', [$this, 'clear_cron_task_hook'], 10, 3);
	}

	public function schedule_cron_events() {
		$files = Wpb_Abstract_Backuper::FILES;
		$db = Wpb_Abstract_Backuper::DB;

		if (
			($option_files = get_option($files, false)) &&
		    ! wp_next_scheduled($files, [$files])
		) {
			$option_schedule_files = get_option('wpb_schedule_' . $files, self::DEFAULT_SCHEDULE);
			wp_schedule_event(
				time() + self::get_schedule_interval($option_schedule_files),
				$option_schedule_files,
				$files,
				[$files]
			);
		} elseif ( ! $option_files ) {
			$this->remove_cron_task($files);
		}

		if (
			($option_db = get_option($db, false)) &&
			! wp_next_scheduled($db, [$db])
		) {
			$option_schedule_db = get_option('wpb_schedule_' . $db, self::DEFAULT_SCHEDULE);
			wp_schedule_event(
				time() + self::get_schedule_interval($option_schedule_db),
				$option_schedule_db,
				$db,
				[$db]
			);
		} elseif ( ! $option_db ) {
			$this->remove_cron_task($db);
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

		// Defaults
		$plugin_cron_tasks = [
			Wpb_Abstract_Backuper::FILES => [
				'name'              => Wpb_Abstract_Backuper::FILES,
				'select_schedule'   => self::get_html_select_for_event(Wpb_Abstract_Backuper::FILES),
				'args'              => [Wpb_Abstract_Backuper::FILES],
				'interval'          => __('Not set', 'wpb'),
				'next_execution'    => __('Not set', 'wpb'),
			],
			Wpb_Abstract_Backuper::DB => [
				'name'              => Wpb_Abstract_Backuper::DB,
				'select_schedule'   => self::get_html_select_for_event(Wpb_Abstract_Backuper::DB),
				'args'              => [Wpb_Abstract_Backuper::DB],
				'interval'          => __('Not set', 'wpb'),
				'next_execution'    => __('Not set', 'wpb'),
			],
		];

		foreach ( $cron_option as $time => $tasks_by_time ) {
			foreach ( $tasks_by_time as $name => $tasks ) {
				// It's plugin's task.
				if ( in_array($name, [Wpb_Abstract_Backuper::FILES, Wpb_Abstract_Backuper::DB]) ) {
					$task = array_shift($tasks);
					$plugin_cron_tasks[$name]['name']               = $name;
					$plugin_cron_tasks[$name]['select_schedule']    = self::get_html_select_for_event($name, $task['schedule']);
					$plugin_cron_tasks[$name]['args']               = $task['args'];
					$plugin_cron_tasks[$name]['interval']           = $task['interval'];
					$plugin_cron_tasks[$name]['next_execution']     = $time;
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

		return __('Unknown', 'wpb');
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
		$schedules['wpb_everyminute'] = [
			'interval'  => MINUTE_IN_SECONDS,
			'display'   => __('Every Minute', 'wpb')
		];
		return $schedules;
	}

	public function clear_cron_task_hook($option, $old_value, $value) {
		if (
			($option === 'wpb_schedule_' . Wpb_Abstract_Backuper::DB) ||
			($option === Wpb_Abstract_Backuper::DB && $value === '')
		) {
			$this->remove_cron_task(Wpb_Abstract_Backuper::DB);
		}

		if (
			($option === 'wpb_schedule_' . Wpb_Abstract_Backuper::FILES) ||
			($option === Wpb_Abstract_Backuper::FILES && $value === '')
		) {
			$this->remove_cron_task(Wpb_Abstract_Backuper::FILES);
		}
	}

	public static function get_html_select_for_event($event, $selected = '') {
		$schedules = wp_get_schedules();

		$select_schedules = [];
		foreach ($schedules as $name => $s) {
			$select_schedules[$name] = $s['display'];
		}

		$args = [
			'name'          => "wpb_schedule_$event",
			'type'          => 'select',
			'options'       => $select_schedules,
			'first_option'  => __('Select schedule...', 'wpb'),
			'default'       => $selected,
			'attributes'    => ['required'=>'required'],
		];

		return Wpb_Admin::render_input($args, false);
	}

	public static function get_schedule_interval($schedule_name) {
		$schedules = wp_get_schedules();

		foreach ( $schedules as $name => $schedule ) {
			if ( $schedule_name === $name ) {
				return $schedule['interval'];
			}
		}

		return false;
	}

	public static function get_schedules_list() {
		return array_keys(wp_get_schedules());
	}

	private function remove_cron_task($hook) {
		wp_clear_scheduled_hook($hook, [$hook]);
	}
}