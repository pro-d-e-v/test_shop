<?php
/**
 * Special Offer.
 *
 * @package RadiusTheme\SB
 */

namespace TinySolutions\cptwooint\Controllers\Notice;

use TinySolutions\cptwooint\Traits\SingletonTrait;
use TinySolutions\cptwooint\Abs\Discount;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Black Friday Offer.
 */
class SpecialDiscount extends Discount {

	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * @return array
	 */
	public function the_options(): array {
		return [
			'option_name'    => 'cptwooint_special_offer_2023',
			'start_date'     => '10 December 2023',
			'end_date'       => '10 January 2024',
			'notice_for'     => 'New Feature Released!!',
			'notice_message' => "<b>Exciting News:</b> Product Type - <b> Variable product/Grouped Product </b> feature released, Prior to returning to our regular pricing plan, here's a coupon for you.</b> with <b>UP TO 20% OFF</b>! Limited time offer!!",
		];
	}
}
