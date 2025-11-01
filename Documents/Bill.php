<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WPDesk\WooCommerceFakturownia\Api\InvoicesException;
use WPDesk\WooCommerceFakturownia\Emails\EmailBill;
use WPDesk\WooCommerceFakturownia\Forms\Integration\BillForm;
use WPDesk\WooCommerceFakturownia\Metadata\BillMetadata;
use WPDesk\WooCommerceFakturownia\WoocommerceIntegration;

/**
 * Class InvoiceDocument
 *
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class Bill extends DocumentType {
	const TYPE_NAME = 'fakturownia-bill';

	const META_DATA_NAME = '_woo_fakturownia_rachunek';

	const META_DATA_TYPE = 'rachunek';

	const EMAIL_SLUG = 'woo_fakturownia_rachunek';

	/**
	 * Prepare Document Metadata.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 * @return \FakturowniaVendor\WPDesk\Invoices\Metadata\DocumentMetadata
	 */
	public function prepareDocumentMetadata( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		return new BillMetadata( $metadata_content );
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
		if ( wc_tax_enabled() ) {
			return false;
		} else {
			if ( $this->getIntegration()->woocommerce_integration->getOptionDocumentType() !== BillForm::OPTION_DOCUMENT_TYPE_BILL ) {
				return false;
			}
			return true;
		}
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
