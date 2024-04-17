<?php

namespace TinySolutions\cptwooint\Controllers\Admin;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Traits\SingletonTrait;

/**
 * Sub menu class
 *
 * @author Mostafa <mostafa.soufi@hotmail.com>
 */
class AdminMenu {

	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Parent Menu Page Slug
	 */
	const MENU_PAGE_SLUG = 'cptwooint-admin';

	/**
	 * Menu capability
	 */
	const MENU_CAPABILITY = 'manage_options';

	/**
	 * Autoload method
	 *
	 * @return void
	 */
	private function __construct() {
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
	}

	/**
	 * Register submenu
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		wp_enqueue_style( 'cptwooint-settings' );
		add_menu_page(
			esc_html__( 'WC Integration', 'cptwooint' ),
			esc_html__( 'WC Integration', 'cptwooint' ),
			self::MENU_CAPABILITY,
			'cptwooint-admin',
			[ $this, 'page_callback' ],
			'dashicons-cart',
			'55.6'
		);

		$tab_title = apply_filters( 'cptwooint/add/get-pro/submenu/label', esc_html__( 'Buy Pro', 'cptwooint' ) );

		$title = '<span class="cptwooint-submenu" style="color: #6BBE66;"> <span class="dashicons-icons" style="transform: rotateX(180deg) rotate(180deg);font-size: 18px;"></span> ' . $tab_title . '</span>';

		add_submenu_page(
			self::MENU_PAGE_SLUG,
			$tab_title,
			$title,
			self::MENU_CAPABILITY,
			'cptwooint-get-pro',
			[ $this, 'pro_pages' ]
		);

		do_action( 'cptwooint/add/more/submenu', self::MENU_PAGE_SLUG, self::MENU_CAPABILITY );
	}

	/**
	 * Render submenu
	 *
	 * @return void
	 */
	public function page_callback() {
		echo '<div class="wrap"><div id="cptwooint_root"></div></div>';
	}

	/**
	 * @return void
	 */
	public function pro_pages() {
		?>
		<div class="wrap cptwooint-license-wrap">
			<?php
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_style( 'freemius-pricing', 'https://wcss.freemius.com/wordpress/pages/pricing.css?v=180' );
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_style( 'freemius-css', 'https://wcss.freemius.com/wordpress/common.css?v=180' );
			?>
			<style>
				.current .cptwooint-submenu,
				.current .dashicons{
					color: #1677ff !important;
				}
				.media_page_cptwooint-get-pro #wpwrap {
					background: #f9faff;
				}

				#cptwooint-pro-page-wrapper{
					display: flex;
					gap: 15px;
					justify-content: center;
					flex-wrap: wrap;
				}
				.cptwooint-pro-page-wrapper #header .product-icon,
				.cptwooint-pro-page-wrapper #header .product-icon img{
					width: 150px;
					height: 150px;
				}

				.cptwooint-pro-page-wrapper #header .product-header {
					position: relative;
					display: flex;
				}

				.cptwooint-pro-page-wrapper #header .product-header-body {
					padding: 32px 0;
					max-width: calc( 100% - 120px );
					width: 100%;
				}

				.cptwooint-pro-page-header #header {
					width: 915px;
					margin-left: auto;
					margin-right: auto;
					position: relative;
					border: 0;
				}

				#cptwooint-pro-page-wrapper .columns {
					width: 450px;
					background: #fff;
					border-radius: 8px;
				}
				#cptwooint-pro-page-wrapper .price li.footer * {
					flex: 1;
				}
				#cptwooint-pro-page-wrapper .price {
					margin: 0;
					padding: 0;
					padding-bottom: 20px;
				}
				#cptwooint-pro-page-wrapper .price .header {
					padding: 25px 15px;
					font-size: 30px;
					display: block;
					font-weight: 700;
					background: #1677ff;
					color: #fff;
					line-height: 1.4;
					margin-bottom: 35px;
				}
				#cptwooint-pro-page-wrapper .price .header span {
					color: #fff;
					font-size: 20px;
				}
				#cptwooint-pro-page-wrapper .price  li.footer {
					margin-top: 20px;
					margin-bottom: 10px;
				}
				#cptwooint-pro-page-wrapper .price li {
					padding: 10px 30px;
					display: flex;
					gap: 10px;
					font-size: 16px;
					line-height: 1.4;
				}

				#cptwooint-pro-page-wrapper .price li span{
					color: #1677ff;
				}

				#cptwooint-pro-page-wrapper .price li a:hover span,
				#cptwooint-pro-page-wrapper .price li a:hover{
					text-decoration: none;
					color: #FE0467 !important;
				}

				#cptwooint-pro-page-wrapper #purchase {
					color: #fff;
					background-color: #1677ff;
					box-shadow: 0 2px 0 rgba(5, 145, 255, 0.1);
					font-size: 16px;
					height: 40px;
					padding: 6.428571428571429px 15px;
					border-radius: 8px;
					cursor: pointer;
					border: 0;
					line-height: 1;
					min-width: 100px;
				}
				#cptwooint-pro-page-wrapper #purchase:hover{
					background-color: #FE0467;
				}
				#cptwooint-pro-page-wrapper #licenses ,
				#cptwooint-pro-page-wrapper #billing_cycle {
					padding: 5px 25px 5px 15px;
					border-radius: 8px;
					border-color: #1677ff;
					height: 40px;
					font-weight: 400;
				}
				#cptwooint-pro-page-wrapper .price .header .price-for {
					display: none;
				}

				#cptwooint-pro-page-wrapper .price .header .price-for.active-plan{
					display: flex;
					flex-direction: column;
					gap: 5px;
				}

				#cptwooint-pro-page-wrapper .price .header .price-for > span{
					display: none;
				}

				#cptwooint-pro-page-wrapper .price .header .price-for.active-plan .active-cycle{
					display: flex;
				}

				.cptwooint-pro-page-footer div#faq {
					background: #fff;
					border-radius: 8px;
				}
				.cptwooint-pro-page-footer {
					margin-top: 30px;
				}
				.payment-cycle{
					border: 1px solid #6bc406;
					padding: 10px 15px;
					margin-bottom: 10px;
					color: #6bc406;
				}
				@media only screen and (max-width: 600px) {
					#cptwooint-pro-page-wrapper .columns {
						width: 100%;
					}
				}
			</style>

			<div class="cptwooint-pro-page-wrapper" >
				<div class="cptwooint-pro-page-header" >
					<header id="header" class="card clearfix" >
						<div class="product-header">
							<div class="product-icon">
								<img src="https://www.wptinysolutions.com/wp-content/uploads/2023/08/cpt-woo-icon-128x128-1.png" alt="">
							</div>
							<div class="product-header-body" style="">
								<h1 class="page-title">Plans and Pricing</h1>
								<h2 class="plugin-title">Custom Post Type Woocommerce Integration Pro</h2>
								<h3>Choose your plan and upgrade in minutes!</h3>
							</div>
						</div>
					</header>
				</div>
				<div id="cptwooint-pro-page-wrapper" >
					<div class="columns">
						<ul class="price">
							<li class="header">
								PRO
								<div style=""></div>
							</li>

							<?php foreach ( Fns::pro_feature_list() as $item ) { ?>
								<li class="item"> <span class="dashicons dashicons-yes-alt"></span>
									<?php echo esc_attr( $item['title'] ); ?>
								</li>
						   <?php } ?>
							<li class="footer-text" >
								<div class="footer text">
									<a style="margin-top:30px;color: #1677ff;display: flex;align-items: center;gap: 5px;font-weight: 600;" target="_blank" href="https://www.wptinysolutions.com/tiny-products/cpt-woo-integration//"> Buy Now <span class="dashicons dashicons-arrow-right-alt"></span></a>
								</div>

							</li>
						</ul>

					</div>
					<div  class="columns" >
						<section id="money_back_guarantee" style="margin: 0;height: 100%;box-sizing: border-box;padding-top:50px;">

							<img style="max-width: 100%;" src="<?php echo esc_url( cptwooint()->get_assets_uri( 'images/pngtree-gold-premium-quality-100-money-back-guaranteed-2.jpg' ) ); ?>" alt="">
							<h1 style="font-size: 20px;">
								<b class="stars">
									<i class="last">⋆</i>
									<i class="middle">⋆</i>
									<i class="first">⋆</i>
								</b>
								<span>30-Days Money Back Guarantee</span>
								<b class="stars">
									<i class="first">⋆</i>
									<i class="middle">⋆</i>
									<i class="last">⋆</i>
								</b>
							</h1>
							<p>
								You are fully protected by our 100% Money Back Guarantee. If during the next 30 days you experience an issue that makes the plugin unusable and we are unable to resolve it, we'll happily consider offering a full refund of your money.<span style="color: #6bc406;"> Please note that if you change your mind without any reason and want to seek a refund, it will not be processed in accordance with our policy.</span>
							</p>
							<div class="active-cycle-wrapper" style="margin-top:50px;">
								<p class="payment-cycle">
									A yearly licence entitles you to 1 year of updates and support. Your subscription will auto-renew each year until cancelled.
								</p>
								<p class="payment-cycle">
									A lifetime licence entitles you to updates and support forever. It is a one-time payment, not a subscription.
								</p>
							</div>
						</section>
					</div>
				</div>

				<div class="cptwooint-pro-page-footer" >
					<div class="container" style="max-width: 915px;margin-bottom: 20px;font-size: 20px;margin: 50px auto;line-height: 1.5;">
						<span style="color: #6bc406;">Are you enjoying the free version? Have you got some valuable feedback to share? Have you encountered a bug and found a solution? If so, we might have a special <span style="color: red; font-weight: bold;"> discount </span> waiting for you!</span>
						Contact us via email to receive assistance and learn more about our current promotions. <a style="color: #1677ff;font-weight: 600;" target="_blank" href="mailto:support@tinysolutions.freshdesk.com"><strong> support@tinysolutions.freshdesk.com </strong></a>
					</div>

					<div class="container" style="max-width: 915px;">

						<div id="faq" style="max-width: 915px;margin: 0;" >
							<h2 style="margin-bottom: 30px;margin-top: 10px; line-height: 1.2;">Frequently Asked Questions</h2>
							<ul>
								<li>
									<h3>Is there a setup fee?</h3>
									<p>No. There are no setup fees on any of our plans.</p>
								</li>
								<li>
									<h3>Can I cancel my account at any time?</h3>
									<p>Yes, if you ever decide that Custom Post Type Woocommerce Integration Pro isn't the best plugin for your business, simply cancel your account from your Account panel.</p>
								</li>
								<li>
									<h3>What's the time span for your contracts?</h3>
									<p>All plans are year-to-year unless you purchase a lifetime plan.</p>
								</li>
								<li>
									<h3>Do you offer a renewals discount?</h3>
									<p>Yes, you get 10% discount for all annual plan automatic renewals. The renewal price will never be increased so long as the subscription is not cancelled.</p>
								</li>
								<li>
									<h3>What payment methods are accepted?</h3>
									<p>We accept all major credit cards including Visa, Mastercard, American Express, as well as PayPal payments.</p>
								</li>
								<li>
									<h3>Do you offer refunds?</h3>
									<p>Yes we do! We stand behind the quality of our product and will refund 100% of your money if you experience an issue that makes the plugin unusable and we are unable to resolve it.</p>
								</li>
								<li>
									<h3>Do you have any restrictions on refunds?</h3>
									<p style="color: #6bc406;"> Please note that if you change your mind without any reason and want to seek a refund, it will not be processed in accordance with our policy.</span>
									</p>
								</li>

								<li>
									<h3>Do I get updates for the premium plugin?</h3>
									<p>Yes! Automatic updates to our premium plugin are available free of charge as long as you stay our paying customer.</p>
								</li>
								<li>
									<h3>Do you offer support if I need help?</h3>
									<p>Yes! Top-notch customer support is key for a quality product, so we'll do our very best to resolve any issues you encounter via our support page.</p>
								</li>
								<li>
									<h3>I have other pre-sale questions, can you help?</h3>
									<p>Yes! You can ask us any question through our <a class="contact-link" data-subject="pre_sale_question" href="mailto:support@tinysolutions.freshdesk.com">support@tinysolutions.freshdesk.com</a>.</p>
								</li>
							</ul>

						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
