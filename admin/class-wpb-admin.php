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

	// Used in plugin URL
	const PLUGIN_URL_BASE = 'tools.php';

	// Pages
	const PAGE_KEY      = 'wpb-settings';

	// Tabs
	const TAB_GENERAL   = self::PAGE_KEY . '-general';
	const TAB_CRON      = self::PAGE_KEY . '-cron';
	const TAB_STATUS    = self::PAGE_KEY . '-status';

	// Options
	const OPTION_BACKUP_EMAIL   = 'wpb_backup_email';
	const OPTION_IS_CRON_SET    = 'wpb_is_cron_set';

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

		$actions[] = '<a href=' . '"' . Wpb_Helpers::plugin_url() . '"' . '>' . __('Settings', 'wpb') . '</a>';

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

		$backup_files   = Wpb_Helpers::post_var('wpb_backup_files', false);
		$backup_db      = Wpb_Helpers::post_var('wpb_backup_db', false);

		if ( ($backup_files xor $backup_db) && Wpb_Helpers::is_plugin_page(self::TAB_GENERAL) ) {
			check_admin_referer('wpb_make_backup', 'wpb_make_backup');

			$backuper = $backup_files ?
				Wpb_Files_Backuper::instance() :
				Wpb_Db_Backuper::instance();

			if ( ! $backuper->make_backup() ) {
				wp_die($backuper->get_errors());
			}

			$backuper->send_backup_to_browser_and_exit();
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

		$is_fs_connected = Wpb_Helpers::is_fs_connected() ;

		$with_fs_info = ! $is_fs_connected;

		$items = [
			[
				'name'              => __('PHP version', 'wpb'),
				'hint'              => '',
				'true'              => true,
				'description_true'  => phpversion(),
				'description_false' => '',
			],
			[
				'name'              => __('FS connected', 'wpb'),
				'hint'              => __('Is successfully connected to filesystem?', 'wpb'),
				'true'              => $is_fs_connected,
				'description_true'  => __('Yes', 'wpb'),
				'description_false' => __('No', 'wpb'),
			],
			[
				'name'              => __('ZipArchive', 'wpb'),
				'hint'              => __('Is ZipArchive PHP library available?', 'wpb'),
				'true'              => Wpb_Helpers::is_zip_archive_available(),
				'description_true'  => __('Available', 'wpb'),
				'description_false' => __('Not available. Using PclZip instead', 'wpb'),
			],
			[
				'name'              => __('exec()', 'wpb'),
				'hint'              => __('Is PHP function exec() is available and allowed for execution?', 'wpb'),
				'true'              => Wpb_Helpers::is_exec_available(),
				'description_true'  => __('Available and allowed', 'wpb'),
				'description_false' => __('Not available and (or) not allowed. Using $wpdb instead', 'wpb'),
			],
			[
				'name'              => __('Is normal WP installation?', 'wpb'),
				'hint'              => __('WP was installed with Bedrock or no?', 'wpb'),
				'true'              => ! Wpb_Helpers::is_bedrock(),
				'description_true'  => __('Normal WP installation', 'wpb'),
				'description_false' => __('Bedrock WP installation', 'wpb'),
			],
		];
		if ( $is_fs_connected ) {

			$tmp_dir = get_temp_dir();

			$items[] = [
				'name'              => __('Directory for backups', 'wpb'),
				'hint'              => 'Full path to temp directory for backups.',
				'true'              => Wpb_Helpers::is_temp_dir_writable(),
				'description_true'  =>
				/* translators: %s: template name */
					sprintf(__('Dir <b>%s</b> is exists and writable', 'wpb'), $tmp_dir),
				'description_false'  =>
				/* translators: %s: template name */
					sprintf(__('Dir <b>%s</b> is NOT writable', 'wpb'), $tmp_dir),
			];
		}

		$view_args = compact('items', 'with_fs_info');

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

}
