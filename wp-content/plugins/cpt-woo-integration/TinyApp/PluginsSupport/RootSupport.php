<?php
/**
 * @wordpress-plugin
 * Plugin Name:       LearnPress woocommerce integration
 * Plugin URI:        https://www.wptinysolutions.com/tiny-products/cpt-woo-integration
 * Description:       Integrate custom post type with woocommerce. Sell Any Kind Of Custom Post
 * Version:           1.0.0
 * Author:            Tiny Solutions
 * Author URI:        https://www.wptinysolutions.com/
 * Tested up to:      6.4
 * WC tested up to:   8.4
 * Text Domain:       lpcptwooint
 * Domain Path:       /languages
 *
 * @package TinySolutions\WM
 */

namespace TinySolutions\cptwooint\PluginsSupport;

// Do not allow directly accessing this file.
use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\PluginsSupport\BaBooking\BabeInit;
use TinySolutions\cptwooint\Traits\SingletonTrait;
use TinySolutions\cptwooint\PluginsSupport\LearnPress\LPInit;
use TinySolutions\cptwooint\PluginsSupport\JetEngine\JeInit;


if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Root Support
 */
class RootSupport {

	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Class Constructor
	 */
	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'plugin_integration' ] );
	}

	/**
	 * Main FIle Integration.
	 *
	 * @return void
	 */
	public function plugin_integration() {
		// Learn Press.
		if ( function_exists( 'LP' ) && Fns::is_supported( LP_COURSE_CPT ) ) {
			LPInit::instance();
		}
		// Jet engine Metabox.
		if ( class_exists( 'Jet_Engine' ) ) {
			JeInit::instance();
		}
		// Ba Books.
		if ( class_exists( 'BABE_Order' ) && ! class_exists( 'BabeInit' ) ) {
			BabeInit::instance();
		}
	}
}
