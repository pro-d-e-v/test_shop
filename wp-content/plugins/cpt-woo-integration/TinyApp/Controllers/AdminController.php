<?php

namespace TinySolutions\cptwooint\Controllers;

// Do not allow directly accessing this file.
use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Traits\SingletonTrait;
use TinySolutions\cptwooint\Controllers\Admin\ProductMetaBoxes;
use TinySolutions\cptwooint\Controllers\Admin\ProductAdminAssets;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
/**
 * AdminController
 */
class AdminController {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 *
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}
	/**
	 * @return void
	 */
	public function admin_init() {
		// Global object containing current admin page.
		global $pagenow;
		$current_post_type = '';
		// If current page is post.php and post isset than query for its post type.
		if ( 'post.php' === $pagenow ) {
			$current_post_type = get_post_type( absint( $_GET['post'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( 'post-new.php' === $pagenow ) {
			$current_post_type = sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! Fns::is_supported( $current_post_type ) ) {
			return;
		}
		ProductMetaBoxes::instance();
		ProductAdminAssets::instance();
	}
}
