<?php
/**
 * Main ActionHooks class.
 *
 * @package TinySolutions\cptwooint
 */

namespace TinySolutions\cptwooint\Hooks;

use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit();

/**
 * Main ActionHooks class.
 */
class ActionHooks {
	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	private function __construct() {
		add_action( 'wp_head', [ $this, 'code_header' ] );
		add_action( 'in_admin_header', [ $this, 'remove_all_notices' ], 99 );
		add_action( 'save_post', [ $this, 'update_product_price' ], 15 );
		add_action( 'cptwooint_display_add_to_cart_button', 'woocommerce_template_loop_add_to_cart', 20 );
		add_action( 'cptwooint_display_single_add_to_cart_button', 'woocommerce_template_single_add_to_cart' );

		add_action( 'woocommerce_account_downloads_column_post-type', [ $this, 'account_downloads_column' ], 15 );

		add_action( 'the_post', [ $this, 'wc_setup_product_data' ], 15 );
		add_action( 'pre_get_posts', [ $this, 'wc_setup_loop' ], 5 );
		add_action( 'wp_body_open', [ $this, 'wp_body_open' ], 5 );
		add_action( 'wp_footer', [ $this, 'wp_footer' ], 99 );
	}


	/**
	 * Post Type.
	 *
	 * @param array $download download.
	 * @return void
	 */
	public function account_downloads_column( $download ) {
		$post_type = get_post_type( $download['product_id'] );
		echo esc_html( $post_type );
	}

	/**
	 *
	 * @return void
	 */
	public function wp_body_open() {
		// Check if it's the main query and not in the admin area.
		if ( ! Fns::is_supported( $GLOBALS['wp_query']->get( 'post_type' ) ) ) {
			return;
		}
		?>
		<div class="product">
		<?php
	}

	/**
	 * @return void
	 */
	public function wp_footer() {
		// Check if it's the main query and not in the admin area.
		if ( ! Fns::is_supported( $GLOBALS['wp_query']->get( 'post_type' ) ) ) {
			return;
		}
		?>
		</div>
		<!-- End Product Class -->
		<?php
	}

	/**
	 * @param $query
	 *
	 * @return void
	 */
	public function wc_setup_loop( $query ) {
		// Check if it's the main query and not in the admin area.
		if ( $query->is_main_query() && ! is_admin() && Fns::is_supported( $GLOBALS['wp_query']->get( 'post_type' ) ) ) {
			 $query->set( 'wc_query', 'product_query' );
		}
	}

	/**
	 * When the_post is called, put product data into a global.
	 *
	 * @param mixed $post Post Object.
	 * @return WC_Product
	 */
	public function wc_setup_product_data( $post ) {
		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}
		if ( ! Fns::is_supported( $post->post_type ) ) {
			return;
		}
		$GLOBALS['product'] = wc_get_product( $post );
		return $GLOBALS['product'];
	}

	/**
	 * @return void
	 */
	public function remove_all_notices() {
		$screen = get_current_screen();

		if ( in_array( $screen->base, [ 'toplevel_page_cptwooint-admin', 'wc-integration_page_cptwooint-get-pro', 'wc-integration_page_cptwooint-pricing-pro' ], true ) ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}
	/**
	 * @param $post_id
	 *
	 * @return void
	 */
	public function update_product_price( $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( ! Fns::is_supported( $post_type ) && current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$is_add_price_meta = Fns::is_add_cpt_meta( $post_type, 'default_price_meta_field' );

		if ( cptwooint()->has_pro() && $is_add_price_meta ) {
			return;
		}

		if ( $is_add_price_meta && isset( $_POST['_regular_price'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$regular_price = sanitize_text_field( wp_unslash( $_POST['_regular_price'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} else {
			$regular_price = Fns::cptwoo_get_price( $post_id );
		}

		update_post_meta( $post_id, '_regular_price', $regular_price );

		if ( $is_add_price_meta && isset( $_POST['_sale_price'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$sale_price = sanitize_text_field( wp_unslash( $_POST['_sale_price'] ?? '' ) );  // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} else {
			$sale_price = Fns::cptwoo_get_price( $post_id, 'sale_price' );
		}

		update_post_meta( $post_id, '_sale_price', $sale_price );
	}
	/**
	 * @return void
	 */
	public function code_header() {
		$options = Fns::get_options();
		$style   = $options['style'] ?? [];

		$field_gap             = trim( $style['fieldGap'] ?? '' );
		$field_width           = trim( $style['fieldWidth'] ?? '' );
		$field_height          = trim( $style['fieldHeight'] ?? '' );
		$button_width          = trim( $style['buttonWidth'] ?? '' );
		$button_color          = trim( $style['buttonColor'] ?? '' );
		$button_bg_color       = trim( $style['buttonBgColor'] ?? '' );
		$button_hover_color    = trim( $style['buttonHoverColor'] ?? '' );
		$button_hover_hg_color = trim( $style['buttonHoverBgColor'] ?? '' );

		ob_start();
		if ( ! empty( $field_width ) ) {
			?>
			width: <?php echo absint( $field_width ); ?>px;
			<?php
		}
		if ( ! empty( $field_height ) ) {
			?>
			height: <?php echo absint( $field_height ); ?>px;
			<?php
		}
		$field_style = str_replace( "\r\n", '', trim( ob_get_clean() ) );

		ob_start();
		?>
		<?php if ( ! empty( $button_width ) ) { ?>
			width: <?php echo absint( $button_width ); ?>px;
		<?php } ?>
		<?php if ( ! empty( $field_height ) ) { ?>
			height: <?php echo absint( $field_height ); ?>px;
		<?php } ?>
		<?php if ( ! empty( $button_color ) ) { ?>
			color: <?php echo esc_html( $button_color ); ?>;
			<?php
		}
		if ( ! empty( $button_bg_color ) ) {
			?>
			background-color: <?php echo esc_html( $button_bg_color ); ?>;
			border-color: <?php echo esc_html( $button_bg_color ); ?>;
			<?php
		}
		$button_style = str_replace( "\r\n", '', trim( ob_get_clean() ) );

		ob_start();
		?>
		<?php if ( ! empty( $button_hover_color ) ) { ?>
			color: <?php echo esc_html( $button_hover_color ); ?>;
		<?php } ?>
		<?php if ( ! empty( $button_hover_hg_color ) ) { ?>
			background-color: <?php echo esc_html( $button_hover_hg_color ); ?>;
			border-color: <?php echo esc_html( $button_hover_hg_color ); ?>;
			<?php
		}
		$button_hover_style = str_replace( "\r\n", '', trim( ob_get_clean() ) );
		ob_start();
		?>
		<?php if ( ! empty( $field_gap ) ) { ?>
			.cptwooint-cart-btn-wrapper .cart{
			gap: <?php echo absint( $field_gap ); ?>px;
			}
		<?php } ?>
		
		<?php if ( ! empty( $field_style ) ) { ?>
			.cptwooint-cart-btn-wrapper .cart input[type="number"],
			.cptwooint-cart-btn-wrapper .cart input[type="number"] {
			box-sizing: border-box;
			padding: 7px 15px;
			border: 1px solid;
			<?php echo esc_html( $field_style ); ?>
			}
		<?php } ?>
		
		<?php if ( ! empty( $button_style ) ) { ?>
			.cptwooint-cart-btn-wrapper .cart .button {
			box-sizing: border-box;
			padding: 5px 10px;
			transition: 0.3s all;
			cursor: pointer;
			border: 1px solid;
			<?php echo esc_html( $button_style ); ?>
			}
		<?php } ?>
		
		<?php if ( ! empty( $button_hover_style ) ) { ?>
			.cptwooint-cart-btn-wrapper .cart .button:focus,
			.cptwooint-cart-btn-wrapper .cart .button:hover {
			<?php echo esc_html( $button_hover_style ); ?>
			}
		<?php } ?>
	   
		<?php
		$generated_style = str_replace( "\r\n", '', trim( ob_get_clean() ) );
		if ( ! empty( $generated_style ) ) {
			?>
			<style id="cptwooint-css">
				<?php echo wp_kses_post( $generated_style ); ?>
			</style>
			<?php
		}
	}
}
