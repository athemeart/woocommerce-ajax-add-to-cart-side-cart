<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Class ATA_WC_Smart_Popup_Cart_Admin
 */
class ATA_WC_Smart_Popup_Cart_Admin {
	
	/**
	* @var striang
	*/
	protected $options;
	/**
	* @var striang
	*/
	protected $sections;	
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
		$this->options = 'wcspcOptions';
		$this->sections = 'wcspcOptions_sections';
			
		add_action( 'admin_menu', array( $this, 'settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		add_action( 'admin_init', array( $this, 'WCSFC_settings_init' )  );
		
		add_action( 'wp_dashboard_setup', array( $this, 'apww_dashboard_widgets' ) );
		
	}
	public function apww_dashboard_widgets() {
		
		global $wp_meta_boxes;
		wp_add_dashboard_widget('athemeart_blog_rss', 'Beginner\'s Guide for WordPress by aThemeArt', array( $this, 'athemeart_blog_rss' ));
		
	}
	
	public function athemeart_blog_rss() {
		
		
		$response = wp_remote_post( 'https://athemeart.com/json.php', array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'cookies' => array()
			)
		);
		
		if ( is_wp_error( $response ) ) {
		   $error_message = $response->get_error_message();
		   echo "Something went wrong: $error_message";
		} else {
		   echo '<div class="rss-widget ata-rss-widgets"><ul>';
		   $data = json_decode( wp_remote_retrieve_body( $response ) );
		   foreach ( $data  as $row ) :
		 
			echo ' <li><a class="rsswidget" href="'.esc_url( $row->url ).'" target="_blank">'.esc_html( $row->title).'</a></li>';
		   endforeach;
		   echo '</ul></div>';
		 
		}
					
			echo '<p class="community-events-footer"><a href="https://athemeart.com/blog-athemeart/" target="_blank">See All  <span class="screen-reader-text">(opens in a new tab)</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>';
	}

	/**
	 * All Admin Style Load.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook == 'woocommerce_page_wcspc' ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'wcspc-fonts', WCSPC_URL . 'assets/css/fonts.css' );
			wp_enqueue_script( 'wcspc-backend', WCSPC_URL . 'assets/js/backend.js', array(
				'jquery',
				'wp-color-picker'
			) );
			wp_localize_script(
				'wcspc-backend',
				'wcspc',
				array(
					'i18n'        => array(
						'mediaTitle'  => esc_html__( 'Choose an image', 'atawc_lang' ),
						'mediaButton' => esc_html__( 'Use image', 'atawc_lang' ),
					),
					'placeholder' => WC()->plugin_url() . '/assets/images/placeholder.png'
				)
			);
		}
	}
	

	/**
	 * settings_page.
	 */
	public function settings_page() {
		
		add_submenu_page( 'woocommerce', 'Modal / Ajax Cart', 'Modal / Ajax Cart', 'manage_options', 'wcspc', array( $this,'WCSFC_options_page' ) );
	}
	
	/**
	 * Options Render.
	 */
	public function WCSFC_options_page(  ) { 
	
	
	?>
    
    <div class="wrap">


<div id="wpcom-stats-meta-box-container" class="metabox-holder">

    <form method="post" action="options.php">
    
    
        
    <div class="postbox-container" style="width:95%;">
    
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div class="postbox" id="ed-global-settings">
                <h3 class="hndle"><span><?php echo esc_html__( 'WooCommerce Modal Fly Cart + Ajax add to cart', 'atawc_lang' ); ?></span></h3>
                <div class="inside ed__settings__loops">
       
					 <?php
                    settings_fields( $this->options );
                    do_settings_sections( $this->options );
                    ?>
                   
                </div>
                <div id="major-publishing-actions">
                    <div id="publishing-action">
                        <?php submit_button();?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
       
    </div>
    
     </form>
    
</div>
</div>
        
	<?php

	}
	public function WCSFC_settings_init(  ) { 
	
		$get_option = wcspc_get_option($this->options);
		
		register_setting( $this->options, $this->options );
	
		add_settings_section(
			$this->sections, 
			'', 
			array( $this,'WCSFC_settings_section_callback' ), 
			$this->options
		);
	
	
		add_settings_field( 
			'_wcspc_auto_show_ajax', 
			__( 'Show up after AJAX add to cart', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_auto_show_ajax',
					'default' => array(
						 'yes' => esc_html__( 'Yes', 'atawc_lang' ),
						 'no' => esc_html__( 'No', 'atawc_lang' ),
					  ),
					 'value' => isset( $get_option['_wcspc_auto_show_ajax'] ) ? $get_option['_wcspc_auto_show_ajax'] : '',
					'description' => __( 'The mini cart will be show up immediately after whenever click to AJAX Add to cart buttons (AJAX enable)? See <a href="'.admin_url( 'admin.php?page=wc-settings&tab=products&section=display' ).'" target="_blank">this setting</a>', 'atawc_lang' ), 
				)
		);
		
		
		
		add_settings_field( 
			'_wcspc_auto_show_normal', 
			__( 'Show up after normal add to cart', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_auto_show_normal',
					'default' => array(
						 'yes' => esc_html__( 'Yes', 'atawc_lang' ),
						 'no' => esc_html__( 'No', 'atawc_lang' ),
					  ),
					  'value' => isset( $get_option['_wcspc_auto_show_normal'] ) ? $get_option['_wcspc_auto_show_normal'] : '',
					'description' => esc_html__( 'The mini cart will be show up immediately after whenever click to normal Add to cart buttons (AJAX is not enable) or Add to cart button in single product page?', 'atawc_lang' ),
				)
		);
		
		add_settings_field( 
			'_wcspc_manual_show', 
			__( 'Manual show up button', 'atawc_lang' ), 
			array( $this,'render_text_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_manual_show',
					'value' => isset( $get_option['_wcspc_manual_show'] ) ? $get_option['_wcspc_manual_show'] : '',
					'description' => __( 'Fill class or id of the button, when click to this button the mini cart
							will be show up.<br/>Example <code>.mini-cart-btn</code> or <code>#mini-cart-btn</code>', 'atawc_lang' ),
				)
		);
		
		
		add_settings_field( 
			'_wcspc_position', 
			__( 'Fly Cart Showing Position', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_position',
					'default' => array(
						'01' => esc_html__( '01 - Page Right', 'atawc_lang' ),
						'02' => esc_html__( '02 - Page Left', 'atawc_lang' ),
						'03' => esc_html__( '03 - Page Top', 'atawc_lang' ),
						'04' => esc_html__( '04 - Page Bottom', 'atawc_lang' ),
						'05' => esc_html__( '05 - Page Center', 'atawc_lang' ),
					  ),
					'value' => $get_option['_wcspc_position'],
				)
		);
		
		
		add_settings_field( 
			'_wcspc_primary_colors', 
			__( 'Primary colors ', 'atawc_lang' ), 
			array( $this,'render_text_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_primary_colors',
					'color_picker'=> true,
					'value' => $get_option['_wcspc_primary_colors'],
				)
		);
		add_settings_field( 
			'_wcspc_secondary_colors', 
			__( 'Secondary color ', 'atawc_lang' ), 
			array( $this,'render_text_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_secondary_colors',
					'color_picker'=> true,
					'value' => $get_option['_wcspc_secondary_colors'],
				)
		);
		add_settings_field( 
			'_wcspc_bg_colors', 
			__( 'Background color ', 'atawc_lang' ), 
			array( $this,'render_text_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_bg_colors',
					'color_picker'=> true,
					'value' => $get_option['_wcspc_bg_colors'],
				)
		);
		
		add_settings_field( 
			'_wcspc_bg_image', 
			__( 'Background image ', 'atawc_lang' ), 
			array( $this,'render_image_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_bg_image',
					'value' => $get_option['_wcspc_bg_image'],
				)
		);
		
		add_settings_field( 
			'_wcspc_attributes', 
			__( 'Show attributes of variation product', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_attributes',
					'default' => array(
						 'yes' => esc_html__( 'Yes', 'atawc_lang' ),
						 'no' => esc_html__( 'No', 'atawc_lang' ),
					  ),
					  'value' => $get_option['_wcspc_attributes'],
					'description' => esc_html__( 'Show attributes of variation product under product title on the list?', 'atawc_lang' ),
				)
		);
		
		add_settings_field( 
			'_wcspc_total', 
			__( 'Show total price', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_total',
					'default' => array(
						 'yes' => esc_html__( 'Yes', 'atawc_lang' ),
						 'no' => esc_html__( 'No', 'atawc_lang' ),
					  ),
					 'value' => $get_option['_wcspc_total'],
				)
		);
		
		add_settings_field( 
			'_wcspc_buttons', 
			__( 'Show action buttons', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_buttons',
					'default' => array(
						 '01' => esc_html__( 'Cart & Checkout', 'atawc_lang' ),
						 '02' => esc_html__( 'Cart only', 'atawc_lang' ),
						 '03' => esc_html__( 'Checkout only', 'atawc_lang' ),
					  ),
					 'value' => $get_option['_wcspc_buttons'],
				)
		);
		
		add_settings_field( 
			'_wcspc_continue', 
			__( 'Show continue shopping', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_continue',
					'default' => array(
						 'yes' => esc_html__( 'Yes', 'atawc_lang' ),
						 'no' => esc_html__( 'No', 'atawc_lang' ),
					  ),
					 'value' => $get_option['_wcspc_continue'],
					'description' => esc_html__( 'Show the continue shopping button at the end of mini-cart?', 'atawc_lang' ),
				)
		);
		
		add_settings_field( 
			'_wcspc_reload', 
			__( 'Reload the cart on page load', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_reload',
					'default' => array(
						 'yes' => esc_html__( 'Yes', 'atawc_lang' ),
						 'no' => esc_html__( 'No', 'atawc_lang' ),
					  ),
					 'value' => $get_option['_wcspc_reload'],
					'description' => esc_html__( 'The cart will be reloaded when opening the page? If you use the cache for your site, please turn on this option.', 'atawc_lang' ),
				)
		);
		
		add_settings_field( 
			'_wcspc_count', 
			__( 'Show cart button', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_count',
					'default' => array(
						 'yes' => esc_html__( 'Yes', 'atawc_lang' ),
						 'no' => esc_html__( 'No', 'atawc_lang' ),
					  ),
					'value' => $get_option['_wcspc_count'],
				)
		);
		
		add_settings_field( 
			'_wcspc_count_position', 
			__( 'Cart button position', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_count_position',
					'default' => array(
						'top-left' => esc_html__( 'Top Left', 'atawc_lang' ),
						'top-right' => esc_html__( 'Top Right', 'atawc_lang' ),
						'bottom-left' => esc_html__( 'Bottom Left', 'atawc_lang' ),
						'bottom-right' => esc_html__( 'Bottom Right', 'atawc_lang' ),
					  ),
					'value' => $get_option['_wcspc_count_position'],
				)
		);
		$icon_list = array();
		for ( $i = 1; $i <= 10; $i ++ ) {
			$icon_list['wcspc-icon-cart'.$i] = 'wcspc-icon-cart'.$i;
		}
		add_settings_field( 
			'_wcspc_count_icon', 
			__( 'Cart button position', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_count_icon',
					'default' => $icon_list,
					'description' => '<span id="wcspc_count_icon_view"></span>',
					'value' => $get_option['_wcspc_count_icon'],
				)
		);
		
		
		add_settings_field( 
			'_wcspc_count_hide_empty', 
			__( 'Button Hide if empty', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_count_hide_empty',
					'default' => array(
						 'yes' => esc_html__( 'Yes', 'atawc_lang' ),
						 'no' => esc_html__( 'No', 'atawc_lang' ),
					  ),
					  'value' => $get_option['_wcspc_count_hide_empty'],
					'description' => esc_html__( 'Hide the cart button if have no product in the cart?', 'atawc_lang' ),
				)
		);
		
		add_settings_field( 
			'_wcspc_count_hide_checkout', 
			__( 'Hide in Cart & Checkout page', 'atawc_lang' ), 
			array( $this,'render_select_field' ), 
			$this->options, 
			$this->sections,
			$args     = array (
					'name' => '_wcspc_count_hide_checkout',
					'default' => array(
						 'yes' => esc_html__( 'Yes', 'atawc_lang' ),
						 'no' => esc_html__( 'No', 'atawc_lang' ),
					  ),
					 'value' => $get_option['_wcspc_count_hide_checkout'],
					'description' => esc_html__( 'Hide the cart button in the Cart & Checkout page?', 'atawc_lang' ),
				)
		);
		
	}
	
	public function render_image_field( array $args )
	{
		
		
		
		$value = ( isset( $args['value'] ) && $args['value'] != "" ) ? $args['value'] : '';
		?>
        <img id="wcspc_image_preview"
        src="<?php echo esc_url( ( empty( $value ) ) ? WC()->plugin_url() . ('/assets/images/placeholder.png' ) :  wp_get_attachment_url( $value ) ); ?>"
        width="80" height="80"
        style="max-height: 80px; width: 80px; border-radius: 4px;">
        
        <input id="wcspc_upload_image_button" type="button" class="button"
        value="<?php echo esc_html__( 'Upload image', 'atawc_lang' ); ?>" disabled="disabled"/>
        
         <input id="wcspc_remove_image_button" type="button" class="button"
        value="<?php echo esc_html__( 'Remove image', 'atawc_lang' ); ?>" disabled="disabled"/>
        
        <input type="hidden"  id="wcspc_image_attachment_url"
        value="<?php echo esc_attr($value); ?>"/>
        <span class="description" style="display:block; color:#F00;"><a href="https://athemeart.com/downloads/woocommerce-popup-cart-ajax/" style="color:#F00;" target="_blank">upgrade pro to unlock</a></span>
	
	<?php
	}
	public function render_text_field( array $args )
	{
		
		$value = ( isset( $args['value'] ) && $args['value'] != "" ) ? $args['value'] : '';
	
		$default = ( isset( $args['default'] ) && $args['default'] != "" ) ? $args['default'] : '';
		
		$color_picker = ( isset( $args['color_picker'] ) && $args['color_picker'] == true ) ? 'wcspc_color_picker' : '';
	?>
    
		<input  type="text" class="<?php echo esc_attr($color_picker);?>" value="<?php echo esc_attr($value);?>" disabled="disabled" />
        
        
        <?php if( isset( $args['description'] ) ) :?>
        <span class="description"><?php echo $args['description'];?></span>
      	<?php endif;?>
		<span class="description" style="display:block; color:#F00;"><a href="https://athemeart.com/downloads/woocommerce-popup-cart-ajax/" style="color:#F00;"  target="_blank">upgrade pro to unlock</a></span>
	
	<?php
	}
	public function render_select_field( array $args )
	{
		
		$default = ( is_array( $args['default'] ) && count( $args['default'] ) > 0  ) ? $args['default'] : array();
		
		$checked = ( isset( $args['value'] ) && $args['value'] != "" ) ? $args['value'] : '';
	?>
		<select disabled="disabled">
        
			<?php if( !empty( $default  ) ) :
			
				foreach ( $default as $key => $val):
			 ?>
             
			<option value='<?php echo esc_attr( $key );?>' <?php selected( $checked, $key ); ?>><?php echo esc_attr( $val );?></option>
            
			<?php endforeach;
			
			 endif;?>
            
			
		</select>
        <?php if( isset( $args['description'] ) ) :?>
        <span class="description"><?php echo $args['description'];?></span>
      	<?php endif;?>
        <span class="description" style="display:block; color:#F00;"><a href="https://athemeart.com/downloads/woocommerce-popup-cart-ajax/" style="color:#F00;"  target="_blank">upgrade pro to unlock</a></span>
      
	<?php
	}
	function WCSFC_settings_section_callback(  ) { 
	
	}

}
