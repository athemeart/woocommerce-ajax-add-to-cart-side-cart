<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Class ATA_WC_Smart_Popup_Cart_Frontend
 */
class ATA_WC_Smart_Popup_Cart_Frontend {
	
	/**
	* @var striang
	*/
	protected $getOptions;
	
	/**
	 * The single instance of the class
	 *
	 * @var ATA_WC_Variation_Swatches_Admin
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return ATA_WC_Variation_Swatches_Admin
	 */
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	
	/**
	 * Class constructor.
	 */
	public function __construct() {
		
		$this->getOptions = wcspc_get_option( 'wcspcOptions' );
		
		add_action( 'wp_footer', array( $this, 'wcspc_wp_footer' ) );
		
		add_action( 'wp_enqueue_scripts',  array( $this, 'wp_enqueue_scripts' ) );
		
		// Ajax get cart
		add_action( 'wp_ajax_wcspc_get_cart', array( $this, 'wcspc_get_cart' ) );
		add_action( 'wp_ajax_nopriv_wcspc_get_cart', array( $this, 'wcspc_get_cart' ) );
		
		// Ajax remove item
		add_action( 'wp_ajax_wcspc_remove_item', array( $this, 'wcspc_remove_item' ) );
		add_action( 'wp_ajax_nopriv_wcspc_remove_item', array( $this, 'wcspc_remove_item' ) );
		
		
		// Ajax update qty
		add_action( 'wp_ajax_wcspc_update_qty', array( $this, 'wcspc_update_qty' ) );
		add_action( 'wp_ajax_nopriv_wcspc_update_qty', array( $this, 'wcspc_update_qty' ));
		
		
		add_action('wp_head', array( $this, 'hook_css' ));
	}
	
	/**
	 * Class Enqueue Scripts.
	 */
	public function wp_enqueue_scripts(){
			// perfect srollbar
		wp_enqueue_style( 'perfect-scrollbar', WCSPC_URL . 'assets/perfect-scrollbar/css/perfect-scrollbar.min.css' );
		wp_enqueue_style( 'perfect-scrollbar-theme', WCSPC_URL . 'assets/perfect-scrollbar/css/custom-theme.css' );
		wp_enqueue_script( 'perfect-scrollbar', WCSPC_URL . 'assets/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js', array( 'jquery' ), '1.0.0', true );
		// main
		wp_enqueue_style( 'wcspc-fonts', WCSPC_URL . 'assets/css/fonts.css' );
		wp_enqueue_style( 'wcspc-frontend', WCSPC_URL . 'assets/css/frontend.css' );
		
		wp_enqueue_script( 'wcspc-woo-ajax-add', WCSPC_URL . 'assets/js/woo-ajax-add-to-cart.js',array(), '1.0.0', true );
		wp_enqueue_script( 'wcspc-frontend', WCSPC_URL . 'assets/js/frontend.js', array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'wcspc-frontend', 'wcspcVars', array(
				'ajaxurl'             => admin_url( 'admin-ajax.php' ),
				'nonce'               => wp_create_nonce( 'wcspc-security' ),
				'auto_show'           => $this->getOptions['_wcspc_auto_show_ajax'],
				'manual_show'         => $this->getOptions['_wcspc_manual_show'],
				'reload'              => $this->getOptions['_wcspc_reload'],
				'hide_count_empty'    => $this->getOptions['_wcspc_count_hide_empty'],
				'hide_count_checkout' => $this->getOptions['_wcspc_count_hide_checkout'],
			)
		);	
	}
	
	public function wcspc_show_cart() {
		$cart_html = $this->wcspc_get_cart_items( );
	
		return $cart_html;
	}
	/*
		Load the Fly Popup
	*/
	public function wcspc_wp_footer() {
		
		?>
		<div id="wcspc-area"
			 class="wcspc-area wcspc-effect-<?php echo esc_attr( $this->getOptions['_wcspc_position'] ); ?> ">
			<?php echo $this->wcspc_show_cart(); ?>
		</div>
		<?php if ( $this->getOptions['_wcspc_count'] == 'yes' ) {
			$wcspc_count      = WC()->cart->get_cart_contents_count();
			$wcspc_count_hide = '';
			if ( ( $this->getOptions['_wcspc_count_hide_empty'] == 'yes' ) && ( $wcspc_count <= 0 ) ) {
				$wcspc_count_hide = 'wcspc-count-hide';
			}
			if ( ( $this->getOptions['_wcspc_count_hide_checkout'] == 'yes' ) && ( is_cart() || is_checkout() ) ) {
				$wcspc_count_hide = 'wcspc-count-hide';
			}
			?>
			<div id="wcspc-count"
				 class="wcspc-count <?php echo esc_attr( 'wcspc-count-' . $this->getOptions['_wcspc_count_position'] ); ?><?php echo( ( $wcspc_count_hide != '' ) ? ' ' . esc_attr( $wcspc_count_hide ) : '' ); ?>">
				<i class="<?php echo esc_attr( $this->getOptions['_wcspc_count_icon'] ); ?>"></i>
                
				<span id="wcspc-count-number"><?php echo esc_attr( $wcspc_count ); ?></span>
			</div>
		<?php } ?>
		<input type="hidden" id="wcspc-nonce" value="<?php echo wp_create_nonce( 'wcspc-security' ); ?>"/>
		<div class="wcspc-overlay"></div>
		
        
		<?php
		if ( ( $this->getOptions['_wcspc_auto_show_normal'] == 'yes' ) && ( isset( $_POST['add-to-cart'] ) || ( isset( $_GET['add-to-cart'] ) ) ) ) {
			?>
            
			<script type="text/javascript">
				jQuery(document).ready(function () {
					setTimeout(function () {
						wcspc_show_cart();
					}, 1000);
				});
			</script>
			<?php
		}
	}
	
	/*
		Ajax Get Cart
	*/
	public function wcspc_get_cart() {
		if ( ! isset( $_POST['security'] ) || ( ! wp_verify_nonce( $_POST['security'], 'wcspc-security' ) && ( $_POST['security'] != $_POST['nonce'] ) ) ) {
			die( '<div class="wcspc-error">' . esc_html__( 'Permissions check failed!', 'atawc_lang' ) . '</div>' );
		}
		$cart          = array();
		$cart['count'] = WC()->cart->get_cart_contents_count();
		$cart['html']  = $this->wcspc_get_cart_items( );
		echo json_encode( $cart );
		die();
	}
	/*
		Cart Items
	*/
	public function wcspc_get_cart_items(  ) {
	
	$cart_html = '<div class="wcspc-close"><i class="wcspc-icon-close"></i></div>';
	
	$items = WC()->cart->get_cart();
	if ( sizeof( $items ) > 0 ) {
		$items = array_reverse( $items );
		$cart_html .= '<div class="wcspc-area-top wcspc-items">';
		foreach ( $items as $cart_item_key => $cart_item ) {
			$_product          = $cart_item['data'];
			$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
			if ( $_product->is_visible() ) {
				
				
					$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
					$cart_html .= '<div data-key="' . $cart_item_key . '" class="wcspc-item"><div class="wcspc-item-inner">';
					$cart_html .= '<div class="wcspc-item-thumb">';
					if ( ! $product_permalink ) {
						$cart_html .= $thumbnail;
					} else {
						$cart_html .= sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
					}
					$cart_html .= '</div>';
					$cart_html .= '<div class="wcspc-item-info">';
					$cart_html .= '<span class="wcspc-item-title">';
					if ( ! $product_permalink ) {
						$cart_html .= apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;';
					} else {
						$cart_html .= apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key );
					}
					$cart_html .= '</span>';
					if ( (  $this->getOptions['_wcspc_attributes'] == 'yes' ) && $_product->is_type( 'variation' ) && is_array( $cart_item['variation'] ) ) {
						if ( count( $_product->get_variation_attributes() ) > 0 ) {
							$cart_html .= '<span class="wcspc-item-data">';
							foreach ( $_product->get_variation_attributes() as $key => $value ) {
								$cart_html .= '<span class="wcspc-item-data-attr">' . wc_attribute_label( str_replace( 'attribute_', '', $key ), $_product ) . ': ' . $value . '</span>';
							}
							$cart_html .= '</span>';
						}
					}
					$cart_html .= '<span class="wcspc-item-price">' . $_product->get_price_html() . '</span>';
					$cart_html .= '</div>';
					$cart_html .= '<div class="wcspc-item-qty"><div class="wcspc-item-qty-inner"><span class="wcspc-item-qty-plus">+</span><input class="wcspc-item-qty-input" type="number" value="' . $cart_item['quantity'] . '" step="1" min="1" max="' . $_product->get_stock_quantity() . '" data-key="' . $cart_item_key . '"/><span class="wcspc-item-qty-minus">-</span></div></div>';
					$cart_html .= '<span class="wcspc-item-remove wcspc-icon-remove" data-key="' . $cart_item_key . '"></span></div></div>';
				
			}
		}
		$cart_html .= '</div>';
		
		$cart_html .= '<div class="wcspc-area-bot">';
		
		if (  $this->getOptions['_wcspc_total'] == 'yes' ) {
			$cart_html .= '<div class="wcspc-total"><div class="wcspc-total-inner"><div class="wcspc-total-left">' . esc_html__( 'Total', 'atawc_lang' ) . '</div><div id="wcspc-subtotal" class="wcspc-total-right">' . WC()->cart->get_cart_subtotal() . '</div></div></div>';
		}
		
		if ( $this->getOptions['_wcspc_buttons'] == '01' ) {
			
			$cart_html .= '<div class="wcspc-action"><div class="wcspc-action-inner"><div class="wcspc-action-left"><a href="' .esc_url( wc_get_cart_url() ). '">' . esc_html__( 'Cart', 'atawc_lang' ) . '</a></div><div class="wcspc-action-right"><a href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html__( 'Checkout', 'atawc_lang' ) . '</a></div></div></div>';
			
		} else if( $this->getOptions['_wcspc_buttons'] == '02' ) {
			
			$cart_html .= '<div class="wcspc-action"><div class="wcspc-action-inner"><div class="wcspc-action-full"><a href="' .esc_url( wc_get_cart_url() ). '">' . esc_html__( 'Cart', 'atawc_lang' ) . '</a></div></div></div>';
			
		}else{
			$cart_html .= '<div class="wcspc-action"><div class="wcspc-action-inner"><div class="wcspc-action-full"><a href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html__( 'Checkout', 'atawc_lang' ) . '</a></div></div></div>';	
		}
		
		if ( $this->getOptions['_wcspc_continue'] == 'yes' ) {
			$cart_html .= '<div class="wcspc-continue"><span id="wcspc-continue">' . esc_html__( 'Continue Shopping', 'atawc_lang' ) . '</span></div>';
		}
		$cart_html .= '</div>';
	} else {
		$cart_html .= '<div class="wcspc-no-item">' . esc_html__( 'Have no product in the cart!', 'atawc_lang' ) . '</div>';
	}

	return $cart_html;
	}
	
	
	
	public function wcspc_remove_item() {
		if ( ! isset( $_POST['security'] ) || ( ! wp_verify_nonce( $_POST['security'], 'wcspc-security' ) && ( $_POST['security'] != $_POST['nonce'] ) ) ) {
			die( '<div class="wcspc-error">' . esc_html__( 'Permissions check failed!', 'atawc_lang' ) . '</div>' );
		}
		if ( isset( $_POST['cart_item_key'] ) ) {
			WC()->cart->remove_cart_item( $_POST['cart_item_key'] );
			$cart             = array();
			$cart['count']    = WC()->cart->get_cart_contents_count();
			$cart['subtotal'] = WC()->cart->get_cart_subtotal();
			echo json_encode( $cart );
			die();
		}
	}
	
	public function wcspc_update_qty() {
		if ( ! isset( $_POST['security'] ) || ( ! wp_verify_nonce( $_POST['security'], 'wcspc-security' ) && ( $_POST['security'] != $_POST['nonce'] ) ) ) {
			die( '<div class="wcspc-error">' . esc_html__( 'Permissions check failed!', 'atawc_lang' ) . '</div>' );
		}
		if ( isset( $_POST['cart_item_key'] ) && isset( $_POST['cart_item_qty'] ) ) {
			WC()->cart->set_quantity( $_POST['cart_item_key'], intval( $_POST['cart_item_qty'] ) );
			$cart             = array();
			$cart['count']    = WC()->cart->get_cart_contents_count();
			$cart['subtotal'] = WC()->cart->get_cart_subtotal();
			echo json_encode( $cart );
			die();
		}
	}
	
	public function hook_css() {
	?>
	<style type="text/css">
		<?php if( !empty( $this->getOptions['_wcspc_bg_colors'] ) ) : ?>
		.wcspc-count,
		.wcspc-area{
			background: <?php echo esc_attr( $this->getOptions['_wcspc_bg_colors'] );?>
		}
		<?php endif;?>
		<?php if( !empty( $this->getOptions['_wcspc_primary_colors'] ) ) : ?>
		.wcspc-count,
		.wcspc-count i,
		.wcspc-area-top.wcspc-items .wcspc-item-inner .wcspc-item-remove:before,
		.wcspc-area .wcspc-area-bot .wcspc-total .wcspc-total-inner,
		.wcspc-area-bot .wcspc-action .wcspc-action-inner > div a,
		.wcspc-area-bot .wcspc-continue span,
		.wcspc-area .wcspc-close{
			color: <?php echo esc_attr( $this->getOptions['_wcspc_primary_colors'] );?>;
		}
		<?php endif;?>
		<?php if( !empty( $this->getOptions['_wcspc_secondary_colors'] ) ) : ?>
		.wcspc-count span,
		.wcspc-area-bot .wcspc-action .wcspc-action-inner > div a{
			background: <?php echo esc_attr( $this->getOptions['_wcspc_secondary_colors'] );?>;
		}
		<?php endif;?>
		<?php if( !empty( $this->getOptions['_wcspc_primary_colors'] ) ) : ?>
		.wcspc-area-bot .wcspc-action .wcspc-action-inner > div a{
			border:2px solid <?php echo esc_attr( $this->getOptions['_wcspc_secondary_colors'] );?>;
		}
		<?php endif;?>
		<?php if( !empty( $this->getOptions['_wcspc_primary_colors'] ) ) : ?>
		.wcspc-area-bot .wcspc-action .wcspc-action-inner > div a:hover,
		.wcspc-area-top.wcspc-items .wcspc-item-inner .wcspc-item-remove:hover:before,
		.wcspc-area-bot .wcspc-continue span:hover,
		.wcspc-area .wcspc-close:hover{
			color:<?php echo esc_attr( $this->getOptions['_wcspc_secondary_colors'] );?>;
		}
		<?php endif;?>
		
		<?php if( !empty( $this->getOptions['_wcspc_bg_image'] ) ) :
		$img =  ( empty( $this->getOptions['_wcspc_bg_image'] ) ) ? WC()->plugin_url() . ('/assets/images/placeholder.jpg' ) :  wp_get_attachment_url( $this->getOptions['_wcspc_bg_image'] ) ;
		
		 ?>
		.wcspc-area{
			background:url(<?php echo esc_url( $img );?>) repeat center center;
			background-size:cover;
		}
		<?php
		$bg = ( $this->getOptions['_wcspc_bg_colors'] != "" ) ? $this->hex2rgba( $this->getOptions['_wcspc_bg_colors'],'0.97' ) : '157, 94, 145, 0.97';
		?>
		.wcspc-area:after {
			content: '';
			top: 0;
			left: 0;
			z-index: -1;
			position: absolute;
			width: 100%;
			height: 100%;
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
			background: -moz-linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 0%, <?php echo esc_attr( $bg );?> 100%);
			background: -o-linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 0%, <?php echo esc_attr( $bg );?> 100%);
			background: -webkit-linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 0%, <?php echo esc_attr( $bg );?> 100%);
			background: -ms-linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 0%, <?php echo esc_attr( $bg );?> 100%);
			background: linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 0%, <?php echo esc_attr( $bg );?> 100%); 
		}

		<?php endif;?>
	</style>
	<?php
	}
	
	
	public function hex2rgba($color, $opacity = false) {
	 
		$default = 'rgb(0,0,0)';
	 
		//Return default if no color provided
		if(empty($color))
			  return $default; 
	 
		//Sanitize $color if "#" is provided 
			if ($color[0] == '#' ) {
				$color = substr( $color, 1 );
			}
	 
			//Check if color has 6 or 3 characters and get values
			if (strlen($color) == 6) {
					$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
			} elseif ( strlen( $color ) == 3 ) {
					$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
			} else {
					return $default;
			}
	 
			//Convert hexadec to rgb
			$rgb =  array_map('hexdec', $hex);
	 
			//Check if opacity is set(rgba or rgb)
			if($opacity){
				if(abs($opacity) > 1)
					$opacity = 1.0;
				$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
			} else {
				$output = 'rgb('.implode(",",$rgb).')';
			}
	 
			//Return rgb(a) color string
			return $output;
	}

	
}


?>