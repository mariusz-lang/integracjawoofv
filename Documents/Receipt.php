<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WPDesk\WooCommerceFakturownia\Api\InvoicesException;
use WPDesk\WooCommerceFakturownia\Emails\EmailReceipt;
use WPDesk\WooCommerceFakturownia\Metadata\ReceiptMetadata;

/**
 * Class Receipt
 *
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class Receipt extends Invoice {
	const TYPE_NAME = 'fakturownia-receipt';

	const META_DATA_NAME = '_woo_fakturownia_paragon';

	const META_DATA_TYPE = 'paragon';


	const EMAIL_SLUG = 'woo_fakturownia_receipt';

	/**
	 * Prepare Document Metadata.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 * @return \FakturowniaVendor\WPDesk\Invoices\Metadata\DocumentMetadata
	 */
	public function prepareDocumentMetadata( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		return new ReceiptMetadata( $metadata_content );
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
	 * Is allowed for order.
	 *
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAllowedForOrder( $order ) {
		return true;
	}
}
