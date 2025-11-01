<?php

namespace WPDesk\WooCommerceFakturownia;

use Exception;
use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumSettingsFields;
use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumValues;
use WC_Integration;
use FakturowniaVendor\WPDesk\WooCommerce\EUVAT\Settings\Settings;
use FakturowniaVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\WooCommerceFakturownia\Forms\Integration\AuthorizationForm;
use WPDesk\WooCommerceFakturownia\Forms\Integration\BillForm;
use WPDesk\WooCommerceFakturownia\Forms\Integration\DebugForm;
use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;
use WPDesk\WooCommerceFakturownia\Forms\Integration\ReceiptForm;
use WPDesk\WooCommerceFakturownia\Forms\Integration\StockForm;
use FakturowniaVendor\WPDesk\Forms\FormsCollection;

/**
 * Class WoocommerceIntegration
 *
 * @package WPDesk\WooCommerceFakturownia
 */
class WoocommerceIntegration extends WC_Integration {

	const INTEGRATION_ID = 'integration-fakturownia';
	const MODULE_TITLE   = 'WooCommerce Fakturownia';

	/**
	 * @var InvoicesIntegration
	 */
	protected $invoices_integration;

	/**
	 * @var string
	 */
	private $calc_taxes;

	/**
	 * @var Renderer
	 */
	public static $renderer;

	/**
	 * WoocommerceIntegration constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {
		$this->id                 = self::INTEGRATION_ID;
		$this->method_title       = __( 'Fakturownia', 'woocommerce-fakturownia' );
		$this->method_description = __( 'Wystawianie faktur w serwisie fakturownia.pl. <a href="https://www.wpdesk.pl/sk/woocommerce-fakturownia-woo-docs/" target="_blank">Instrukcja konfiguracji &rarr;</a>', 'woocommerce-fakturownia' );

		$this->invoices_integration = new InvoicesIntegration( $this );
		$this->invoices_integration->hooks();

		$this->calc_taxes = 'yes' === get_option( 'woocommerce_calc_taxes', 'no' );

		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_integration_' . $this->id, [ $this, 'process_admin_options' ], 11 );
	}

	/**
	 * Settings mapping for EU VAT Number.
	 *
	 * @return Settings
	 */
	public function eu_vat_settings(): Settings {
		$settings                                  = new Settings();
		$settings->vat_field_label                 = __( 'VAT', 'woocommerce-fakturownia' );
		$settings->vat_field_placeholder           = __( 'Vat number', 'woocommerce-fakturownia' );
		$settings->eu_vat_vies_validate            = 'yes' === $this->get_option( InvoiceForm::OPTION_MOSS_VIES_VALIDATION, 'no' );
		$settings->eu_vat_remove_vat_from_base_b2b = false;
		$settings->eu_vat_failure_handling         = $this->get_option( InvoiceForm::OPTION_MOSS_FAILURE_HANDLING, 'reject' );
		$settings->moss_tax_classes                = $this->get_option( InvoiceForm::OPTION_MOSS_TAX_CLASSES, [] );
		$settings->moss_validate_ip                = 'yes' === $this->get_option( InvoiceForm::OPTION_MOSS_VALIDATE_IP, 'no' );

		return $settings;
	}

	/**
	 * Set renderer.
	 *
	 * @param Renderer $renderer .
	 */
	public static function set_renderer( Renderer $renderer ) {
		self::$renderer = $renderer;
	}

	/**
	 * Get renderer.
	 *
	 * @return Renderer
	 */
	public function get_renderer(): Renderer {
		return self::$renderer;
	}

	public function init_fields_setup() {
		if ( '' === $this->get_option( StockForm::OPTION_WAREHOUSE_WEBHOOK_TOKEN, '' ) ) {
			$this->update_option( StockForm::OPTION_WAREHOUSE_WEBHOOK_TOKEN, md5( rand( 1, 10000 ) + time() ) );
		}
	}

	/**
	 * Initialize integration settings form fields.
	 *
	 * @throws Exception
	 */
	public function init_form_fields() {
		parent::init_form_fields();
		$this->init_fields_setup();

		$formsCollection = new FormsCollection();

		$stock = new Forms\Integration\StockForm();
		$stock->set_integration( $this->invoices_integration );
		$formsCollection->add_forms(
			[
				new Forms\Integration\AuthorizationForm( $this->getUserToken(), $this->getAccountName() ),
				new Forms\Integration\ReceiptForm(
					true
				),
				new Forms\Integration\InvoiceForm(
					$this->calc_taxes
				),
				new Forms\Integration\BillForm(
					! $this->calc_taxes
				),
				$stock,
				new Forms\Integration\DebugForm(),
			]
		);

		$this->form_fields = $formsCollection->get_forms_data();
	}

	/**
	 * Woocommerce hook override. It gets form fields, load template, render view and display.
	 *
	 * @param array $form_fields
	 * @param bool  $echo
	 */
	public function generate_settings_html( $form_fields = [], $echo = true ) {
		$form_fields = empty( $form_fields ) ? $this->get_form_fields() : $form_fields;

		echo $this->get_renderer()->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'integration/settings',
			[
				'form_fields'       => $form_fields, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'rendered_settings' => parent::generate_settings_html( $form_fields, false ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'text_domain'       => 'woocommerce-fakturownia',
				'module_title'      => self::MODULE_TITLE, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			]
		);
	}

	/**
	 * Woocommerce hook. Starts tab container and return rendered view as string.
	 *
	 * @param string $key
	 * @param array  $data
	 *
	 * @return string
	 */
	public function generate_tab_open_html( string $key, $data ): string {
		$defaults = [
			'title' => '',
			'class' => '',
		];

		return $this->get_renderer()->render(
			'integration/settings_tab_open',
			[
				'data' => wp_parse_args( $data, $defaults ),
			]
		);
	}

	/**
	 * Woocommerce hook. End tab container and return rendered view as string.
	 *
	 * @param string $key
	 * @param array  $data
	 *
	 * @return string
	 */
	public function generate_connection_status_html( string $key, array $data ): string {
		return $this->get_renderer()->render(
			'integration/settings_connection_status',
			[
				'key'  => $key,
				'data' => $data,
			]
		);
	}

	/**
	 * Woocommerce hook. End tab container and return rendered view as string.
	 *
	 * @param string $key
	 * @param array  $data
	 *
	 * @return string
	 */
	public function generate_tab_close_html( string $key, array $data ): string {
		return $this->get_renderer()->render( 'integration/settings_tab_close' );
	}

	/**
	 * Get option: user token.
	 *
	 * @return string.
	 */
	public function getUserToken(): string {
		return $this->get_option( AuthorizationForm::OPTION_TOKEN );
	}

	/**
	 * Get option: user token.
	 *
	 * @return string.
	 */
	public function getDepartmentId(): string {
		return $this->get_option( AuthorizationForm::OPTION_DEPARTMENT_ID );
	}

	/**
	 * Get option: user name.
	 *
	 * @return string.
	 */
	public function getAccountName(): string {
		return $this->get_option( AuthorizationForm::OPTION_ACCOUNT_NAME );
	}

	/**
	 * Get option generate invoice.
	 *
	 * @return string
	 */
	public function getOptionGenerateInvoice(): string {
		return $this->get_option( InvoiceForm::OPTION_GENERATE_INVOICE );
	}

	/**
	 * Get option generate invoice.
	 *
	 * @return string
	 */
	public function getOptionGenerateReceipt(): string {
		return $this->get_option( ReceiptForm::OPTION_GENERATE_RECEIPT );
	}

	/**
	 * Get option invoice status.
	 *
	 * @return string|array
	 */
	public function getOptionGenerateInvoiceStatus(): array {
		return (array) $this->get_option( InvoiceForm::OPTION_GENERATE_INVOICE_STATUS, [] );
	}

	/**
	 * Get option invoice status.
	 *
	 * @return string|array
	 */
	public function getOptionGenerateReceiptStatus(): array {
		return (array) $this->get_option( ReceiptForm::OPTION_GENERATE_RECEIPT_STATUS, [] );
	}

	/**
	 * Get option generate bill.
	 *
	 * @return string
	 */
	public function getOptionGenerateBill(): string {
		return $this->get_option( BillForm::OPTION_GENERATE_BILL );
	}

	/**
	 * Get option bill status.
	 *
	 * @return string|array
	 */
	public function getOptionGenerateBillStatus(): array {
		return (array) $this->get_option( BillForm::OPTION_GENERATE_BILL_STATUS, [] );
	}

	/**
	 * Get option autosend invoice.
	 *
	 * @return string
	 */
	public function getOptionAutoSendInvoice(): string {
		return $this->get_option( InvoiceForm::OPTION_AUTOSEND_INVOICE, 'no' );
	}

	/**
	 * @return array
	 */
	public function getOptionGenerateProformaStatus(): array {
		return (array) $this->get_option( InvoiceForm::OPTION_GENERATE_PROFORMA_STATUS, [] );
	}

	/**
	 * @return string
	 */
	public function getOptionAutoSendInvoiceProforma(): string {
		return $this->get_option( InvoiceForm::OPTION_AUTOSEND_INVOICE_PROFORMA, 'no' );
	}

	/**
	 * @return string
	 */
	public function getOptionAutoSendReceipt(): string {
		return $this->get_option( ReceiptForm::OPTION_AUTOSEND_RECEIPT, 'no' );
	}

	/**
	 * @return string
	 */
	public function getOptionAutoSendBill(): string {
		return $this->get_option( BillForm::OPTION_AUTOSEND_BILL, 'no' );
	}

	/**
	 * @return string
	 */
	public function getOptionPlaceOfIssue(): string {
		return $this->get_option( InvoiceForm::OPTION_PLACE_OF_ISSUE );
	}

	/**
	 * @return int
	 */
	public function getOptionPaymentDate(): int {
		return (int) $this->get_option( InvoiceForm::OPTION_PAYMENT_DATE, 7 );
	}

	/**
	 * Get option proforma payment date.
	 *
	 * @return int
	 */
	public function getProformaOptionPaymentDate(): int {
		return (int) $this->get_option( InvoiceForm::OPTION_PAYMENT_TO_KIND, 7 );
	}

	/**
	 * @return string
	 */
	public function getOptionInvoiceLang(): string {
		return $this->get_option( InvoiceForm::OPTION_INVOICE_LANG, 'pl' );
	}

	/**
	 * Get option PKWiU.
	 *
	 * @return string
	 */
	public function getOptionReceiptLang(): string {
		return $this->get_option( ReceiptForm::OPTION_RECEIPT_LANG );
	}

	/**
	 * Get option proforma payment date.
	 *
	 * @return string
	 */
	public function getReceiptOptionPaymentDate(): string {
		return $this->get_option( ReceiptForm::OPTION_PAYMENT_DATE );
	}


	/**
	 * Get option comment content.
	 *
	 * @return string
	 */
	public function getOptionCommentContent(): string {
		return $this->get_option( InvoiceForm::OPTION_COMMENT_CONTENT );
	}

	/**
	 * Get option comment content for receipt.
	 *
	 * @return string
	 */
	public function getOptionCommentContentForReceipt(): string {
		return $this->get_option( ReceiptForm::OPTION_COMMENT_CONTENT );
	}

	/**
	 * Get option tax_rate_exempt.
	 *
	 * @return string
	 */
	public function getOptionTaxRateExempt(): string {
		return $this->get_option( InvoiceForm::OPTION_EXEMPT_TAX_KIND );
	}

	/**
	 * Get option legal basis.
	 *
	 * @return string
	 */
	public function getOptionLegalBasis(): string {
		return $this->get_option( InvoiceForm::OPTION_LEGAL_BASIS );
	}

	/**
	 * Get option PKWiU.
	 *
	 * @return string
	 */
	public function getOptionPkwiuAttribute(): string {
		return $this->get_option( InvoiceForm::OPTION_PKWIU_ATTRIBUTE );
	}

	/**
	 * Get option document_type.
	 *
	 * @return string
	 */
	public function getOptionDocumentType(): string {
		return $this->get_option(
			InvoiceForm::OPTION_DOCUMENT_TYPE,
			BillForm::OPTION_DOCUMENT_TYPE_BILL
		);
	}

	/**
	 * @return bool
	 */
	public function is_warehouse_enabled(): bool {
		return 'yes' === $this->get_option( StockForm::OPTION_SYNCHRONIZATION );
	}

	public function is_price_sync_enabled(): bool {
		return filter_var( $this->get_option( StockForm::OPTION_SYNC_WOO_PRICES ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get option warehouse ID.
	 *
	 * @return string
	 */
	public function getWarehouseID(): string {
		return $this->get_option( StockForm::OPTION_WAREHOUSE_ID, '' );
	}

	/**
	 * Get option create products.
	 *
	 * @return string
	 */
	public function getOptionCreateProducts(): string {
		return $this->get_option( StockForm::OPTION_CREATE_PRODUCTS );
	}

	/**
	 * Get option to sync fakturownia prices with those set on woocommerce.
	 *
	 * @return bool
	 */
	public function getOptionSyncWooPrices(): bool {
		return filter_var( $this->get_option( StockForm::OPTION_SYNC_WOO_PRICES ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get option warehouse webhook token.
	 *
	 * @return string
	 */
	public function getOptionWarehouseWebhookToken(): string {
		return $this->get_option( StockForm::OPTION_WAREHOUSE_WEBHOOK_TOKEN );
	}

	/**
	 * Get option if NIP in checkout should be validated.
	 *
	 * @return bool
	 */
	public function isOptionValidateCheckoutNip(): bool {
		return $this->get_option( InvoiceForm::OPTION_VALIDATE_CHECKOUT_NIP ) === 'yes';
	}

	/**
	 * Get option if corrections should be automatically issued.
	 *
	 * @return bool
	 */
	public function isAutoCorrectionIssue(): bool {
		return filter_var( $this->get_option( InvoiceForm::OPTION_AUTO_ISSUE_CORRECTION ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * @return bool
	 */
	public function isDebugEnabled(): bool {
		return $this->get_option( DebugForm::OPTION_DEBUG_MODE ) === 'yes';
	}

	/**
	 * Get option: lump sum enabled.
	 *
	 * @return string.
	 */
	public function is_lump_sum_enabled(): bool {
		return filter_var( $this->get_option( LumpSumSettingsFields::FIELD_LUMP_SUM, 'no' ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get option: default lump sum.
	 *
	 * @return string.
	 */
	public function get_default_lump_sum(): string {
		return $this->get_option( LumpSumSettingsFields::FIELD_LUMP_SUM_DEFAULT_VALUE, LumpSumValues::LUMP_SUM_DEFAULT_VALUE );
	}
}
