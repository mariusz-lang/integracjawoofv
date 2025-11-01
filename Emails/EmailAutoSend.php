<?php

namespace WPDesk\WooCommerceFakturownia\Emails;

use FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent;
use WPDesk\WooCommerceFakturownia\Documents\Bill;
use WPDesk\WooCommerceFakturownia\Documents\DocumentType;
use WPDesk\WooCommerceFakturownia\Documents\Invoice;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceCorrection;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceProforma;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceForeign;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceProFormaWithoutVat;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceWithoutVat;
use WPDesk\WooCommerceFakturownia\Documents\Receipt;
use WPDesk\WooCommerceFakturownia\Forms\Integration\BillForm;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;

/**
 * Handle automatic email send on document create.
 */
class EmailAutoSend {

	/**
	 * Document type.
	 *
	 * @var DocumentType
	 */
	private $type;

	/**
	 * @param DocumentType $type Document type.
	 */
	public function __construct( $type ) {
		$this->type = $type;
	}

	/**
	 * Get emails.
	 *
	 * @return array
	 */
	private function get_emails() {
		return WC()->mailer()->get_emails();
	}

	/**
	 * Should send email?\
	 *
	 * @param InvoicesIntegration $integration Integration.
	 *
	 * @return bool
	 */
	private function should_send_email( InvoicesIntegration $integration ) {
		$send_email = false;
		if ( wc_tax_enabled() ) {
			if ( $this->type->getTypeName() === Invoice::TYPE_NAME && 'yes' === $integration->woocommerce_integration->getOptionAutoSendInvoice() ) {
				$send_email = true;
			}

			if ( $this->type->getTypeName() === InvoiceProForma::TYPE_NAME && 'yes' === $integration->woocommerce_integration->getOptionAutoSendInvoiceProforma() ) {
				$send_email = true;
			}

			if ( $this->type->getTypeName() === Receipt::TYPE_NAME && 'yes' === $integration->woocommerce_integration->getOptionAutoSendReceipt() ) {
				$send_email = true;
			}
			if ( $this->type->getTypeName() === Receipt::TYPE_NAME && 'yes' === $integration->woocommerce_integration->getOptionAutoSendReceipt() ) {
				$send_email = true;
			}
			if ( $this->type->getTypeName() === InvoiceForeign::TYPE_NAME && 'yes' === $integration->woocommerce_integration->getOptionAutoSendInvoice() ) {
				$send_email = true;
			}
			if ( $this->type->getTypeName() === InvoiceCorrection::TYPE_NAME && $integration->woocommerce_integration->isAutoCorrectionIssue() ) {
				$send_email = true;
			}
		} else {
			if ( BillForm::OPTION_DOCUMENT_TYPE_BILL === $integration->woocommerce_integration->getOptionDocumentType()
				&& 'yes' === $integration->woocommerce_integration->getOptionAutoSendBill()
				&& $this->type->getTypeName() === Bill::TYPE_NAME
			) {
				$send_email = true;
			}
			if ( BillForm::OPTION_DOCUMENT_TYPE_INVOICE_WITHOUT_TAX === $integration->woocommerce_integration->getOptionDocumentType()
				&& 'yes' === $integration->woocommerce_integration->getOptionAutoSendInvoice()
				&& $this->type->getTypeName() === InvoiceWithoutVat::TYPE_NAME
			) {
				$send_email = true;
			}
			if ( $this->type->getTypeName() === Receipt::TYPE_NAME && 'yes' === $integration->woocommerce_integration->getOptionAutoSendReceipt() ) {
				$send_email = true;
			}
			if ( $this->type->getTypeName() === InvoiceProFormaWithoutVat::TYPE_NAME && 'yes' === $integration->woocommerce_integration->getOptionAutoSendInvoiceProforma() ) {
				$send_email = true;
			}
			if ( $this->type->getTypeName() === InvoiceCorrection::TYPE_NAME && $integration->woocommerce_integration->isAutoCorrectionIssue() ) {
				$send_email = true;
			}
		}

		return $send_email;
	}

	/**
	 * Maybe send email after document create.
	 *
	 * @param \WC_Order $order Order.
	 * @param string $download_hash Download hash string.
	 */
	public function maybe_send_email( $order, $download_hash ) {
		$integration = $this->type->getIntegration();

		if ( $this->should_send_email( $integration ) ) {
			$email_class = $this->type->getEmailClass();
			$emails      = $this->get_emails();
			if ( ! empty( $emails ) && ! empty( $emails[ $email_class ] ) ) {
				$meta_data_name    = $this->type->getMetaDataName();
				$metadata_content  = new MetadataContent( $meta_data_name, $order );
				$document_pdf      = $integration->getDocumentPdf( $metadata_content );
				$document_metadata = $this->type->prepareDocumentMetadata( $metadata_content );
				$number            = $document_metadata->getNumber();
				$file_name         = str_replace(
					[ ' ', '/' ],
					'_',
					sprintf( '%1$s_%2$s.pdf', $document_metadata->getTypeName(), $number )
				);

				$download_url = add_query_arg(
					[
						'order_id'         => $order->get_id(),
						'type'             => $this->type->getMetaDataName(),
						'invoice_download' => $download_hash,
					],
					get_site_url()
				);

				$emails[ $email_class ]->trigger( $order, $document_pdf, $file_name, $download_url );
			}
		}
	}
}
