"use strict";

jQuery( document ).ready( function( $ ) {

	// Confirm Voucher code Redeem Reverse
	$( document ).on( 'click', '#woo_vou_reverse_redeem', function() {

		if ( confirm( WooVouRrCheck.reverse_redeem_confirm ) == true ) {

			//Voucher Code
			var voucode = $( '#woo_vou_valid_voucher_code' ).val();

			//hide submit row
			$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();

			//show loader
			$( '.woo-vou-check-voucher-code-loader' ).css( 'display', 'inline' );

			//hide error message
			$( '.woo-vou-voucher-code-msg' ).hide();
			$( '.woo-vou-voucher-reverse-redeem' ).html( "" ).hide();

			var data = {
							action	: 'woo_vou_rr_process',
							voucode	: voucode,
							ajax	: true,
						};

			//call ajax to save voucher code
			jQuery.post( WooVouRrCheck.ajaxurl, data, function( response ) {

				var response_data = jQuery.parseJSON(response);

				if( response_data.success ) {

					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-error' ).addClass( 'woo-vou-voucher-code-success' ).html( response_data.success_message ).show();
					
				} else {

					//Voucher Code
					$( '#woo_vou_voucher_code' ).val( '' );
					$( '#woo_vou_valid_voucher_code' ).val( '' );
					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( response_data.error_message ).show();
				}
				//hide loader
				$( '.woo-vou-check-voucher-code-loader' ).hide();
			});

	    } else {
	        return false;
	    }
	});

	// Confirm Voucher code Redeem Post Reverse
	$( document ).on( 'click', '.woo_vou_reverse_redeem_post', function() {

		if ( confirm( WooVouRrCheck.reverse_redeem_confirm ) == true ) {

			//Voucher partial id
			var partial_id = $( this ).data("partial_id");

			//hide submit row
			$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();

			//show loader
			$( '.woo-vou-check-voucher-code-loader' ).css( 'display', 'inline' );

			//hide error message
			$( '.woo-vou-voucher-code-msg' ).hide();
			$( '.woo-vou-voucher-reverse-redeem' ).html( "" ).hide();

			var data = {
							action		: 'woo_vou_rr_reverse_partial_post_process',
							partial_id 	: partial_id,
							ajax	 	: true,
						};

			//call ajax to save voucher code
			jQuery.post( WooVouCheck.ajaxurl, data, function( response ) {

				var response_data = jQuery.parseJSON(response);

				if( response_data.success ) {

					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-error' ).addClass( 'woo-vou-voucher-code-success' ).html( response_data.success_message ).show();

				} else {

					//Voucher Code
					$( '#woo_vou_voucher_code' ).val( '' );
					$( '#woo_vou_valid_voucher_code' ).val( '' );
					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( response_data.error_message ).show();
				}
				//hide loader
				$( '.woo-vou-check-voucher-code-loader' ).hide();

			});

	    } else {
	        return false;
	    }
	});
});