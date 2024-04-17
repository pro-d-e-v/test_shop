<?php
/**
 *
 */

namespace TinySolutions\cptwooint\Traits;

// Do not allow directly accessing this file.
use TinySolutions\cptwooint\Helpers\Fns;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

trait CptProductDataStoreReadTrait {
	/**
	 * Method to read a product from the database.
	 *
	 * @param WC_Product
	 */
	public function read( &$product ) {
		$product->set_defaults();

		if ( ( ! $product->get_id() || ! ( $post_object = get_post( $product->get_id() ) ) || 'product' !== $post_object->post_type ) && ! Fns::is_supported( get_post_type( $product->get_id() ) ) ) {
			throw new Exception( esc_html__( 'Invalid product.', 'cptwooint' ) );
		}

		$id = $product->get_id();

		$product->set_props(
			[
				'name'              => $post_object->post_title,
				'slug'              => $post_object->post_name,
				'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
				'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
				'status'            => $post_object->post_status,
				'description'       => $post_object->post_content,
				'short_description' => $post_object->post_excerpt,
				'parent_id'         => $post_object->post_parent,
				'menu_order'        => $post_object->menu_order,
				'reviews_allowed'   => 'open' === $post_object->comment_status,
			]
		);

		$this->read_attributes( $product );
		$this->read_downloads( $product );
		$this->read_visibility( $product );
		$this->read_product_data( $product );
		$this->read_extra_data( $product );
		$product->set_object_read( true );

		do_action( 'woocommerce_product_read', $id );
	}
}
