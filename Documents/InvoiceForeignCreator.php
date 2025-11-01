<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WC_Order;
use WPDesk\WooCommerceFakturownia\Data\DocumentData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceForeignData;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;
use WPDesk\WooCommerceFakturownia\Data\InvoiceOrderDefaults;

/**
 * Class InvoiceForeignCreator
 *
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class InvoiceForeignCreator extends InvoiceCreator {

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
		return InvoiceForeignData::createFromOrderAndDefaultsAndSettings(
			$order,
			$vat_number_field,
			$defaults,
			$integration
		);
	}

	/**
	 * Validate currency.
	 *
	 * @param DocumentData $data Data.
	 */
	protected function validate_currency( DocumentData $data ) {
		return true;
	}

	/**
	 * Is allowed for order.
	 *
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAllowedForOrder( $order ) {
		return wc_tax_enabled() && $order->get_currency() !== 'PLN';
	}
}
