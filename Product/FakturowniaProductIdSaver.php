<?php

namespace WPDesk\WooCommerceFakturownia\Product;

use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Handles product meta data save for Fakturownia product ID.
 */
class FakturowniaProductIdSaver implements Hookable {

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_field' ] );
		add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_field' ], 10, 2 );
	}

	/**
	 * Save product field.
	 *
	 * @param int $post_id .
	 */
	public function save_product_field( $post_id ) {
		$fakturownia_product_id = isset( $_POST[ FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID ] ) ? sanitize_text_field( wp_unslash( $_POST[ FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID ] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing

		$product = wc_get_product( $post_id );
		$product->update_meta_data( FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID, $fakturownia_product_id );
		$product->save();
	}

	/**
	 * Save variation field.
	 *
	 * @param int $variation_id .
	 * @param int $i            .
	 */
	public function save_variation_field( $variation_id, $i ) {
		$product_name = isset( $_POST[ FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID_VARIATION ][ $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID_VARIATION ][ $i ] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing

		$variation = wc_get_product( $variation_id );
		$variation->update_meta_data( FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID, $product_name );
		$variation->save();
	}
}
