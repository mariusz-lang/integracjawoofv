<?php

namespace WPDesk\WooCommerceFakturownia\Metabox;

use FakturowniaVendor\WPDesk\Invoices\Metabox\Fields\CommentField;
use FakturowniaVendor\WPDesk\Invoices\Metabox\Fields\IssueDateField;
use FakturowniaVendor\WPDesk\Invoices\Metabox\Fields\IssueSignatureField;
use FakturowniaVendor\WPDesk\Invoices\Metabox\Fields\LegalBasisField;
use FakturowniaVendor\WPDesk\Invoices\Metabox\Fields\PaymentDateField;
use FakturowniaVendor\WPDesk\Invoices\Metabox\Fields\SaleDateField;
use FakturowniaVendor\WPDesk\Invoices\Metabox\OrderMetaboxFields;
use WPDesk\WooCommerceFakturownia\Data\InvoiceOrderDefaults;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;

/**
 * @package WPDesk\WooCommerceFakturownia\Metabox\Fields
 */
class MetaBoxFields extends OrderMetaboxFields {

	/**
	 * Integration.
	 *
	 * @var InvoicesIntegration
	 */
	protected $integration;

	/**
	 * @param string              $type_name   .
	 * @param InvoicesIntegration $integration .
	 */
	public function __construct( $type_name, InvoicesIntegration $integration ) {
		parent::__construct( $type_name );
		$this->integration = $integration;
	}

	/**
	 * @return InvoicesIntegration .
	 */
	public function getIntegration() {
		return $this->integration;
	}

	/**
	 * Add paid amount field to metabox.
	 */
	public function addPaidAmountField() {
		$this->addMetaBoxField(
			new PaidAmountField( $this->getTypeName() . '_paid_amount', 'paid_amount', __( 'Zapłacono', 'woocommerce-fakturownia' ) )
		);
	}

	/**
	 * Add issue date field to metabox.
	 */
	public function addIssueDateField() {
		$this->addMetaBoxField(
			new IssueDateField( $this->getTypeName() . '_issue_date', 'issue_date', __( 'Data wystawienia', 'woocommerce-fakturownia' ) )
		);
	}

	/**
	 * Add sale date field to metabox.
	 */
	public function addSaleDateField() {
		$this->addMetaBoxField(
			new SaleDateField( $this->getTypeName() . '_sale_date', 'sale_date', __( 'Data sprzedaży', 'woocommerce-fakturownia' ) )
		);
	}

	/**
	 * Add payment date field to metabox.
	 */
	public function addPaymentDateField() {
		$this->addMetaBoxField(
			new PaymentDateField(
				$this->getTypeName() . '_payment_date',
				'payment_date',
				__( 'Termin płatności', 'woocommerce-fakturownia' ),
				$this->getIntegration()->woocommerce_integration->getOptionPaymentDate()
			)
		);
	}

	/**
	 * Add proforma payment date field to metabox.
	 */
	public function addProformaPaymentDateField() {
		$this->addMetaBoxField(
			new ProformaPaymentDateField(
				$this->getTypeName() . '_payment_date',
				'payment_date',
				__( 'Termin płatności', 'woocommerce-fakturownia' ),
				$this->getIntegration()->woocommerce_integration->getProformaOptionPaymentDate()
			)
		);
	}

	/**
	 * Add receipt payment date field to metabox.
	 */
	public function addReceiptPaymentDateField() {
		$this->addMetaBoxField(
			new ProformaPaymentDateField(
				$this->getTypeName() . '_payment_date',
				'payment_date',
				__( 'Termin płatności', 'woocommerce-fakturownia' ),
				$this->getIntegration()->woocommerce_integration->getReceiptOptionPaymentDate()
			)
		);
	}

	/**
	 * Add payment method field to metabox.
	 */
	public function addPaymentMethodField() {
		$payment_method_defaults = [
			InvoiceOrderDefaults::PAYMENT_METHOD_TRANSFER => __( 'Przelew', 'woocommerce-fakturownia' ),
			InvoiceOrderDefaults::PAYMENT_METHOD_CARD     => __( 'Karta płatnicza', 'woocommerce-fakturownia' ),
			InvoiceOrderDefaults::PAYMENT_METHOD_CASH     => __( 'Gotówka', 'woocommerce-fakturownia' ),
		];
		$this->addMetaBoxField(
			new PaymentMethodField(
				$this->getTypeName() . '_payment_method',
				'payment_method',
				__( 'Sposób płatności', 'woocommerce-fakturownia' ),
				$payment_method_defaults
			)
		);
	}

	/**
	 * Add comments field to metabox.
	 */
	public function addCommentsField() {
		$this->addMetaBoxField(
			new CommentField(
				$this->getTypeName() . '_comments',
				'comments',
				__( 'Uwagi', 'woocommerce-fakturownia' )
			)
		);
	}

	/**
	 * Add issuer signature field to metabox.
	 */
	public function addIssuerSignatureField() {
		$this->addMetaBoxField(
			new IssueSignatureField(
				$this->getTypeName() . '_issuer_signature',
				'issuer_signature',
				__( 'Podpis wystawcy', 'woocommerce-fakturownia' )
			)
		);
	}

	/**
	 * Add legal basis field to metabox.
	 */
	public function addLegalBasisField() {
		$this->addMetaBoxField(
			new LegalBasisField(
				$this->getTypeName() . '_legal_basis',
				'legal_basis',
				__( 'Podstawa zwolnienia', 'woocommerce-fakturownia' )
			)
		);
	}
}
