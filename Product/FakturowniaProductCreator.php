<?php

namespace WPDesk\WooCommerceFakturownia\Product;

use FakturowniaVendor\Psr\Http\Client\RequestExceptionInterface;
use WPDesk\WooCommerceFakturownia\Api\FakturowniaApi;
use WPDesk\WooCommerceFakturownia\Helpers\WooCommerceSettings;

/**
 * Creates product in Fakturownia.
 */
class FakturowniaProductCreator {

	private FakturowniaApi $fakturownia_api;

	private WooCommerceSettings $woocommerce_settings;

	public function __construct( FakturowniaApi $fakturownia_api, WooCommerceSettings $woocommerce_settings ) {
		$this->fakturownia_api      = $fakturownia_api;
		$this->woocommerce_settings = $woocommerce_settings;
	}

	/**
	 * Create from Woocommerce product.
	 *
	 * @param \WC_Product $product .
	 *
	 * @return array
	 * @throws RequestExceptionInterface .
	 */
	public function create_from_product( \WC_Product $product ) {
		$sku       = $product->get_sku();
		$image_url = '';
		if ( $product->get_image_id() ) {
			$image_src = wp_get_attachment_image_src( $product->get_image_id() );
			$image_url = $image_src[0];
		}

		$price_raw = (float) $product->get_regular_price();

		$price_net   = $price_raw;
		$price_gross = $price_raw;
		$tax_rate    = 0;

		if ( $this->woocommerce_settings->taxes_enabled() ) {
			$tax_class = $product->get_tax_class();
			$tax_rates = \WC_Tax::get_rates( $tax_class );

			if ( ! empty( $tax_rates ) ) {
				$rate     = array_shift( $tax_rates );
				$tax_rate = (float) $rate['rate'];
			}

			if ( $this->woocommerce_settings->prices_include_taxes() ) {
				$price_gross = $price_raw;
				$price_net   = wc_get_price_excluding_tax( $product, [ 'price' => $price_raw ] );
			} else {
				$price_net   = $price_raw;
				$price_gross = wc_get_price_including_tax( $product, [ 'price' => $price_raw ] );
			}
		}

		$prices = [
			'price_net'   => $price_net,
			'price_gross' => $price_gross,
		];

		$product_create_response = $this->fakturownia_api->create_product(
			apply_filters( 'fakturownia/core/create_product/name', $product->get_name(), $product ),
			apply_filters( 'fakturownia/core/create_product/sku', $sku, $product ),
			apply_filters( 'fakturownia/core/create_product/prices', $prices, $product ),
			apply_filters( 'fakturownia/core/create_product/tax', $tax_rate, $product ),
			apply_filters( 'fakturownia/core/create_product/description', $product->get_description(), $product ),
			apply_filters( 'fakturownia/core/create_product/img_url', $image_url, $product )
		);

		return $product_create_response->get_product();
	}

	/**
	 * Set fakturownia product id on WooCommerce product.
	 *
	 * @param \WC_Product $product                .
	 * @param string      $fakturownia_product_id .
	 */
	public function set_fakturownia_product_id_on_product( \WC_Product $product, $fakturownia_product_id ) {
		$product->update_meta_data( FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID, $fakturownia_product_id );
		$product->save();
	}
}
