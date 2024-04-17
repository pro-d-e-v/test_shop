<?php

namespace TinySolutions\cptwooint\PluginsSupport\LearnPress;

// Do not allow directly accessing this file.
use TinySolutions\cptwooint\Traits\SingletonTrait;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * LPInit
 */
class LPInit {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Class Constructor
	 */
	private function __construct() {
		add_action( 'woocommerce_order_status_completed', [ $this, 'wc_payment_for_lp' ], 10, 1 );
		add_filter( 'cptwoo_product_get_price', [ $this, 'lp_cptwoo_product_get_price' ], 20, 3 );
		add_action( 'init', [ $this, 'remove_lp_course_button' ] );
		add_action( 'learn-press/course-buttons', [ $this, 'add_learnpress_course_button' ], 10 );
	}

	/**
	 * @return void
	 */
	public function remove_lp_course_button() {
		remove_action(
			'learn-press/course-buttons',
			[
				\LearnPress::instance()->template( 'course' ),
				'course_purchase_button',
			],
			10
		);
	}

	/**
	 * Add learnpress course button.
	 *
	 * @param object $course Course object.
	 *
	 * @return void
	 */
	public function add_learnpress_course_button( $course ) {
		if ( empty( $course ) ) {
			$course = learn_press_get_course();
		}
		$user = learn_press_get_current_user();

		$can_purchase = $user->can_purchase_course( $course->get_id() );
		if ( is_wp_error( $can_purchase ) ) {
			return;
		}
		echo do_shortcode( '[cptwooint_cart_button/]' );
	}

	/**
	 * Get Lp price.
	 *
	 * @param int    $price product price.
	 * @param object $product product.
	 * @param string $post_type post type name.
	 *
	 * @return mixed
	 */
	public function lp_cptwoo_product_get_price( $price, $product, $post_type ) {
		if ( LP_COURSE_CPT !== $post_type ) {
			return $price;
		}
		$course = learn_press_get_course( $product->get_id() );

		return $course->get_price();
	}

	/**
	 * Create payment
	 *
	 * @param int $order_id order id.
	 *
	 * @return mixed
	 * @throws \Exception Exception.
	 */
	public function wc_payment_for_lp( $order_id ) {

		$wc_order = wc_get_order( $order_id );
		$items    = $wc_order->get_items();
		$lp_user  = learn_press_get_user( $wc_order->get_user_id(), false );

		foreach ( $items as $item ) {

			$wp_course = get_post( $item['product_id'] );
			// Check if lp_course exists.
			if ( LP_COURSE_CPT !== $wp_course->post_type ) {
				continue;
			}

			$lp_item_data = [
				'order_item_name' => $wp_course->post_title,
				'item_id'         => $wp_course->ID,
				'quantity'        => 1,
			];
			$lp_order     = new \LP_Order();
			$lp_order->set_created_via( 'external' );
			$lp_order->set_total( $item->get_total() );
			$lp_order->set_subtotal( $item->get_subtotal() );
			$lp_order->set_user_ip_address( $wc_order->get_customer_ip_address() );
			$lp_order->set_user_agent( $wc_order->get_customer_user_agent() );
			$lp_order->set_user_id( $wc_order->get_user_id() );
			$lp_order->save();
			$lp_order->add_item( $lp_item_data );
			$lp_order->save();
			$lp_order->update_status( 'lp-completed', true );

			$user_course_data = $lp_user->get_course_data( $wp_course->ID );
			$user_course_data->set_status( LP_COURSE_ENROLLED );
			$user_course_data->set_start_time( time() );
			$user_course_data->set_end_time();
			$user_course_data->set_graduation( LP_COURSE_GRADUATION_IN_PROGRESS );
			$user_course_data->update();

		}

		return $order_id;
	}
}
