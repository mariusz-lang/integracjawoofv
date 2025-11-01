<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WPDesk\WooCommerceFakturownia\Api\InvoicesException;
use WPDesk\WooCommerceFakturownia\Emails\EmailInvoice;
use WPDesk\WooCommerceFakturownia\Metadata\InvoiceMetadata;

/**
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class Invoice extends DocumentType {
	const TYPE_NAME = 'fakturownia-invoice';

	const META_DATA_NAME = '_woo_fakturownia_faktura';

	const META_DATA_TYPE = 'faktura';

	const EMAIL_SLUG = 'woo_fakturownia_faktura';

	/**
	 * Prepare Document Metadata.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 * @return \FakturowniaVendor\WPDesk\Invoices\Metadata\DocumentMetadata
	 */
	public function prepareDocumentMetadata( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		return new InvoiceMetadata( $metadata_content );
	}

	/**
	 * Get document Pdf.
	 *
	 * @param string $document_id Document ID.
	 *
	 * @return string
	 * @throws \WPDesk\HttpClient\HttpClientRequestException HTTP Exception.
	 * @throws InvoicesException API Exception.
	 */
	public function getDocumentPdf( $document_id ) {
		$integration = $this->getIntegration();
		return $integration->get_api()->get_invoice_pdf( intval( $document_id ) )->getResponseBody()['pdf'];
	}

	/**
	 * Is allowed for order.
	 *
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAllowedForOrder( $order ) {
		return wc_tax_enabled() && $order->get_currency() === 'PLN';
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
