<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent;
use WC_Order;
use WPDesk\WooCommerceFakturownia\Data\InvoiceData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceProFormaData;
use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;
use WPDesk\WooCommerceFakturownia\Forms\Integration\ReceiptForm;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;
use WPDesk\WooCommerceFakturownia\Data\InvoiceOrderDefaults;

/**
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class InvoiceProformaCreator extends InvoiceCreator {

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
		return InvoiceProFormaData::createFromOrderAndDefaultsAndSettings(
			$order,
			$vat_number_field,
			$defaults,
			$integration
		);
	}

	public function isAutoCreateAllowedForOrder( $order ) {
		$statuses     = $this->type->getIntegration()->woocommerce_integration->getOptionGenerateProformaStatus();
		$order_status = 'wc-' . $order->get_status();
		if ( ! is_array( $statuses ) ) {
			$statuses = [];
		}

		return in_array( $order_status, $statuses, true );
	}

	/**
	 * Is allowed for order.
	 *
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAllowedForOrder( $order ) {
		return wc_tax_enabled();
	}
}
