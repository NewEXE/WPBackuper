<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://newexe.pp.ua
 * @since      1.0.0
 *
 * @package    Wpb
 * @subpackage Wpb/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wpb
 * @subpackage Wpb/includes
 * @author     NewEXE <v.voloshyn96@gmail.com>
 */
class Wpb {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wpb_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'wpb';
		$this->version = WPB_VERSION;
		$this->save_version_in_db();

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_admin_notices_hooks();
		$this->set_cron_tasks();
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wpb_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpb_Loader. Orchestrates the hooks of the plugin.
	 * - Wpb_i18n. Defines internationalization functionality.
	 * - Wpb_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Plugin's helpers.
		 */
		require_once WPB_PLUGIN_MAIN_DIR . 'includes/class-wpb-helpers.php';

		$includes = [
			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			'includes/class-wpb-loader.php',

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			'includes/class-wpb-i18n.php',

			'includes/abstracts/interface-wpb-archiver.php',

			'includes/class-wpb-zipper.php',

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			'admin/class-wpb-admin.php',

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			'admin/class-wpb-admin-sanitizator.php',

			/**
			 * The class for printing formatted notices messages.
			 */
			'admin/class-wpb-admin-notices.php',

			'includes/abstracts/class-wpb-abstract-backuper.php',

			/**
			 * The class for performing backup of WP directory.
			 */
			'includes/class-wpb-files-backuper.php',

			/**
			 * The class for performing backup of WP database.
			 */
			'includes/class-wpb-db-backuper.php',

			'includes/class-wpb-cron.php',
		];

		foreach ( $includes as $include ) {
			require_once Wpb_Helpers::path($include);
		}

		$this->loader = new Wpb_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpb_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Wpb_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Wpb_Admin();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_management_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

		$plugin_file = plugin_basename(WPB_PLUGIN_MAIN_FILE);
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_file, $plugin_admin, 'add_settings_link' );

		$this->loader->add_action( 'load-tools_page_' . Wpb_Admin::PAGE_KEY, $plugin_admin, 'general_tasks' );
	}

	private function define_admin_notices_hooks() {
		$admin_notices = new Wpb_Admin_Notices();
		$this->loader->add_action('admin_notices', $admin_notices, 'maybe_add_fs_credentials_notice' );

		$this->loader->add_action('admin_notices', $admin_notices, 'maybe_add_settings_updated_notice' );
		$this->loader->add_action('admin_notices', $admin_notices, 'maybe_add_flash_notice' );
	}

	private function set_cron_tasks() {
		$cron = new Wpb_Cron();

		$cron->define_cron_hooks();
		$cron->schedule_cron_events();
	}

	/**
	 * Save plugin version in database.
	 */
	private function save_version_in_db() {
		update_option('wpb_version', $this->get_version());
	}

}
