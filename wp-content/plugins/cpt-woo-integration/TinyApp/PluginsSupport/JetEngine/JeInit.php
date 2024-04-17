<?php

namespace TinySolutions\cptwooint\PluginsSupport\JetEngine;

// Do not allow directly accessing this file.
use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Traits\SingletonTrait;
use Jet_Engine\Compatibility\Packages\Jet_Engine_Woo_Package\Meta_Boxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * LPInit
 */
class JeInit {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Class Constructor
	 */
	private function __construct() {
		add_action( 'init', [ $this, 'remove_lp_course_button' ], 1 );
	}

	/**
	 * @return void
	 */
	public function remove_lp_course_button() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! empty( $_GET['post'] ) && 'edit' === wp_unslash( $_GET['action'] ?? '' ) && Fns::is_supported( get_post_type( wp_unslash( $_GET['post'] ) ) ) ) {
			remove_action(
				'jet-engine/meta-boxes/register-custom-source/woocommerce_product_data',
				[
					Meta_Boxes\Manager::instance(),
					'register_product_panel_meta_box',
				],
				10
			);
			remove_action(
				'jet-engine/meta-boxes/register-custom-source/woocommerce_product_variation',
				[
					Meta_Boxes\Manager::instance(),
					'register_product_variation_meta_box',
				],
				10
			);
		}
	}
}
