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
class FeatureReleaseNotice extends Discount {

	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * @return array
	 */
	public function the_options(): array {
		return [
			'is_condition'   => ! defined( 'CPTWIP_VERSION' ) || ! version_compare( CPTWIP_VERSION, '1.2.0', '>=' ) ,
			'check_pro'      => false,
			'option_name'    => 'feature_release_notice_v_1_3_1',
			'start_date'     => '10 December 2023',
			'end_date'       => '10 March 2024',
			'notice_for'     => 'New Feature Released!!',
			'notice_message' => '<b>Exciting News:</b> Product review feature has released.',
		];
	}
}
