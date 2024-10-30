jQuery( function( $ ) {
	'use strict';

	/**
	 * Object to handle Credo admin functions.
	 */
	var wc_credo_admin = {
		/**
		 * Initialize.
		 */
		init: function() {

			// Toggle api key settings.
			$( document.body ).on( 'change', '#woocommerce_credo_testmode', function() {
				var test_secret_key = $( '#woocommerce_credo_test_secret_key' ).parents( 'tr' ).eq( 0 ),
					test_public_key = $( '#woocommerce_credo_test_public_key' ).parents( 'tr' ).eq( 0 ),
					live_secret_key = $( '#woocommerce_credo_live_secret_key' ).parents( 'tr' ).eq( 0 ),
					live_public_key = $( '#woocommerce_credo_live_public_key' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {
					test_secret_key.show();
					test_public_key.show();
					live_secret_key.hide();
					live_public_key.hide();
				} else {
					test_secret_key.hide();
					test_public_key.hide();
					live_secret_key.show();
					live_public_key.show();
				}
			} );

			$( '#woocommerce_credo_testmode' ).change();

			$( document.body ).on( 'change', '.woocommerce_credo_split_payment', function() {
				var subaccount_code = $( '.woocommerce_credo_subaccount_code' ).parents( 'tr' ).eq( 0 ),
					subaccount_charge = $( '.woocommerce_credo_split_payment_charge_account' ).parents( 'tr' ).eq( 0 ),
					transaction_charge = $( '.woocommerce_credo_split_payment_transaction_charge' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {
					subaccount_code.show();
					subaccount_charge.show();
					transaction_charge.show();
				} else {
					subaccount_code.hide();
					subaccount_charge.hide();
					transaction_charge.hide();
				}
			} );

			$( '#woocommerce_credo_split_payment' ).change();

			// Toggle Custom Metadata settings.
			$( '.wc-credo-metadata' ).change( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '.wc-credo-meta-order-id, .wc-credo-meta-name, .wc-credo-meta-email, .wc-credo-meta-phone, .wc-credo-meta-billing-address, .wc-credo-meta-shipping-address, .wc-credo-meta-products' ).closest( 'tr' ).show();
				} else {
					$( '.wc-credo-meta-order-id, .wc-credo-meta-name, .wc-credo-meta-email, .wc-credo-meta-phone, .wc-credo-meta-billing-address, .wc-credo-meta-shipping-address, .wc-credo-meta-products' ).closest( 'tr' ).hide();
				}
			} ).change();

			// Toggle Bank filters settings.
			$( '.wc-credo-payment-channels' ).on( 'change', function() {

				var channels = $( ".wc-credo-payment-channels" ).val();

				if ( $.inArray( 'card', channels ) != '-1' ) {
					$( '.wc-credo-cards-allowed' ).closest( 'tr' ).show();
					$( '.wc-credo-banks-allowed' ).closest( 'tr' ).show();
				}
				else {
					$( '.wc-credo-cards-allowed' ).closest( 'tr' ).hide();
					$( '.wc-credo-banks-allowed' ).closest( 'tr' ).hide();
				}

			} ).change();

			$( ".wc-credo-payment-icons" ).select2( {
				templateResult: formatCredoPaymentIcons,
				templateSelection: formatCredoPaymentIconDisplay
			} );

			$( '#woocommerce_credo_test_secret_key, #woocommerce_credo_live_secret_key' ).after(
				'<button class="wc-credo-toggle-secret" style="height: 30px; margin-left: 2px; cursor: pointer"><span class="dashicons dashicons-visibility"></span></button>'
			);

			$( '.wc-credo-toggle-secret' ).on( 'click', function( event ) {
				event.preventDefault();

				let $dashicon = $( this ).closest( 'button' ).find( '.dashicons' );
				let $input = $( this ).closest( 'tr' ).find( '.input-text' );
				let inputType = $input.attr( 'type' );

				if ( 'text' == inputType ) {
					$input.attr( 'type', 'password' );
					$dashicon.removeClass( 'dashicons-hidden' );
					$dashicon.addClass( 'dashicons-visibility' );
				} else {
					$input.attr( 'type', 'text' );
					$dashicon.removeClass( 'dashicons-visibility' );
					$dashicon.addClass( 'dashicons-hidden' );
				}
			} );
		}
	};

	function formatCredoPaymentIcons( payment_method ) {
		if ( !payment_method.id ) {
			return payment_method.text;
		}

		var $payment_method = $(
			'<span><img src=" ' + wc_credo_admin_params.plugin_url + '/assets/images/' + payment_method.element.value.toLowerCase() + '.png" class="img-flag" style="height: 15px; weight:18px;" /> ' + payment_method.text + '</span>'
		);

		return $payment_method;
	};

	function formatCredoPaymentIconDisplay( payment_method ) {
		return payment_method.text;
	};

	wc_credo_admin.init();

} );
