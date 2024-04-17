<?php
/**
 * Main initialization class.
 *
 * @package TinySolutions\cptwooint
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

require_once CPTWI_PATH . 'vendor/autoload.php';

use TinySolutions\cptwooint\Controllers\Admin\AdminMenu;
use TinySolutions\cptwooint\Controllers\Admin\Api;
use TinySolutions\cptwooint\Controllers\AdminController;
use TinySolutions\cptwooint\Controllers\AssetsController;
use TinySolutions\cptwooint\Controllers\Dependencies;
use TinySolutions\cptwooint\Controllers\Installation;
use TinySolutions\cptwooint\Controllers\Notice\AdminNotice;
use TinySolutions\cptwooint\Controllers\ShortCodes;
use TinySolutions\cptwooint\Hooks\ActionHooks;
use TinySolutions\cptwooint\Hooks\FilterHooks;
use TinySolutions\cptwooint\PluginsSupport\RootSupport;
use TinySolutions\cptwooint\Traits\SingletonTrait;

if ( ! class_exists( CptWooInt::class ) ) {
	/**
	 * Main initialization class.
	 */
	final class CptWooInt {

		/**
		 * Nonce id
		 *
		 * @var string
		 */
		public $nonceId = 'cptwooint_wpnonce';

		/**
		 * Post Type.
		 *
		 * @var string
		 */
		public $category = 'cptwooint_category';
		/**
		 * Singleton
		 */
		use SingletonTrait;

		/**
		 * Class Constructor
		 */
		private function __construct() {

			add_action( 'init', [ $this, 'init' ] );
			add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ], 11 );

			// Register Plugin Active Hook.
			register_activation_hook( CPTWI_FILE, [ Installation::class, 'activate' ] );
			// Register Plugin Deactivate Hook.
			register_deactivation_hook( CPTWI_FILE, [ Installation::class, 'deactivation' ] );
			// HPOS.
			add_action(
				'before_woocommerce_init',
				function () {
					if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CPTWI_FILE, true );
					}
				}
			);

			 $this->init_controller();
		}

		/**
		 * Assets url generate with given assets file
		 *
		 * @param string $file File.
		 *
		 * @return string
		 */
		public function get_assets_uri( $file ) {
			$file = ltrim( $file, '/' );
			return trailingslashit( CPTWI_URL . '/assets' ) . $file;
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function get_template_path() {
			return apply_filters( 'cptwooint_template_path', 'templates/' );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( CPTWI_FILE ) );
		}

		/**
		 * Load Text Domain
		 */
		public function init() {
			load_plugin_textdomain( 'cptwooint', false, CPTWI_ABSPATH . '/languages/' );
		}

		/**
		 * Load Text Domain
		 */
		public function plugins_loaded() {
		}

		/**
		 * Init
		 *
		 * @return void
		 */
		public function init_controller() {
			if ( ! Dependencies::instance()->check() ) {
				return;
			}

			do_action( 'cptwooint/before_loaded' );

			// Include File.
			AssetsController::instance();
			FilterHooks::instance();
			ActionHooks::instance();
			RootSupport::instance();
			Api::instance();

			if ( is_admin() ) {
				AdminNotice::instance();
				AdminController::instance();
				AdminMenu::instance();
			} else {
				ShortCodes::instance();
			}

			do_action( 'cptwooint/after_loaded' );
		}

		/**
		 * Checks if Pro version installed
		 *
		 * @return boolean
		 */
		public function has_pro() {
			if ( function_exists( 'cptwoointp' ) && version_compare( CPTWIP_VERSION, '1.1.4', '>=' ) ) {
				return cptwoointp()->user_can_use_cptwooinitpro() || ( defined( 'TINY_DEBUG_CPTWI_PRO_1_2_0' ) && TINY_DEBUG_CPTWI_PRO_1_2_0 );
			}
			return false;
		}

		/**
		 * PRO Version URL.
		 *
		 * @return string
		 */
		public function pro_version_link() {
			return 'https://www.wptinysolutions.com/tiny-products/cpt-woo-integration/';
		}
	}

	/**
	 * @return CptWooInt
	 */
	function cptwooint() {
		return CptWooInt::instance();
	}

	cptwooint();
}
