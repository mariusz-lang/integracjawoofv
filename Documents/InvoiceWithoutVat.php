<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WPDesk\WooCommerceFakturownia\Forms\Integration\BillForm;
use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;
use WPDesk\WooCommerceFakturownia\Metadata\InvoiceWithoutVatMetadata;
use WPDesk\WooCommerceFakturownia\WoocommerceIntegration;

/**
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class InvoiceWithoutVat extends Invoice {
	const TYPE_NAME = 'fakturownia-invoice-without-vat';

	const META_DATA_NAME = '_woo_fakturownia_faktura';

	const META_DATA_TYPE = 'faktura_bez_vat';

	const EMAIL_SLUG = 'woo_fakturownia_faktura';

	/**
	 * Prepare Document Metadata.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 * @return \FakturowniaVendor\WPDesk\Invoices\Metadata\DocumentMetadata
	 */
	public function prepareDocumentMetadata( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		return new InvoiceWithoutVatMetadata( $metadata_content );
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
}
