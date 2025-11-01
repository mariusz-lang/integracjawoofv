<?php

namespace WPDesk\WooCommerceFakturownia\Product;

use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\WooCommerceFakturownia\Api\FakturowniaApi;
use WPDesk\WooCommerceFakturownia\WoocommerceIntegration;

/**
 * Handle AJAX for fakturownia product select field.
 */
class FakturowniaProductIdAjax implements Hookable {

	/**
	 * API.
	 *
	 * @var FakturowniaApi
	 */
	private $fakturownia_api;

	/**
	 * FakturowniaProductIdAjax constructor.
	 *
	 * @param FakturowniaApi $fakturownia_api .
	 */
	public function __construct( FakturowniaApi $fakturownia_api ) {
		$this->fakturownia_api = $fakturownia_api;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'wp_ajax_fakturownia_product_id', [ $this, 'get_fakturownia_products' ] );
	}

	/**
	 * Handle AJAX request.
	 *
	 * @throws \WPDesk\HttpClient\HttpClientRequestException .
	 */
	public function get_fakturownia_products() {
		check_ajax_referer( 'search-products', 'security' );

		if ( isset( $_GET['term'] ) ) {
			$term = (string) wc_clean( wp_unslash( $_GET['term'] ) );
		}

		if ( empty( $term ) ) {
			wp_die();
		}
		$products = [];
		try {
			$fakturownia_products_query_response = $this->fakturownia_api->query_products( $term );

			$fakturownia_products = $fakturownia_products_query_response->get_products();

			foreach ( $fakturownia_products as $fakturownia_product ) {
				$products[ $fakturownia_product['id'] ] = rawurldecode( sprintf( '%1$s (#%2$s)', $fakturownia_product['name'], $fakturownia_product['id'] ) );
			}
		} catch ( \Exception $e ) { //phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// do nothing
		}

		wp_send_json( $products );
	}
}
