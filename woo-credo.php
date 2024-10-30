<?php

/**
 * Plugin Name: Credo WooCommerce Payment Gateway
 * Plugin URI: https://wordpress.org/plugins/credo-payment-forms
 * Description: WooCommerce payment gateway for Credo
 * Version: 2.0.2
 * Author: Credo Software Engineering
 * Author URI: https://credocentral.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 7.0
 * WC tested up to: 8.8.3
 * Text Domain: woo-credo
 * Domain Path: /languages
 */

use Automattic\WooCommerce\Admin\Notes\Note;
use Automattic\WooCommerce\Admin\Notes\Notes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


define('WC_CREDO_MAIN_FILE', __FILE__);
define( 'WC_CREDO_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

const WC_CREDO_VERSION = '2.0.2';

/**
 * Initialize Credo WooCommerce payment gateway.
 */
function tbz_wc_credo_init() {

    load_plugin_textdomain( 'woo-credo', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', 'tbz_wc_credo_wc_missing_notice' );
        return;
    }

    add_action( 'admin_init', 'tbz_wc_credo_testmode_notice' );

    require_once __DIR__ . '/includes/class-wc-gateway-credo.php';

    add_filter( 'woocommerce_payment_gateways', 'tbz_wc_add_credo_gateway', 99 );

    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'tbz_woo_credo_plugin_action_links' );

}
add_action( 'plugins_loaded', 'tbz_wc_credo_init', 99 );

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function tbz_woo_credo_plugin_action_links( $links ) {

    $settings_link = array(
        'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=credo' ) . '" title="' . __( 'View Credo WooCommerce Settings', 'woo-credo' ) . '">' . __( 'Settings', 'woo-credo' ) . '</a>',
    );

    return array_merge( $settings_link, $links );

}

/**
 * Add Credo Gateway to WooCommerce.
 *
 * @param array $methods WooCommerce payment gateways methods.
 *
 * @return array
 */
function tbz_wc_add_credo_gateway( $methods ) {
    $methods[] = 'WC_Gateway_Credo';
    return $methods;

}

/**
 * Display a notice if WooCommerce is not installed
 */
function tbz_wc_credo_wc_missing_notice() {
    echo '<div class="error"><p><strong>' . sprintf( __( 'Credo requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'woo-credo' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) . '" class="thickbox open-plugin-details-modal">here</a>' ) . '</strong></p></div>';
}

/**
 * Display the test mode notice.
 **/
function tbz_wc_credo_testmode_notice() {

    if ( ! class_exists( Notes::class ) ) {
        return;
    }

    if ( ! class_exists( WC_Data_Store::class ) ) {
        return;
    }

    if ( ! method_exists( Notes::class, 'get_note_by_name' ) ) {
        return;
    }

    $test_mode_note = Notes::get_note_by_name( 'credo-test-mode' );

    if ( false !== $test_mode_note ) {
        return;
    }

    $credo_settings = get_option( 'woocommerce_credo_settings' );
    $test_mode         = isset( $credo_settings['testmode'] ) ? $credo_settings['testmode'] : '';

    if ( 'yes' !== $test_mode ) {
        Notes::delete_notes_with_name( 'credo-test-mode' );

        return;
    }

    $note = new Note();
    $note->set_title( __( 'Credo test mode enabled', 'woo-credo' ) );
    $note->set_content( __( 'Credo test mode is currently enabled. Remember to disable it when you want to start accepting live payment on your site.', 'woo-credo' ) );
    $note->set_type( Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
    $note->set_layout( 'plain' );
    $note->set_is_snoozable( false );
    $note->set_name( 'credo-test-mode' );
    $note->set_source( 'woo-credo' );
    $note->add_action( 'disable-credo-test-mode', __( 'Disable Credo test mode', 'woo-credo' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=credo' ) );
    $note->save();
}

add_action(
    'before_woocommerce_init',
    function () {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
);


/**
 * Registers WooCommerce Blocks integration.
 */
function tbz_wc_gateway_credo_woocommerce_block_support() {
    if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        require_once __DIR__ . '/includes/class-wc-gateway-credo-blocks-support.php';

        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            static function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
                $payment_method_registry->register( new WC_Gateway_Credo_Blocks_Support() );

            }
        );
    }
}
add_action( 'woocommerce_blocks_loaded', 'tbz_wc_gateway_credo_woocommerce_block_support' );

