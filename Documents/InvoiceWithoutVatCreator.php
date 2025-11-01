<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WPDesk\WooCommerceFakturownia\Data\InvoiceData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceOrderDefaults;
use WPDesk\WooCommerceFakturownia\Data\InvoiceWithoutVatData;
use WPDesk\WooCommerceFakturownia\Forms\Integration\BillForm;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;

/**
 * Class InvoiceWithoutVatCreator
 *
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class InvoiceWithoutVatCreator extends InvoiceCreator {

	/**
	 * Create from order.
	 *
	 * @param \WC_Order                                               $order            Order.
	 * @param \FakturowniaVendor\WPDesk\Invoices\Field\VatNumber|null $vat_number_field Vat number field.
	 * @param InvoiceOrderDefaults                                    $defaults         Defaults.
	 * @param InvoicesIntegration                                     $integration      Integration.
	 *
	 * @return InvoiceData
	 */
	protected function createFromOrderAndDefaultsAndSettings(
		\WC_Order $order,
		$vat_number_field,
		InvoiceOrderDefaults $defaults,
		InvoicesIntegration $integration
	) {
		return InvoiceWithoutVatData::createFromOrderAndDefaultsAndSettings(
			$order,
			$vat_number_field,
			$defaults,
			$integration
		);
	}

	/**
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAutoCreateAllowedForOrder( $order ) {
		$integration   = $this->type->getIntegration();
		$document_type = $integration->woocommerce_integration->getOptionDocumentType();
		if ( $document_type !== BillForm::OPTION_DOCUMENT_TYPE_INVOICE_WITHOUT_TAX ) {
			return false;
		}

		return parent::isAutoCreateAllowedForOrder( $order );
	}

	/**
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAllowedForOrder( $order ) {
		if ( ! wc_tax_enabled() ) {
			return true;
		}

		return false;
	}
}
