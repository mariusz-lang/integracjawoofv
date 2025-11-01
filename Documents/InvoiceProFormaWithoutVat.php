<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WPDesk\WooCommerceFakturownia\Api\InvoicesException;
use WPDesk\WooCommerceFakturownia\Emails\EmailInvoiceProForma;
use WPDesk\WooCommerceFakturownia\Forms\Integration\BillForm;
use WPDesk\WooCommerceFakturownia\Metadata\InvoiceProFormaWithoutVatMetadata;
use WPDesk\WooCommerceFakturownia\WoocommerceIntegration;

/**
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class InvoiceProFormaWithoutVat extends DocumentType {
	const TYPE_NAME = 'fakturownia-invoice-proforma-without-vat';

	const META_DATA_NAME = '_woo_fakturownia_faktura_proforma_without_vat';

	const META_DATA_TYPE = 'faktura';

	const EMAIL_SLUG = 'woo_fakturownia_faktura_proforma';

	/**
	 * Prepare Document Metadata.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 *
	 * @return \FakturowniaVendor\WPDesk\Invoices\Metadata\DocumentMetadata
	 */
	public function prepareDocumentMetadata( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		return new InvoiceProFormaWithoutVatMetadata( $metadata_content );
	}

	/**
	 * Is allowed for order.
	 *
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAllowedForOrder( $order ) {
		if ( ! wc_tax_enabled() && $this->getIntegration()->woocommerce_integration->getOptionDocumentType() === BillForm::OPTION_DOCUMENT_TYPE_INVOICE_WITHOUT_TAX ) {
			return true;
		}

		return false;
	}

	/**
	 * Get email class;
	 *
	 * @return string
	 */
	public function getEmailClass() {
		return self::EMAIL_SLUG;
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
}
