<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Gateway_Credo extends WC_Payment_Gateway_CC {

    /**
     * Is test mode active?
     *
     * @var bool
     */
    public $testmode;

    /**
     * Should orders be marked as complete after payment?
     *
     * @var bool
     */
    public $autocomplete_order;

    /**
     * Credo payment page type.
     *
     * @var string
     */
    public $payment_page;

    /**
     * Credo test public key.
     *
     * @var string
     */
    public $test_public_key;

    /**
     * Credo test secret key.
     *
     * @var string
     */
    public $test_secret_key;

    /**
     * Credo live public key.
     *
     * @var string
     */
    public $live_public_key;

    /**
     * Credo live secret key.
     *
     * @var string
     */
    public $live_secret_key;



    /**
     * Should Credo dynamic settlement be enabled.
     *
     * @var bool
     */
    public $split_payment;

    /**
     * Should the cancel & remove order button be removed on the pay for order page.
     *
     * @var bool
     */
    public $remove_cancel_order_button;

    /**
     * Credo service account code.
     *
     * @var string
     */
    public $service_code;

    /**
     * Who bears Credo charges?
     *
     * @var string
     */
    public $charges_bearer;

    /**
     * Should custom metadata be enabled?
     *
     * @var bool
     */
    public $custom_metadata;

    /**
     * Should the order id be sent as a custom metadata to Credo?
     *
     * @var bool
     */
    public $meta_order_id;

    /**
     * Should the customer name be sent as a custom metadata to Credo?
     *
     * @var bool
     */
    public $meta_name;

    /**
     * Should the billing email be sent as a custom metadata to Credo?
     *
     * @var bool
     */
    public $meta_email;

    /**
     * Should the billing phone be sent as a custom metadata to Credo?
     *
     * @var bool
     */
    public $meta_phone;

    /**
     * Should the billing address be sent as a custom metadata to Credo?
     *
     * @var bool
     */
    public $meta_billing_address;

    /**
     * Should the shipping address be sent as a custom metadata to Credo?
     *
     * @var bool
     */
    public $meta_shipping_address;

    /**
     * Should the order items be sent as a custom metadata to Credo?
     *
     * @var bool
     */
    public $meta_products;

    /**
     * API public key
     *
     * @var string
     */
    public $public_key;

    /**
     * API secret key
     *
     * @var string
     */
    public $secret_key;

    /**
     * Gateway disabled message
     *
     * @var string
     */
    public $msg;
    /**
     * Credo api base url
     *
     * @var string
     */

    public $base_url;

    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'credo';
        $this->method_title       = __( 'Credo', 'woo-credo' );
        $this->method_description = sprintf( __( 'Credo provide merchants with the tools and services needed to accept online payments from local and international customers using Mastercard, Visa, Verve Cards and Bank Transfers. <a href="%1$s" target="_blank">Sign up</a> for a Credo account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'woo-credo' ), 'https://credocentral.com', 'https://credocentral.com/dashboard' );
        $this->has_fields         = true;

        $this->payment_page = $this->get_option( 'payment_page' );

        $this->supports = array(
            'products',
        );

        // Load the form fields
        $this->init_form_fields();

        // Load the settings
        $this->init_settings();

        // Get setting values

        $this->title              = $this->get_option( 'title' );
        $this->description        = $this->get_option( 'description' );
        $this->enabled            = $this->get_option( 'enabled' );
        $this->testmode           = $this->get_option( 'testmode' ) === 'yes';
        $this->autocomplete_order = $this->get_option( 'autocomplete_order' ) === 'yes';

        $this->test_public_key = $this->get_option( 'test_public_key' );
        $this->test_secret_key = $this->get_option( 'test_secret_key' );

        $this->live_public_key = $this->get_option( 'live_public_key' );
        $this->live_secret_key = $this->get_option( 'live_secret_key' );



        $this->split_payment              = $this->get_option( 'split_payment' ) === 'yes';
        $this->remove_cancel_order_button = $this->get_option( 'remove_cancel_order_button' ) === 'yes';
        $this->service_code               = $this->get_option( 'service_code' );
        $this->charges_bearer             = $this->get_option( 'credo_charge_bearer' );


        $this->custom_metadata = $this->get_option( 'custom_metadata' ) === 'yes';

        $this->meta_order_id         = $this->get_option( 'meta_order_id' ) === 'yes';
        $this->meta_name             = $this->get_option( 'meta_name' ) === 'yes';
        $this->meta_email            = $this->get_option( 'meta_email' ) === 'yes';
        $this->meta_phone            = $this->get_option( 'meta_phone' ) === 'yes';
        $this->meta_billing_address  = $this->get_option( 'meta_billing_address' ) === 'yes';
        $this->meta_shipping_address = $this->get_option( 'meta_shipping_address' ) === 'yes';
        $this->meta_products         = $this->get_option( 'meta_products' ) === 'yes';

        $this->public_key = $this->testmode ? $this->test_public_key : $this->live_public_key;
        $this->secret_key = $this->testmode ? $this->test_secret_key : $this->live_secret_key;
        $this->base_url   = $this->testmode ? 'https://api.credodemo.com' : 'https://api.credocentral.com';


        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            array(
                $this,
                'process_admin_options',
            )
        );

        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

        // Payment listener/API hook.
        add_action( 'woocommerce_api_wc_gateway_credo', array( $this, 'verify_credo_transaction' ) );

        // Webhook listener/API hook.
        add_action( 'woocommerce_api_tbz_wc_credo_webhook', array( $this, 'process_webhooks' ) );

        // Check if the gateway can be used.
        if ( ! $this->is_valid_for_use() ) {
            $this->enabled = false;
        }

    }

    /**
     * Check if this gateway is enabled and available in the user's country.
     */
    public function is_valid_for_use() {

        if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_credo_supported_currencies', array(
            'NGN',
            'USD'
        ) ) ) ) {

            $this->msg = sprintf( __( 'Credo does not support your store currency. Kindly set it to either NGN (&#8358), GHS (&#x20b5;), USD (&#36;), KES (KSh), ZAR (R), XOF (CFA), or EGP (E£) <a href="%s">here</a>', 'woo-credo' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) );

            return false;

        }

        return true;

    }

    /**
     * Display credo payment icon.
     */
    public function get_icon() {

        #$base_location = wc_get_base_location();

        $icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/credo-wc.png', WC_CREDO_MAIN_FILE ) ) . '" alt="Credo Payment Options" />';


        return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

    }

    /**
     * Check if Credo merchant details is filled.
     */
    public function admin_notices() {

        if ( $this->enabled == 'no' ) {
            return;
        }

        // Check required fields.
        if ( ! ( $this->public_key && $this->secret_key ) ) {
            echo '<div class="error"><p>' . sprintf( __( 'Please enter your Credo merchant details <a href="%s">here</a> to be able to use the Credo WooCommerce plugin.', 'woo-credo' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=credo' ) ) . '</p></div>';

            return;
        }

    }

    /**
     * Check if Credo gateway is enabled.
     *
     * @return bool
     */
    public function is_available() {

        if ( 'yes' == $this->enabled ) {

            if ( ! ( $this->public_key && $this->secret_key ) ) {

                return false;

            }

            return true;

        }

        return false;

    }

    /**
     * Admin Panel Options.
     */
    public function admin_options() {

        ?>

        <h2><?php _e( 'Credo', 'woo-credo' ); ?>
            <?php
            if ( function_exists( 'wc_back_link' ) ) {
                wc_back_link( __( 'Return to payments', 'woo-credo' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
            }
            ?>
        </h2>

        <h4>
            <strong><?php printf( __( 'Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a> to the URL below<span style="color: red"><pre><code>%2$s</code></pre></span>', 'woo-credo' ), 'https://credocentral.com/settings/developer/webhooks', WC()->api_request_url( 'Tbz_WC_Credo_Webhook' ) ); ?></strong>
        </h4>

        <?php

        if ( $this->is_valid_for_use() ) {

            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';

        } else {
            ?>
            <div class="inline error"><p>
                    <strong><?php _e( 'Credo Payment Gateway Disabled', 'woo-credo' ); ?></strong>: <?php echo $this->msg; ?>
                </p></div>

            <?php
        }

    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {

        $form_fields = array(
            'enabled'                    => array(
                'title'       => __( 'Enable/Disable', 'woo-credo' ),
                'label'       => __( 'Enable Credo', 'woo-credo' ),
                'type'        => 'checkbox',
                'description' => __( 'Enable Credo as a payment option on the checkout page.', 'woo-credo' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'title'                      => array(
                'title'       => __( 'Title', 'woo-credo' ),
                'type'        => 'text',
                'description' => __( 'This controls the payment method title which the user sees during checkout.', 'woo-credo' ),
                'default'     => __( 'Card/ Bank Transfer', 'woo-credo' ),
                'desc_tip'    => true,
            ),
            'description'                => array(
                'title'       => __( 'Description', 'woo-credo' ),
                'type'        => 'textarea',
                'description' => __( 'This controls the payment method description which the user sees during checkout.', 'woo-credo' ),
                'default'     => __( 'Make payment using your debit and credit cards', 'woo-credo' ),
                'desc_tip'    => true,
            ),
            'testmode'                   => array(
                'title'       => __( 'Test mode', 'woo-credo' ),
                'label'       => __( 'Enable Test Mode', 'woo-credo' ),
                'type'        => 'checkbox',
                'description' => __( 'Test mode enables you to test payments before going live. <br />Once the LIVE MODE is enabled on your Credo account uncheck this.', 'woo-credo' ),
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'payment_page'               => array(
                'title'       => __( 'Payment Option', 'woo-credo' ),
                'type'        => 'select',
                'description' => __( 'Redirect will redirect the customer to credo gateway.', 'woo-credo' ),
                'default'     => '',
                'desc_tip'    => false,
                'options'     => array(
                    'redirect' => __( 'Redirect', 'woo-credo' ),
                    'pop up' => __('Pop up', 'woo-credo'),

                ),
            ),
            'test_secret_key'            => array(
                'title'       => __( 'Test Secret Key', 'woo-credo' ),
                'type'        => 'password',
                'description' => __( 'Enter your Test Secret Key here', 'woo-credo' ),
                'default'     => '',
            ),
            'test_public_key'            => array(
                'title'       => __( 'Test Public Key', 'woo-credo' ),
                'type'        => 'text',
                'description' => __( 'Enter your Test Public Key here.', 'woo-credo' ),
                'default'     => '',
            ),
            'live_secret_key'            => array(
                'title'       => __( 'Live Secret Key', 'woo-credo' ),
                'type'        => 'password',
                'description' => __( 'Enter your Live Secret Key here.', 'woo-credo' ),
                'default'     => '',
            ),
            'live_public_key'            => array(
                'title'       => __( 'Live Public Key', 'woo-credo' ),
                'type'        => 'text',
                'description' => __( 'Enter your Live Public Key here.', 'woo-credo' ),
                'default'     => '',
            ),
            'autocomplete_order'         => array(
                'title'       => __( 'Autocomplete Order After Payment', 'woo-credo' ),
                'label'       => __( 'Autocomplete Order', 'woo-credo' ),
                'type'        => 'checkbox',
                'class'       => 'wc-credo-autocomplete-order',
                'description' => __( 'If enabled, the order will be marked as complete after successful payment', 'woo-credo' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'remove_cancel_order_button' => array(
                'title'       => __( 'Remove Cancel Order & Restore Cart Button', 'woo-credo' ),
                'label'       => __( 'Remove the cancel order & restore cart button on the pay for order page', 'woo-credo' ),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no',
            ),
            'split_payment'              => array(
                'title'       => __( 'Dynamic Settlement', 'woo-credo' ),
                'label'       => __( 'Enable Dynamic Settlement', 'woo-credo' ),
                'type'        => 'checkbox',
                'description' => __('Dynamic settlement splits allow you to distribute funds from a single transaction among multiple recipients/accounts based on predefined rules.  

These rules can consider various factors like percentages, or fixed amounts. The dynamic nature ensures that splits adapt in real-time to changing circumstances. '),
                'class'       => 'woocommerce_credo_split_payment',
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'service_code'               => array(
                'title'       => __( 'Service Code', 'woo-credo' ),
                'type'        => 'text',
                'description' => __( 'Enter your service code here.', 'woo-credo' ),
                'class'       => 'woocommerce_credo_subaccount_code',
                'default'     => '',
            ),


            'credo_charge_bearer' => array(
                'title'       => __( 'Credo Charges Bearer', 'woo-credo' ),
                'type'        => 'select',
                'description' => __( 'Who bears Credo charges?', 'woo-credo' ),




                'default'     => '',
                'desc_tip'    => false,
                'options'     => array(

                    'customer' => __( 'Customer', 'woo-credo' ),
                    'merchant' => __( 'Merchant', 'woo-credo' ),
                ),
            ),

            'custom_metadata'       => array(
                'title'       => __( 'Custom Metadata', 'woo-credo' ),
                'label'       => __( 'Enable Custom Metadata', 'woo-credo' ),
                'type'        => 'checkbox',
                'class'       => 'wc-credo-metadata',
                'description' => __( 'If enabled, you will be able to send more information about the order to Credo.', 'woo-credo' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'meta_order_id'         => array(
                'title'       => __( 'Order ID', 'woo-credo' ),
                'label'       => __( 'Send Order ID', 'woo-credo' ),
                'type'        => 'checkbox',
                'class'       => 'wc-credo-meta-order-id',
                'description' => __( 'If checked, the Order ID will be sent to Credo', 'woo-credo' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'meta_name'             => array(
                'title'       => __( 'Customer Name', 'woo-credo' ),
                'label'       => __( 'Send Customer Name', 'woo-credo' ),
                'type'        => 'checkbox',
                'class'       => 'wc-credo-meta-name',
                'description' => __( 'If checked, the customer full name will be sent to Credo', 'woo-credo' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'meta_email'            => array(
                'title'       => __( 'Customer Email', 'woo-credo' ),
                'label'       => __( 'Send Customer Email', 'woo-credo' ),
                'type'        => 'checkbox',
                'class'       => 'wc-credo-meta-email',
                'description' => __( 'If checked, the customer email address will be sent to Credo', 'woo-credo' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'meta_phone'           => array(
                'title'       => __( 'Customer Phone', 'woo-credo' ),
                'label'       => __( 'Send Customer Phone', 'woo-credo' ),
                'type'        => 'checkbox',
                'class'       => 'wc-credo-meta-phone',
                'description' => __( 'If checked, the customer phone will be sent to Credo', 'woo-credo' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'meta_billing_address'  => array(
                'title'       => __( 'Order Billing Address', 'woo-credo' ),
                'label'       => __( 'Send Order Billing Address', 'woo-credo' ),
                'type'        => 'checkbox',
                'class'       => 'wc-credo-meta-billing-address',
                'description' => __( 'If checked, the order billing address will be sent to Credo', 'woo-credo' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'meta_shipping_address' => array(
                'title'       => __( 'Order Shipping Address', 'woo-credo' ),
                'label'       => __( 'Send Order Shipping Address', 'woo-credo' ),
                'type'        => 'checkbox',
                'class'       => 'wc-credo-meta-shipping-address',
                'description' => __( 'If checked, the order shipping address will be sent to Credo', 'woo-credo' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
            'meta_products'         => array(
                'title'       => __( 'Product(s) Purchased', 'woo-credo' ),
                'label'       => __( 'Send Product(s) Purchased', 'woo-credo' ),
                'type'        => 'checkbox',
                'class'       => 'wc-credo-meta-products',
                'description' => __( 'If checked, the product(s) purchased will be sent to Credo', 'woo-credo' ),
                'default'     => 'no',
                'desc_tip'    => true,
            ),
        );

        if ( 'NGN' !== get_woocommerce_currency() ) {
            unset( $form_fields['custom_gateways'] );
        }

        $this->form_fields = $form_fields;

    }

    /**
     * Payment form on checkout page
     */
    public function payment_fields() {

        if ( $this->description ) {
            echo wpautop( wptexturize( $this->description ) );
        }

        if ( ! is_ssl() ) {
            return;
        }



    }

    /**
     * Outputs scripts used for credo payment.
     */


    /**
     * Load admin scripts.
     */

    public function payment_scripts() {

        if ( isset( $_GET['pay_for_order'] ) || ! is_checkout_pay_page() ) {
            return;
        }

        if ( $this->enabled === 'no' ) {
            return;
        }

        $order_key = urldecode( $_GET['key'] );
        $order_id  = absint( get_query_var( 'order-pay' ) );

        $order = wc_get_order( $order_id );

        if ( $this->id !== $order->get_payment_method() ) {
            return;
        }

        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_script( 'jquery' );

        wp_enqueue_script( 'credo', 'https://pay.credodemo.com/inline.js', array( 'jquery' ), WC_CREDO_VERSION, false );

        wp_enqueue_script( 'wc_credo', plugins_url( 'assets/js/credo' . $suffix . '.js',  WC_CREDO_MAIN_FILE ), array( 'jquery', 'credo' ), WC_CREDO_VERSION, false );

        $credo_params = array(
            'key' => $this->public_key,
        );

        if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

            $email         = $order->get_billing_email();
            $amount        = $order->get_total() * 100;
            $txnref        = $order_id . '_' . time();
            $the_order_id  = $order->get_id();
            $the_order_key = $order->get_order_key();
            $currency      = $order->get_currency();

            if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

                $credo_params['email']    = $email;
                $credo_params['amount']   = $amount;
                $credo_params['txnref']   = $txnref;
                $credo_params['currency'] = $currency;
                $credo_params['meta_first_name'] = $order->get_billing_first_name();
                $credo_params['meta_last_name'] =  $order->get_billing_last_name();
                $credo_params['meta_phone'] = $order->get_billing_phone();

            }

            $credo_params['bearer'] = $this->charges_bearer === 'customer' ? 0 : 1;


            if ( $this->split_payment && !empty( $this->service_code ) ) {
                $credo_params['serviceCode'] = $this->service_code;
            }



            if ( $this->custom_metadata ) {

                if ( $this->meta_order_id ) {

                    $credo_params['meta_order_id'] = $order_id;

                }

                if ( $this->meta_name ) {

                    $credo_params['meta_first_name'] = $order->get_billing_first_name();
                    $credo_params['meta_last_name'] =  $order->get_billing_last_name();


                }

                if ( $this->meta_email ) {

                    $credo_params['meta_email'] = $email;

                }

                if ( $this->meta_phone ) {

                    $credo_params['meta_phone'] = $order->get_billing_phone();

                }

                if ( $this->meta_products ) {

                    $line_items = $order->get_items();

                    $products = '';

                    foreach ( $line_items as $item_id => $item ) {
                        $name      = $item['name'];
                        $quantity  = $item['qty'];
                        $products .= $name . ' (Qty: ' . $quantity . ')';
                        $products .= ' | ';
                    }

                    $products = rtrim( $products, ' | ' );

                    $credo_params['meta_products'] = $products;

                }

                if ( $this->meta_billing_address ) {

                    $billing_address = $order->get_formatted_billing_address();
                    $billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

                    $credo_params['meta_billing_address'] = $billing_address;

                }

                if ( $this->meta_shipping_address ) {

                    $shipping_address = $order->get_formatted_shipping_address();
                    $shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

                    if ( empty( $shipping_address ) ) {

                        $billing_address = $order->get_formatted_billing_address();
                        $billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

                        $shipping_address = $billing_address;

                    }

                    $credo_params['meta_shipping_address'] = $shipping_address;

                }
            }

            $order->update_meta_data( '_credo_txn_ref', $txnref );
            $order->save();
        }

        wp_localize_script( 'wc_credo', 'wc_credo_params', $credo_params );

    }

    public function admin_scripts() {

        if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
            return;
        }

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        $credo_admin_params = array(
            'plugin_url' => WC_CREDO_URL,
        );

        wp_enqueue_script( 'wc_credo_admin', plugins_url( 'assets/js/credo-admin' . $suffix . '.js', WC_CREDO_MAIN_FILE ), array(), WC_CREDO_VERSION, true );

        wp_localize_script( 'wc_credo_admin', 'wc_credo_admin_params', $credo_admin_params );

    }

    /**
     * Process the payment.
     *
     * @param int $order_id
     *
     * @return array|void
     */
    public function process_payment( $order_id ) {

        if ( 'redirect' === $this->payment_page ) {

            return $this->process_redirect_payment_option( $order_id );

        }else {

            $order = wc_get_order($order_id);

            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true),
            );
        }

    }

    /**
     * Process a redirect payment option payment.
     *
     * @param int $order_id
     *
     * @return array|void
     * @since 5.7
     */
    public function process_redirect_payment_option( $order_id ) {

        $order        = wc_get_order( $order_id );
        $amount       = $order->get_total() * 100;
        $txnref       = $order_id . '_' . time();
        $callback_url = WC()->api_request_url( 'WC_Gateway_Credo' );

        $payment_channels = $this->get_gateway_payment_channels( $order );


        $credo_params = array(
            'amount'              => $amount,
            'email'               => $order->get_billing_email(),
            'currency'            => $order->get_currency(),
            'reference'           => $txnref,
            'callbackUrl'         => $callback_url,
            'customerFirstName'   => $order->get_billing_first_name(),
            'customerLastName'    => $order->get_billing_last_name(),
            'customerPhoneNumber' => $order->get_billing_phone(),


        );

        if ( ! empty( $payment_channels ) ) {
            $credo_params['channels'] = $payment_channels;
        }

        $credo_params['bearer'] = $this->charges_bearer === 'customer' ? 0 : 1;


        if ( $this->split_payment && !empty( $this->service_code ) ) {
            $credo_params['serviceCode'] = $this->service_code;
        }

        $credo_params['metadata']['custom_fields'] = $this->get_custom_fields( $order_id );
        $credo_params['metadata']['cancel_action'] = wc_get_cart_url();

        $order->update_meta_data( '_credo_txn_ref', $txnref );
        $order->save();

        $credo_url = $this->base_url . '/transaction/initialize';

        $headers = array(
            'Authorization' => $this->public_key,
            'Content-Type'  => 'application/json',
        );

        $args = array(
            'headers' => $headers,
            'timeout' => 60,
            'body'    => json_encode( $credo_params ),
        );

        $request = wp_remote_post( $credo_url, $args );

        if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

            $credo_response = json_decode( wp_remote_retrieve_body( $request ) );


            return array(
                'result'   => 'success',
                'redirect' => $credo_response->data->authorizationUrl,
            );

        } else {
            wc_add_notice( __( 'Unable to process payment try again', 'woo-credo' ), 'error' );

            return;
        }

    }


    /**
     * Show new card can only be added when placing an order notice.
     */
    public function add_payment_method() {

        wc_add_notice( __( 'You can only add a new card when placing an order.', 'woo-credo' ), 'error' );

        return;

    }

    /**
     * Displays the payment page.
     *
     * @param $order_id
     */
    public function receipt_page( $order_id ) {

        $order = wc_get_order( $order_id );

        echo '<div id="wc-credo-form">';

        echo '<p>' . __( 'Thank you for your order, please click the button below to pay with Credo.', 'woo-credo' ) . '</p>';

        echo '<div id="credo_form"><form id="order_review" method="post" action="' . WC()->api_request_url( 'WC_Gateway_Credo' ) . '"></form><button class="button" id="credo-payment-button">' . __( 'Pay Now', 'woo-credo' ) . '</button>';

        if ( ! $this->remove_cancel_order_button ) {
            echo '  <a class="button cancel" id="credo-cancel-payment-button" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woo-credo' ) . '</a></div>';
        }

        echo '</div>';

    }

    /**
     * Verify Credo payment.
     */
    public function verify_credo_transaction() {

        if ( isset( $_REQUEST['transRef'] ) ) {
            $credo_txn_ref = sanitize_text_field( $_REQUEST['transRef'] );
        } elseif ( isset( $_REQUEST['reference'] ) ) {
            $credo_txn_ref = sanitize_text_field( $_REQUEST['reference'] );
        } else {
            $credo_txn_ref = false;
        }

        @ob_clean();

        if ( $credo_txn_ref ) {

            $credo_response = $this->get_credo_transaction( $credo_txn_ref );

            if ( false !== $credo_response ) {

                if ( 0 == $credo_response->data->status ) {

                    $order_details = explode( '_', $credo_response->data->businessRef );
                    $order_id      = (int) $order_details[0];
                    $order         = wc_get_order( $order_id );

                    if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

                        wp_redirect( $this->get_return_url( $order ) );

                        exit;

                    }

                    $order_total      = $order->get_total();
                    $order_currency   = $order->get_currency();
                    $currency_symbol  = get_woocommerce_currency_symbol( $order_currency );
                    $amount_paid      = $credo_response->data->transAmount;
                    $credo_ref        = $credo_response->data->transRef;
                    $payment_currency = strtoupper( $credo_response->data->currencyCode );
                    $gateway_symbol   = get_woocommerce_currency_symbol( $payment_currency );

                    // check if the amount paid is equal to the order amount.
                    if ( $amount_paid < $order_total ) {

                        $order->update_status( 'on-hold', '' );

                        $order->add_meta_data( '_transaction_id', $credo_ref, true );

                        $notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-credo' ), '<br />', '<br />', '<br />' );
                        $notice_type = 'notice';

                        // Add Customer Order Note
                        $order->add_order_note( $notice, 1 );

                        // Add Admin Order Note
                        $admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Credo Transaction Reference:</strong> %9$s', 'woo-credo' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $credo_ref );
                        $order->add_order_note( $admin_order_note );

                        function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

                        wc_add_notice( $notice, $notice_type );

                    } else {

                        if ( $payment_currency !== $order_currency ) {

                            $order->update_status( 'on-hold', '' );

                            $order->update_meta_data( '_transaction_id', $credo_ref );

                            $notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-credo' ), '<br />', '<br />', '<br />' );
                            $notice_type = 'notice';

                            // Add Customer Order Note
                            $order->add_order_note( $notice, 1 );

                            // Add Admin Order Note
                            $admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Credo Transaction Reference:</strong> %9$s', 'woo-credo' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $credo_ref );
                            $order->add_order_note( $admin_order_note );

                            function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

                            wc_add_notice( $notice, $notice_type );

                        } else {

                            $order->payment_complete( $credo_ref );
                            $order->add_order_note( sprintf( __( 'Payment via Credo successful (Transaction Reference: %s)', 'woo-credo' ), $credo_ref ) );

                            if ( $this->is_autocomplete_order_enabled( $order ) ) {
                                $order->update_status( 'completed' );
                            }
                        }
                    }

                    $order->save();


                    WC()->cart->empty_cart();

                } else {

                    $order_details = explode( '_', $_REQUEST['reference'] );

                    $order_id = (int) $order_details[0];

                    $order = wc_get_order( $order_id );

                    $order->update_status( 'failed', __( 'Payment was declined by Credo.', 'woo-credo' ) );

                }
            }

            wp_redirect( $this->get_return_url( $order ) );

            exit;
        }

        wp_redirect( wc_get_page_permalink( 'cart' ) );

        exit;

    }

    /**
     * Process Webhook.
     */
    public function process_webhooks() {

        if ( ! array_key_exists( 'HTTP_X_CREDO_SIGNATURE', $_SERVER ) || ( strtoupper( $_SERVER['REQUEST_METHOD'] ) !== 'POST' ) ) {
            exit;
        }

        $json = file_get_contents( 'php://input' );

        // validate event do all at once to avoid timing attack.
        if ( $_SERVER['HTTP_X_CREDO_SIGNATURE'] !== hash_hmac( 'sha512', $json, $this->secret_key ) ) {
            exit;
        }

        $event = json_decode( $json );

        if ( 'transaction.success' !== strtolower( $event->event ) ) {
            return;
        }

        sleep( 10 );

        $credo_response = $this->get_credo_transaction( $event->data->transRef );

        if ( false === $credo_response ) {
            return;
        }

        $order_details = explode( '_', $credo_response->data->reference );

        $order_id = (int) $order_details[0];

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        $credo_txn_ref = $order->get_meta( '_credo_txn_ref' );

        if ( $credo_response->data->reference != $credo_txn_ref ) {
            exit;
        }

        http_response_code( 200 );

        if ( in_array( strtolower( $order->get_status() ), array( 'processing', 'completed', 'on-hold' ), true ) ) {
            exit;
        }

        $order_currency = $order->get_currency();

        $currency_symbol = get_woocommerce_currency_symbol( $order_currency );

        $order_total = $order->get_total();

        $amount_paid = $credo_response->data->amount / 100;

        $credo_ref = $credo_response->data->transRef;

        $payment_currency = strtoupper( $credo_response->data->currency );

        $gateway_symbol = get_woocommerce_currency_symbol( $payment_currency );

        // check if the amount paid is equal to the order amount.
        if ( $amount_paid < $order_total ) {

            $order->update_status( 'on-hold', '' );

            $order->add_meta_data( '_transaction_id', $credo_ref, true );

            $notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-credo' ), '<br />', '<br />', '<br />' );
            $notice_type = 'notice';

            // Add Customer Order Note.
            $order->add_order_note( $notice, 1 );

            // Add Admin Order Note.
            $admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Credo Transaction Reference:</strong> %9$s', 'woo-credo' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $credo_ref );
            $order->add_order_note( $admin_order_note );

            function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

            wc_add_notice( $notice, $notice_type );

            WC()->cart->empty_cart();

        } else {

            if ( $payment_currency !== $order_currency ) {

                $order->update_status( 'on-hold', '' );

                $order->update_meta_data( '_transaction_id', $credo_ref );

                $notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-credo' ), '<br />', '<br />', '<br />' );
                $notice_type = 'notice';

                // Add Customer Order Note.
                $order->add_order_note( $notice, 1 );

                // Add Admin Order Note.
                $admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Credo Transaction Reference:</strong> %9$s', 'woo-credo' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $credo_ref );
                $order->add_order_note( $admin_order_note );

                function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

                wc_add_notice( $notice, $notice_type );

            } else {

                $order->payment_complete( $credo_ref );

                $order->add_order_note( sprintf( __( 'Payment via Credo successful (Transaction Reference: %s)', 'woo-credo' ), $credo_ref ) );

                WC()->cart->empty_cart();

                if ( $this->is_autocomplete_order_enabled( $order ) ) {
                    $order->update_status( 'completed' );
                }
            }
        }

        $order->save();


        exit;
    }


    /**
     * Get custom fields to pass to Credo.
     *
     * @param int $order_id WC Order ID
     *
     * @return array
     */
    public function get_custom_fields( $order_id ) {

        $order = wc_get_order( $order_id );

        $custom_fields = array();

        $custom_fields[] = array(
            'display_name'  => 'Plugin',
            'variable_name' => 'plugin',
            'value'         => 'woo-credo',
        );

        if ( $this->custom_metadata ) {

            if ( $this->meta_order_id ) {

                $custom_fields[] = array(
                    'display_name'  => 'Order ID',
                    'variable_name' => 'order_id',
                    'value'         => $order_id,
                );

            }

            if ( $this->meta_name ) {

                $custom_fields[] = array(
                    'display_name'  => 'Customer Name',
                    'variable_name' => 'customer_name',
                    'value'         => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                );

            }

            if ( $this->meta_email ) {

                $custom_fields[] = array(
                    'display_name'  => 'Customer Email',
                    'variable_name' => 'customer_email',
                    'value'         => $order->get_billing_email(),
                );

            }

            if ( $this->meta_phone ) {

                $custom_fields[] = array(
                    'display_name'  => 'Customer Phone',
                    'variable_name' => 'customer_phone',
                    'value'         => $order->get_billing_phone(),
                );

            }

            if ( $this->meta_products ) {

                $line_items = $order->get_items();

                $products = '';

                foreach ( $line_items as $item_id => $item ) {
                    $name     = $item['name'];
                    $quantity = $item['qty'];
                    $products .= $name . ' (Qty: ' . $quantity . ')';
                    $products .= ' | ';
                }

                $products = rtrim( $products, ' | ' );

                $custom_fields[] = array(
                    'display_name'  => 'Products',
                    'variable_name' => 'products',
                    'value'         => $products,
                );

            }

            if ( $this->meta_billing_address ) {

                $billing_address = $order->get_formatted_billing_address();
                $billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

                $credo_params['meta_billing_address'] = $billing_address;

                $custom_fields[] = array(
                    'display_name'  => 'Billing Address',
                    'variable_name' => 'billing_address',
                    'value'         => $billing_address,
                );

            }

            if ( $this->meta_shipping_address ) {

                $shipping_address = $order->get_formatted_shipping_address();
                $shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

                if ( empty( $shipping_address ) ) {

                    $billing_address = $order->get_formatted_billing_address();
                    $billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

                    $shipping_address = $billing_address;

                }
                $custom_fields[] = array(
                    'display_name'  => 'Shipping Address',
                    'variable_name' => 'shipping_address',
                    'value'         => $shipping_address,
                );

            }

        }

        return $custom_fields;
    }


    /**
     * Checks if WC version is less than passed in version.
     *
     * @param string $version Version to check against.
     *
     * @return bool
     */
    public function is_wc_lt( $version ) {
        return version_compare( WC_VERSION, $version, '<' );
    }

    /**
     * Checks if autocomplete order is enabled for the payment method.
     *
     * @param WC_Order $order Order object.
     *
     * @return bool
     * @since 5.7
     */
    protected function is_autocomplete_order_enabled( $order ) {
        $autocomplete_order = false;

        $payment_method = $order->get_payment_method();

        $credo_settings = get_option( 'woocommerce_' . $payment_method . '_settings' );

        if ( isset( $credo_settings['autocomplete_order'] ) && 'yes' === $credo_settings['autocomplete_order'] ) {
            $autocomplete_order = true;
        }

        return $autocomplete_order;
    }

    /**
     * Retrieve the payment channels configured for the gateway
     *
     * @param WC_Order $order Order object.
     *
     * @return array
     * @since 5.7
     */
    protected function get_gateway_payment_channels( $order ) {

        $payment_method = $order->get_payment_method();

        if ( 'credo' === $payment_method ) {
            return array();
        }

        $payment_channels = $this->payment_channels;

        if ( empty( $payment_channels ) ) {
            $payment_channels = array( 'card' );
        }

        return $payment_channels;
    }

    /**
     * Retrieve a transaction from Credo.
     *
     * @param $credo_txn_ref
     *
     * @return false|mixed
     * @since 5.7.5
     */
    private function get_credo_transaction( $credo_txn_ref ) {

        $credo_url = $this->base_url . '/transaction/' . $credo_txn_ref . '/verify';

        $headers = array(
            'Authorization' => $this->secret_key,
        );

        $args = array(
            'headers' => $headers,
            'timeout' => 60,
        );

        $request = wp_remote_get( $credo_url, $args );

        if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {
            return json_decode( wp_remote_retrieve_body( $request ) );
        }

        return false;
    }


    public function get_logo_url() {

        $base_location = wc_get_base_location();


            $url = WC_HTTPS::force_https_url( plugins_url( 'assets/images/credo-wc.png', WC_CREDO_MAIN_FILE ) );


        return apply_filters( 'wc_paystack_gateway_icon_url', $url, $this->id );
    }
}
