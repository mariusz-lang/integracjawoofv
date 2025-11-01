<?php

namespace WPDesk\WooCommerceFakturownia\Webhoks;

use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\WooCommerceFakturownia\Helpers\WooCommerceSettings;
use WPDesk\WooCommerceFakturownia\Product\FakturowniaProductId;
use WPDesk\WooCommerceFakturownia\WoocommerceIntegration;

class ProductUpdateListener implements Hookable {

	private const API_TOKEN = 'api_token';

	public const URL_PARAMETER = 'fakturownia-stock';

	private string $url_parameter;

	private string $webhook_token;

	private WooCommerceSettings $woocommerce_settings;

	private WoocommerceIntegration $woocommerce_integration;

	public function __construct( WoocommerceIntegration $woocommerce_integration, WooCommerceSettings $woocommerce_settings ) {
		$this->url_parameter           = self::URL_PARAMETER;
		$this->woocommerce_integration = $woocommerce_integration;
		$this->webhook_token           = $woocommerce_integration->getOptionWarehouseWebhookToken();
		$this->woocommerce_settings    = $woocommerce_settings;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'init', [ $this, 'listen' ] );
	}

	/**
	 * Is valid token?
	 *
	 * @param array $request_data .
	 *
	 * @return bool
	 */
	private function is_valid_token( array $request_data ) {
		if ( isset( $request_data[ self::API_TOKEN ] ) && $request_data[ self::API_TOKEN ] === $this->webhook_token ) {
			return true;
		}
		return false;
	}

	/**
	 * Return product IDs based on fakturownia product id.
	 *
	 * @param string $fakturownia_product_id Product ID.
	 *
	 * @return int[]
	 */
	public function get_product_ids_fakturownia_product_id( $fakturownia_product_id ) {
		global $wpdb;

		$ids_results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"
				SELECT posts.ID
				FROM {$wpdb->posts} as posts
				INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
				WHERE posts.post_type IN ( 'product', 'product_variation' )
				AND posts.post_status != 'trash'
				AND postmeta.meta_key = %s
				AND postmeta.meta_value = %s
				",
				FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID,
				$fakturownia_product_id
			)
		);

		$ids = [];
		foreach ( $ids_results as $id_result ) {
			$ids[] = $id_result->ID;
		}

		return $ids;
	}

	/**
	 * Update stock level for given products IDs.
	 *
	 * @param array $product_ids .
	 * @param float $stock_level .
	 */
	private function update_stock_for_products_ids( array $product_ids, $stock_level ) {
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			$product->set_stock_quantity( $stock_level );
			$product->save();
		}
	}

	private function update_price_for_products_ids( array $product_ids, string $price_to_set ) {
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			$product->set_regular_price( $price_to_set );
			$product->save();
		}
	}


	/**
	 * Update stock for product.
	 *
	 * @param array $product_data .
	 */
	private function update_stock_for_product( array $product_data ) {
		$product_ids = $this->get_product_ids_fakturownia_product_id( $product_data['external_ids']['fakturownia'] );
		$this->update_stock_for_products_ids( $product_ids, floatval( $product_data['stock_level'] ) );
		if ( ! empty( $product_data['code'] ) ) {
			$product_id = wc_get_product_id_by_sku( $product_data['code'] );
			if ( $product_id ) {
				$product_ids = [ $product_id ];
				$this->update_stock_for_products_ids( $product_ids, floatval( $product_data['stock_level'] ) );
			}
		}
	}

	private function update_price_for_product( array $product_data ) {
		$product_ids = $this->get_product_ids_fakturownia_product_id( $product_data['external_ids']['fakturownia'] );

		$new_price = $product_data['price_net'];

		if ( $this->woocommerce_settings->taxes_enabled() && $this->woocommerce_settings->prices_include_taxes() ) {
			$new_price = $product_data['price_gross'];
		}

		if ( $new_price <= 0 ) {
			return;
		}

		$this->update_price_for_products_ids( $product_ids, (string) $new_price );
		if ( ! empty( $product_data['code'] ) ) {
			$product_id = wc_get_product_id_by_sku( $product_data['code'] );
			if ( $product_id ) {
				$product_ids = [ $product_id ];
				$this->update_price_for_products_ids( $product_ids, (string) $new_price );
			}
		}
	}

	/**
	 * Listen.
	 */
	public function listen() {
		if ( ! isset( $_GET[ $this->url_parameter ] ) || '1' !== $_GET[ $this->url_parameter ] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$raw_request_data = file_get_contents( 'php://input' );
		$request_data     = json_decode( $raw_request_data, true );
		if ( is_array( $request_data ) && $this->is_valid_token( $request_data ) ) {
			do_action( 'fakturownia/sync/before_webhook_update', $request_data );
			if ( isset( $request_data['product'] ) && is_array( $request_data['product'] ) ) {
				if ( $this->woocommerce_integration->is_price_sync_enabled() ) {
					$this->update_price_for_product( $request_data['product'] );
				}
				if ( $this->woocommerce_integration->is_warehouse_enabled() ) {
					$this->update_stock_for_product( $request_data['product'] );
				}
			}
			do_action( 'fakturownia/sync/after_webhook_update', $request_data );
		}
	}
}
