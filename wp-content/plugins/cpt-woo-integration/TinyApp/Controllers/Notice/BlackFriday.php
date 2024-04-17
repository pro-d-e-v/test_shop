<?php
/**
 * Black Friday Offer.
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
class BlackFriday {

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
		add_action(
			'admin_init',
			function () {
				$current = time();
				$start   = strtotime( '19 November 2023' );
				$end     = strtotime( '10 December 2023' );
				// Black Friday Notice.
				if ( ! cptwooint()->has_pro() && $start <= $current && $current <= $end ) {
					if ( get_option( 'cptwooint_bf_2023' ) != '1' ) {
						if ( ! isset( $GLOBALS['cptwooint__notice'] ) ) {
							$GLOBALS['cptwooint__notice'] = 'cptwooint_bf_2023';
							self::black_friday_notice();
						}
					}
				}
			}
		);
	}

	/**
	 * Black Friday Notice.
	 *
	 * @return void
	 */
	public static function black_friday_notice() {
		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_script( 'jquery' );
			}
		);

		add_action(
			'admin_notices',
			function () {
				?>
				<style>
					.cptwooint-bf-notice {
						--e-button-context-color: #2179c0;
						--e-button-context-color-dark: #2271b1;
						--e-button-context-tint: rgb(75 47 157/4%);
						--e-focus-color: rgb(75 47 157/40%);
						display:grid;
						grid-template-columns: 100px auto;
						padding-top: 25px;
						padding-bottom: 22px;
						column-gap: 15px;
					}

					.cptwooint-bf-notice img {
						grid-row: 1 / 4;
						align-self: center;
						justify-self: center;
					}

					.cptwooint-bf-notice h3,
					.cptwooint-bf-notice p {
						margin: 0 !important;
					}

					.cptwooint-bf-notice .notice-text {
						margin: 0 0 2px;
						padding: 5px 0;
						max-width: 100%;
						font-size: 14px;
					}

					.cptwooint-bf-notice .button-primary,
					.cptwooint-bf-notice .button-dismiss {
						display: inline-block;
						border: 0;
						border-radius: 3px;
						background: var(--e-button-context-color-dark);
						color: #fff;
						vertical-align: middle;
						text-align: center;
						text-decoration: none;
						white-space: nowrap;
						margin-right: 5px;
						transition: all 0.3s;
					}

					.cptwooint-bf-notice .button-primary:hover,
					.cptwooint-bf-notice .button-dismiss:hover {
						background: var(--e-button-context-color);
						border-color: var(--e-button-context-color);
						color: #fff;
					}

					.cptwooint-bf-notice .button-primary:focus,
					.cptwooint-bf-notice .button-dismiss:focus {
						box-shadow: 0 0 0 1px #fff, 0 0 0 3px var(--e-button-context-color);
						background: var(--e-button-context-color);
						color: #fff;
					}

					.cptwooint-bf-notice .button-dismiss {
						border: 1px solid;
						background: 0 0;
						color: var(--e-button-context-color);
						background: #fff;
					}
				</style>
				<?php
				$plugin_name   = 'Custom Post Type Woocommerce Integration Pro';
				$download_link = 'https://www.wptinysolutions.com/tiny-products/cpt-woo-integration/';
				?>
				<div class="cptwooint-bf-notice notice notice-info is-dismissible" data-cptwoointdismissable="cptwooint_bf_2023">
					<img alt="<?php echo esc_attr( $plugin_name ); ?>"
						 src="<?php echo esc_url( cptwooint()->get_assets_uri( 'images/cpt-woo-icon-150x150.png' ) ); ?>" width="100px"
						 height="100px" />
<!--					<h3>--><?php // echo sprintf( '%s – Black Friday Deal!!', esc_html( $plugin_name ) ); ?><!--</h3>-->
						<h3><?php echo sprintf( '%s – Cyber Monday Deal!!', esc_html( $plugin_name ) ); ?></h3>

					<p class="notice-text">
						<?php echo esc_html__( "Don't miss out on our biggest sale of the year! Get your", 'cptwooint' ); ?>
						<b><?php echo esc_html( $plugin_name ); ?> plan</b> with <b>UP TO 40% OFF</b>! Limited time
						offer!!
					</p>

					<p>
						<a class="button button-primary" href="<?php echo esc_url( $download_link ); ?>" target="_blank">Buy
							Now</a>
						<a class="button button-dismiss" href="#">Dismiss</a>
					</p>
				</div>
				<?php
			}
		);

		add_action(
			'admin_footer',
			function () {
				?>
				<script type="text/javascript">
					(function ($) {
						$(function () {
							setTimeout(function () {
								$('div[data-cptwoointdismissable] .notice-dismiss, div[data-cptwoointdismissable] .button-dismiss')
									.on('click', function (e) {
										e.preventDefault();
										$.post(ajaxurl, {
											'action': 'cptwooint_dismiss_bf_admin_notice',
											'nonce': <?php echo wp_json_encode( wp_create_nonce( 'cptwooint-bf-dismissible-notice' ) ); ?>
										});
										$(e.target).closest('.is-dismissible').remove();
									});
							}, 1000);
						});
					})(jQuery);
				</script>
				<?php
			}
		);

		add_action(
			'wp_ajax_cptwooint_dismiss_bf_admin_notice',
			function () {
				check_ajax_referer( 'cptwooint-bf-dismissible-notice', 'nonce' );

				update_option( 'cptwooint_bf_2023', '1' );
				wp_die();
			}
		);
	}
}
