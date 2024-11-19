"use strict";

$(function(){
	// Confirm Voucher code Redeem Reverse
    $( document ).on( 'click', '.woo_vou_reverse_redeem_post', function() {

        if ( confirm( WooVou_reverse_redeem_confirm ) == true ) {

            //Voucher partial id
            var partial_id = $( this ).data("partial_id");

            var data = {
                            action      : 'woo_vou_rr_reverse_partial_post_process',
                            partial_id  : partial_id,
                            ajax        : true,
                        };

            //call ajax to save voucher code
            jQuery.post( WooVou_Ajaxurl, data, function( response ) {

                var response_data = jQuery.parseJSON(response);

                if( response_data.success ) {

                    var response_message = '<tr><td><div class="woo-vou-voucher-code-msg success">'+response_data.success_message+'</div></td></tr>';
                    $(".woo-vou-check-vou-code-form table.woo-vou-check-code").html(response_message);

                    setTimeout(function(){
                        location.reload(true);
                    }, 3000);

                } else {

                    var response_message = '<tr><td><div class="woo-vou-voucher-code-msg error">'+response_data.error_message+'</div></td></tr>';
                    $(".woo-vou-check-vou-code-form table.woo-vou-check-code").html(response_message);

                    setTimeout(function(){
                        location.reload(true);
                    }, 3000);

                }

            });
            

            return true;
        } else {
            return false;
        }
    });

    // Confirm Voucher code Redeem Reverse
    $( document ).on( 'click', '#woo_vou_reverse_redeem', function() {

        if ( confirm( WooVou_reverse_redeem_confirm ) == true ) {

            return true;
        } else {
            return false;
        }
    });
});