<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://junnaedi.com
 * @since             0.0.1
 * @package           Popaket
 *
 * @wordpress-plugin
 * Plugin Name:       Popaket - WooCommerce
 * Plugin URI:        https://junnaedi.com/popaket_woocommerce
 * Description:       Plugin addon WooCommerce untuk shipping agregator Popaket.
 * Version:           1.0.1
 * Author:            Junnaedi
 * Author URI:        https://junnaedi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       popaket-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'POPAKET_WOOCOMMERCE_VERSION', '1.0.0' );
/**
 * Define Popaket path and uri
 */
define( 'POPAKET_WOOCOMMERCE_PLUGIN_FILE', __FILE__ );
define( 'POPAKET_WOOCOMMERCE_PATH', plugin_dir_path( __FILE__ ) );
define( 'POPAKET_WOOCOMMERCE_URL', plugin_dir_url( __FILE__ ) );

define( 'POPAKET_WOOCOMMERCE_BASENAME', plugin_basename( __FILE__ ) );
define( 'POPAKET_WOOCOMMERCE_BASEDIR', plugin_basename( __DIR__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-popaket-woocommerce-activator.php
 */
function activate_popaket_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-popaket-woocommerce-activator.php';
	Popaket_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-popaket-woocommerce-deactivator.php
 */
function deactivate_popaket_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-popaket-woocommerce-deactivator.php';
	Popaket_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_popaket_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_popaket_woocommerce' );

/**
 * Register API class
 */
require plugin_dir_path( __FILE__ ) . 'core/class-popaket-api.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'core/class-popaket-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_popaket_woocommerce() {
	$plugin = new Popaket_WooCommerce();
}
run_popaket_woocommerce();