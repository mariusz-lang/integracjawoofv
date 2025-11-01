<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WPDesk\WooCommerceFakturownia\Api\InvoicesException;
use WPDesk\WooCommerceFakturownia\Emails\EmailInvoiceForeign;
use WPDesk\WooCommerceFakturownia\Metadata\InvoiceForeignMetadata;
use WPDesk\WooCommerceFakturownia\Metadata\InvoiceMetadata;
use WPDesk\WooCommerceFakturownia\Metadata\InvoiceProFormaMetadata;

/**
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class InvoiceForeign extends Invoice {
	const TYPE_NAME = 'fakturownia-invoice-foreign';

	const META_DATA_NAME = '_woo_fakturownia_faktura_walutowa';

	const META_DATA_TYPE = 'faktura';

	const EMAIL_SLUG = 'woo_fakturownia_faktura_foreign';

	/**
	 * Prepare Document Metadata.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 *
	 * @return \FakturowniaVendor\WPDesk\Invoices\Metadata\DocumentMetadata
	 */
	public function prepareDocumentMetadata( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		return new InvoiceForeignMetadata( $metadata_content );
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

	/**
	 * Get email class;
	 *
	 * @return string
	 */
	public function getEmailClass() {
		return self::EMAIL_SLUG;
	}
}
