<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WC_Order;
use WPDesk\WooCommerceFakturownia\Data\InvoiceCorrectionData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceData;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;
use WPDesk\WooCommerceFakturownia\Data\InvoiceOrderDefaults;

/**
 * Class InvoiceCorrectionCreator
 *
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class InvoiceCorrectionCreator extends InvoiceCreator {

	/**
	 * Create from order.
	 *
	 * @param WC_Order                                                $order            Order.
	 * @param \FakturowniaVendor\WPDesk\Invoices\Field\VatNumber|null $vat_number_field Vat number field.
	 * @param InvoiceOrderDefaults                                    $defaults         Defaults.
	 * @param InvoicesIntegration                                     $integration      Integration.
	 *
	 * @return InvoiceData
	 */
	protected function createFromOrderAndDefaultsAndSettings(
		WC_Order $order,
		$vat_number_field,
		InvoiceOrderDefaults $defaults,
		InvoicesIntegration $integration
	) {
		return InvoiceCorrectionData::createFromOrderAndDefaultsAndSettings(
			$order,
			$vat_number_field,
			$defaults,
			$integration
		);
	}

	public function hooks() {
		if ( $this->type->getIntegration()->woocommerce_integration->isAutoCorrectionIssue() ) {
			add_action( 'woocommerce_order_partially_refunded', [ $this, 'generate_correction_for_refund' ], 1, 2 );
			add_action( 'woocommerce_order_fully_refunded', [ $this, 'generate_correction_for_refund' ], 1, 2 );
		}
	}

	protected function maybe_throw_document_already_exists_exception( $order, $overwrite_existing ) {
		/*multiple corrections can be created for one order, therefore we don't need to throw exception here. Do nothing.*/
	}

	public function isAutoCreateAllowedForOrder( $order ) {
		return false;
	}
	public function isAllowedForOrder( $order ) {
		return true;
	}

	public function generate_correction_for_refund( int $order_id ): void {
		$order = wc_get_order( $order_id );
		$this->createDocumentForOrder( $order, [], false );
	}
}
