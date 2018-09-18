<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://newexe.pp.ua
 * @since             1.0.0
 * @package           Wpb
 *
 * @wordpress-plugin
 * Plugin Name:       WPBackuper
 * Plugin URI:        http://newexe.pp.ua/wpbackuper
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            NewEXE
 * Author URI:        http://newexe.pp.ua
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpb
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPB_VERSION', '1.0.0' );

define( 'WPB_PLUGIN_MAIN_FILE', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpb-activator.php
 */
function activate_wpb() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpb-activator.php';
	Wpb_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpb-deactivator.php
 */
function deactivate_wpb() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpb-deactivator.php';
	Wpb_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpb' );
register_deactivation_hook( __FILE__, 'deactivate_wpb' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WPB_PLUGIN_MAIN_FILE . 'includes/class-wpb.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpb() {

	$plugin = new Wpb();
	$plugin->run();

}
run_wpb();
