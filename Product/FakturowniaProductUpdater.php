<?php

namespace WPDesk\WooCommerceFakturownia\Product;

use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\WooCommerceFakturownia\Api\FakturowniaApi;
use WPDesk\WooCommerceFakturownia\Api\Products\Exceptions\UpdatingProductException;
use WPDesk\WooCommerceFakturownia\Helpers\WooCommerceSettings;
use WPDesk\WooCommerceFakturownia\Services\TransientNotice;

class FakturowniaProductUpdater implements Hookable {

	private FakturowniaApi $api;

	private WooCommerceSettings $woocommerce_settings;

	public function __construct( FakturowniaApi $api, WooCommerceSettings $woocommerce_settings ) {
		$this->api                  = $api;
		$this->woocommerce_settings = $woocommerce_settings;
	}

	public function hooks() {
		add_action( 'woocommerce_process_product_meta', [ $this, 'sync_on_product_save' ], 20 );
		add_action( 'woocommerce_save_product_variation', [ $this, 'sync_on_variation_save' ], 20, 2 );
		add_action( 'admin_notices', [ $this, 'maybe_display_transient_notice' ] );
	}

	public function sync_on_product_save( $product_id ) {
		$product = wc_get_product( $product_id );

		if ( ! $product || $product->is_type( 'variable' ) ) {
			return;
		}

		$this->push_product_data_to_fakturownia( $product );
	}

	public function sync_on_variation_save( $variation_id, $i ) {
		$variation = wc_get_product( $variation_id );

		if ( ! $variation ) {
			return;
		}

		$this->push_product_data_to_fakturownia( $variation );
	}

	private function push_product_data_to_fakturownia( \WC_Product $product ) {
		$fakturownia_id = $product->get_meta( '_fakturownia_product_id' );

		try {
			if ( empty( $fakturownia_id ) ) {
				throw new UpdatingProductException( esc_html__( 'Product is not connected to Fakturownia warehouse. Please connect the product to enable synchronization.', 'woocommerce-fakturownia' ) );
			}

			$regular_price = $product->get_regular_price();

			$payload = [
				'price_gross' => (float) ( $this->woocommerce_settings->prices_include_taxes() ? $regular_price : wc_get_price_including_tax( $product ) ),
			];

			if ( $this->woocommerce_settings->taxes_enabled() ) {
				$tax_value      = $this->resolve_product_tax_rate_percent( $product );
				$payload['tax'] = $tax_value;
			} else {
				$payload['tax'] = 0;
			}

			$response = $this->api->update_product( $fakturownia_id, $payload );

			if ( $response->isError() ) {
				// translators: %s errors from response
				throw new UpdatingProductException( sprintf( esc_html__( 'Could not connect with Fakturownia\'s API while trying to update product: %s', 'woocommerce-fakturownia' ), $response->get_error_info() ) );
			}

			$response_code = $response->getResponseCode();
			if ( $response_code >= 200 && $response_code < 300 ) {
				return true;
			} else {
				// translators: %s errors from response
				throw new UpdatingProductException( sprintf( esc_html__( 'Error occured while trying to update: %s', 'woocommerce-fakturownia' ), $response->get_error_info() ) );
			}
		} catch ( UpdatingProductException $e ) {
			$this->add_transient_notice( $e->getMessage() );
			return false;
		}
	}

	private function resolve_product_tax_rate_percent( \WC_Product $product ): float {
		if ( $product->get_tax_status() !== 'taxable' ) {
			return 0.0;
		}

		$tax_class = $product->get_tax_class();
		$rates     = \WC_Tax::get_rates( $tax_class );

		if ( empty( $rates ) ) {
			return 0.0;
		}

		$total_percent = 0.0;
		foreach ( $rates as $rate ) {
			if ( isset( $rate['rate'] ) ) {
				$total_percent += (float) $rate['rate'];
			}
		}

		return round( $total_percent, 2 );
	}

	private function add_transient_notice( string $message ): void {
		$transient_notice = new TransientNotice();
		$transient_notice->set_admin_notice( $message );
	}

	public function maybe_display_transient_notice(): void {
		$transient_notice = new TransientNotice();
		$transient_notice->display_admin_notices();
	}
}
