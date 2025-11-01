<?php

namespace WPDesk\WooCommerceFakturownia;

use FakturowniaVendor\WPDesk\Invoices\Ajax\AjaxGetPdfHandler;
use FakturowniaVendor\WPDesk\Invoices\Order\OrderDocumentView;
use WPDesk\WooCommerceFakturownia\Documents\BillCreator;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceCorrection;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceCorrectionCreator;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceCreator;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceForeignCreator;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceProformaCreator;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceProFormaWithoutVat;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceProformaWithoutVatCreator;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceWithoutVat;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceWithoutVatCreator;
use WPDesk\WooCommerceFakturownia\Documents\Bill;
use WPDesk\WooCommerceFakturownia\Documents\Invoice;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceForeign;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceProForma;
use WPDesk\WooCommerceFakturownia\Documents\Receipt;
use WPDesk\WooCommerceFakturownia\Documents\ReceiptCreator;
use WPDesk\WooCommerceFakturownia\Metabox\MetaBoxFields;

class SupportedDocuments {

	/**
	 * @var InvoicesIntegration
	 */
	private $integration;

	/**
	 * @var AjaxGetPdfHandler
	 */
	private $ajax_get_pdf_handler;

	/**
	 * SupportedDocuments constructor.
	 *
	 * @param InvoicesIntegration $integration
	 * @param AjaxGetPdfHandler   $ajax_get_pdf_handler
	 */
	public function __construct( InvoicesIntegration $integration, AjaxGetPdfHandler $ajax_get_pdf_handler ) {
		$this->integration          = $integration;
		$this->ajax_get_pdf_handler = $ajax_get_pdf_handler;
	}

	/**
	 * Add support for invoice
	 */
	public function add_support_for_invoice() {
		$invoice_metabox_fields = new MetaBoxFields(
			Invoice::TYPE_NAME,
			$this->integration
		);

		$this->add_common_invoice_fields_to_metabox( $invoice_metabox_fields );
		$invoice = new Invoice(
			Invoice::TYPE_NAME,
			Invoice::META_DATA_NAME,
			Invoice::META_DATA_TYPE,
			__( 'Wystaw fakturę', 'woocommerce-fakturownia' ),
			__( 'parametry', 'woocommerce-fakturownia' ),
			$invoice_metabox_fields,
			$this->integration
		);
		new InvoiceCreator( $invoice );
		$invoice->setDocumentView( new OrderDocumentView( $this->ajax_get_pdf_handler, $this->integration->get_renderer() ) );
		$this->integration->addSupportedDocumentType( $invoice );
	}

	/**
	 * Add support for invoice correction
	 */
	public function add_support_for_correction() {
		$invoice_metabox_fields = new MetaBoxFields(
			InvoiceCorrection::TYPE_NAME,
			$this->integration
		);
		$invoice                = new InvoiceCorrection(
			InvoiceCorrection::TYPE_NAME,
			InvoiceCorrection::META_DATA_NAME,
			InvoiceCorrection::META_DATA_TYPE,
			__( 'Wystaw korektę', 'woocommerce-fakturownia' ),
			'',
			$invoice_metabox_fields,
			$this->integration
		);
		( new InvoiceCorrectionCreator( $invoice ) )->hooks();
		$invoice->setDocumentView( new OrderDocumentView( $this->ajax_get_pdf_handler, $this->integration->get_renderer() ) );
		$this->integration->addSupportedDocumentType( $invoice );
	}

	/**
	 * Add support for invoice without VAT
	 */
	public function add_support_for_invoice_without_vat() {
		$invoice_metabox_fields = new MetaBoxFields(
			InvoiceWithoutVat::TYPE_NAME,
			$this->integration
		);

		$this->add_common_invoice_fields_to_metabox( $invoice_metabox_fields );

		$invoice = new InvoiceWithoutVat(
			InvoiceWithoutVat::TYPE_NAME,
			InvoiceWithoutVat::META_DATA_NAME,
			InvoiceWithoutVat::META_DATA_TYPE,
			__( 'Wystaw fakturę bez VAT', 'woocommerce-fakturownia' ),
			__( 'parametry', 'woocommerce-fakturownia' ),
			$invoice_metabox_fields,
			$this->integration
		);
		new InvoiceWithoutVatCreator( $invoice );
		$invoice->setDocumentView( new OrderDocumentView( $this->ajax_get_pdf_handler, $this->integration->get_renderer() ) );
		$this->integration->addSupportedDocumentType( $invoice );
	}

	/**
	 * Add support for invoice proforma
	 */
	public function add_support_for_invoice_proforma() {
		$invoice_proforma_metabox_fields = new MetaBoxFields(
			InvoiceProForma::TYPE_NAME,
			$this->integration
		);

		$this->add_proforma_invoice_fields_to_metabox( $invoice_proforma_metabox_fields );
		$invoice_proforma = new InvoiceProForma(
			InvoiceProForma::TYPE_NAME,
			InvoiceProForma::META_DATA_NAME,
			InvoiceProForma::META_DATA_TYPE,
			__( 'Wystaw pro formę', 'woocommerce-fakturownia' ),
			__( 'parametry', 'woocommerce-fakturownia' ),
			$invoice_proforma_metabox_fields,
			$this->integration
		);

		new InvoiceProformaCreator( $invoice_proforma );
		$invoice_proforma->setDocumentView( new OrderDocumentView( $this->ajax_get_pdf_handler, $this->integration->get_renderer() ) );
		$this->integration->addSupportedDocumentType( $invoice_proforma );
	}

	/**
	 * Add support for proforma invoice without VAT
	 */
	public function add_support_for_invoice_proforma_without_vat() {
		$invoice_metabox_fields = new MetaBoxFields(
			InvoiceProFormaWithoutVat::TYPE_NAME,
			$this->integration
		);

		$this->add_proforma_invoice_fields_to_metabox( $invoice_metabox_fields );
		$invoice = new InvoiceProFormaWithoutVat(
			InvoiceProFormaWithoutVat::TYPE_NAME,
			InvoiceProFormaWithoutVat::META_DATA_NAME,
			InvoiceProFormaWithoutVat::META_DATA_TYPE,
			__( 'Wystaw pro formę bez VAT', 'woocommerce-fakturownia' ),
			__( 'parametry', 'woocommerce-fakturownia' ),
			$invoice_metabox_fields,
			$this->integration
		);
		new InvoiceProformaWithoutVatCreator( $invoice );
		$invoice->setDocumentView( new OrderDocumentView( $this->ajax_get_pdf_handler, $this->integration->get_renderer() ) );
		$this->integration->addSupportedDocumentType( $invoice );
	}

	/**
	 * Add support for bill
	 */
	public function add_support_for_bill() {
		$bill_metabox_fields = new MetaBoxFields(
			Bill::TYPE_NAME,
			$this->integration
		);
		$this->add_common_invoice_fields_to_metabox( $bill_metabox_fields );
		$bill = new Bill(
			Bill::TYPE_NAME,
			Bill::META_DATA_NAME,
			Bill::META_DATA_TYPE,
			__( 'Wystaw rachunek', 'woocommerce-fakturownia' ),
			__( 'parametry', 'woocommerce-fakturownia' ),
			$bill_metabox_fields,
			$this->integration
		);
		new BillCreator( $bill );
		$bill->setDocumentView( new OrderDocumentView( $this->ajax_get_pdf_handler, $this->integration->get_renderer() ) );
		$this->integration->addSupportedDocumentType( $bill );
	}

	/**
	 * Add support for receipt
	 */
	public function add_support_for_receipt() {
		$receipt_metabox_fields = new MetaBoxFields(
			Receipt::TYPE_NAME,
			$this->integration
		);
		$this->add_receipt_fields_to_metabox( $receipt_metabox_fields );
		$receipt = new Receipt(
			Receipt::TYPE_NAME,
			Receipt::META_DATA_NAME,
			Receipt::META_DATA_TYPE,
			__( 'Wystaw paragon', 'woocommerce-fakturownia' ),
			__( 'parametry', 'woocommerce-fakturownia' ),
			$receipt_metabox_fields,
			$this->integration
		);
		new ReceiptCreator( $receipt );
		$receipt->setDocumentView( new OrderDocumentView( $this->ajax_get_pdf_handler, $this->integration->get_renderer() ) );
		$this->integration->addSupportedDocumentType( $receipt );
	}

	/**
	 * Add support for foreign invoice.
	 */
	public function add_support_for_invoice_foreign() {
		$foreign_currency_invoice_metabox_fields = new MetaBoxFields(
			InvoiceForeign::TYPE_NAME,
			$this->integration
		);

		$this->add_common_invoice_fields_to_metabox( $foreign_currency_invoice_metabox_fields );
		$invoice = new InvoiceForeign(
			InvoiceForeign::TYPE_NAME,
			InvoiceForeign::META_DATA_NAME,
			InvoiceForeign::META_DATA_TYPE,
			__( 'Wystaw fakturę walutową', 'woocommerce-fakturownia' ),
			__( 'parametry', 'woocommerce-fakturownia' ),
			$foreign_currency_invoice_metabox_fields,
			$this->integration
		);
		new InvoiceForeignCreator( $invoice );
		$invoice->setDocumentView( new OrderDocumentView( $this->ajax_get_pdf_handler, $this->integration->get_renderer() ) );
		$this->integration->addSupportedDocumentType( $invoice );
	}

	/**
	 * Add common invoice fields to metabox.
	 *
	 * @param MetaBoxFields $metabox_fields Metabox fields.
	 */
	private function add_proforma_invoice_fields_to_metabox( $metabox_fields ) {
		$metabox_fields->addIssueDateField();
		$metabox_fields->addProformaPaymentDateField();
		$metabox_fields->addPaymentMethodField();
		$metabox_fields->addCommentsField();
	}

	/**
	 * Add receipt fields to metabox.
	 *
	 * @param MetaBoxFields $metabox_fields Metabox fields.
	 */
	private function add_receipt_fields_to_metabox( $metabox_fields ) {
		$metabox_fields->addPaidAmountField();
		$metabox_fields->addIssueDateField();
		$metabox_fields->addSaleDateField();
		$metabox_fields->addReceiptPaymentDateField();
		$metabox_fields->addPaymentMethodField();
		$metabox_fields->addCommentsField();
	}

	/**
	 * Add common invoice fields to metabox.
	 *
	 * @param MetaBoxFields $metabox_fields Metabox fields.
	 */
	private function add_common_invoice_fields_to_metabox( $metabox_fields ) {
		$metabox_fields->addPaidAmountField();
		$metabox_fields->addIssueDateField();
		$metabox_fields->addSaleDateField();
		$metabox_fields->addPaymentDateField();
		$metabox_fields->addPaymentMethodField();
		$metabox_fields->addCommentsField();
	}
}
