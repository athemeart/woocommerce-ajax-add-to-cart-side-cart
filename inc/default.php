<?php
/**
 * Default options.
 *
 * @package WooCommerce Smart Fly CartWCSFC
 */

if ( ! function_exists( 'wcspc_get_default_options' ) ) :

	/**
	 * Get default  options
	 *
	 * @since 1.0.0
	 *
	 * @return array Default  options.
	 */
	function wcspc_get_default_options() {

		$defaults = array();
		
		
		// Slider Section.
		$defaults['_wcspc_auto_show_ajax']				= esc_attr('yes');
		$defaults['_wcspc_auto_show_normal']			= esc_attr('yes');
		$defaults['_wcspc_manual_show']					= '';
		$defaults['_wcspc_position']					= esc_attr('02');
		$defaults['_wcspc_primary_colors']				= esc_attr('#ffffff');
		$defaults['_wcspc_secondary_colors']			= esc_attr('#a7a7a7');
		$defaults['_wcspc_bg_colors']					= esc_attr('#9b5c8f');
		$defaults['_wcspc_bg_image']					= '';
		$defaults['_wcspc_attributes']					= esc_attr('no');
		$defaults['_wcspc_total']						= esc_attr('yes');
		$defaults['_wcspc_buttons']						= esc_attr('01');
		$defaults['_wcspc_continue']					= esc_attr('yes');
		$defaults['_wcspc_reload']						= esc_attr('no');
		
		$defaults['_wcspc_total']						= esc_attr('yes');
		$defaults['_wcspc_buttons']						= esc_attr('01');
		$defaults['_wcspc_continue']					= esc_attr('yes');
		$defaults['_wcspc_reload']						= esc_attr('no');
		
		$defaults['_wcspc_count']						= esc_attr('yes');
		$defaults['_wcspc_count_position']				= esc_attr('bottom-left');
		$defaults['_wcspc_count_icon']					= esc_attr('wcspc-icon-cart1');
		$defaults['_wcspc_count_hide_empty']			= esc_attr('no');
		$defaults['_wcspc_count_hide_checkout']			= esc_attr('no');
		$defaults['_wcspc_style']						= esc_attr('01');

		// Pass through filter.
		$defaults = apply_filters( 'wcspc_get_default_options', $defaults );

		return $defaults;

	}

endif;



if ( ! function_exists( 'wcspc_get_option' ) ) :

	/**
	 * Get theme option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Option key.
	 * @return mixed Option value.
	 */
	function wcspc_get_option( $key ) {

		if ( empty( $key ) ) {
			return;
		}

		$value = '';

		$default = wcspc_get_default_options();
		

		/*if ( is_array( $default ) && count( $default ) > 0 ) {
			$value = get_option( $key, $default );
		}else{
			$value = get_option( $key );
		}*/

		return $default;
	}

endif;