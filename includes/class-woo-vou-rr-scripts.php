<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Scripts Class
 *
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 *
 * @package WooCommerce PDF Vouchers - Reverse Redemption
 * @since 1.0.0
 */
class WOO_Vou_Rr_Script {

	/**
	 * Enqueue Scrips
	 * 
	 * Handles to enqueue script on 
	 * needed pages
	 * 
	 * @package WooCommerce PDF Vouchers - Reverse Redemption
	 * @since 1.0.0
	 */
	public function woo_vou_rr_admin_scripts ( $hook_suffix ){

		$wc_screen_id		= woo_vou_get_wc_screen_id();
		$woo_vou_screen_id	= woo_vou_get_voucher_screen_id();
		
		if( $hook_suffix == $wc_screen_id . '_page_woo-vou-check-voucher-code' || $hook_suffix == $woo_vou_screen_id . '_page_woo-vou-check-voucher-code'|| $hook_suffix == $woo_vou_screen_id . '_page_woo-vou-codes' || $hook_suffix == $wc_screen_id . '_page_woo-vou-codes' || $hook_suffix == 'toplevel_page_woo-vou-codes' ) {

			// add js for check code in admin
			wp_register_script( 'woo-vou-rr-check-code-script', WOO_VOU_RR_URL . 'includes/js/woo-vou-rr-check-code.js', array('jquery'), WOO_VOU_RR_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-rr-check-code-script' );

			wp_localize_script( 
				'woo-vou-rr-check-code-script' , 
				'WooVouRrCheck', 
				array(
					'ajaxurl' 					=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					'reverse_redeem_confirm' 	=> esc_html__( 'Are you sure you want to reverse redeem this voucher code?', 'woovoucherrr' ),
				) 
			);
		}
	}

	/**
	 * Adding Scripts
	 *
	 * Adding Scripts for check code public
	 *
	 * @package WooCommerce PDF Vouchers - Reverse Redemption
	 * @since 1.0.0
	 */
	public function woo_vou_rr_public_scripts(){

		global $woocommerce, $post, $wp_version;

		$post_content = isset($post->post_content) ? $post->post_content : '';

		// to add js and css on archive page as has_shortcode will not work on archive page
        $is_archive = ( (get_option('show_on_front') == 'posts' && is_front_page() ) || is_archive() );

		if( has_shortcode( $post_content, 'woo_vou_check_code' ) || $is_archive || apply_filters( 'woo_vou_enqueue_check_code_script', false ) || isset($_GET['woo_vou_code']) ) {

			// add js for check code in public
			wp_register_script( 'woo-vou-public-rr-check-code-script', WOO_VOU_RR_URL . 'includes/js/woo-vou-rr-check-code.js', array( 'jquery' ), WOO_VOU_RR_PLUGIN_VERSION );
			wp_enqueue_script( 'woo-vou-public-rr-check-code-script' );

			wp_localize_script( 
				'woo-vou-public-rr-check-code-script',
				'WooVouRrCheck',
				array( 
					'ajaxurl' 					=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					'reverse_redeem_confirm' 	=> esc_html__( 'Are you sure you want to reverse redeem this voucher code?', 'woovoucherrr' ),
				)
			);
		}
		
	}
	

	/**
	 * Adding Hooks
	 *
	 * Adding proper hoocks for the scripts.
	 *
	 * @package WooCommerce PDF Vouchers - Reverse Redemption
	 * @since 1.0.0
	 */
	public function add_hooks() {

		//add script for new and edit post and purchased voucher code
		add_action( 'admin_enqueue_scripts', array( $this, 'woo_vou_rr_admin_scripts' ) );

		//add scripts for check code front side
		add_action( 'wp_enqueue_scripts', array( $this, 'woo_vou_rr_public_scripts' ) );

		
	}
}