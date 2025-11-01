<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent;
use WC_Order;
use WPDesk\WooCommerceFakturownia\Api\DocumentGetResponseJson;
use WPDesk\WooCommerceFakturownia\Api\Invoice\PostResponseJson;
use WPDesk\WooCommerceFakturownia\Api\InvoicesException;
use WPDesk\WooCommerceFakturownia\Data\DocumentData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceProFormaWithoutVatData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceWithoutVatData;
use WPDesk\WooCommerceFakturownia\Forms\Integration\BillForm;
use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;
use WPDesk\WooCommerceFakturownia\Data\InvoiceOrderDefaults;

/**
 * Class InvoiceProFormaWithoutVatCreator
 *
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class InvoiceProformaWithoutVatCreator extends InvoiceProformaCreator {

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
		return InvoiceProFormaWithoutVatData::createFromOrderAndDefaultsAndSettings(
			$order,
			$vat_number_field,
			$defaults,
			$integration
		);
	}

	public function isAllowedForOrder( $order ) {
		if ( ! wc_tax_enabled() ) {
			return true;
		}

		return false;
	}
}
