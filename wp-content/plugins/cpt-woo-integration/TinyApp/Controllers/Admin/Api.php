<?php

namespace TinySolutions\cptwooint\Controllers\Admin;

use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Traits\SingletonTrait;

class Api {

	/**
	 * Singleton
	 */
	use SingletonTrait;

	private $namespace = 'TinySolutions/cptwooint/v1';

	private $resource_name = '/cptwooint';

	/**
	 * Construct
	 */
	private function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register our routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->resource_name . '/getOptions',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_options' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);
		register_rest_route(
			$this->namespace,
			$this->resource_name . '/updateOptions',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'update_option' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);
		register_rest_route(
			$this->namespace,
			$this->resource_name . '/getPostTypes',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_post_types' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);
		register_rest_route(
			$this->namespace,
			$this->resource_name . '/getPostMetas',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_post_metas' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);
		register_rest_route(
			$this->namespace,
			$this->resource_name . '/clearCache',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'clear_data_cache' ],
				'permission_callback' => [ $this, 'login_permission_callback' ],
			]
		);
	}

	/**
	 * @return true
	 */
	public function login_permission_callback() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @return true
	 */
	public function clear_data_cache() {
		$result            = [
			'updated' => false,
			'message' => esc_html__( 'Action Failed ', 'cptwooint' ),
		];
		$result['updated'] = Fns::clear_data_cache();
		if ( $result['updated'] ) {
			$result['message'] = esc_html__( 'Cache Cleared.', 'cptwooint' );
		}

		return $result;
	}

	/**
	 * @return false|string
	 */
	public function update_option( $request_data ) {

		$result = [
			'updated' => false,
			'message' => esc_html__( 'Update failed. Maybe change not found. ', 'cptwooint' ),
		];

		$parameters = $request_data->get_params();

		$the_settings = get_option( 'cptwooint_settings', [] );

		$the_settings['price_position']                 = sanitize_text_field( $parameters['price_position'] ?? '' );
		$the_settings['price_after_content_post_types'] = array_map( 'sanitize_text_field', $parameters['price_after_content_post_types'] ?? [] );

		$the_settings['cart_button_position']                 = sanitize_text_field( $parameters['cart_button_position'] ?? '' );
		$the_settings['cart_button_after_content_post_types'] = array_map( 'sanitize_text_field', $parameters['cart_button_after_content_post_types'] ?? [] );

		$the_settings['selected_post_types'] = $parameters['selected_post_types'] ?? []; // Multi label Array No need sanitization.

		$the_settings['default_price_meta_field'] = array_map( 'sanitize_text_field', $parameters['default_price_meta_field'] ?? [] );

		$the_settings['show_shortdesc_meta'] = array_map( 'sanitize_text_field', $parameters['show_shortdesc_meta'] ?? [] );

		$the_settings['show_gallery_meta'] = array_map( 'sanitize_text_field', $parameters['show_gallery_meta'] ?? [] );

		$the_settings['archive_similar_shop_page'] = array_map( 'sanitize_text_field', $parameters['archive_similar_shop_page'] ?? [] );

		$the_settings['details_similar_product_page'] = array_map( 'sanitize_text_field', $parameters['details_similar_product_page'] ?? [] );

		$the_settings['enable_product_review'] = array_map( 'sanitize_text_field', $parameters['enable_product_review'] ?? [] );

		$the_settings['enable_product_schema'] = array_map( 'sanitize_text_field', $parameters['enable_product_schema'] ?? [] );

		$styles                = $parameters['style'] ?? [];
		$the_settings['style'] = [];
		if ( is_array( $styles ) ) {
			foreach ( $styles as $key => $value ) {
				if ( ! empty( $key ) ) {
					$the_settings['style'][ $key ] = sanitize_text_field( $value );
				}
			}
		}

		$options = update_option( 'cptwooint_settings', $the_settings );

		$result['updated'] = boolval( $options );

		if ( $result['updated'] ) {
			$result['message'] = esc_html__( 'Updated.', 'cptwooint' );
		}

		return $result;
	}

	/**
	 * @return false|string
	 */
	public function get_options() {
		$options = Fns::get_options();
		return wp_json_encode( $options );
	}

	/**
	 * @return false|string
	 */
	public function get_post_types() {
		// Get all meta keys saved in posts of the specified post type.
		$cpt_args        = [
			'public'   => true,
			'_builtin' => false,
		];
		$post_types      = get_post_types( $cpt_args, 'objects' );
		$post_type_array = apply_filters(
			'cptwooint_post_types',
			[
				[
					'value' => 'post',
					'label' => 'Posts',
				],
				[
					'value' => 'page',
					'label' => 'Page',
				],
			]
		);
		
		// BabeInit tripfery theme support.

		if ( class_exists( 'BABE_Order' ) && ! class_exists( 'BabeInit' ) ) {
			$post_type_array[] = [
				'value' => 'order',
				'label' => 'Order ( BA Book Everything )',
			];
		}

		foreach ( $post_types as $key => $post_type ) {
			if ( 'product' === $key ) {
				continue;
			}
			$post_type_array[] = [
				'value' => $post_type->name,
				'label' => $post_type->label,
			];
		}

		return wp_json_encode( $post_type_array );
	}

	/**
	 * @return false|string
	 */
	public function get_post_metas( $request_data ) {

		$parameters = $request_data->get_params();
		$meta_keys  = [];
		if ( ! empty( $parameters['post_type'] ) ) {
			$post_type = $parameters['post_type'];
			// Get all meta keys saved in posts of the specified post type.
			$cache_key = 'cptwooint_meta_query_' . $post_type;
			// Removed Cache.

			$meta_keys = wp_cache_get( $cache_key );
			if ( false === $meta_keys ) {
				global $wpdb;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$meta_keys = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT DISTINCT meta_key
					FROM $wpdb->postmeta
					INNER JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.ID
					WHERE $wpdb->posts.post_type = %s",
						$post_type
					)
				);
				wp_cache_set( $cache_key, $meta_keys, '', HOUR_IN_SECONDS );
			}
		}

		$the_metas = [];
		if ( ! empty( $meta_keys ) ) {
			$remove_wp_default = [
				'_pingme',
				'_edit_last',
				'_encloseme',
				'_edit_lock',
				'_sale_price',
				'_regular_price',
				'_wp_page_template',
				'total_sales',
				'_tax_status',
				'_tax_class',
				'_manage_stock',
				'_backorders',
				'_sold_individually',
				'_virtual',
				'_downloadable',
				'_download_limit',
				'_download_expiry',
				'_sku',
				'_stock',
				'_price',
				'_stock_status',
				'_wc_average_rating',
				'_wc_review_count',
				'_product_attributes',
				'_product_version',
				'_wc_rating_count',
				'_thumbnail_id',
				'_product_image_gallery',
				'_wp_trash_meta_status',
				'_wp_trash_meta_time',
				'_wp_desired_post_slug',
				'_wp_trash_meta_comments_status',
			];
			foreach ( $meta_keys as $result ) {
				if ( in_array( $result->meta_key, $remove_wp_default, true ) ) {
					continue;
				}
				$the_metas[] = [
					'value' => $result->meta_key,
					'label' => $result->meta_key,
				];
			}
		}

		return wp_json_encode( $the_metas );
	}
}
