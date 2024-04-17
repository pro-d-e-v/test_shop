<?php

namespace TinySolutions\cptwooint\PluginsSupport\BaBooking;

// Do not allow directly accessing this file.
use TinySolutions\cptwooint\Traits\SingletonTrait;
use Automattic\WooCommerce\Internal\Admin\Orders\PageController;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * LPInit
 */
class BabeInit {
	/**
	 * Singleton
	 */
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Class Constructor
	 */
	private function __construct() {
		remove_filter( 'babe_checkout_content', [ \BABE_Order::class, 'checkout_page_prepare' ], 10 );
		add_filter( 'babe_checkout_content', [ $this, 'checkout_page_prepare' ], 15 );
		add_filter( 'cptwoo_product_get_price', [ $this, 'bb_cptwoo_product_get_price' ], 20, 3 );

		add_action( 'babe_order_created', [ $this, 'after_created_order' ], 15, 1 );

		add_action( 'woocommerce_checkout_order_processed', [ $this, 'so_payment_complete' ] );
		add_action( 'woocommerce_order_status_completed', [ $this, 'wc_payment_for_bb' ], 10, 1 );
		add_action( 'cmb2_admin_init', [ $this, 'order_metabox' ], 10, 1 );
	}

	/**
	 * @return void
	 */
	public static function order_metabox() {
		$page_controller = new PageController();
		$prefix          = '_';
		$cmb             = new_cmb2_box(
			[
				'id'           => 'order_metabox_extra',
				'title'        => __( 'WooCommerce Order', 'ba-book-everything' ),
				'object_types' => [ \BABE_Post_types::$order_post_type ],
				'context'      => 'side',
				'priority'     => 'high',
			]
		);
		$order_id        = $cmb->object_id();
		$order_number    = get_post_meta( $order_id, '_wc_payment_id', true );
		$name            = 'N/A';
		if ( absint( $order_number ) ) {
			$name = __( 'WooCommerce Order Id: ', 'ba-book-everything' ) . '<br/><a style="padding:10px 0;display: inline-block;" href="' . esc_url( $page_controller->get_edit_url( absint( $order_number ) ) ) . '"> ' . absint( $order_number ) . ' </a>';
		}
		$cmb->add_field(
			[
				'name' => $name,
				'id'   => $prefix . 'connect_to_wc_order',
				'type' => 'title',
			]
		);
	}

	/**
	 * @param number $order_id Order Id.
	 * @return void
	 */
	public function wc_payment_for_bb( $order_id ) {
		$wc_order = wc_get_order( $order_id );
		$items    = $wc_order->get_items();
		foreach ( $items as $item ) {
			$bb_order = get_post( $item['product_id'] );
			if ( \BABE_Post_types::$order_post_type !== $bb_order->post_type ) {
				continue;
			}
			\BABE_Order::update_order_status( $item['product_id'], 'completed' );
			// payment_deferred.
		}
	}

	/**
	 * @param number $order_id Order Id.
	 * @return void
	 */
	public function so_payment_complete( $order_id ) {
		$wc_order = wc_get_order( $order_id );
		$items    = $wc_order->get_items();
		foreach ( $items as $item ) {
			$bb_order = get_post( $item['product_id'] );
			if ( \BABE_Post_types::$order_post_type !== $bb_order->post_type ) {
				continue;
			}
			\BABE_Order::update_order_status( $item['product_id'], 'payment_deferred' );
			update_post_meta( $item['product_id'], '_wc_payment_id', $order_id );
			// payment_deferred.
		}
	}
	/**
	 * @return float
	 */
	public function bb_cptwoo_product_get_price( $price, $product, $post_type ) {
		if ( 'order' !== $post_type ) {
			return $price;
		}
		$total_with_coupon = \BABE_Order::get_order_total_amount( $product->get_id() );
		$prepaid_received  = \BABE_Order::get_order_prepaid_received( $product->get_id() ) - \BABE_Order::get_order_refunded_amount( $product->get_id() );
		$amount_to_pay     = $total_with_coupon - $prepaid_received;
		return $amount_to_pay;
	}
	/**
	 * @return void
	 */
	public function after_created_order( $order_id ) {
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $order_id, '1' );
	}
	/**
	 * @param string $content Content.
	 * @return mixed|string
	 */
	public function checkout_page_prepare( $content ) {
		$output = $content;
		$args   = (array) wp_parse_args(
			$_GET,
			[
				'order_id'   => 0,
				'order_num'  => '',
				'order_hash' => '',
			]
		);

		// is order data valid.
		$order_id = absint( $args['order_id'] );

		if ( ! WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $order_id ) ) ) {
			// No, it isn't in cart!
			$this->after_created_order( $order_id );
		}

		if ( \BABE_Order::is_order_valid( $order_id, $args['order_num'], $args['order_hash'] ) ) {
			// get order meta.
			$order_meta = \BABE_Order::get_order_meta( $order_id );
			if ( empty( $order_meta ) ) {
				return $output;
			}
			$args['total_amount']   = $order_meta['_total_amount'];
			$args['prepaid_amount'] = $order_meta['_prepaid_amount'];
			$args['payment_model']  = $order_meta['_payment_model'];
			$args['order_currency'] = $order_meta['_order_currency'];
			$order_status           = $order_meta['_status'];
			// clear order meta.
			$order_meta   = \BABE_Order::clear_order_meta( $order_meta );
			$args['meta'] = $order_meta;
			if ( 'payment_expected' === $order_status || 'draft' === $order_status ) {

				if ( ! isset( $order_meta['first_name'] ) ) {
					// get user meta if user is logged in.
					$user_info = wp_get_current_user();
					if ( null != $user_info && $user_info->ID > 0 ) {
						$args['meta']['email']       = $user_info->user_email;
						$args['meta']['email_check'] = $user_info->user_email;
						$args['meta']['first_name']  = $user_info->first_name;
						$args['meta']['last_name']   = $user_info->last_name;
						$contacts                    = get_user_meta( $user_info->ID, 'contacts', 1 );
						if ( is_array( $contacts ) ) {
							$args['meta'] += $contacts;
						}
					}
				} else {
					$args['meta']['email_check'] = $args['meta']['email'];
				}
				// Select Action.
				if ( 'payment_expected' === $order_status || ( 'draft' === $order_status && 'auto' === \BABE_Settings::$settings['order_availability_confirm'] ) ) {
					$args['action'] = 'to_pay';
				} else {
					$args['action'] = 'to_av_confirm';
				}
				$output .= $this->checkout_form( $args );
			} // end if payment_expected or draft.
		}

		return $output;
	}


	/**
	 * Add checkout form to page.
	 *
	 * @param array $args
	 * @return string
	 */
	public function checkout_form( $args ) {

		$output       = '';
		$input_fields = [];

		$args = wp_parse_args(
			$args,
			[
				'order_id'       => 0,
				'order_num'      => '',
				'order_hash'     => '',
				'total_amount'   => 0,
				'prepaid_amount' => 0,
				'payment_model'  => 'full',
				'order_currency' => '',
				'action'         => 'to_pay', // to_pay or to_av_confirm.
				'meta'           => [],
			]
		);

		$args['meta'] = wp_parse_args(
			$args['meta'],
			[
				'first_name'  => '',
				'last_name'   => '',
				'email'       => '',
				'email_check' => '',
				'phone'       => '',
			]
		);

		$order_id       = $args['order_id'];
		$order_num      = $args['order_num'];
		$order_hash     = $args['order_hash'];
		$action         = $args['action'];
		$total_amount   = $args['total_amount'];
		$prepaid_amount = $args['prepaid_amount'];

		$payment_model = $args['payment_model'];
		$currency      = $args['order_currency'] ?: \BABE_Order::get_order_currency( $order_id );

		$args['meta'] = apply_filters( 'babe_checkout_args', $args['meta'], $args );

		/* translators: %s is a order number */
		$output .= '<h2>' . sprintf( __( 'Order #%s', 'ba-book-everything' ), $order_num ) . '</h2>';

		$output .= \BABE_html::order_items( $order_id );

		$output = apply_filters( 'babe_checkout_after_order_items', $output, $args );
		// fields.
		foreach ( $args['meta'] as $field_name => $field_content ) {

			if ( in_array( $field_name, [ 'extra_guests', 'billing_address' ], true ) ) {
				continue;
			}

			$add_content_class = $field_content ? 'checkout_form_input_field_content' : '';

			$input_fields[ $field_name ] = '
            <div class="checkout-form-block">
               
               <div class="checkout_form_input_field ' . $add_content_class . '">
                   <label class="checkout_form_input_label">' . \BABE_html::checkout_field_label( $field_name ) . '</label>
				   <input type="text" class="checkout_input_field checkout_input_required" name="' . $field_name . '" id="' . $field_name . '" value="' . $field_content . '" ' . apply_filters( 'babe_checkout_field_required', '', $field_name ) . '/>
                   <div class="checkout_form_input_underline"><span class="checkout_form_input_ripple"></span></div>
			   </div>
            
            </div>';
		}

		// Get checkout object.
		ob_start();
		if ( ! WC()->cart->is_empty() ) {
			woocommerce_output_all_notices();
			wc_print_notices();
			echo do_shortcode( '[woocommerce_checkout]' );
			wp_enqueue_script( 'wc-checkout' );
			wp_enqueue_script( 'woocommerce' );
			wp_enqueue_style( 'select2', plugins_url( 'assets/css/select2.css', WC_PLUGIN_FILE ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_script( 'selectWoo' );
		}
		$output .= ob_get_clean();
		$output .= '<div id="babe_search_result_refresh">
               <i class="fas fa-spinner fa-spin fa-3x"></i>
            </div>';

		$output = apply_filters( 'babe_checkout_form_html', $output, $args );

		if ( $output ) {
			$output = '
                <div id="checkout_form_block">
                ' . $output . '
                </div>';
		}

		return $output;
	}
}
