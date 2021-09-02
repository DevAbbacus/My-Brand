<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://techxperts.co.in/
 * @since             1.0.0
 * @package           Xperts_Crms_Wc
 *
 * @wordpress-plugin
 * Plugin Name:       Current RMS WooCommerce Integration
 * Plugin URI:        https://www.o3rental.be
 * Description:       Current RMS WooCommerce Product sync Via Webhooks from CRMS
 * Version:           1.0.0
 * Author:            o3rental.be
 * Author URI:        https://www.o3rental.be
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xperts-crms-wc
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
define( 'XPERTS_CRMS_WC_VERSION', '1.0.0' );

define('CRMS_SUBDOMAIN','out-of-office');
define('CRMS_API_KEY','TKkVt8kSxqdPPxRcT25n');
define('CRMS_API_URL','https://api.current-rms.com/api/v1/');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-xperts-crms-wc-activator.php
 */
function activate_xperts_crms_wc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xperts-crms-wc-activator.php';
	Xperts_Crms_Wc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-xperts-crms-wc-deactivator.php
 */
function deactivate_xperts_crms_wc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xperts-crms-wc-deactivator.php';
	Xperts_Crms_Wc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_xperts_crms_wc' );
register_deactivation_hook( __FILE__, 'deactivate_xperts_crms_wc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-xperts-crms-wc.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_xperts_crms_wc() {

	$plugin = new Xperts_Crms_Wc();
	$plugin->run();

}
run_xperts_crms_wc();
