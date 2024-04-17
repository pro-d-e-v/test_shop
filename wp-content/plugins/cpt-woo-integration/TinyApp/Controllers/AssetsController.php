<?php

namespace TinySolutions\cptwooint\Controllers;

use WC_Frontend_Scripts;
use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * AssetsController
 */
class AssetsController {

	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Ajax URL
	 *
	 * @var string
	 */
	private $ajaxurl;

	/**
	 * Class Constructor
	 */
	public function __construct() {
		$this->version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : CPTWI_VERSION;
		/**
		 * Admin scripts.
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'backend_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_assets' ] );
	}
	/**
	 * Frontend Script
	 */
	public function frontend_assets() {
		$styles = [
			[
				'handle' => 'cptwooint-public',
				'src'    => cptwooint()->get_assets_uri( 'css/frontend/frontend.css' ),
			],
		];

		// Register public styles.
		foreach ( $styles as $style ) {
			wp_register_style( $style['handle'], $style['src'], '', $this->version );
		}

		$post_type            = get_post_type( get_queried_object_id() );
		$wc_script_permission = true;
		if ( $wc_script_permission ) {
			WC_Frontend_Scripts::init();
		}

		if ( is_single() && Fns::is_supported( $post_type ) && $wc_script_permission ) {

			if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
				wp_enqueue_script( 'zoom' );
			}

			if ( current_theme_supports( 'wc-product-gallery-slider' ) ) {
				wp_enqueue_script( 'flexslider' );
			}

			if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
				wp_enqueue_script( 'photoswipe-ui-default' );
				wp_enqueue_style( 'photoswipe-default-skin' );

			}
			wp_enqueue_script( 'wc-single-product' );

		}

		if ( Fns::is_supported( $post_type ) ) {
			do_action( 'cptwooint_supported_post_type_frontend_assets', $post_type );
		}

		if ( ( is_single() && Fns::is_supported( get_the_ID() ) ) || ( is_archive() && get_post_type( get_queried_object_id() ) ) ) {
			wp_enqueue_style( 'cptwooint-public' );
		}
		if ( is_single() && Fns::is_supported( get_the_ID() ) ) {
			add_action( 'wp_footer', 'woocommerce_photoswipe' );
		}
	}
	/**
	 * Registers Admin scripts.
	 *
	 * @return void
	 */
	public function backend_assets( $hook ) {

		$styles = [
			[
				'handle' => 'cptwooint-settings',
				'src'    => cptwooint()->get_assets_uri( 'css/backend/admin-settings.css' ),
			],
		];

		// Register public styles.
		foreach ( $styles as $style ) {
			wp_register_style( $style['handle'], $style['src'], '', $this->version );
		}

		$scripts = [
			[
				'handle' => 'cptwooint-settings',
				'src'    => cptwooint()->get_assets_uri( 'js/backend/admin-settings.js' ),
				'deps'   => [],
				'footer' => true,
			],
			[
				'handle' => 'cptwooint-metabox-scripts',
				'src'    => cptwooint()->get_assets_uri( 'js/backend/cptwooint-metabox-scripts.js' ),
				'deps'   => [],
				'footer' => true,
			],
		];

		// Register public scripts.
		foreach ( $scripts as $script ) {
			wp_register_script( $script['handle'], $script['src'], $script['deps'], $this->version, $script['footer'] );
		}

		global $pagenow;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'admin.php' === $pagenow && 'cptwooint-admin' === sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) ) ) {
			wp_enqueue_style( 'cptwooint-settings' );
			wp_enqueue_script( 'cptwooint-settings' );
			wp_localize_script(
				'cptwooint-settings',
				'cptwoointParams',
				[
					'adminUrl'           => esc_url( admin_url() ),
					'restApiUrl'         => esc_url_raw( rest_url() ),
					'hasExtended'        => cptwooint()->has_pro(),
					'proFeature'         => wp_json_encode( Fns::pro_feature_list() ),
					'ajaxUrl'            => esc_url( admin_url( 'admin-ajax.php' ) ),
					'rest_nonce'         => wp_create_nonce( 'wp_rest' ),
					'proLink'            => cptwooint()->pro_version_link(),
					cptwooint()->nonceId => wp_create_nonce( cptwooint()->nonceId ),

				]
			);

		}
	}
}
