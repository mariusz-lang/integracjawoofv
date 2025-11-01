<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WPDesk\WooCommerceFakturownia\Emails\EmailInvoiceProForma;
use WPDesk\WooCommerceFakturownia\Forms\Integration\BillForm;
use WPDesk\WooCommerceFakturownia\Metadata\InvoiceProFormaMetadata;

/**
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class InvoiceProForma extends Invoice {
	const TYPE_NAME = 'fakturownia-invoice-proforma';

	const META_DATA_NAME = '_woo_fakturownia_faktura_proforma';

	const META_DATA_TYPE = 'faktura';

	const ORDER_STATUS_ON_HOLD = 'on-hold';

	const EMAIL_SLUG = 'woo_fakturownia_faktura_proforma';

	/**
	 * Prepare Document Metadata.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 * @return \FakturowniaVendor\WPDesk\Invoices\Metadata\DocumentMetadata
	 */
	public function prepareDocumentMetadata( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		return new InvoiceProFormaMetadata( $metadata_content );
	}

	/**
	 * Get email class;
	 *
	 * @return string
	 */
	public function getEmailClass() {
		return self::EMAIL_SLUG;
	}

	public function isAllowedForOrder( $order ) {
		return wc_tax_enabled();
	}
}
