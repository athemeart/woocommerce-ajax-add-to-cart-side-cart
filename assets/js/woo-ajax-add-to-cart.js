(function ($) {

  $.fn.serializeArrayAll = function () {
    var rCRLF = /\r?\n/g;
    return this.map(function () {
      return this.elements ? jQuery.makeArray(this.elements) : this;
    }).map(function (i, elem) {
      var val = jQuery(this).val();
      if (val == null) {
        return val == null
        //next 2 lines of code look if it is a checkbox and set the value to blank 
        //if it is unchecked
      } else if (this.type == "checkbox" && this.checked == false) {
        return {name: this.name, value: this.checked ? this.value : ''}
        //next lines are kept from default jQuery implementation and 
        //default to all checkboxes = on
      } else {
        return jQuery.isArray(val) ?
                jQuery.map(val, function (val, i) {
                  return {name: elem.name, value: val.replace(rCRLF, "\r\n")};
                }) :
                {name: elem.name, value: val.replace(rCRLF, "\r\n")};
      }
    }).get();
  };


  $(document).on('click', '.single_add_to_cart_button:not(.disabled)', function (e) {
	e.preventDefault();
	
    var $thisbutton = $(this),
            $form = $thisbutton.closest('form.cart'),
            //quantity = $form.find('input[name=quantity]').val() || 1,
            //product_id = $form.find('input[name=variation_id]').val() || $thisbutton.val(),
            data = $form.find('input:not([name="product_id"]), select, button, textarea').serializeArrayAll() || 0,
			
			variation = {};

		
		if($form.find('input[name=variation_id]').length){
			
			$.each(data, function (i, item) {
				if( item.name.match('attribute') ){
					variation[item.name] = item.value;
				} 
			});
			var data_send = {
				action: 'wcspc_add_variation_to_cart',
				//data:data,
				"product_id" :$form.find('input[name=product_id]').val(),
				"variation_id" :$form.find('input[name=variation_id]').val(),
				"quantity" : $form.find('input[name=quantity]').val(),
				"variation" : variation,
			};
			jQuery.post( wcspcVars.ajaxurl, data_send, function( response ) {
				if (response.error & response.product_url) {
				  window.location = response.product_url;
				  return;
				}
				$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
			} );
			
		}else{
			$.each(data, function (i, item) {
			  if (item.name == 'add-to-cart') {
				item.name = 'product_id';
				item.value = $form.find('input[name=variation_id]').val() || $thisbutton.val();
			  }
			});
			
			$(document.body).trigger('adding_to_cart', [$thisbutton, data]);
			
			  $.ajax({
				  type: 'POST',
				  url: woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
				  data: data,
				  beforeSend: function (response) {
					$thisbutton.removeClass('added').addClass('loading');
				  },
				  complete: function (response) {
					$thisbutton.addClass('added').removeClass('loading');
				  },
				  success: function (response) {
			
					if (response.error & response.product_url) {
					  window.location = response.product_url;
					  return;
					}
			
					$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
				  },
				});

		}
		
    return false;

  });
})(jQuery);