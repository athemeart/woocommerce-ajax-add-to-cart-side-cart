jQuery( document ).ready( function() {

	// Reload the cart
	if ( wcspcVars.reload == 'yes' ) {
		wcspc_get_cart();
	}

	// Perfect scrollbar
	wcspc_perfect_scrollbar();

	// Qty minus
	jQuery( 'body' ).on( 'click', '#wcspc-area .wcspc-item-qty-minus', function() {
		var qtyMin = 1;
		var step = 1;
		var qtyInput = jQuery( this ).parent().find( '.wcspc-item-qty-input' );
		var qty = Number( qtyInput.val() );
		if ( (
			     qtyInput.attr( 'min' ) != ''
		     ) && (
			     qtyInput.attr( 'min' ) != null
		     ) ) {
			qtyMin = Number( qtyInput.attr( 'min' ) );
		}
		if ( qtyInput.attr( 'step' ) != '' ) {
			step = Number( qtyInput.attr( 'step' ) );
		}
		var qtyStep = qtyMin + step;
		if ( qty >= qtyStep ) {
			qtyInput.val( qty - step );
		}
		qtyInput.trigger( 'change' );
	} );

	// Qty plus
	jQuery( 'body' ).on( 'click', '#wcspc-area .wcspc-item-qty-plus', function() {
		var qtyMax = 100;
		var step = 1;
		var qtyInput = jQuery( this ).parent().find( '.wcspc-item-qty-input' );
		var qty = Number( qtyInput.val() );
		if ( (
			     qtyInput.attr( 'max' ) != ''
		     ) && (
			     qtyInput.attr( 'max' ) != null
		     ) ) {
			qtyMax = Number( qtyInput.attr( 'max' ) );
		}
		if ( qtyInput.attr( 'step' ) != '' ) {
			step = Number( qtyInput.attr( 'step' ) );
		}
		var qtyStep = qty + step;
		if ( qtyMax >= qtyStep ) {
			qtyInput.val( qtyStep );
		}
		qtyInput.trigger( 'change' );
	} );

	// Qty on change
	jQuery( 'body' ).on( 'change', '#wcspc-area .wcspc-item-qty-input', function() {
		var item_key = jQuery( this ).attr( 'data-key' );
		var item_qty = jQuery( this ).val();
		wcspc_update_qty( item_key, item_qty );
	} );

	// Qty validate
	var t = false;
	jQuery( 'body' ).on( 'focus', '#wcspc-area .wcspc-item-qty-input', function() {
		var thisQty = jQuery( this );
		var thisQtyMin = thisQty.attr( 'min' );
		var thisQtyMax = thisQty.attr( 'max' );
		if ( (
			     thisQtyMin == null
		     ) || (
			     thisQtyMin == ''
		     ) ) {
			thisQtyMin = 1;
		}
		if ( (
			     thisQtyMax == null
		     ) || (
			     thisQtyMax == ''
		     ) ) {
			thisQtyMax = 1000;
		}
		t = setInterval(
			function() {
				if ( (
					     thisQty.val() < thisQtyMin
				     ) || (
					     thisQty.val().length == 0
				     ) ) {
					thisQty.val( thisQtyMin )
				}
				if ( thisQty.val() > thisQtyMax ) {
					thisQty.val( thisQtyMax )
				}
			}, 50 )
	} );

	jQuery( 'body' ).on( 'blur', '#wcspc-area .wcspc-item-qty-input', function() {
		if ( t != false ) {
			window.clearInterval( t )
			t = false;
		}
		var item_key = jQuery( this ).attr( 'data-key' );
		var item_qty = jQuery( this ).val();
		wcspc_update_qty( item_key, item_qty );
	} );

	// Remove item
	jQuery( 'body' ).on( 'click', '#wcspc-area .wcspc-item-remove', function() {
		jQuery( this ).closest( '.wcspc-item' ).addClass( 'wcspc-item-removing' );
		var item_key = jQuery( this ).attr( 'data-key' );
		wcspc_remove_item( item_key );
		jQuery( this ).closest( '.wcspc-item' ).slideUp();
	} );

	jQuery( 'body' ).on( 'click tap', '.wcspc-overlay', function() {
		wcspc_hide_cart();
	} );

	jQuery( 'body' ).on( 'click tap', '.wcspc-close', function() {
		wcspc_hide_cart();
	} );

	jQuery( 'body' ).on( 'click tap', '#wcspc-continue', function() {
		wcspc_hide_cart();
	} );

	// Count button
	jQuery( 'body' ).on( 'click', '#wcspc-count,.single_add_to_cart_flyer', function() {
		wcspc_show_cart();
	} );

	// Auto show
	if ( wcspcVars.auto_show == 'yes' ) {
		jQuery( 'body' ).on( 'added_to_cart', function() {
			wcspc_get_cart();
			wcspc_show_cart();
		} );
	} else {
		jQuery( 'body' ).on( 'added_to_cart', function() {
			wcspc_get_cart();
		} );
	}

	// Manual show
	if ( wcspcVars.manual_show != '' ) {
		jQuery( 'body' ).on( 'click', wcspcVars.manual_show, function() {
			wcspc_show_cart();
		} );
	}
} );

function wcspc_update_qty( cart_item_key, cart_item_qty ) {
	jQuery( '#wcspc-count' ).addClass( 'wcspc-count-loading' ).removeClass( 'wcspc-count-shake' );
	var data = {
		action: 'wcspc_update_qty',
		cart_item_key: cart_item_key,
		cart_item_qty: cart_item_qty,
		nonce: jQuery( '#wcspc-nonce' ).val(),
		security: wcspcVars.nonce
	};
	jQuery.post( wcspcVars.ajaxurl, data, function( response ) {
		var cart_response = JSON.parse( response );
		jQuery( '#wcspc-subtotal' ).html( cart_response['subtotal'] );
		jQuery( '#wcspc-count-number' ).html( cart_response['count'] );
		jQuery( '.fly_counter_load' ).html( cart_response['count'] );
		
		jQuery( '#wcspc-count' ).addClass( 'wcspc-count-shake' ).removeClass( 'wcspc-count-loading' );
		if ( (
			     wcspcVars.hide_count_empty == 'yes'
		     ) && (
			     cart_response['count'] == 0
		     ) ) {
			jQuery( '#wcspc-count' ).addClass( 'wcspc-count-hide' );
		} else {
			jQuery( '#wcspc-count' ).removeClass( 'wcspc-count-hide' );
		}
	} );
}

function wcspc_remove_item( cart_item_key ) {

	jQuery( '#wcspc-count' ).addClass( 'wcspc-count-loading' ).removeClass( 'wcspc-count-shake' );
	var data = {
		action: 'wcspc_remove_item',
		cart_item_key: cart_item_key,
		nonce: jQuery( '#wcspc-nonce' ).val(),
		security: wcspcVars.nonce
	};
	jQuery.post( wcspcVars.ajaxurl, data, function( response ) {
	
		var cart_response = JSON.parse( response );
		jQuery( '#wcspc-subtotal' ).html( cart_response['subtotal'] );
		jQuery( '#wcspc-count-number' ).html( cart_response['count'] );
		jQuery( '.fly_counter_load' ).html( cart_response['count'] );
		jQuery( '#wcspc-count' ).addClass( 'wcspc-count-shake' ).removeClass( 'wcspc-count-loading' );
		if ( (
			     wcspcVars.hide_count_empty == 'yes'
		     ) && (
			     cart_response['count'] == 0
		     ) ) {
			jQuery( '#wcspc-count' ).addClass( 'wcspc-count-hide' );
		} else {
			jQuery( '#wcspc-count' ).removeClass( 'wcspc-count-hide' );
		}
	} );
}

function wcspc_get_cart() {
	jQuery( '#wcspc-area' ).addClass( 'wcspc-area-loading' );
	jQuery( '#wcspc-count' ).addClass( 'wcspc-count-loading' ).removeClass( 'wcspc-count-shake' );
	var data = {
		action: 'wcspc_get_cart',
		nonce: jQuery( '#wcspc-nonce' ).val(),
		security: wcspcVars.nonce
	};
	jQuery.post( wcspcVars.ajaxurl, data, function( response ) {
		var cart_response = JSON.parse( response );
		jQuery( '#wcspc-area' ).html( cart_response['html'] );
		jQuery( '.fly_counter_load' ).html( cart_response['count'] );
		wcspc_perfect_scrollbar();
		jQuery( '#wcspc-count-number' ).html( cart_response['count'] );
		jQuery( '#wcspc-area' ).removeClass( 'wcspc-area-loading' );
		jQuery( '#wcspc-count' ).addClass( 'wcspc-count-shake' ).removeClass( 'wcspc-count-loading' );
		if ( (
			     (
				     wcspcVars.hide_count_empty == 'yes'
			     ) && (
				     cart_response['count'] == 0
			     )
		     ) || (
			     (
				     wcspcVars.hide_count_checkout == 'yes'
			     ) && (
				     jQuery( 'body' ).hasClass( 'woocommerce-cart' ) || jQuery( 'body' ).hasClass( 'woocommerce-checkout' )
			     )
		     ) ) {
			jQuery( '#wcspc-count' ).addClass( 'wcspc-count-hide' );
		} else {
			jQuery( '#wcspc-count' ).removeClass( 'wcspc-count-hide' );
		}
	} );
}

function wcspc_perfect_scrollbar() {
	jQuery( '#wcspc-area .wcspc-area-top' ).perfectScrollbar( {suppressScrollX: true, theme: 'wcspc'} );
}

function wcspc_show_cart() {
	jQuery( 'body' ).addClass( 'wcspc-body-show' );
	jQuery( '#wcspc-area' ).addClass( 'wcspc-area-show' );
}

function wcspc_hide_cart() {
	jQuery( '#wcspc-area' ).removeClass( 'wcspc-area-show' );
	jQuery( 'body' ).removeClass( 'wcspc-body-show' );
}