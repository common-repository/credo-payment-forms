jQuery( function( $ ) {

	let credo_submit = false;

	$( '#wc-credo-form' ).hide();


	wcCredoFormHandler();

	jQuery( '#credo-payment-button' ).click( function() {
		return wcCredoFormHandler();
	} );

	jQuery( '#credo_form form#order_review' ).submit( function() {
		return wcCredoFormHandler();
	} );

	function wcCredoCustomFields() {

		let custom_fields = [
			{
				"display_name": "Plugin",
				"variable_name": "plugin",
				"value": "woo-credo"
			}
		];

		if ( wc_credo_params.meta_order_id ) {

			custom_fields.push( {
				display_name: "Order ID",
				variable_name: "order_id",
				value: wc_credo_params.meta_order_id
			} );

		}

		if ( wc_credo_params.meta_name ) {

			custom_fields.push( {
				display_name: "Customer Name",
				variable_name: "customer_name",
				value: wc_credo_params.meta_name
			} );
		}

		if ( wc_credo_params.meta_email ) {

			custom_fields.push( {
				display_name: "Customer Email",
				variable_name: "customer_email",
				value: wc_credo_params.meta_email
			} );
		}

		if ( wc_credo_params.meta_phone ) {

			custom_fields.push( {
				display_name: "Customer Phone",
				variable_name: "customer_phone",
				value: wc_credo_params.meta_phone
			} );
		}

		if ( wc_credo_params.meta_billing_address ) {

			custom_fields.push( {
				display_name: "Billing Address",
				variable_name: "billing_address",
				value: wc_credo_params.meta_billing_address
			} );
		}

		if ( wc_credo_params.meta_shipping_address ) {

			custom_fields.push( {
				display_name: "Shipping Address",
				variable_name: "shipping_address",
				value: wc_credo_params.meta_shipping_address
			} );
		}

		if ( wc_credo_params.meta_products ) {

			custom_fields.push( {
				display_name: "Products",
				variable_name: "products",
				value: wc_credo_params.meta_products
			} );
		}

		return custom_fields;
	}

	function wcCredoCustomFilters() {

		let custom_filters = {};

		if ( wc_credo_params.card_channel ) {

			if ( wc_credo_params.banks_allowed ) {

				custom_filters[ 'banks' ] = wc_credo_params.banks_allowed;

			}

			if ( wc_credo_params.cards_allowed ) {

				custom_filters[ 'card_brands' ] = wc_credo_params.cards_allowed;
			}

		}

		return custom_filters;
	}

	function wcPaymentChannels() {

		let payment_channels = [];

		if ( wc_credo_params.bank_channel ) {
			payment_channels.push( 'bank' );
		}

		if ( wc_credo_params.card_channel ) {
			payment_channels.push( 'card' );
		}



		return payment_channels;
	}

	function wcCredoFormHandler() {

		$( '#wc-credo-form' ).hide();

		if ( credo_submit ) {
			credo_submit = false;
			return true;
		}

		let $form = $( 'form#payment-form, form#order_review' );

		let amount = Number( wc_credo_params.amount );
		let sericeCode = wc_credo_params.serviceCode===null || wc_credo_params.serviceCode === undefined ? null : wc_credo_params.serviceCode;
		let credo_callback = function( transaction ) {
			$form.append( '<input type="hidden" class="credo_txnref" name="transRef" value="' + transaction.transRef + '"/>' );
			credo_submit = true;

			$form.submit();

			$( 'body' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				},
				css: {
					cursor: "wait"
				}
			} );
		};

		let paymentData = {
			key: wc_credo_params.key,
			email: wc_credo_params.email,
			amount: amount,
			reference: wc_credo_params.txnref,
			customerFirstName: wc_credo_params.meta_first_name,
			customerLastName: wc_credo_params.meta_last_name,
			customerPhoneNumber: wc_credo_params.meta_phone,
			currency: wc_credo_params.currency,
			bearer: Number(wc_credo_params.bearer),
			serviceCode: sericeCode,
			metadata: {
				custom_fields: wcCredoCustomFields(),
			},
			callBack: credo_callback,
			onCancel: () => {
				$( '#wc-credo-form' ).show();
				$( this.el ).unblock();
			}
		};

		if ( Array.isArray( wcPaymentChannels() ) && wcPaymentChannels().length ) {
			paymentData[ 'channels' ] = wcPaymentChannels();
			if ( !$.isEmptyObject( wcCredoCustomFilters() ) ) {
				paymentData[ 'metadata' ][ 'custom_filters' ] = wcCredoCustomFilters();
			}
		}


		const credo = CredoWidget.setup(paymentData);

		credo.openIframe()
	}

} );