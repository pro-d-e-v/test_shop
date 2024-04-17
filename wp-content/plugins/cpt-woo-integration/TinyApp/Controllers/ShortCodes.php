<?php

namespace TinySolutions\cptwooint\Controllers;

// Do not allow directly accessing this file.
use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Traits\SingletonTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

class ShortCodes {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 *
	 */
	private function __construct() {
		add_action( 'init', [ $this,'cptwooint_shortcodes' ] );
	}

	/***
	 * @return void
	 */
	public function cptwooint_shortcodes() {
		$shortcodes = [
			'short_description',
			'cart_button',
			'price',
		];
		foreach ( $shortcodes as $shortcode ) :
			add_shortcode( 'cptwooint_' . $shortcode, [ $this, $shortcode . '_shortcode' ] );
		endforeach;
	}

	/***
	 * @param $content
	 *
	 * @return mixed|string
	 */
	public function short_description_shortcode( $atts ) {
		if ( ! Fns::is_supported( get_the_ID() ) ) {
			return;
		}

		ob_start();
		do_action( 'cptwooint_before_display_short_description' );

		global $product;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( get_the_ID() );
		}
		?>
		<div class="cptwooint-cart-btn-wrapper">
			<?php
				$description = $product->get_short_description();
				Fns::print_html( apply_filters( 'cptwooint_display_short_description', $description, $product ) );
			?>
		</div>
		<?php
		do_action( 'cptwooint_after_display_short_description' );
		return ob_get_clean();
	}

	/***
	 * @param $content
	 *
	 * @return mixed|string
	 */
	public function price_shortcode( $atts ) {
		if ( ! Fns::is_supported( get_the_ID() ) ) {
			return;
		}
		global $product;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( get_the_ID() );
		}
		ob_start();
		?>
		<div class=" cptwooint-product-price">
		<?php
		do_action( 'cptwooint_before_display_price' );
		woocommerce_template_single_price();
		do_action( 'cptwooint_after_display_price' );
		// https://stackoverflow.com/questions/48763989/set-product-sale-price-programmatically-in-woocommerce-3
		?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param $atts
	 *
	 * @return false|string|void
	 */
	public function cart_button_shortcode( $atts ) {
		$current_post_type = get_post_type( get_the_ID() );
		if ( ! Fns::is_supported( $current_post_type ) ) {
			return;
		}
		global $product;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( get_the_ID() );
		}
		$options = Fns::get_options();
		ob_start();
		?>
		<div class="cptwooint-cart-btn-wrapper ">

			<?php
			if ( is_single( get_the_ID() ) ) {
				do_action( 'cptwooint_display_single_add_to_cart_button', $product, $options, $current_post_type );
			} else {
				do_action( 'cptwooint_display_add_to_cart_button', $product, $options, $current_post_type );
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}
}