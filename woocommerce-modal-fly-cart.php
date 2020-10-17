<?php
/*
Plugin Name: WooCommerce Modal Fly Cart + Ajax add to cart 
Plugin URI: https://athemeart.com/downloads/woocommerce-popup-cart-ajax/
Description: WooCommerce Modal Fly Cart + Ajax add to cart.
Version: 1.4.3
Author: aThemeArt
Author URI: http://athemeart.com/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tested up to: 5.5.5
WC requires at least: 2.6
WC tested up to: 4.5.5
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

defined( 'WCSPC_PATH' )    or  define( 'WCSPC_PATH',    plugin_dir_path( __FILE__ ) );
defined( 'WCSPC_URL' )     or  define( 'WCSPC_URL',    plugin_dir_url( __FILE__ ) );
defined( 'WCSPC_FILE' )    or  define( 'WCSPC_FILE', plugin_basename( __FILE__ ) );

/**
 * The main plugin class
 */
final class ATA_WC_Smart_Popup_Cart {
	/**
	 * The single instance of the class
	 *
	 * @var ATA_WC_Variation_Swatches
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return ATA_WC_Variation_Swatches
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
	
		$this->includes();
		$this->init_hooks();
	}
	
	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		require_once 'inc/class-admin.php';
		require_once 'inc/class-frontend.php';
	
		require_once 'inc/default.php';
	}

	/**
	 * Initialize hooks
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

	

		if ( is_admin() ) {
			add_action( 'init', array( 'ATA_WC_Smart_Popup_Cart_Admin', 'instance' ) );
		} 
		add_action( 'init', array( 'ATA_WC_Smart_Popup_Cart_Frontend', 'instance' ) );
		
		
	
	}
	

	/**
	 * Load plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'atawc_lang', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}


}


/**
 * Main instance of plugin
 *
 * @return ATA_WC_Variation_Swatches
 */
function ATA_WCFC() {
	return ATA_WC_Smart_Popup_Cart::instance();
}

/**
 * Display notice in case of WooCommerce plugin is not activated
 */
function ata_wc_fly_cart_notice() {
	?>

	<div class="error">
		<p><?php esc_html_e( 'WooCommerce Popup Cart + ajax is enabled but not effective. It requires WooCommerce in order to work.', 'atawc_lang' ); ?></p>
	</div>

	<?php
}

/**
 * Construct plugin when plugins loaded in order to make sure WooCommerce API is fully loaded
 * Check if WooCommerce is not activated then show an admin notice
 * or create the main instance of plugin
 */
function ata_wc_fly_cart_constructor() {
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'ata_wc_fly_cart_notice' );
	} else {
		ATA_WCFC();
	}
}

add_action( 'plugins_loaded', 'ata_wc_fly_cart_constructor' );



class ata_wc_fly_pro {
	
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ), 99 );
		add_filter( 'plugin_action_links', array( $this, 'go_pro' ), 10, 2 );
		
	}

	public function admin_notices() {
		if ( !isset( $_COOKIE['qa-pro-notice'] ) ) {
			echo '<div id="dwqa-message" class="notice is-dismissible"><p>To support this WooCommerce Popup Cart + Ajax and get all features, <a href="https://athemeart.com/downloads/woocommerce-popup-cart-ajax/" target="_blank">upgrade to  WooCommerce Popup Cart + Ajax Pro &rarr;</a></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		}
		
	}
	public function go_pro( $actions, $file ) {
		if ( $file == plugin_basename( __FILE__ )) {
			$actions['eds_go_pro'] = '<a href="https://athemeart.com/downloads/woocommerce-popup-cart-ajax/" style="color: red; font-weight: bold">Go Pro!</a>';
			$action = $actions['eds_go_pro'];
			unset( $actions['eds_go_pro'] );
			array_unshift( $actions, $action );
		}
		return $actions;
	}


}

new ata_wc_fly_pro();



