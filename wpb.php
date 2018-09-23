<?php

/**
 * The plugin bootstrap file.
 * The plugin is built on the WordPress Plugin Boilerplate skeleton:
 * @see https://github.com/DevinVinson/WordPress-Plugin-Boilerplate
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

define( 'WPB_VERSION', '1.0.0' );
define( 'WPB_PLUGIN_MAIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPB_PLUGIN_MAIN_FILE', __FILE__ );

/**
 * Fired during plugin activation.
 */
function activate_wpb() {
}

/**
 * Fired during plugin deactivation.
 */
function deactivate_wpb() {
}

register_activation_hook( __FILE__, 'activate_wpb' );
register_deactivation_hook( __FILE__, 'deactivate_wpb' );

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

	// The core plugin class.
	require WPB_PLUGIN_MAIN_DIR . 'includes/class-wpb.php';

	$plugin = new Wpb();
	$plugin->run();
}

run_wpb();