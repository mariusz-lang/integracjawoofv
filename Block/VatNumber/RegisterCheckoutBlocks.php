<?php

namespace WPDesk\WooCommerceFakturownia\Block\VatNumber;

use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;
use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FakturowniaVendor\WPDesk\WooCommerce\EUVAT\Settings\Settings;
use FakturowniaVendor\WPDesk\WooCommerce\EUVAT\Settings\ShopSettings;
use FakturowniaVendor\WPDesk_Plugin_Info;


class RegisterCheckoutBlocks implements Hookable {

	/**
	 * @var WPDesk_Plugin_Info
	 */
	private $plugin_info;

	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var ShopSettings
	 */
	private $shop_settings;

	public function __construct( WPDesk_Plugin_Info $plugin_info, Settings $settings, ShopSettings $shop_settings ) {
		$this->plugin_info   = $plugin_info;
		$this->settings      = $settings;
		$this->shop_settings = $shop_settings;
	}

	public function hooks() {
		add_action( 'woocommerce_blocks_checkout_block_registration', [ $this, 'register_block' ] );
		add_action( 'woocommerce_store_api_checkout_update_order_meta', [ $this, 'update_order_data_from_request' ], 5, 2 );
		$this->extend_rest_api();
	}

	public function extend_rest_api() {
		if ( $this->settings->eu_vat_vies_validate ) {
			return;
		}

		$extend = StoreApi::container()->get( ExtendSchema::class );
		$extend->register_update_callback(
			[
				'namespace' => BlocksIntegration::BLOCK_NAME,
				'callback'  => function ( $data ) {
					WC()->session->set( 'invoice_ask', $data['billing_invoice_ask'] );
					WC()->session->set( 'vat_number', $data['billing_vat_number'] );
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
	}

	public function vat_number_information(): array {
		return [
			'billing_invoice_ask' => WC()->session->get( 'invoice_ask' ),
			'billing_vat_number'  => WC()->session->get( 'vat_number' ),
			'validation'          => [
				'valid' => false,
				'error' => false,
			],
		];
	}

	/**
	 * Save Vat Number for order.
	 *
	 * @param \WC_Order $order
	 * @param           $request
	 *
	 * @return void
	 */
	public function update_order_data_from_request( $order ) {
		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		$invoice_ask = WC()->session->get( 'invoice_ask' );
		$vat_number  = WC()->session->get( 'vat_number' );
		$order->update_meta_data( '_billing_nip', $vat_number );
		$order->update_meta_data( '_billing_faktura', (int) $invoice_ask );
		$order->save();
	}

	public function register_block( $integration_registry ) {
		$integration_registry->register( new BlocksIntegration( $this->plugin_info, $this->get_vies_settings() ) );
	}

	public function get_vies_settings(): array {
		$settings         = get_option( 'woocommerce_integration-fakturownia_settings' );
		$generate_invoice = $settings['generate_invoice'] ?? '';
		$shop_country     = $this->get_shop_country();
		$eu_countries     = $this->get_eu_countries( $shop_country );

		return [
			'ip_country'          => $this->shop_settings->get_ip_country(),
			'ip_address'          => \WC_Geolocation::get_ip_address(),
			'eu_countries'        => $eu_countries,
			'shop_country'        => $shop_country,
			'is_eu_vat_enabled'   => $this->settings->eu_vat_vies_validate ? 'yes' : 'no',
			'input_label'         => esc_html__( 'Vat Number', 'woocommerce-fakturownia' ),
			'input_description'   => esc_html__( 'Enter the correct VAT number for your country.', 'woocommerce-fakturownia' ),
			'failure_handler'     => $this->settings->eu_vat_failure_handling,
			'validate_ip_country' => $this->settings->moss_validate_ip ? 'yes' : 'no',
			'generate_invoice'    => $generate_invoice,
		];
	}

	private function get_eu_countries( $shop_country ): array {
		$eu_countries = array_flip( $this->shop_settings->get_eu_countries() );
		if ( isset( $eu_countries[ $shop_country ] ) ) {
			unset( $eu_countries[ $shop_country ] );
		}

		return array_values( array_flip( $eu_countries ) );
	}

	private function get_shop_country(): string {
		$shop_country = get_option( 'woocommerce_default_country' );
		if ( strpos( ':', $shop_country ) ) {
			$shop_country = explode( ':', $shop_country );
			$shop_country = $shop_country[0];
		}

		return $shop_country;
	}
}
