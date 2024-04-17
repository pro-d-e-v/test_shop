<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Custom Post Type WooCommerce Integration
 * Plugin URI:        https://www.wptinysolutions.com/tiny-products/cpt-woo-integration
 * Description:       Integrate custom post type with woocommerce. Sell Any Kind Of Custom Post
 * Version:           1.3.6
 * Author:            Tiny Solutions
 * Author URI:        https://www.wptinysolutions.com/
 * Tested up to:      6.5
 * WC tested up to:   8.7
 * Text Domain:       cptwooint
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * @package TinySolutions\WM
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Define cptwooint Constant.
 */

define( 'CPTWI_VERSION', '1.3.6' );

define( 'CPTWI_FILE', __FILE__ );

define( 'CPTWI_BASENAME', plugin_basename( CPTWI_FILE ) );

define( 'CPTWI_URL', plugins_url( '', CPTWI_FILE ) );

define( 'CPTWI_ABSPATH', dirname( CPTWI_FILE ) );

define( 'CPTWI_PATH', plugin_dir_path( __FILE__ ) );


/**
 * App Init.
 */
require_once 'TinyApp/cptwooint.php';

// Available all functionality and variable
// https://www.businessbloomer.com/woocommerce-easily-get-product-info-title-sku-desc-product-object/
