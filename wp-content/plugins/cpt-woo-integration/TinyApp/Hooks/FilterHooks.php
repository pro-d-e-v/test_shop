<?php
/**
 * Main FilterHooks class.
 *
 * @package TinySolutions\WM
 */

namespace TinySolutions\cptwooint\Hooks;

defined( 'ABSPATH' ) || exit();

use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Modal\CPTOrderItemProduct;
use TinySolutions\cptwooint\Modal\CPTProductDataStore;
use TinySolutions\cptwooint\Traits\SingletonTrait;

/**
 * Main FilterHooks class.
 */
class FilterHooks {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	private function __construct() {
		// Plugins Setting Page.
		add_filter( 'body_class', [ $this, 'add_body_class' ] );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );

		add_filter( 'plugin_action_links_' . CPTWI_BASENAME, [ $this, 'plugins_setting_links' ] );
		add_filter( 'woocommerce_data_stores', [ $this, 'cptwoo_data_stores' ], 99 );
		add_filter( 'woocommerce_product_get_price', [ $this, 'cptwoo_product_get_price' ], 15, 2 );
		// Show meta value after post content THis will be shortcode.
		add_filter( 'the_content', [ $this, 'display_price_and_cart_button' ] );
		// Order Product Class.
		add_filter( 'woocommerce_get_order_item_classname', [ $this, 'get_order_item_classname' ], 12, 3 );
		// Checkout Page issue. Plugin Support.
		add_filter( 'woocommerce_checkout_create_order_line_item_object', [ $this, 'checkout_create_order_line_item_object' ], 12 );
		// Add suggestions to the product tabs.
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'product_data_tabs' ], 20 );
		add_filter( 'woocommerce_format_sale_price', [ $this, 'format_sale_price' ], 20 );

		// TODO:: Conflicting With jetengine. Need Check and implementation.
		 add_filter( 'is_woocommerce', [ $this, 'is_woocommerce' ], 20 );

		// Generating dynamically the product "sale price".
		add_filter( 'woocommerce_product_get_sale_price', [ $this, 'custom_dynamic_sale_price' ], 10, 2 );
		add_filter( 'woocommerce_product_reviews_tab_title', [ $this, 'product_reviews_tab_title' ], 12, 2 );

		// Generating dynamically the product "regular price".
		add_filter( 'woocommerce_product_get_regular_price', [ $this, 'custom_dynamic_regular_price' ], 10, 2 );
		add_filter( 'woocommerce_account_downloads_columns', [ $this, 'account_downloads_columns' ], 15 );
		// Custom Page Template.
		add_filter( 'template_include', [ $this, 'archive_post_template' ], 99 );
		add_filter( 'comments_template', [ $this, 'comments_template_loader' ], 50 );
	}
	/**
	 * Add specific body class when the Wishlist page is opened
	 *
	 * @param array $classes Existing boy classes.
	 *
	 * @return array
	 */
	public function add_body_class( $classes ) {
		$classes[] = 'cpt-woo-integration';
		if ( cptwooint()->has_pro() ) {
			$classes[] = 'cpt-woo-integration-pro';
		}
		return $classes;
	}


	/**
	 * Load comments template.
	 *
	 * @param string $template template to load.
	 * @return string
	 */
	public static function comments_template_loader( $template ) {
		$type         = get_post_type();
		$is_supported = Fns::is_review_enabled( $type );
		$is_single    = Fns::is_single_page_like_product_page( $type );
		if ( $is_supported ) {
			return $template;
		}
		$check_dirs = [
			trailingslashit( get_stylesheet_directory() ) ,
			trailingslashit( get_template_directory() ),
		];
		foreach ( $check_dirs as $dir ) {
			if ( file_exists( trailingslashit( $dir ) . 'comments.php' ) ) {
				return trailingslashit( $dir ) . 'comments.php';
			}
		}
		return $template;
	}

	/**
	 * Change Archive Page Template.
	 *
	 * @param string $template file path.
	 * @return string
	 */
	public function archive_post_template( $template ) {
		$post_type = get_query_var( 'post_type' );
		if ( is_post_type_archive() && Fns::is_archive_page_like_shop_page( $post_type ) ) {
			$template = wc_get_template( 'archive-product.php' );
		} elseif ( is_singular( $post_type ) && Fns::is_single_page_like_product_page( $post_type ) ) {
			$template = wc_get_template( 'single-product.php' );
		}
		return $template;
	}

	/**
	 * @param string $title Tab Title.
	 * @return string
	 */
	public function product_reviews_tab_title( $title ) {
		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return $title;
		}
		$post_type = get_post_type( $product->get_id() );
		if ( Fns::is_supported( $post_type ) && ! ( cptwooint()->has_pro() && Fns::is_review_enabled( $post_type ) ) ) {
			$title = esc_html__( 'Comments', 'cptwooint' );
		}
		return $title;
	}

	/**
	 * Column list
	 *
	 * @param array $column columns.
	 * @return mixed
	 */
	public function account_downloads_columns( $column ) {
		$column['post-type'] = apply_filters( 'cptwooint_account_downloads_columns_type', esc_html__( 'Type', 'cptwooint' ) );
		return $column;
	}

	/**
	 * Dunamic Reguler Price
	 *
	 * @param float  $regular_price price.
	 * @param object $product Product object.
	 * @return float|int|mixed|string
	 */
	public function custom_dynamic_regular_price( $regular_price, $product ) {
		$post_type = get_post_type( $product->get_id() );
		if ( ! Fns::is_supported( $post_type ) ) {
			return $regular_price;
		}

		return $regular_price ?: Fns::cptwoo_get_price( $product->get_id() );
	}
	/**
	 * Sale Price
	 *
	 * @param float  $sale_price Price.
	 * @param object $product Product object.
	 * @return float|int|mixed|string
	 */
	public function custom_dynamic_sale_price( $sale_price, $product ) {
		$post_type = get_post_type( $product->get_id() );
		if ( ! Fns::is_supported( $post_type ) ) {
			return $sale_price;
		}

		return $sale_price ?: Fns::cptwoo_get_price( $product->get_id(), 'sale_price' );
	}

	/**
	 * @param $is_woocommerce
	 *
	 * @return bool
	 */
	public function __is_woocommerce( $is_woocommerce ) {
		$post_type = get_post_type( get_queried_object_id() );

		return $is_woocommerce || Fns::is_supported( $post_type );
	}

	/**
	 * @param $is_woocommerce
	 *
	 * @return bool
	 */
	public function is_woocommerce( $is_woocommerce ) {
		if ( $is_woocommerce ) {
			return $is_woocommerce;
		}
		$post_type = get_post_type( get_queried_object_id() );
		$_is       = ( is_post_type_archive( $post_type ) && Fns::is_archive_page_like_shop_page( $post_type ) ) || ( is_singular( $post_type ) );
		if ( 'page' !== $post_type && $_is ) {
			return true;
		}
		return $is_woocommerce;
	}

	/**
	 * @param $links
	 * @param $file
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $file == CPTWI_BASENAME ) {
			$report_url         = 'https://www.wptinysolutions.com/contact';
			$row_meta['issues'] = sprintf( '%2$s <a target="_blank" href="%1$s">%3$s</a>', esc_url( $report_url ), esc_html__( 'Facing issue?', 'cptwooint' ), '<span style="color: red">' . esc_html__( 'Please open a support ticket.', 'cptwooint' ) . '</span>' );

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * @param $types
	 *
	 * @return mixed
	 */
	public function format_sale_price( $price ) {
		return '<span class="cpt-price-wrapper">' . $price . '</span>';
	}

	/**
	 * Product data tabs filter
	 *
	 * Adds a new Extensions tab to the product data meta box.
	 *
	 * @param array $tabs Existing tabs.
	 *
	 * @return array
	 */
	public function product_data_tabs( $tabs ) {
		if ( ! Fns::is_supported( get_post_type( get_the_ID() ) ) ) {
			return $tabs;
		}
		unset(
			$tabs['marketplace-suggestions']
		);

		return $tabs;
	}

	/**
	 * @param $obj_WC_Order_Item_Product
	 * @param $cart_item_key
	 * @param $values
	 * @param $order
	 *
	 * @return mixed
	 */
	public function checkout_create_order_line_item_object( $obj_WC_Order_Item_Product ) {
		$obj_WC_Order_Item_Product = new CPTOrderItemProduct();

		return $obj_WC_Order_Item_Product;
	}

	/***
	 * @param $content
	 *
	 * @return mixed|string
	 */
	public function display_price_and_cart_button( $content ) {

		$current_post_type = get_post_type( get_the_ID() );
		$options           = Fns::get_options();
		$content          .= '<div class="cpt-price-and-cart-button">';
		if ( ! empty( $options['price_after_content_post_types'] ) &&
			 is_array( $options['price_after_content_post_types'] ) &&
			 in_array( $current_post_type, $options['price_after_content_post_types'] )
		) {
			$content .= do_shortcode( '[cptwooint_price/]' );
		}

		if (
			! empty( $options['cart_button_after_content_post_types'] ) &&
			is_array( $options['cart_button_after_content_post_types'] ) &&
			in_array( $current_post_type, $options['cart_button_after_content_post_types'] )
		) {
			$content .= do_shortcode( '[cptwooint_cart_button/]' );
		}
		$content .= '</div>';

		return $content;
	}

	/**
	 * @param $price
	 * @param $product
	 *
	 * @return mixed
	 */
	public function cptwoo_product_get_price( $price, $product ) {
		$post_type = get_post_type( $product->get_id() );
		if ( ! Fns::is_supported( $post_type ) ) {
			return $price;
		}
		$is_add_price_meta = Fns::is_add_cpt_meta( $post_type, 'default_price_meta_field' );
		if ( $is_add_price_meta ) {
			$price = get_post_meta( $product->get_id(), '_sale_price', true );
			if ( is_null( $price ) || '' === $price ) {
				$price = get_post_meta( $product->get_id(), '_regular_price', true );
			}
			if ( is_null( $price ) || '' === $price ) {
				$price = wc_format_decimal( Fns::cptwoo_get_price( $product->get_id(), 'sale_price' ) ?: Fns::cptwoo_get_price( $product->get_id() ) );
			}
		} else {
			$price = wc_format_decimal( Fns::cptwoo_get_price( $product->get_id(), 'sale_price' ) ?: Fns::cptwoo_get_price( $product->get_id() ) );
		}

		return apply_filters( 'cptwoo_product_get_price', $price, $product, $post_type );
	}

	/**
	 * @param $stores
	 *
	 * @return mixed
	 */
	public function cptwoo_data_stores( $stores ) {
		$stores['product'] = CPTProductDataStore::class;

		return $stores;
	}

	/**
	 * @param $stores
	 *
	 * @return mixed
	 */
	public function get_order_item_classname( $classname, $item_type, $id ) {
		if ( 'WC_Order_Item_Product' === $classname ) {
			$classname = '\TinySolutions\cptwooint\Modal\CPTOrderItemProduct';
		}

		return $classname;
	}

	/**
	 * @param array $links default plugin action link
	 *
	 * @return array [array] plugin action link
	 */
	public function plugins_setting_links( $links ) {
		$new_links                       = [];
		$new_links['cptwooint_settings'] = '<a href="' . admin_url( 'admin.php?page=cptwooint-admin' ) . '">' . esc_html__( 'Settings', 'cptwooint' ) . '</a>';
		if ( ! Fns::is_plugins_installed( 'cpt-woo-integration-pro/cpt-woo-integration-pro.php' ) ) {
			$links['cptwooint_pro'] = sprintf( '<a href="https://www.wptinysolutions.com/tiny-products/cpt-woo-integration/" target="_blank" style="color: #39b54a; font-weight: bold;">' . esc_html__( 'Go Pro', 'cptwooint' ) . '</a>' );
		}

		return array_merge( $new_links, $links );
	}
}
