<?php

namespace WPDesk\WooCommerceFakturownia\Forms\Integration;

use FakturowniaVendor\WPDesk\Forms\AbstractForm;
use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumSettingsFields;
use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumValues;
use WPDesk\WooCommerceFakturownia\Forms\ConditionalFormInterface;
use WPDesk\WooCommerceFakturownia\Helpers\WooCommerce;

/**
 * Class BillingsForm
 *
 * @package WPDesk\WooCommerceFakturownia\Forms\Integration
 */
class BillForm extends AbstractForm implements ConditionalFormInterface {

	const MANUAL_GENERATE                          = 'r';
	const AUTO_GENERATE                            = 'a';
	const ASK_AND_AUTO_GENERATE                    = 'p';
	const ASK_AND_NOT_GENERATE                     = 'n';
	const OPTION_GENERATE_INVOICE_STATUS           = 'generate_invoice_status';
	const OPTION_GENERATE_INVOICE                  = 'generate_invoice';
	const OPTION_GENERATE_BILL                     = 'generate_bill';
	const OPTION_GENERATE_BILL_STATUS              = 'generate_bill_status';
	const OPTION_PLACE_OF_ISSUE                    = 'issue_place';
	const OPTION_PAYMENT_DATE                      = 'payment_day';
	const OPTION_DOCUMENT_TYPE                     = 'document_type';
	const OPTION_DOCUMENT_TYPE_BILL                = 'bill';
	const OPTION_DOCUMENT_TYPE_INVOICE_WITHOUT_TAX = 'invoice_without_tax';
	const OPTION_AUTOSEND_BILL                     = 'autosend_bill';
	const OPTION_AUTOSEND_INVOICE                  = 'autosend_invoice';
	const OPTION_COMMENT_CONTENT                   = 'comment_content';
	const OPTION_VALIDATE_CHECKOUT_NIP             = 'validate_checkout_nip';
	const OPTION_LEGAL_BASIS                       = 'legal_basis';
	const OPTION_SELLER_NAME                       = 'seller_name';
	const OPTION_PAYMENT_TO_KIND                   = 'payment_to_kind';
	const OPTION_INVOICE_LANG                      = 'invoice_lang';
	const CLIENT_COUNTRY                           = 'client_country';
	const SHORTCODE_ORDER_NUMBER                   = '[order_number]';

	/**
	 * Form ID.
	 *
	 * @var string
	 */
	protected $form_id = 'bill';

	/**
	 * Is active.
	 *
	 * @var bool
	 */
	private $is_active;

	/**
	 * BillForm constructor.
	 *
	 * @param string $is_active Is active?
	 *
	 * @throws \Exception
	 */
	public function __construct(
		$is_active
	) {
		$this->is_active = $is_active;
	}

	/**
	 * Checks if form should be active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->is_active;
	}

	/**
	 * Create form data and return an associative array.
	 *
	 * @return array
	 */
	protected function create_form_data() {

		$lump_sums = new LumpSumValues();
		/* Remove 'np' value as it is not supported by Fakturownia */
		unset( $lump_sums['np'] );
		$lump_sum_settings_fields = new LumpSumSettingsFields( $lump_sums );
		$lump_sum_fields          = $lump_sum_settings_fields->get_fields();

		return [
			'invoice_without_vat_tab' => [
				'title' => __( 'Wystawianie faktur bez VAT', 'woocommerce-fakturownia' ),
				'type'  => 'tab_open',
			],
			self::OPTION_DOCUMENT_TYPE => [
				'title'       => __( 'Typ dokumentu', 'woocommerce-fakturownia' ),
				'type'        => 'select',
				'description' => __( 'Typ wystawianych dokumentów.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'options'     => [
					self::OPTION_DOCUMENT_TYPE_BILL => __( 'Rachunki', 'woocommerce-fakturownia' ),
					self::OPTION_DOCUMENT_TYPE_INVOICE_WITHOUT_TAX => __( 'Faktury bez VAT', 'woocommerce-fakturownia' ),
				],
				'default'     => 'bill',
			],
			'wystawianiefaktur' => [
				'title' => __( 'Wystawianie faktur bez VAT', 'woocommerce-fakturownia' ),
				'type'  => 'title',
				'class' => 'fakturownia-invoice-without-vat',
			],
			self::OPTION_GENERATE_INVOICE => [
				'title'       => __( 'Wystawianie faktur', 'woocommerce-fakturownia' ),
				'type'        => 'select',
				'description' => __( 'Sposób wystawiania faktur.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'options'     => $this->get_generate_field_options(),
				'default'     => '1',
				'class'       => 'fakturownia-invoice-without-vat option-generate',
			],
			self::OPTION_GENERATE_INVOICE_STATUS => [
				'title'       => __( 'Status zamówienia', 'woocommerce-fakturownia' ),
				'type'        => 'multiselect',
				'description' => __(
					'W przypadku automatycznego wystawiania faktur (Zawsze) lub faktur wystawianych na życzenie kupującego (Pytaj kupującego), wybierz status zamówienia przy którym faktura zostanie automatycznie wystawiona.',
					'woocommerce-fakturownia'
				),
				'desc_tip'    => false,
				'options'     => WooCommerce::get_woocommerce_statuses(),
				'class'       => 'fakturownia-select2 fakturownia-invoice-without-vat',
				'default'     => '1',
			],
			self::OPTION_AUTOSEND_INVOICE => [
				'title'    => __( 'Automatycznie wysyłaj faktury po wystawieniu', 'woocommerce-fakturownia' ),
				'type'     => 'checkbox',
				'desc_tip' => false,
				'default'  => 'yes',
				'class'    => 'fakturownia-invoice-without-vat',
			],
			InvoiceForm::OPTION_AUTO_ISSUE_CORRECTION => [
				'title'    => __( 'Automatycznie wystawiaj korekty po zwrocie', 'woocommerce-fakturownia' ),
				'label'    => __( 'Automatycznie wystawiaj korekty po zwrocie zamówienia', 'woocommerce-fakturownia' ),
				'type'     => 'checkbox',
				'desc_tip' => false,
				'default'  => 'no',
				'class'    => 'option-autosend',
			],
			'wystawianierachunkow' => [
				'title' => __( 'Wystawianie rachunków', 'woocommerce-fakturownia' ),
				'type'  => 'title',
				'class' => 'fakturownia-bill',
			],
			self::OPTION_GENERATE_BILL => [
				'title'    => __( 'Wystawianie rachunków', 'woocommerce-fakturownia' ),
				'type'     => 'select',
				'desc_tip' => false,
				'options'  => $this->get_generate_field_options(),
				'default'  => '1',
				'class'    => 'fakturownia-bill option-generate',
			],
			self::OPTION_GENERATE_BILL_STATUS => [
				'title'       => __( 'Status zamówienia', 'woocommerce-fakturownia' ),
				'type'        => 'multiselect',
				'description' => __( 'Wybierz status zamówienia przy którym rachunek zostanie automatycznie wystawiony.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'options'     => WooCommerce::get_woocommerce_statuses(),
				'class'       => 'fakturownia-select2 fakturownia-bill',
				'default'     => '1',
			],
			self::OPTION_AUTOSEND_BILL => [
				'title'    => __( 'Automatycznie wysyłaj rachunki po wystawieniu', 'woocommerce-fakturownia' ),
				'type'     => 'checkbox',
				'desc_tip' => false,
				'default'  => 'yes',
				'class'    => 'fakturownia-bill',
			],
			self::OPTION_INVOICE_LANG => [
				'title'       => __( 'Język rachunków/faktur', 'woocommerce-fakturownia' ),
				'type'        => 'select',
				'options'     => [
					self::CLIENT_COUNTRY => __( 'Język kraju klienta, jeżeli dostępny lub angielski', 'woocommerce-fakturownia' ),
					'en'                 => __( 'Zawsze angielski', 'woocommerce-fakturownia' ),
					'pl'                 => __( 'Zawsze polski', 'woocommerce-fakturownia' ),
				],
				'description' => __(
					'Dostępne języki: pl, en, en-GB, ar, cn, cz, de, es, et, fa, fr, hu, hr, it, nl, ru, sk, sl, tr',
					'woocommerce-fakturownia'
				),
				'default'     => self::CLIENT_COUNTRY,
			],
			'danerachunku' => [
				'title' => __( 'Parametry rachunku', 'woocommerce-fakturownia' ),
				'type'  => 'title',
				'class' => 'fakturownia-bill',
			],
			'danefaktury' => [
				'title' => __( 'Parametry faktury', 'woocommerce-fakturownia' ),
				'type'  => 'title',
				'class' => 'fakturownia-invoice-without-vat',
			],

			self::OPTION_VALIDATE_CHECKOUT_NIP => [
				'label'       => __( 'Włącz sprawdzanie poprawności numeru NIP na stronie zamówienia', 'woocommerce-fakturownia' ),
				'title'       => __( 'Poprawność numeru NIP', 'woocommerce-fakturownia' ),
				'type'        => 'checkbox',
				'description' => __( 'Niepoprawny NIP zablokuje możliwość złożenia zamówienia.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'default'     => 'no',
			],
			self::OPTION_PAYMENT_DATE => [
				'title'    => __( 'Termin płatności dla dokumentów wysyłkowych (za pobraniem)', 'woocommerce-fakturownia' ),
				'type'     => 'select',
				'desc_tip' => false,
				'options'  => $this->get_payment_date_options(),
				'default'  => '7',
			],
			self::OPTION_LEGAL_BASIS => [
				'title'       => __( 'Podstawa prawna zwolnienia', 'woocommerce-fakturownia' ),
				'type'        => 'text',
				'description' => __( 'Numer artykułu, uprawniającego do zwolnienia, np. dla zwolnienia podmiotowego: Art. 113 ust. 1.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'default'     => '',
				'class'       => 'fakturownia-invoice-without-vat',
			],
			self::OPTION_PLACE_OF_ISSUE => [
				'title'    => __( 'Miejsce wystawienia', 'woocommerce-fakturownia' ),
				'type'     => 'text',
				'desc_tip' => false,
				'default'  => '',
			],
			self::OPTION_COMMENT_CONTENT => [
				'title'       => __( 'Uwagi do dokumentu', 'woocommerce-fakturownia' ),
				'type'        => 'text',
				'description' => sprintf(
				// Translators: order number shortcode.
					__( 'Możesz użyć shortcode %1$s - numer zamówienia.', 'woocommerce-fakturownia' ),
					self::SHORTCODE_ORDER_NUMBER,
					self::SHORTCODE_ORDER_NUMBER
				),
				'default'     => '',
				'desc_tip'    => false,
			],
			'danefakturyProForma' => [
				'title' => __( 'Parametry faktury proforma', 'woocommerce-fakturownia' ),
				'type'  => 'title',
				'class' => 'fakturownia-invoice-without-vat',
			],
			self::OPTION_PAYMENT_TO_KIND => [
				'title'    => __( 'Termin płatności dla faktur pro forma', 'woocommerce-fakturownia' ),
				'type'     => 'select',
				'desc_tip' => false,
				'options'  => $this->get_payment_date_options(),
				'default'  => '3',
				'class'    => 'fakturownia-invoice-without-vat',
			],
			InvoiceForm::OPTION_GENERATE_PROFORMA_STATUS => [
				'title'       => __( 'Status zamówienia', 'woocommerce-fakturownia' ),
				'type'        => 'multiselect',
				'description' => __(
					'Wybierz status zamówienia przy którym faktura proforma zostanie automatycznie wystawiona.',
					'woocommerce-fakturownia'
				),
				'desc_tip'    => false,
				'class'       => 'fakturownia-select2 option-status fakturownia-invoice-without-vat',
				'options'     => WooCommerce::get_woocommerce_statuses(),
				'default'     => '1',
			],
			InvoiceForm::OPTION_AUTOSEND_INVOICE_PROFORMA => [
				'title'    => __( 'Automatycznie wysyłaj faktury proformy po wystawieniu', 'woocommerce-fakturownia' ),
				'type'     => 'checkbox',
				'desc_tip' => false,
				'default'  => 'yes',
				'class'    => 'option-autosend fakturownia-invoice-without-vat',
			],
		] + $lump_sum_fields + [
			'invoice_close' => [
				'type' => 'tab_close',
			],
		];
	}

	/**
	 * Get generate settings field.
	 *
	 * @return array
	 */
	private function get_generate_field_options() {
		return [
			self::MANUAL_GENERATE       => __( 'Ręcznie', 'woocommerce-fakturownia' ),
			self::ASK_AND_NOT_GENERATE  => __( 'Pytaj kupującego i nie wystawiaj automatycznie', 'woocommerce-fakturownia' ),
			self::ASK_AND_AUTO_GENERATE => __( 'Pytaj kupującego i wystawiaj automatycznie', 'woocommerce-fakturownia' ),
			self::AUTO_GENERATE         => __( 'Zawsze, automatycznie', 'woocommerce-fakturownia' ),
		];
	}

	/**
	 * Get generate settings field.
	 *
	 * @return array
	 */
	private function get_payment_date_options() {
		return [
			'1'  => __( '1 dzień', 'woocommerce-fakturownia' ),
			'2'  => __( '2 dni', 'woocommerce-fakturownia' ),
			'3'  => __( '3 dni', 'woocommerce-fakturownia' ),
			'4'  => __( '4 dni', 'woocommerce-fakturownia' ),
			'5'  => __( '5 dni', 'woocommerce-fakturownia' ),
			'6'  => __( '6 dni', 'woocommerce-fakturownia' ),
			'7'  => __( '7 dni', 'woocommerce-fakturownia' ),
			'8'  => __( '8 dni', 'woocommerce-fakturownia' ),
			'9'  => __( '9 dni', 'woocommerce-fakturownia' ),
			'10' => __( '10 dni', 'woocommerce-fakturownia' ),
			'11' => __( '11 dni', 'woocommerce-fakturownia' ),
			'12' => __( '12 dni', 'woocommerce-fakturownia' ),
			'13' => __( '13 dni', 'woocommerce-fakturownia' ),
			'14' => __( '14 dni', 'woocommerce-fakturownia' ),
		];
	}

	/**
	 * Set is active.
	 *
	 * @param bool $is_active Is active.
	 */
	public function set_is_active( $is_active ) {
		$this->is_active = $is_active;
	}
}
