<?php
/**
 * WooCommerce Meta Boxes
 *
 * Sets up the write panels used by products and orders (custom post types).
 *
 * @package WooCommerce\Admin\Meta Boxes
 */

namespace TinySolutions\cptwooint\Controllers\Admin;

use WC_Meta_Box_Product_Data;
use WC_Meta_Box_Product_Short_Description;
use TinySolutions\cptwooint\Helpers\Fns;
use TinySolutions\cptwooint\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * WC_Admin_Meta_Boxes.
 */
class ProductMetaBoxes {

	/**
	 * Singleton
	 */
	use SingletonTrait;

	/**
	 * @var string
	 */
	private $is_add_price_meta = false;

	/**
	 * @var string
	 */
	private $show_shortdesc_meta = true;

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
	}

	/**
	 * Add WC Meta boxes.
	 */
	public function add_meta_boxes() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$post_type = $screen->post_type;

		$this->is_add_price_meta = Fns::is_add_cpt_meta( $post_type, 'default_price_meta_field' );
		$this->show_shortdesc_meta = Fns::is_add_cpt_meta( $post_type, 'show_shortdesc_meta' );

		if ( ! Fns::is_supported( $post_type ) ) {
			return;
		}
		wp_enqueue_script('cptwooint-metabox-scripts');
		// Products.
		if( $this->show_shortdesc_meta ){
            add_meta_box( 'postexcerpt', __( 'Product short description', 'woocommerce' ), [$this, 'add_wc_product_short_description' ], $post_type, 'normal' );
        }
        if( $this->is_add_price_meta ){
			add_meta_box( 'woocommerce-product-data', __( 'Product data', 'woocommerce' ), [ $this, 'add_wc_product_data' ], $post_type, 'normal', 'high' );
        }

	}


	/**
	 * @param $post
	 *
	 * @return void
	 */
	public function add_wc_product_data( $post ){
		// Instantiate your custom class.
		// $current_post_type = get_post_type( get_the_ID() );
		// $is_add_price_meta = Fns::is_add_price_meta( $current_post_type );
        $add_class = $this->is_add_price_meta ? 'cptwoo-use-price-field' : 'cptwoo-cant-use-price-field';
		?>
		<div class="cptwooint-product-metabox <?php echo esc_attr( cptwooint()->has_pro() ? 'cptwoo-permitted' : 'cptwoo-pro-disable ' . $add_class ); ?>">
            <?php WC_Meta_Box_Product_Data::output( $post ); ?>
            <?php if( ! cptwooint()->has_pro() ){
	            Fns::pro_message_button( 'Upgrade to the Pro version and unlock other features' );
            } ?>
		</div>

		<?php
	}

	/**
	 * @param $post
	 *
	 * @return void
	 */
	public function add_wc_product_short_description( $post ){
		// Instantiate your custom class.
		?>
        <div class="cptwooint-product-metabox cptwooint-short-desc">
			<?php WC_Meta_Box_Product_Short_Description::output( $post ); ?>
        </div>
		<?php
	}





}


