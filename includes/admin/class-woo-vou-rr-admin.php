<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 *
 * Handles generic Admin functionality and AJAX requests.
 *
 * @package WooCommerce PDF Vouchers - Reverse Redemption
 * @since 1.0.0
 */
class WOO_Vou_Rr_Admin {
	
	/**
	 * Add Reverse Redeem settings
	 * 
	 * Handle to add more reverse redeem settings
	 *
	 * @package WooCommerce PDF Vouchers - Reverse Redemption
	 * @since 1.0.0
	 */
	public function woo_vou_rr_settings( $settings, $current_section ) {
        
        $rr_settings = array();

        if( $current_section == 'vou_extensions_settings' ){

            global $wp_roles;

            $reverse_redeem_options = array();

            if( !empty( $wp_roles->role_names ) ) {

                foreach ($wp_roles->role_names as $role_key => $role_name) {
                    $reverse_redeem_options[$role_key] = $role_name;
                }
            }

            $rr_settings = apply_filters( 'woo_vou_rr_general_settings', array(
                    array(
                        'title' => esc_html__( 'Reverse Redemption Settings', 'woovoucherrr' ),
                        'type'  => 'title',
                        'id'    => 'vou_reverse_redeem_settings'
                    ),
                    // Reverse Redeem Settings
                    array(
                        'id'        => 'vou_allow_reverse_redeem_role',
                        'name'      => esc_html__( 'Allow Reverse Redeem', 'woovoucherrr' ),
                        'desc'      => '<p class="description">'.esc_html__( 'Select the user roles that can reverse the redeemed vouchers.', 'woovoucherrr' ).'</p>',
                        'type'      => 'multiselect',
                        'class'     => 'wc-enhanced-select',
                        'options'   => $reverse_redeem_options
                    ),
                    array(
                        'type'  => 'sectionend',
                        'id'    => 'vou_reverse_redeem_settings'
                    ),
                )
            );
        }


        if( empty( $settings ) || ( !empty( $settings ) && isset( $settings[0]['id'] ) && $settings[0]['id'] != 'vou_general_settings' ) ) {
            $settings = array_merge( $settings, $rr_settings);            
        } elseif( !empty( $rr_settings ) ){
            $settings = $rr_settings;
        }

		return $settings;
	}


    /**
     * Action hook function
     * 
     * Handle to add extenstion setting tab
     *
     * @package WooCommerce PDF Vouchers - Reverse Redemption
     * @since 1.0.0
     */
    public function woo_vou_rr_check_extenstion_setting( $sections ){
        if( !isset( $sections['vou_extensions_settings'] ) ){
            $sections['vou_extensions_settings'] =  esc_html__( 'Extensions Settings', 'woovoucherrr' );
        }

        return $sections;
    }


	/**
     * Get Reverse Redeem Button
     * 
     * Filter function to add Reverse redeem column to the check voucher code page
     * 
     * @package WooCommerce PDF Vouchers - Reverse Redemption
     * @since 1.0.0
     */
	public function woo_vou_rr_add_column( $columns ){

		global $current_user;

		$user_roles		= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role		= array_shift( $user_roles );

		$reverse_redeem_role = get_option('vou_allow_reverse_redeem_role');

		if( !empty($user_role) && is_array($reverse_redeem_role) && in_array($user_role, $reverse_redeem_role) ){

			$columns['reverse_redeem'] = esc_html__( 'Reverse Redeem', 'woovoucherrr' );
		}

		return $columns;

	}
	
	/**
     * Get Reverse Redeem Button
     * 
     * Filter function to add Reverse redeem column value to the check voucher code page
     * 
     * @package WooCommerce PDF Vouchers - Reverse Redemption
     * @since 1.0.0
     */
	public function woo_vou_rr_add_column_value( $column_value, $col_key, $voucodeid, $item_id, $order_id, $redeemed_info) {

		if( $col_key == 'reverse_redeem' ){

			$redeem_id = (!empty($redeemed_info['redeem_id'])) ? $redeemed_info['redeem_id'] : '';

			$reverse_button = $this->woo_vou_rr_display_button( array(), $voucodeid,$redeem_id);

			if( isset( $reverse_button['reverse_redeem'] ) && !empty( $reverse_button['reverse_redeem'] ) ){
			 	$column_value = '<div class="woo_pdf_res_vou">' . esc_html__( 'Reverse Redeem', 'woovoucherrr' ) . '</div>';
			 	$column_value .= $reverse_button['reverse_redeem'];
			 }
		}

		return $column_value;
	}

	/**
     * Get Reverse Redeem Button
     * 
     * Handles to get reverse redeem button on check voucher page
     * 
     * @package WooCommerce PDF Vouchers - Reverse Redemption
     * @since 1.0.0
     */
	public function woo_vou_rr_display_button( $response, $voucodeid, $partial_id='' ){

		// Get global variables
        global $current_user, $woo_vou_model;

        // Declare prefix variable
        $prefix = WOO_VOU_META_PREFIX;
        $html = $vou_code_status = '';

        $user_roles = !empty($current_user->roles) ? $current_user->roles : '';
        $user_role = !empty($user_roles) ? $user_roles[0] : '';
        $reverse_redeem_role = get_option('vou_allow_reverse_redeem_role');
        $redeem_method = get_post_meta( $voucodeid, $prefix.'redeem_method', true );
        $used_codes = get_post_meta( $voucodeid, $prefix.'used_codes', true );

        // get voucher expired date
        $expiry_date = get_post_meta($voucodeid, $prefix . 'exp_date', true);

        // check voucher is expired or not      
        if (isset($expiry_date) && !empty($expiry_date)) {

            if ($expiry_date < $woo_vou_model->woo_vou_current_date()) {
                // set voucher status to expired
                $vou_code_status = 'expired';
            }
        }

        $args = array(
                'woo_vou_list' => true,
                'post_parent' => $voucodeid
            );  
    
        //get partially used voucher codes data from database
        $redeemed_data              = woo_vou_get_partially_redeem_details( $args );
        $partially_redeemed_data    = isset( $redeemed_data['data'] ) ? $redeemed_data['data'] : '';

        // If voucher have redeem method or used codes meta AND not expired AND current user have access for reverse redeem.
        if( ( !empty($redeem_method) || !empty($used_codes) ) && !empty($user_role) && is_array($reverse_redeem_role) && in_array($user_role, $reverse_redeem_role) && empty($vou_code_status) ){

            if( !empty($partial_id) ){
                if( !empty( $partially_redeemed_data ) && count( $partially_redeemed_data ) > 1 ){
                    if( !empty($_GET['woo_vou_code']) ) {

                        $html = '<input type="button" class="button-primary woo_vou_reverse_redeem_post" value="'.esc_html__('Reverse Redeem', 'woovoucherrr').'" data-partial_id="'.$partial_id.'" >';
                    } else {
                        $html = '<input type="button" name="woo_vou_reverse_redeem_post" class="button-primary woo_vou_reverse_redeem_post" value="'.esc_html__('Reverse Redeem', 'woovoucherrr').'" data-partial_id="'.$partial_id.'" >';
                    }
                } else{
                    if( !empty($_GET['woo_vou_code']) ){

                        $html = '<input type="submit" id="woo_vou_reverse_redeem" name="woo_vou_reverse_redeem" class="button-primary" value="'.esc_html__('Reverse Redeem', 'woovoucherrr').'" >';
                    } else {

                        $html = '<input type="button" id="woo_vou_reverse_redeem" name="woo_vou_reverse_redeem" class="button-primary" value="'.esc_html__('Reverse Redeem', 'woovoucherrr').'" >';
                    }
                }
                

            } else {

                if( !empty( $used_codes ) || !empty( $redeem_method) ){
                    

                    if( !empty($_GET['woo_vou_code']) ){

                        $html = '<input type="submit" id="woo_vou_reverse_redeem" name="woo_vou_reverse_redeem" class="button-primary" value="'.esc_html__('Full Reverse Redeem', 'woovoucherrr').'" >';
                    } else {

                        $html = '<input type="button" id="woo_vou_reverse_redeem" name="woo_vou_reverse_redeem" class="button-primary" value="'.esc_html__('Full Reverse Redeem', 'woovoucherrr').'" >';
                    }
                }
            }
        } elseif(  ( !empty($partial_id) || !empty( $partially_redeemed_data ) ) && !empty($user_role) && is_array($reverse_redeem_role) && in_array($user_role, $reverse_redeem_role) && empty($vou_code_status) ){ // Unlimited redeem

            if( !empty($partial_id) ){

                if( !empty( $partially_redeemed_data ) && count( $partially_redeemed_data ) > 1 ){
                    if( !empty($_GET['woo_vou_code']) ) {

                        $html = '<input type="button" class="button-primary woo_vou_reverse_redeem_post" value="'.esc_html__('Reverse Redeem', 'woovoucherrr').'" data-partial_id="'.$partial_id.'" >';
                    } else {
                        $html = '<input type="button" name="woo_vou_reverse_redeem_post" class="button-primary woo_vou_reverse_redeem_post" value="'.esc_html__('Reverse Redeem', 'woovoucherrr').'" data-partial_id="'.$partial_id.'" >';
                    }
                } else{
                    if( !empty($_GET['woo_vou_code']) ){

                        $html = '<input type="submit" id="woo_vou_reverse_redeem" name="woo_vou_reverse_redeem" class="button-primary" value="'.esc_html__('Reverse Redeem', 'woovoucherrr').'" >';
                    } else {

                        $html = '<input type="button" id="woo_vou_reverse_redeem" name="woo_vou_reverse_redeem" class="button-primary" value="'.esc_html__('Reverse Redeem', 'woovoucherrr').'" >';
                    }
                }

            } else{

                 if( !empty($_GET['woo_vou_code']) ){

                    $html = '<input type="submit" id="woo_vou_reverse_redeem" name="woo_vou_reverse_redeem" class="button-primary" value="'.esc_html__('Full Reverse Redeem', 'woovoucherrr').'" >';
                } else {

                    $html = '<input type="button" id="woo_vou_reverse_redeem" name="woo_vou_reverse_redeem" class="button-primary" value="'.esc_html__('Full Reverse Redeem', 'woovoucherrr').'" >';
                }
            }
        }

        $button = apply_filters('woo_vou_reverse_redeem_button', $html, $voucodeid);

        if( !empty( $button ) ){
        	$response['reverse_redeem'] = $button;
        }

        return $response;
	}

	/**
     * Reverse Redeem Process
     * 
     * Handles to processing reverse redeem
     * 
     * @package WooCommerce PDF Vouchers - Reverse Redemption
     * @since 1.0.0
     */
    public function woo_vou_rr_process() {

        // Get global variables
        global $current_user;

        // Declare prefix variable
        $prefix = WOO_VOU_META_PREFIX;

        $total_redeemed_price = 0;
        $result = array();

        // get voucher code and voucher id 
        $voucode = (!empty($_POST['voucode'])) ? $_POST['voucode'] : '';
        $voucodeid = woo_vou_get_voucodeid_from_voucode($voucode);

        // Add third party plugin to validate before redeem voucher code
        $response = array();
        if( empty($voucode) || empty($voucodeid) ){

            $response['fail'] = 'fail';
            $response['error_message'] = apply_filters('woo_vou_rr_invalid_voucher_message', esc_html__('Voucher code doesn\'t exist.', 'woovoucherrr'), $voucodeid);

            if (isset($_POST['ajax']) && $_POST['ajax'] == true) { // if request through ajax
                echo json_encode($response);
                exit;
            } else {
                return 'fail';
            }
        }

        $user_roles = !empty($current_user->roles) ? $current_user->roles : '';
        $user_role = !empty($user_roles) ? $user_roles[0] : '';
        $reverse_redeem_role = get_option('vou_allow_reverse_redeem_role');
        $redeem_method = get_post_meta( $voucodeid, $prefix.'redeem_method', true );
        $purchased_codes = get_post_meta( $voucodeid, $prefix.'purchased_codes', true );
        $used_codes = get_post_meta( $voucodeid, $prefix.'used_codes', true );

        $args = array(
                'woo_vou_list' => true,
                'post_parent' => $voucodeid
            );  
    
        //get partially used voucher codes data from database
        $redeemed_data              = woo_vou_get_partially_redeem_details( $args );
        $partially_redeemed_data    = isset( $redeemed_data['data'] ) ? $redeemed_data['data'] : '';

        // Add third party plugin to validate before redeem voucher code
        if( empty($redeem_method) && empty( $used_codes ) ){

            if( empty( $partially_redeemed_data ) ){

                $response['fail'] = 'fail';
                $response['error_message'] = apply_filters('woo_vou_rr_unused_voucher_message', esc_html__('Voucher code doesn\'t redeemed previously.', 'woovoucherrr'), $voucodeid);

                if (isset($_POST['ajax']) && $_POST['ajax'] == true) { // if request through ajax
                    echo json_encode($response);
                    exit;
                } else {
                    return 'fail';
                }
            }
        }

        if( ( !empty($redeem_method) || !empty($used_codes) ) && !empty($user_role) && is_array($reverse_redeem_role) && in_array($user_role, $reverse_redeem_role) ){

            delete_post_meta( $voucodeid, $prefix.'redeem_method' );
            delete_post_meta( $voucodeid, $prefix.'used_codes' );
            delete_post_meta( $voucodeid, $prefix.'redeem_by' );
            delete_post_meta( $voucodeid, $prefix.'used_code_date' );
            delete_post_meta( $voucodeid, $prefix.'redeemed_page' );
            update_post_meta( $voucodeid, $prefix.'reverse_redeem', 1 );

            // get all patially redeemed post for voucher code = $voucodeid
            $args = array(
                'post_type' => array(WOO_VOU_PARTIAL_REDEEM_POST_TYPE, WOO_VOU_UNLIMITED_REDEEM_POST_TYPE),
                'post_parent' => $voucodeid,
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => $prefix . 'partial_redeem_amount',
                    ),
                ),
            );
            $partially_redeemed_posts = get_posts($args);

            // if found any parially redeemed post, then calculate total redeemed price
            if (!empty($partially_redeemed_posts) && is_array($partially_redeemed_posts)) {

                foreach ($partially_redeemed_posts as $key => $partially_redeemed_post) {

                    if( $partially_redeemed_post->post_type != WOO_VOU_UNLIMITED_REDEEM_POST_TYPE ) { 

                        // get redeemed price
                        $price = get_post_meta($partially_redeemed_post->ID, $prefix . 'partial_redeem_amount', true);
                        // add redeemed price to total
                        $total_redeemed_price += $price;
                    }

                    wp_update_post(array(
                        'ID'    =>  $partially_redeemed_post->ID,
                        'post_status'   =>  'trash'
                    ));
                }
            }

            // arguments for getting coupon id
            $args = array(
                'fields' => 'ids',
                'name' => strtolower($purchased_codes),
                array(
                    'key' => $prefix . 'coupon_type',
                    'value' => 'voucher_code'
                ),
            );

            // Get Coupon code data
            $coupon_code_data = woo_vou_get_coupon_details($args);

            if (!empty($coupon_code_data)) {

                foreach ($coupon_code_data as $coupon_code) {

                    // Get coupon_type
                    $coupon_type = get_post_meta($coupon_code, $prefix . 'coupon_type', true);

                    // Get coupon amount
                    $coupon_amount = get_post_meta($coupon_code, 'coupon_amount', true);
                    $coupon_amount += $total_redeemed_price;

                    if( $coupon_type == "voucher_code" ){

                        delete_post_meta( $coupon_code, '_used_by' );
                        update_post_meta( $coupon_code, 'usage_count', 0 );
                        update_post_meta( $coupon_code, 'coupon_amount', $coupon_amount );
                    }
                }
            }

            $response['success'] = 'success';
            $response['success_message'] = apply_filters('woo_vou_rr_success_message', esc_html__('Reverse redemption for this voucher code done successfully.', 'woovoucherrr'), $voucodeid);

            if (isset($_POST['ajax']) && $_POST['ajax'] == true) { // if request through ajax
                echo json_encode($response);
                exit;
            } else {
                return $response;
            }

        } 
        elseif ( !empty( $partially_redeemed_data )  && !empty($user_role) && is_array($reverse_redeem_role) && in_array($user_role, $reverse_redeem_role) ) { // for Unlimited redeem

            delete_post_meta( $voucodeid, $prefix.'redeem_method' );
            delete_post_meta( $voucodeid, $prefix.'used_codes' );
            delete_post_meta( $voucodeid, $prefix.'redeem_by' );
            delete_post_meta( $voucodeid, $prefix.'used_code_date' );
            delete_post_meta( $voucodeid, $prefix.'redeemed_page' );
            update_post_meta( $voucodeid, $prefix.'reverse_redeem', 1 );

            // get all patially redeemed post for voucher code = $voucodeid
            $args = array(
                'post_type' => array(WOO_VOU_PARTIAL_REDEEM_POST_TYPE, WOO_VOU_UNLIMITED_REDEEM_POST_TYPE),
                'post_parent' => $voucodeid,
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => $prefix . 'partial_redeem_amount',
                    ),
                ),
            );
            $partially_redeemed_posts = get_posts($args);

            // if found any parially redeemed post, then calculate total redeemed price
            if (!empty($partially_redeemed_posts) && is_array($partially_redeemed_posts)) {

                foreach ($partially_redeemed_posts as $key => $partially_redeemed_post) {

                    if( $partially_redeemed_post->post_type != WOO_VOU_UNLIMITED_REDEEM_POST_TYPE ) {

                        // get redeemed price
                        $price = get_post_meta($partially_redeemed_post->ID, $prefix . 'partial_redeem_amount', true);
                        // add redeemed price to total
                        $total_redeemed_price += $price;
                    }

                    wp_update_post(array(
                        'ID'    =>  $partially_redeemed_post->ID,
                        'post_status'   =>  'trash'
                    ));
                }
            }

            // arguments for getting coupon id
            $args = array(
                'fields' => 'ids',
                'name' => strtolower($purchased_codes),
                array(
                    'key' => $prefix . 'coupon_type',
                    'value' => 'voucher_code'
                ),
            );

            // Get Coupon code data
            $coupon_code_data = woo_vou_get_coupon_details($args);

            if (!empty($coupon_code_data)) {

                foreach ($coupon_code_data as $coupon_code) {

                    // Get coupon_type
                    $coupon_type = get_post_meta($coupon_code, $prefix . 'coupon_type', true);

                    // Get coupon amount
                    $coupon_amount = get_post_meta($coupon_code, 'coupon_amount', true);
                    $coupon_amount += $total_redeemed_price;

                    if( $coupon_type == "voucher_code" ){

                        delete_post_meta( $coupon_code, '_used_by' );
                        update_post_meta( $coupon_code, 'usage_count', 0 );
                        update_post_meta( $coupon_code, 'coupon_amount', $coupon_amount );
                    }
                }
            }

            $response['success'] = 'success';
            $response['success_message'] = apply_filters('woo_vou_rr_success_message', esc_html__('Reverse redemption for this voucher code done successfully.', 'woovoucherrr'), $voucodeid);

            if (isset($_POST['ajax']) && $_POST['ajax'] == true) { // if request through ajax
                echo json_encode($response);
                exit;
            } else {
                return $response;
            }
        }
        else {

            $response['fail'] = 'fail';
            $response['error_message'] = apply_filters('woo_vou_rr_accessed_message', esc_html__('Your account doesn\'t access for reverse redeem.', 'woovoucherrr'), $voucodeid);

            if (isset($_POST['ajax']) && $_POST['ajax'] == true) { // if request through ajax
                echo json_encode($response);
                exit;
            } else {
                return 'fail';
            }
        }

    }	

    /**
     * Get Reverse Redeem Button
     * 
     * Handles to get reverse redeem button on check voucher page
     * 
     * @package WooCommerce PDF Vouchers - Reverse Redemption
     * @since 1.0.0
     */
    public function woo_vou_rr_reverse_partial_post_process() {

        // Get global variables
        global $current_user;

        // Declare prefix variable
        $prefix     = WOO_VOU_META_PREFIX;
        $user_roles = !empty($current_user->roles) ? $current_user->roles : '';
        $user_role  = !empty($user_roles) ? $user_roles[0] : '';
        $partial_id = !empty($_POST['partial_id']) ? $_POST['partial_id'] : '';
        $partial_post = !empty($partial_id) ? get_post( $partial_id ) : '';
        $reverse_redeem_role = get_option('vou_allow_reverse_redeem_role');

        // If is partial post and it's have publish status
        if( !empty($partial_post) && !empty($partial_post->ID) && ($partial_post->post_status == "publish") && ( $partial_post->post_type == WOO_VOU_PARTIAL_REDEEM_POST_TYPE || $partial_post->post_type == WOO_VOU_UNLIMITED_REDEEM_POST_TYPE ) ){
            
            $voucodeid = $partial_post->post_parent;
            // get voucher code meta
            $redeem_method = get_post_meta( $voucodeid, $prefix.'redeem_method', true );
            $purchased_codes = get_post_meta( $voucodeid, $prefix.'purchased_codes', true );
            $partial_price = get_post_meta($partial_id, $prefix . 'partial_redeem_amount', true);
            $used_codes = get_post_meta( $voucodeid, $prefix.'used_codes', true );

            if( $partial_post->post_type == WOO_VOU_UNLIMITED_REDEEM_POST_TYPE ){
                $partial_price = 0;
            }

            if( !empty($user_role) && is_array($reverse_redeem_role) && in_array($user_role, $reverse_redeem_role) ){
                wp_delete_post($partial_id);
                delete_post_meta( $voucodeid, $prefix.'used_codes' );
                delete_post_meta( $voucodeid, $prefix.'redeem_by' );
                delete_post_meta( $voucodeid, $prefix.'used_code_date' );
                delete_post_meta( $voucodeid, $prefix.'redeemed_page' );
                update_post_meta( $voucodeid, $prefix.'reverse_redeem', 1 );

                // arguments for getting coupon id
                $args = array(
                    'fields' => 'ids',
                    'name' => strtolower($purchased_codes),
                    array(
                        'key' => $prefix . 'coupon_type',
                        'value' => 'voucher_code'
                    ),
                );
                // Get Coupon code data
                $coupon_code_data = woo_vou_get_coupon_details($args);

                if (!empty($coupon_code_data)) {

                    foreach ($coupon_code_data as $coupon_code) {

                        // Get coupon meta
                        $coupon_type = get_post_meta($coupon_code, $prefix . 'coupon_type', true);
                        $coupon_amount = get_post_meta($coupon_code, 'coupon_amount', true);
                        $usage_count = get_post_meta($coupon_code, 'usage_count', true);

                        $coupon_amount += $partial_price;
                        $usage_count = $usage_count - 1;

                        if( $coupon_type == "voucher_code" ){

                            delete_post_meta( $coupon_code, '_used_by' );
                            update_post_meta( $coupon_code, 'usage_count', $usage_count );
                            update_post_meta( $coupon_code, 'coupon_amount', $coupon_amount );
                        }
                    }
                }

                // Change move to status
                wp_update_post(array(
                    'ID'    =>  $partial_id,
                    'post_status'   =>  'trash'
                ));

                $response['success'] = 'success';
                $response['success_message'] = apply_filters('woo_vou_rr_reverse_partial_success_message', esc_html__('Partial reverse redemption for this voucher code done successfully.', 'woovoucherrr'), $voucodeid);

                if (isset($_POST['ajax']) && $_POST['ajax'] == true) { // if request through ajax
                    echo json_encode($response);
                    exit;
                } else {
                    return $response;
                }

            }
        }

        $response['fail'] = 'fail';
        $response['error_message'] = apply_filters('woo_vou_rr_reverse_redeem_accessed_message', esc_html__('This partial redeem doesn\'t reversed.', 'woovoucherrr'), $partial_id);

        if (isset($_POST['ajax']) && $_POST['ajax'] == true) { // if request through ajax
            echo json_encode($response);
            exit;
        } else {
            return 'fail';
        }

    }

    /**
     * Handle to span for reverse redeem messages
     *
     * @package WooCommerce PDF Vouchers - Reverse Redemption
     * @since 1.0.0
     */
    public function woo_vou_rr_reverse_redeem_span(){
        echo '<span class="woo-vou-voucher-reverse-redeem"></span>';
    }


    /**
     * Add Reverse redeem button to QRCODE voucher check page
     * 
     * Handle to add more reverse redeem button
     *
     * @package WooCommerce PDF Vouchers - Reverse Redemption
     * @since 1.0.0
     */
    public function woo_vou_rr_check_for_reverse_redeem_process(){

        if( !empty( $_POST['woo_vou_reverse_redeem'] ) ) { // if form is submited

            // save voucher code
            $redeem_response = $this->woo_vou_rr_process();
        }
    }

    /**
     * Add Reverse redeem button to QRCODE voucher check page
     * 
     * Handle to add more reverse redeem button
     *
     * @package WooCommerce PDF Vouchers - Reverse Redemption
     * @since 1.0.0
     */
    public function woo_vou_rr_before_qrcode_product_info( $voucher_data ){
        ?>
        <div class="woo-vou-voucher-reverse-redeem"><?php echo (!empty($voucher_data['reverse_redeem'])) ? $voucher_data['reverse_redeem'] : ''; ?></div>
        <?php
    }

	/**
	 * Adding Hooks
	 *
	 * @package WooCommerce PDF Vouchers - Reverse Redemption
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		// add filter to add more fonts for language support		
		add_filter( 'woocommerce_get_settings_woo-vou-settings', array( $this, 'woo_vou_rr_settings' ), 10, 2);

        add_filter( 'woo_vou_setting_sections', array( $this, 'woo_vou_rr_check_extenstion_setting' ) );

		add_filter( 'woo_vou_check_vou_partial_redeem_info_fields', array( $this, 'woo_vou_rr_add_column' ), 10, 1 );
		
		add_filter( 'woo_vou_check_partial_voucher_column_value', array( $this, 'woo_vou_rr_add_column_value'), 10, 6 );

		add_filter('woo_vou_voucher_code_check_response', array( $this, 'woo_vou_rr_display_button'), 10, 3 );

		//ajax call to voucher code redeem reverse
        add_action('wp_ajax_woo_vou_rr_process', array($this, 'woo_vou_rr_process'));
        add_action('wp_ajax_nopriv_woo_vou_rr_process', array($this, 'woo_vou_rr_process'));

        //ajax call to voucher code reverse of partial post
        add_action('wp_ajax_woo_vou_rr_reverse_partial_post_process', array($this, 'woo_vou_rr_reverse_partial_post_process'));
        add_action('wp_ajax_nopriv_woo_vou_rr_reverse_partial_post_process', array($this, 'woo_vou_rr_reverse_partial_post_process'));

        add_action('woo_vou_after_check_voucher_code_loader', array( $this, 'woo_vou_rr_reverse_redeem_span'));

        add_action('woo_vou_check_qrcode_content_before_template', array( $this, 'woo_vou_rr_check_for_reverse_redeem_process') );

        add_action('woo_vou_before_qrcode_product_details', array( $this, 'woo_vou_rr_before_qrcode_product_info') );
	}
}