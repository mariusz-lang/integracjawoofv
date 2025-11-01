<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WPDesk\WooCommerceFakturownia\Api\InvoicesException;
use WPDesk\WooCommerceFakturownia\Metadata\InvoiceCorrectionMetadata;

/**
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class InvoiceCorrection extends Invoice {
	const TYPE_NAME      = 'fakturownia-invoice-correction';
	const META_DATA_NAME = '_woo_fakturownia_faktura_korekta';
	const META_DATA_TYPE = 'faktura-korekta';

	const EMAIL_SLUG = 'woo_fakturownia_faktura_korekta';

	/**
	 * Prepare Document Metadata.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 *
	 * @return \FakturowniaVendor\WPDesk\Invoices\Metadata\DocumentMetadata
	 */
	public function prepareDocumentMetadata( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		return new InvoiceCorrectionMetadata( $metadata_content );
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
		return $integration->get_api()->get_invoice_pdf( (int) $document_id )->getResponseBody()['pdf'];
	}

	/**
	 * Is allowed for order.
	 *
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAllowedForOrder( $order ) {
		return apply_filters( 'fakturownia/ui/correction_button', false );
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
