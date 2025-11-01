<?php

namespace WPDesk\WooCommerceFakturownia\Block\VatNumber;

use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Exception;
use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FakturowniaVendor\WPDesk\WooCommerce\EUVAT\Integration\ValidateOSS;


class DataStore implements Hookable {

	/**
	 * @var ValidateOSS
	 */
	private $vies_validator;

	public function __construct( ValidateOSS $vies_validator ) {
		$this->vies_validator = $vies_validator;
		$this->extend_rest_api();
	}

	/**
	 * Initialization.
	 */
	public function hooks() {
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', [ $this, 'update_order_from_request' ], 10, 2 );
	}

	public function update_order_from_request( \WC_Order $order, \WP_REST_Request $request ) {
		$vat_number           = WC()->session->get( 'vat_number' );
		$data                 = $request['extensions']['woocommerce-eu-vat-number'];
		$country              = $order->get_billing_country();
		$is_from_base_country = $this->vies_validator->get_shop_settings()->is_customer_from_base_country( $country );
		if ( ! $is_from_base_country ) {
			if ( $this->vies_validator->get_shop_settings()->get_ip_country() === $country || $data['location_confirmation'] ) {
				$order->update_meta_data( '_customer_ip_country', $this->vies_validator->get_shop_settings()->get_ip_country() );
				$order->update_meta_data( '_customer_self_declared_country', isset( $data['location_confirmation'] ) ? 'true' : 'false' );
			}

			if ( ! empty( $vat_number ) && $data['vat_confirmation'] ) {
				$validate = $this->validate();
				$order->update_meta_data( '_vat_number', $vat_number );
				if ( ! $validate['validation']['valid'] ) {
					$order->update_meta_data( '_vat_number_self_declared', $data['vat_confirmation'] ? 'true' : 'false' );
				}
				$order->update_meta_data( '_vat_number_is_validated', 'true' );
				$order->update_meta_data( '_vat_number_is_valid', $validate['validation']['valid'] ? 'true' : 'false' );
			}
		}
	}

	/**
	 * @throws Exception
	 */
	public function extend_rest_api() {
		$extend = StoreApi::container()->get( ExtendSchema::class );
		$extend->register_update_callback(
			[
				'namespace' => BlocksIntegration::BLOCK_NAME,
				'callback'  => function ( $data ) {
					$vat_number = $data['billing_vat_number'];
					WC()->session->set( 'invoice_ask', $data['billing_invoice_ask'] );
					WC()->session->set( 'vat_number', $vat_number );
					if ( $data['validate_oss'] ) {
						WC()->session->set( 'validate_oss', true );
						if ( empty( $vat_number ) ) {
							WC()->session->set( 'vat_number', null );
							WC()->customer->set_is_vat_exempt( false );
						} else {
							WC()->session->set( 'vat_number', strtoupper( $vat_number ) );
							$this->validate();
						}
					} else {
						WC()->session->set( 'validate_oss', false );
					}
				},
			]
		);

		$extend->register_endpoint_data(
			[
				'endpoint'      => CartSchema::IDENTIFIER,
				'namespace'     => BlocksIntegration::BLOCK_NAME,
				'data_callback' => [ $this, 'vat_number_information' ],
				'schema_type'   => ARRAY_A,
			]
		);

		$extend->register_endpoint_data(
			[
				'endpoint'        => CheckoutSchema::IDENTIFIER,
				'namespace'       => BlocksIntegration::BLOCK_NAME,
				'schema_callback' => function () {
					return [
						'location_confirmation' => [
							'description' => __( 'Location confirmation.', 'woocommerce-fakturownia' ),
							'type'        => [ 'boolean', 'null' ],
							'context'     => [ 'view', 'edit' ],
						],
						'vat_confirmation'      => [
							'description' => __( 'Vat confirmation.', 'woocommerce-fakturownia' ),
							'type'        => [ 'boolean', 'null' ],
							'context'     => [ 'view', 'edit' ],
						],
					];
				},
				'data_callback'   => function () {
					return [
						'location_confirmation' => false,
						'vat_confirmation'      => false,
						'validate_oss'          => false,
						'billing_invoice_ask'   => false,
						'billing_vat_number'    => '',
					];
				},
				'schema_type'     => ARRAY_A,
			]
		);
	}

	/**
	 * Information about the status of the given VAT Number.
	 *
	 * @return array Information about the validity of the VAT Number.
	 */
	public function vat_number_information(): array {
		return $this->validate();
	}

	public function set_data( $vat_number, $invoice_ask, $valid, $error ): array {
		$data['billing_vat_number']  = $vat_number;
		$data['billing_invoice_ask'] = $invoice_ask;
		$data['validation']          = [
			'valid' => $valid,
			'error' => $error,
		];

		return $data;
	}

	/**
	 * Checks if VAT number is formatted correctly.
	 *
	 * @return array Information about the result of the validation.
	 */
	public function validate(): array {
		$validate_oss = WC()->session->get( 'validate_oss' );
		$vat_number   = WC()->session->get( 'vat_number' );
		$invoice_ask  = WC()->session->get( 'invoice_ask' );

		if ( ! $validate_oss ) {
			return $this->set_data( $vat_number, $invoice_ask, false, '' );
		}

		if ( empty( $vat_number ) ) {
			WC()->customer->set_is_vat_exempt( false );

			return $this->set_data( '', $invoice_ask, false, false );
		}

		$billing_country  = WC()->customer->get_billing_country();
		$shipping_country = $billing_country;

		try {
			$is_valid = $this->vies_validator->should_exempt_vat_for_b2b( $vat_number, false, $billing_country, $shipping_country, false );
			if ( $is_valid ) {
				return $this->set_data( $vat_number, $invoice_ask, true, false );
			} else {
				return $this->set_data( $vat_number, $invoice_ask, false, false );
			}
		} catch ( Exception $e ) {
			return $this->set_data( $vat_number, $invoice_ask, false, $e->getMessage() );
		}
	}
}
