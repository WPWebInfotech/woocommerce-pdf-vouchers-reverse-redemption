<?php
/**
 * Plugin Name: WooCommerce PDF Vouchers - Reverse Redemption
 * Plugin URI:  https://wpwebelite.com/
 * Description: Reverse Redemption add-on allows you to undo already redeemed voucher codes.
 * Version: 1.0.6
 * Author: WPWeb
 * Author URI: https://wpwebelite.com/
 * Text Domain: woovoucherrr
 * Domain Path: languages
 *
 * WC tested up to: 8.2.1
 * Tested up to: 6.3.2
 * 
 * @package WooCommerce PDF Vouchers - Reverse Redemption
 * @category Core
 * @author WPWeb
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions
 * 
 * @package WooCommerce PDF Vouchers - Reverse Redemption
 * @since 1.0.0
 */
if( !defined( 'WOO_VOU_RR_PLUGIN_VERSION' ) ) {
	define( 'WOO_VOU_RR_PLUGIN_VERSION', '1.0.6' ); //Plugin version number
}
if( !defined( 'WOO_VOU_RR_DIR' ) ) {
	define( 'WOO_VOU_RR_DIR', dirname( __FILE__ ) ); // plugin dir
}
if( !defined( 'WOO_VOU_RR_URL' ) ) {
	define( 'WOO_VOU_RR_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}
if( !defined( 'WOO_VOU_RR_ADMIN' ) ) {
	define( 'WOO_VOU_RR_ADMIN', WOO_VOU_RR_DIR . '/includes/admin' ); // plugin admin dir
}
if( !defined( 'WOO_VOU_RR_PLUGIN_BASENAME' ) ) {
	define( 'WOO_VOU_RR_PLUGIN_BASENAME', basename( WOO_VOU_RR_DIR ) ); //Plugin base name
}
if ( ! defined( 'WOO_VOU_RR_PLUGIN_KEY' ) ) {
	define( 'WOO_VOU_RR_PLUGIN_KEY', 'woorrvouchers' );
}


/**
 * Admin notices
 *
 * @package WooCommerce PDF Vouchers - Reverse Redemption
 * @since 1.0.0
*/
function woo_vou_rr_admin_notices() {
	
	if ( !defined( 'WOO_VOU_DIR' ) ) {
		
		echo '<div class="error">';
		echo "<p><strong>" . esc_html__( 'Woocommerce PDF Vouchers needs to be activated to be able to use the Woocommerce PDF Voucher Reverse Redeem.', 'woovoucherrr' ) . "</strong></p>";
		echo '</div>';
	}
}

/**
 * Check WooCommerce PDF Vouchers Plugin
 *
 * Handles to check WooCoommerce Pdf voucher plugin is enabled
 * if not activated then deactivate our plugin
 *
 * @package WooCommerce PDF Vouchers - Reverse Redemption
 * @since 1.0.0
 */
function woo_vou_rr_check_activation() {

	// Check if constants are defined and PDF Vouchers plugin is active
	if ( !defined('WOO_VOU_PLUGIN_BASENAME') && !defined('WOO_VOU_PLUGIN_BASE_FILENAME') 
		&& !is_plugin_active('woocommerce-pdf-vouchers/woocommerce-pdf-vouchers.php') ) {

		// is this plugin active?
		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			// deactivate the plugin
	 		deactivate_plugins( plugin_basename( __FILE__ ) );
	 		// unset activation notice
	 		unset( $_GET[ 'activate' ] );
	 		// display notice
	 		add_action( 'admin_notices', 'woo_vou_rr_admin_notices' );
		}
	}
}

//Check WooCommerce Pdf voucher plugin is Activated or not
add_action( 'admin_init', 'woo_vou_rr_check_activation' );


/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 * 
 * @package WooCommerce PDF Vouchers - Reverse Redemption
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'woo_vou_rr_install' );


/**
 * Plugin Setup (On Activation)
 * 
 * Does the initial setup,
 * stest default values for the plugin options.
 * 
 * @package WooCommerce PDF Vouchers - Reverse Redemption
 * @since 1.0.0
 */
function woo_vou_rr_install() {

	//get option for when plugin is activating first time
	$woo_vou_rr_set_option = get_option('woo_vou_rr_set_option');
	
	if ( empty($woo_vou_rr_set_option) ) {

        //set default settings for reverse redeem role
        update_option('vou_allow_reverse_redeem_role', array('administrator','woo_vou_vendors') );

        //update plugin version to option 
        update_option('woo_vou_rr_set_option', '1.0');
    }
}

/**
 * Load Text Domain
 * 
 * This gets the plugin ready for translation.
 * 
 * @package WooCommerce PDF Vouchers - Reverse Redemption
 * @since 1.0.0
 */
function woo_vou_rr_load_text_domain() {
	
	// Set filter for plugin's languages directory
	$woo_vou_rr_lang_dir	= dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$woo_vou_rr_lang_dir	= apply_filters( 'woo_vou_rr_languages_directory', $woo_vou_rr_lang_dir );
	
	// Traditional WordPress plugin locale filter
	$locale	= apply_filters( 'plugin_locale',  get_locale(), 'woovoucher' );
	$mofile	= sprintf( '%1$s-%2$s.mo', 'woovoucherrr', $locale );
	
	// Setup paths to current locale file
	$mofile_local	= $woo_vou_rr_lang_dir . $mofile;
	$mofile_global	= WP_LANG_DIR . '/' . WOO_VOU_RR_PLUGIN_BASENAME . '/' . $mofile;
	
	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/woocommerce-pdf-vouchers-pdf-reverse-redeem folder
		load_textdomain( 'woovoucherrr', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) { // Look in local /wp-content/plugins/woocommerce-pdf-vouchers-pdf-reverse-redeem/languages/ folder
		load_textdomain( 'woovoucherrr', $mofile_local );
	} else { // Load the default language files
		load_plugin_textdomain( 'woovoucherrr', false, $woo_vou_rr_lang_dir );
	}
}

//add action to load plugin
add_action( 'plugins_loaded', 'woo_vou_rr_plugin_loaded', 9999 );

/**
 * Load Plugin
 * 
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 * 
 * @package WooCommerce PDF Vouchers - Reverse Redemption
 * @since 1.0.0
 */
function woo_vou_rr_plugin_loaded() {

	//check Woocommerce is activated or not
	if( class_exists( 'Woocommerce' ) && defined('WOO_VOU_PLUGIN_BASENAME') ) {
		
		/**
		 * Add plugin action links
		 *
		 * Adds a Settings, Support and Docs link to the plugin list.
		 *
		 * @package WooCommerce PDF Vouchers - Reverse Redemption
		 * @since 1.0.0
		 */
		function woo_vou_rr_add_plugin_links( $links ) {
			$plugin_links = array(
				'<a href="admin.php?page=wc-settings&tab=woo-vou-settings&section=vou_extensions_settings">' . esc_html__( 'Settings', 'woovoucherrr' ) . '</a>',
				'<a href="https://support.wpwebelite.com/">' . esc_html__( 'Support', 'woovoucherrr' ) . '</a>',
				'<a href="https://docs.wpwebelite.com/woocommerce-pdf-vouchers-addons/reverse-redeem/">' . esc_html__( 'Docs', 'woovoucherrr' ) . '</a>'
			);

			return array_merge( $plugin_links, $links );
		}

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woo_vou_rr_add_plugin_links' );

		// load first plugin text domain
		woo_vou_rr_load_text_domain();
		
		global $woo_vou_rr_admin, $woo_vou_rr_scripts;
		
		//Admin Pages Class for admin side
		require_once( WOO_VOU_RR_ADMIN . '/class-woo-vou-rr-admin.php' );
		$woo_vou_rr_admin = new WOO_Vou_Rr_Admin();
		$woo_vou_rr_admin->add_hooks();

		//Script Pages Class for admin side
		require_once( WOO_VOU_RR_DIR . '/includes/class-woo-vou-rr-scripts.php' );
		$woo_vou_rr_scripts = new WOO_Vou_Rr_Script();
		$woo_vou_rr_scripts->add_hooks();
		
	} //end if to check class Woocommerce is exist or not
	
} //end if to check plugin loaded is called or not



/**
* Declare compatible with HPOS
* 
* @package WooCommerce PDF Vouchers - Reverse Redemption
* @since 1.0.6
*/
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );