<?php
/**
 * Special Offer.
 *
 * @package RadiusTheme\SB
 */

namespace TinySolutions\cptwooint\Controllers\Notice;

use TinySolutions\cptwooint\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Black Friday Offer.
 */
class AdminNotice {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * Class Constructor.
	 *
	 * @return void
	 */
	private function __construct() {
		FeatureReleaseNotice::instance();
		// WIll Add Later : SpecialDiscount::instance().
		Review::instance();
	}
}
