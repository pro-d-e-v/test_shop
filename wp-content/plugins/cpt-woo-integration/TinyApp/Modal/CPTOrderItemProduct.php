<?php

namespace TinySolutions\cptwooint\Modal;

use TinySolutions\cptwooint\Helpers\Fns;
use WC_Order_Item_Product;

class CPTOrderItemProduct extends WC_Order_Item_Product {
	/**
	 * @var $legacy_values
	 */
	public $legacy_values;
	/**
	 * @var $legacy_cart_item_key
	 */
	public $legacy_cart_item_key;
	/**
	 * Set Product ID
	 *
	 * @param int $value Product ID.
	 */
	public function set_product_id( $value ) {
		$current_post_type = get_post_type( absint( $value ) );
		if ( ! Fns::is_supported( $current_post_type ) ) {
			parent::set_product_id( absint( $value ) );
		} else {
			$this->set_prop( 'product_id', absint( $value ) );
		}
	}


}
