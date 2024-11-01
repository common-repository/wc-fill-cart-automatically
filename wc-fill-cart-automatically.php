<?php
/**
 * Plugin Name: Fill Cart Automatically for WooCommerce
 * Plugin URI:  
 * Description: Autofill carts with previously selected products through a link that is sent to the user or by default when the user enters the web.
 * Version:     1.0.9
 * Author:      Manuel Muñoz Rodríguez
 * Author URI:  https://profiles.wordpress.org/manolomunoz/
 * Text Domain: fill-woo-cart-automatically
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package     WordPress
 * @author      Manuel Muñoz Rodríguez <mmr010496@gmail.com>
 * @copyright   2022
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 *
 * Prefix:      fwc
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

define( 'FWC_VERSION', '1.0.9' );
define( 'FWC_PLUGIN', __FILE__ );
define( 'FWC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FWC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * # Includes
 * ---------------------------------------------------------------------------------------------------- */
if ( ! class_exists( 'WooCommerce' ) ) {
	require_once FWC_PLUGIN_PATH . 'admin/class-fill-cart-setting.php';
	require_once FWC_PLUGIN_PATH . 'public/cart-functions.php';
}

add_action( 'plugins_loaded', 'fwc_plugin_init' );
/**
 * Load localization files
 *
 * @return void
 */
function fwc_plugin_init() {
	load_plugin_textdomain( 'fill-woo-cart-automatically', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
