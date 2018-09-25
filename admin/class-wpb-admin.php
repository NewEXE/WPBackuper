<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @author     NewEXE <v.voloshyn96@gmail.com>
 * @link       http://newexe.pp.ua
 * @since      1.0.0
 *
 * @package    Wpb
 * @subpackage Wpb/admin
 */
class Wpb_Admin {

	// Pages
	const PAGE_KEY      = 'wpb-settings';

	// Tabs
	const TAB_GENERAL   = self::PAGE_KEY . '-general';
	const TAB_CRON      = self::PAGE_KEY . '-cron';
	const TAB_STATUS    = self::PAGE_KEY . '-status';

	// Options
	const OPTION_BACKUP_EMAIL = 'wpb_backup_email';

	public function __construct( ) {  }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpb-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpb-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Add submenu to WP management page.
	 */
	public function add_management_page() {

		add_management_page(
			__( 'WPBackuper', 'wpb' ),
			__( 'WPBackuper', 'wpb' ),
			'manage_options',
			self::PAGE_KEY,
			[$this, 'display_page']
		);
	}

	/**
	 * Add 'Settings' link on 'Plugins' page.
	 *
	 * @param array $actions
	 * @return array
	 */
	public function add_settings_link( $actions ) {

		$url = admin_url('admin.php?page=' . self::PAGE_KEY);
		$settings_label = __('Settings', 'wpb');
		$link = "<a href='$url'>$settings_label</a>";
		$actions[] = $link;
		return $actions;
	}

	/**
	 * Add settings.
	 */
	public function register_settings() {
		$this->register_settings_general();
	}

	/**
	 * Shows the options page.
	 */
	public function display_page() {

		$selected_tab = Wpb_Helpers::get_var('tab', self::TAB_GENERAL);

		// Render header.
		$this->display_header($selected_tab);

		// Render selected tab.
		switch ($selected_tab) {
			case self::TAB_CRON:
				$this->display_tab_cron();
				break;
			case self::TAB_STATUS:
				$this->display_tab_status();
				break;
			case self::TAB_GENERAL:
			default:
				$this->display_tab_general();
		}

		// Render footer.
		$this->display_footer();
	}

	public function download_backup() {

		//todo check nonce

		if ( Wpb_Helpers::get_var( 'wpb_files_zip', false ) ) {
			$files_backuper = Wpb_Files_Backuper::instance();

			if ( is_wp_error($maybe_error = $files_backuper->make_backup()) ) {
				wp_die($maybe_error);
			}

			if ( is_wp_error($maybe_error = $files_backuper->send_backup_to_email()) ) {
				wp_die($maybe_error);
			}

		}

		if ( Wpb_Helpers::get_var( 'wpb_db_zip', false ) ) {
			$db_backuper = Wpb_Db_Backuper::instance();

			$db_backuper->make_backup();
			$db_backuper->send_backup_to_browser_and_exit();
		}
	}

	private function display_header($selected_tab) {

		$tabs = [
			self::TAB_GENERAL   => __('General', 'wpb'),
			self::TAB_CRON      => __('Cron Schedule', 'wpb'),
			self::TAB_STATUS    => __('System Status', 'wpb'),
		];
		$page_key = self::PAGE_KEY;

		$view_args = compact(
			'tabs',
			'selected_tab',
			'page_key'
		);

		echo self::render('header', $view_args);
	}

	private function display_tab_cron() {

		$view_args = [
			'settings_cron'     => self::TAB_CRON,
			'section_general'   => self::TAB_CRON . '-general',
		];

		echo self::render('tab-cron', $view_args);
	}

	private function display_tab_status() {

		$items = [
			[
				'name'              => 'FS connected',
				'hint'              => 'Is successfully connected to filesystem?',
				'true'              => ! is_wp_error(Wpb_Helpers::connect_to_fs()),
				'description_true'  => 'Yes',
				'description_false' => 'No',
			],
			[
				'name'              => 'ZipArchive',
				'hint'              => 'Is ZipArchive PHP library available?',
				'true'              => Wpb_Helpers::is_zip_archive_available(),
				'description_true'  => 'Available',
				'description_false' => 'Not available. Using PclZip instead',
			],
			[
				'name'              => 'exec()',
				'hint'              => 'Is PHP function exec() is available and allowed for execution',
				'true'              => Wpb_Helpers::is_exec_available(),
				'description_true'  => 'Available and allowed',
				'description_false' => 'Not available and (or) not allowed. Using $wpdb instead',
			],
			[
				'name'              => 'Is Bedrock?',
				'hint'              => 'WP was installed with Bedrock or no?',
				'true'              => Wpb_Helpers::is_bedrock(),
				'description_true'  => 'Bedrock WP installation',
				'description_false' => 'Normal WP installation',
			],
		];

		$view_args = compact('items');

		echo self::render('tab-status', $view_args);
	}

	private function display_tab_general() {

		$view_args = [
			'settings_general'  => self::TAB_GENERAL,
			'section_general'   => self::TAB_GENERAL . '-general',
		];

		echo self::render('tab-general', $view_args);
	}

	private function display_footer() {
		echo self::render('footer');
	}

	private function register_settings_general() {

		$section_id = self::TAB_GENERAL . '-general';
		$field_backup_email = self::OPTION_BACKUP_EMAIL;

		$default_email = Wpb_Helpers::get_user_email();

		add_settings_section(
			$section_id,
			'',
			'',
			$section_id
		);

		add_settings_field(
			$field_backup_email,
			__( 'E-mail to send a backup', 'wpb' ),
			[ $this, 'render_input' ],
			$section_id,
			$section_id,
			array(
				'name'      => $field_backup_email,
				'type'      => 'email',
				'default'   => $default_email,
				'attributes'=> array(
					'required'    => 'required',
					'placeholder' => $default_email,
					'title'       => __('E-mail to send a backup', 'wpb')
				),
				'description' => __('For example, ' . $default_email, 'wpb') // sprintf
			)
		);

		register_setting(self::TAB_GENERAL, $field_backup_email, [
			'sanitize_callback' => [Wpb_Admin_Sanitizator::instance(), 'sanitize_email']
		]);
	}

	/**
	 * Render template file with params.
	 *
	 * @param $template_name
	 * @param array $args
	 * @return string
	 */
	static public function render($template_name, $args = []) {
		if ( ! empty($args) ) {
			extract($args);
		}

		//ob_start();
		include Wpb_Helpers::path("admin/partials/$template_name.php");
		//return ob_get_clean();
	}

	/**
	 * Render input template file.
	 *
	 * @param array $args
	 */
	public function render_input($args) {

		$args['default']        = isset($args['default']) ? $args['default'] : '';
		$args['description']    = isset($args['description']) ? $args['description'] : '';
		$args['value']          = get_option($args['name'], $args['default']);
		$type = $args['type'];
		switch ( $type ) {
			case 'text':
				if ( isset($args['subtype']) ) $args['type'] = $args['subtype'];
				if ( ! isset($args['class']) ) $args['class'] = 'regular-text';
				break;
			case 'textarea':
				if ( ! isset($args['rows']) ) $args['rows'] = 5;
				if ( ! isset($args['cols']) ) $args['cols'] = 30;
				break;
			case 'checkbox':
				if ( ! isset($args['label']) ) $args['label'] = __('Enable', 'wpb');
				break;
			case 'number':
				if ( ! isset($args['step']) ) $args['step'] = 1;
				if ( ! isset($args['min']) ) $args['min'] = '';
				if ( ! isset($args['max']) ) $args['max'] = '';
				if ( ! isset($args['label']) ) $args['label'] = '';
				break;
			default:
				if ( ! isset($args['attributes']['type']) ) $args['attributes']['type'] = $type;
				$type = 'text';
				if ( isset($args['subtype']) ) $args['type'] = $args['subtype'];
				if ( ! isset($args['class']) ) $args['class'] = 'regular-text';
		}
		$args['attributes_html'] = '';
		if( isset($args['attributes']) ) {
			foreach($args['attributes'] as $key => $value) {
				$args['attributes_html'] .= ' '.$key.'="'.$value.'"';
			}
		}

		echo self::render("inputs/$type", $args);
	}

	private function add_notice($message, $type = '', $only_on_plugin_page = false) {

		if ( $only_on_plugin_page && ! Wpb_Helpers::is_plugin_page() ) {
			return;
		}
	}

}
