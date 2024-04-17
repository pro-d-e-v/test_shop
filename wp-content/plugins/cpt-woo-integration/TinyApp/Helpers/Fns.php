<?php
/**
 * Fns Helpers class
 *
 * @package  TinySolutions\cptwooint
 */

namespace TinySolutions\cptwooint\Helpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Fns class
 */
class Fns {
	/**
	 * @var array
	 */
	private static $cache = [];

	/**
	 * @param $plugin_file_path
	 *
	 * @return bool
	 */
	public static function is_plugins_installed( $plugin_file_path = null ) {
		$installed_plugins_list = get_plugins();

		return isset( $installed_plugins_list[ $plugin_file_path ] );
	}

	/**
	 *  Verify nonce.
	 *
	 * @return bool
	 */
	public static function verify_nonce() {
		$nonce = isset( $_REQUEST[ cptwooint()->nonceId ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ cptwooint()->nonceId ] ) ) : null;
		if ( wp_verify_nonce( $nonce, cptwooint()->nonceId ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public static function get_options() {
		$defaults = [
			'selected_post_types'      => [
				'post' => [
					'regular_price' => '_regular_price',
					'sale_price'    => '_sale_price',
				],
			],
			'default_price_meta_field' => [],
		];

		$options = get_option( 'cptwooint_settings' );
		$options = wp_parse_args( $options, $defaults );

		return apply_filters( 'cptwooint_default_settings', $options );
	}

	/**
	 * @return array|int[]|string[]
	 */
	public static function supported_post_types() {
		$options = self::get_options();

		return ! empty( $options['selected_post_types'] ) ? array_keys( $options['selected_post_types'] ) : [];
	}

	/**
	 * @param $current_post_type
	 * @param $key 'regular_price' , 'sale_price'
	 *
	 * @return mixed|string
	 */
	public static function price_meta_key( $current_post_type, $key = 'regular_price' ) {

		$is_add_price_meta = self::is_add_cpt_meta( $current_post_type, $key );
		if ( $is_add_price_meta ) {
			if ( 'sale_price' == $key ) {
				$meta_key = '_sale_price';
			} else {
				$meta_key = '_regular_price';
			}
		} else {
			$options  = self::get_options();
			$meta_key = ! empty( $options['selected_post_types'][ $current_post_type ][ $key ] ) ? $options['selected_post_types'][ $current_post_type ][ $key ] : '';
		}

		return $meta_key;
	}

	/**
	 * @return bool
	 */
	public static function is_supported( $post ) {
		if ( is_numeric( $post ) || $post instanceof \WP_Post ) {
			$post_type = get_post_type( $post );
		} elseif ( is_string( $post ) ) {
			$post_type = $post;
		} else {
			$post_type = $post;
		}
		if ( empty( $post_type ) ) {
			return false;
		}
		$supported_types = self::supported_post_types();
		return in_array( $post_type, $supported_types, true );
	}

	/**
	 * @param $post_type
	 *
	 * @return bool
	 */
	public static function is_add_cpt_meta( $post_type, $key ) {
		if ( empty( $post_type ) ) {
			return false;
		}
		$is_add_price_meta = self::get_options();
		$default           = $is_add_price_meta[ $key ] ?? [];
		if ( empty( $default ) ) {
			return false;
		}
		return self::is_supported( $post_type ) && in_array( $post_type, $default, true );
	}

	/**
	 * @param $product_id
	 * @param $key
	 *
	 * @return int|mixed
	 */
	public static function cptwoo_get_price_meta_value( $product_id, $key = 'regular_price' ) {
		$price = '';
		if ( ! $product_id ) {
			return $price;
		}
		$current_post_type = get_post_type( $product_id );
		if ( ! self::is_supported( $current_post_type ) ) {
			return $price;
		}
		$meta_key = self::price_meta_key( $current_post_type, $key );

		if ( $meta_key ) {
			$price = get_post_meta( $product_id, $meta_key, true );
		}

		return $price;
	}

	/**
	 * @param $product_id
	 *
	 * @return float
	 */
	public static function cptwoo_get_price( $product_id, $key = 'regular_price' ) {
		$_price = self::cptwoo_get_price_meta_value( $product_id, $key );

		return $_price;
	}
	/**
	 * @param $post_type
	 *
	 * @return bool
	 */
	public static function is_archive_page_like_shop_page( $post_type ) {
		if ( empty( $post_type ) ) {
			return false;
		}
		$options = self::get_options();
		$default = $options['archive_similar_shop_page'] ?? [];
		if ( empty( $default ) ) {
			return false;
		}
		return self::is_supported( $post_type ) && in_array( $post_type, $default, true );
	}
	/**
	 * @param $post_type
	 *
	 * @return bool
	 */
	public static function is_single_page_like_product_page( $post_type ) {
		if ( empty( $post_type ) ) {
			return false;
		}
		$options = self::get_options();
		$default = $options['details_similar_product_page'] ?? [];
		if ( empty( $default ) ) {
			return false;
		}
		return self::is_supported( $post_type ) && in_array( $post_type, $default, true );
	}

	/**
	 * @param $post_type
	 *
	 * @return bool
	 */
	public static function is_review_enabled( $post_type ) {
		if ( empty( $post_type ) ) {
			return false;
		}
		$options = self::get_options();
		$default = $options['enable_product_review'] ?? [];
		if ( empty( $default ) ) {
			return false;
		}
		return self::is_supported( $post_type ) && in_array( $post_type, $default, true );
	}
	/**
	 * @param $post_type
	 *
	 * @return bool
	 */
	public static function is_schema_enabled( $post_type ) {
		if ( empty( $post_type ) ) {
			return false;
		}
		$options = self::get_options();
		$default = $options['enable_product_schema'] ?? [];
		if ( empty( $default ) ) {
			return false;
		}
		return self::is_supported( $post_type ) && in_array( $post_type, $default, true );
	}

	/**
	 * @return true
	 */
	public static function clear_data_cache() {
		$prefix = '_transient_cptwooint_';
		// Get all transients with the specified prefix.
		global $wpdb;
		$query      = $wpdb->prepare(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
			$wpdb->esc_like( $prefix ) . '%'
		);
		$transients = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		// Delete transients.
		$deleted = [];
		foreach ( $transients as $transient ) {
			$trans = str_replace( '_transient_', '', $transient->option_name );
			if ( delete_transient( $trans ) ) {
				$deleted[] = $trans;
			}
		}

		return count( $transients ) === count( $deleted );
	}
	/**
	 * @param string $text get pro.
	 * @return void
	 */
	public static function pro_message_button( $text = 'Get Pro' ) {
		// Instantiate your custom class.
		?>
		<div class="cptwooint-pro-button">
			<a target="_blank" href="<?php echo esc_url( cptwooint()->pro_version_link() ); ?>"> <?php echo esc_html( $text ); ?></a>
		</div>
		<?php
	}
	/**
	 * Prints HTMl.
	 *
	 * @param string $html HTML.
	 * @param bool   $allHtml All HTML.
	 *
	 * @return void
	 */
	public static function print_html( $html, $allHtml = false ) {
		if ( ! $html ) {
			return;
		}
		if ( $allHtml ) {
			echo stripslashes_deep( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo wp_kses_post( stripslashes_deep( $html ) );
		}
	}

	/**
	 * @return array[]
	 */
	public static function pro_feature_list() {
		return [
			[
				'title' => 'Add sale price dates',
				'desc'  => 'Sales can be limited in duration, such as a one-day flash sale.',
			],
			[
				'title' => 'Stock Management ( Inventory Tab )',
				'desc'  => 'Manage product inventory including SKU, stock quantity, stock status, and individual sale availability. Product SKUs are unique identifiers. Track stock quantity for the products. Stock status indicates whether the product is in stock, out of stock, or on backorder. Products can be available for purchase separately.',
			],
			[
				'title' => 'Shipping product data',
				'desc'  => 'Shipping Product data tab added.',
			],
			[
				'title' => 'Product Gallery Images',
				'desc'  => 'Use thumbnails or a carousel to display multiple images, allowing users to easily switch between views.',
			],
			[
				'title' => 'Product Type - Simple product',
				'desc'  => 'In WooCommerce, one of the most basic product types is the "Simple Product".',
			],
			[
				'title' => 'Add Downloadable Product',
				'desc'  => 'WooCommerce Downloadable products provide a convenient way to sell digital content online.',
			],
			[
				'title' => 'Download file form WooCommerce user account.',
				'desc'  => 'The customer can download the file(s) to their device form My Account page',
			],
			[
				'title' => 'Downloadable files.',
				'desc'  => 'WooCommerce Downloadable Product all feature added. ',
			],
			[
				'title' => 'Download limit.',
				'desc'  => 'Download limit can set for downloadable product',
			],
			[
				'title' => 'Download expiry.',
				'desc'  => 'Enter the number of days before a download link expires.',
			],
			[
				'title' => 'CPT Upsells.',
				'desc'  => 'Upselling is a sales technique where a seller invites the customer to purchase more.',
			],
			[
				'title' => 'CPT Cross-sells.',
				'desc'  => 'Products offered as a cross-sell appear on the shopping cart page, just before the customer begins the checkout process.',
			],
			[
				'title' => 'Product Type - Variable product',
				'desc'  => 'Variable products in WooCommerce let you offer a set of variations on a product, with control over prices, stock, image and more for each variation..',
			],
			[
				'title' => 'Product Type - External/Affiliate product',
				'desc'  => 'This product type is commonly used by affiliates and online marketers to earn commissions by driving traffic to other websites.',
			],
			[
				'title' => 'Product Review',
				'desc'  => 'Activate Review Functionality.',
			],
			[
				'title' => 'Product Schema',
				'desc'  => 'Structured data, SEO-friendly, rich results, enhanced visibility, better user experience.',
			],
			[
				'title' => 'Pro plugin updates directly from the dashboard.',
				'desc'  => 'Activate licence key and update latest version form dashboard.',
			],
		];
	}
}
