<?php

namespace WPDesk\WooCommerceFakturownia\Forms\Integration;

use FakturowniaVendor\WPDesk\Forms\AbstractForm;
use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumSettingsFields;
use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumValues;
use WPDesk\WooCommerceFakturownia\Forms\ConditionalFormInterface;
use WPDesk\WooCommerceFakturownia\Helpers\WooCommerce;


/**
 * Class InvoiceForm
 *
 * @package WPDesk\WooCommerceFakturownia\Forms\Integration
 */
class InvoiceForm extends AbstractForm implements ConditionalFormInterface {

	const OPTION_DOCUMENT_TYPE                = 'document_type';
	const MANUAL_GENERATE                     = 'r';
	const AUTO_GENERATE                       = 'a';
	const ASK_AND_AUTO_GENERATE               = 'p';
	const ASK_AND_NOT_GENERATE                = 'n';
	const OPTION_GENERATE_INVOICE_STATUS      = 'generate_invoice_status';
	const OPTION_GENERATE_PROFORMA_STATUS     = 'generate_invoice_proforma_status';
	const OPTION_GENERATE_INVOICE             = 'generate_invoice';
	const OPTION_AUTOSEND_INVOICE             = 'autosend_invoice';
	public const OPTION_AUTO_ISSUE_CORRECTION = 'auto_issue_correction';
	const OPTION_AUTOSEND_INVOICE_PROFORMA    = 'autosend_proforma';
	const OPTION_EXEMPT_TAX_KIND              = 'exempt_tax_kind';
	const OPTION_PLACE_OF_ISSUE               = 'issue_place';
	const OPTION_PAYMENT_DATE                 = 'payment_day';
	const OPTION_COMMENT_CONTENT              = 'comment_content';
	const OPTION_PAYMENT_TO_KIND              = 'payment_to_kind';
	const TAX_EXEMPT_OPTION_NONE              = 'none';
	const OPTION_VALIDATE_CHECKOUT_NIP        = 'validate_checkout_nip';
	const OPTION_LEGAL_BASIS                  = 'legal_basis';
	const OPTION_PKWIU_ATTRIBUTE              = 'pkwiu_attribute';
	const OPTION_SELLER_NAME                  = 'seller_name';
	const OPTION_INVOICE_LANG                 = 'invoice_lang';
	const CLIENT_COUNTRY                      = 'client_country';
	const OPTION_MOSS_VIES_VALIDATION         = 'moss_vies_validation';
	const OPTION_MOSS_FAILURE_HANDLING        = 'moss_failure_handling';
	const OPTION_MOSS_TAX_CLASSES             = 'moss_tax_classes';
	const OPTION_MOSS_VALIDATE_IP             = 'moss_validate_ip';
	const SHORTCODE_ORDER_NUMBER              = '[order_number]';

	/**
	 * Form ID.
	 *
	 * @var string
	 */
	protected $form_id = 'invoice';

	/**
	 * Is active.
	 *
	 * @var bool
	 */
	private $is_active;

	/**
	 * InvoiceForm constructor.
	 *
	 * @param bool $is_active Is active?
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
	 * Create form data.
	 *
	 * @return array
	 */
	protected function create_form_data() {
		$tax_rate_exempt_options = array_merge( [ self::TAX_EXEMPT_OPTION_NONE => __( 'Brak', 'woocommerce-fakturownia' ) ], wc_get_product_tax_class_options() );
		$link                    = 'https://wpdesk.pl/sk/woocommerce-fakturownia-oss/';

		$validation_handling_values = [
			'reject'             => __( 'Reject the order and show the customer an error message.', 'woocommerce-fakturownia' ),
			'accept_with_vat'    => __( 'Accept the order, but do not remove VAT.', 'woocommerce-fakturownia' ),
			'accept_without_vat' => __( 'Accept the order and remove VAT.', 'woocommerce-fakturownia' ),
		];

		$lump_sums = new LumpSumValues();
		/* Remove 'np' value as it is not supported by Fakturownia */
		unset( $lump_sums['np'] );
		$lump_sum_settings_fields = new LumpSumSettingsFields( $lump_sums );
		$lump_sum_fields          = $lump_sum_settings_fields->get_fields();

		return [
			'invoice_issue'                        => [
				'title' => __( 'Wystawianie faktur', 'woocommerce-fakturownia' ),
				'type'  => 'tab_open',
			],
			self::OPTION_GENERATE_INVOICE          => [
				'title'   => __( 'Wystawianie faktur', 'woocommerce-fakturownia' ),
				'type'    => 'select',
				'options' => [
					self::MANUAL_GENERATE       => __( 'Ręcznie', 'woocommerce-fakturownia' ),
					self::ASK_AND_NOT_GENERATE  => __( 'Pytaj kupującego i nie wystawiaj automatycznie', 'woocommerce-fakturownia' ),
					self::ASK_AND_AUTO_GENERATE => __( 'Pytaj kupującego i wystawiaj automatycznie', 'woocommerce-fakturownia' ),
					self::AUTO_GENERATE         => __( 'Zawsze, automatycznie', 'woocommerce-fakturownia' ),
				],
				'default' => '1',
				'class'   => 'select option-generate',
			],
			self::OPTION_GENERATE_INVOICE_STATUS   => [
				'title'       => __( 'Status zamówienia', 'woocommerce-fakturownia' ),
				'type'        => 'multiselect',
				'description' => __(
					'Wybierz status zamówienia przy którym faktura zostanie automatycznie wystawiona.',
					'woocommerce-fakturownia'
				),
				'desc_tip'    => false,
				'class'       => 'fakturownia-select2 option-status',
				'options'     => WooCommerce::get_woocommerce_statuses(),
				'default'     => '1',
			],
			self::OPTION_AUTOSEND_INVOICE          => [
				'title'    => __( 'Automatycznie wysyłaj faktury po wystawieniu', 'woocommerce-fakturownia' ),
				'type'     => 'checkbox',
				'desc_tip' => false,
				'default'  => 'yes',
				'class'    => 'option-autosend',
			],
			self::OPTION_AUTO_ISSUE_CORRECTION => [
				'title'    => __( 'Automatycznie wystawiaj korekty po zwrocie', 'woocommerce-fakturownia' ),
				'label'    => __( 'Automatycznie wystawiaj korekty po zwrocie zamówienia', 'woocommerce-fakturownia' ),
				'type'     => 'checkbox',
				'desc_tip' => false,
				'default'  => 'no',
				'class'    => 'option-autosend',
			],
			'danefaktury'                          => [
				'title' => __( 'Parametry faktury', 'woocommerce-fakturownia' ),
				'type'  => 'title',
			],
			self::OPTION_EXEMPT_TAX_KIND           => [
				'title'       => __( 'Klasa podatkowa dla stawki ZW', 'woocommerce-fakturownia' ),
				'type'        => 'select',
				'description' => __( 'Klasa podatkowa dla stawki ZW.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'options'     => $tax_rate_exempt_options,
				'default'     => self::TAX_EXEMPT_OPTION_NONE,
			],
			self::OPTION_PKWIU_ATTRIBUTE           => [
				'title'       => __( 'PKWiU', 'woocommerce-fakturownia' ),
				'type'        => 'text',
				'default'     => 'pkwiu_attribute',
				'description' => sprintf(
					'%1$s<br/>%2$s',
					__( 'Wpisz tutaj nazwę atrybutu, z którego będzie pobierany numer PKWiU.', 'woocommerce-fakturownia' ),
					__( 'W serwisie Fakturownia.pl, w Ustawienia → Konfiguracja → Faktury i Dokumenty, wśród pozycji faktury odnajdź „Dodatkowe pole”, wybierz „PKWiU”, a następnie zaznacz „Zawsze umieszczaj na fakturze”.', 'woocommerce-fakturownia' )
				),
			],
			self::OPTION_LEGAL_BASIS               => [
				'title'       => __( 'Podstawa prawna zwolnienia', 'woocommerce-fakturownia' ),
				'type'        => 'text',
				'description' => __( 'Numer artykułu, uprawniającego do zwolnienia, np. dla zwolnienia podmiotowego: Art. 113 ust. 1.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'default'     => '',
				'class'       => 'fakturownia-invoice-without-vat',
			],
			self::OPTION_VALIDATE_CHECKOUT_NIP     => [
				'label'       => __( 'Włącz sprawdzanie poprawności numeru NIP na stronie zamówienia', 'woocommerce-fakturownia' ),
				'title'       => __( 'Poprawność numeru NIP', 'woocommerce-fakturownia' ),
				'type'        => 'checkbox',
				'description' => __( 'Niepoprawny NIP zablokuje możliwość złożenia zamówienia.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'default'     => 'no',
			],
			self::OPTION_PAYMENT_DATE              => [
				'title'   => __( 'Termin płatności dla faktur wysyłkowych (za pobraniem)', 'woocommerce-fakturownia' ),
				'type'    => 'select',
				'options' => $this->get_payment_date_options(),
				'default' => '7',
			],
			self::OPTION_PLACE_OF_ISSUE            => [
				'title'   => __( 'Miejsce wystawienia', 'woocommerce-fakturownia' ),
				'type'    => 'text',
				'default' => '',
			],
			self::OPTION_INVOICE_LANG              => [
				'title'       => __( 'Język faktur', 'woocommerce-fakturownia' ),
				'type'        => 'select',
				'options'     => [
					self::CLIENT_COUNTRY => __( 'Język kraju klienta, jeżeli dostępny lub angielski', 'woocommerce-fakturownia' ),
					'en'                 => __( 'Zawsze angielski', 'woocommerce-fakturownia' ),
					'pl'                 => __( 'Zawsze polski', 'woocommerce-fakturownia' ),
				],
				'description' => __( 'Dostępne języki: pl, en, en-GB, ar, cn, cz, de, es, et, fa, fr, hu, hr, it, nl, ru, sk, sl, tr', 'woocommerce-fakturownia' ),
				'default'     => self::CLIENT_COUNTRY,
			],
			self::OPTION_COMMENT_CONTENT           => [
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
			'danefakturyProForma'                  => [
				'title' => __( 'Parametry faktury proforma', 'woocommerce-fakturownia' ),
				'type'  => 'title',
			],
			self::OPTION_PAYMENT_TO_KIND           => [
				'title'    => __( 'Termin płatności dla faktur pro forma', 'woocommerce-fakturownia' ),
				'type'     => 'select',
				'desc_tip' => false,
				'options'  => $this->get_payment_date_options(),
				'default'  => '3',
			],
			self::OPTION_GENERATE_PROFORMA_STATUS  => [
				'title'       => __( 'Status zamówienia', 'woocommerce-fakturownia' ),
				'type'        => 'multiselect',
				'description' => __(
					'Wybierz status zamówienia przy którym faktura proforma zostanie automatycznie wystawiona.',
					'woocommerce-fakturownia'
				),
				'desc_tip'    => false,
				'class'       => 'fakturownia-select2 option-status',
				'options'     => WooCommerce::get_woocommerce_statuses(),
				'default'     => '1',
			],
			self::OPTION_AUTOSEND_INVOICE_PROFORMA => [
				'title'    => __( 'Automatycznie wysyłaj faktury proformy po wystawieniu', 'woocommerce-fakturownia' ),
				'type'     => 'checkbox',
				'desc_tip' => false,
				'default'  => 'yes',
				'class'    => 'option-autosend',
			],
			'moss'                                 => [
				'title'       => __( 'Wsparcie dla OSS', 'woocommerce-fakturownia' ),
				'type'        => 'title',
				// translators: link.
				'description' => sprintf( __( 'Procedura OSS jest rozszerzeniem MOSS. Od 07.2021 r. podatek VAT od wszystkich towarów musi być obliczany na podstawie lokalizacji klienta po przekroczeniu progu sprzedaży do krajów UE w wysokości 42.000 zł. Dlatego podczas transakcji należy potwierdzić adres IP i adres rozliczeniowy. Transakcje B2B podlegają odwrotnemu obciążeniu. <a href="%s" target="_blank">Przeczytaj ten przewodnik</a>, aby dowiedzieć się więcej.', 'woocommerce-fakturownia' ), $link ),
			],
			self::OPTION_MOSS_VIES_VALIDATION      => [
				'label'       => __( 'Enable VIES Validation on checkout', 'woocommerce-fakturownia' ),
				'title'       => __( 'VIES Validation', 'woocommerce-fakturownia' ),
				'type'        => 'checkbox',
				'description' => __( 'The VAT number field will be validated based on VIES.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'default'     => 'no',
			],
			self::OPTION_MOSS_FAILURE_HANDLING     => [
				'title'    => __( 'Failed Validation Handling', 'woocommerce-fakturownia' ),
				'type'     => 'select',
				'class'    => 'fakturownia-moss select',
				'desc_tip' => false,
				'options'  => $validation_handling_values,
				'default'  => 'reject',
			],
			self::OPTION_MOSS_TAX_CLASSES          => [
				'title'       => __( 'Stawki VAT dla OSS ', 'woocommerce-fakturownia' ),
				'type'        => 'multiselect',
				'desc_tip'    => false,
				'options'     => $this->get_tax_classes(),
				'class'       => 'fakturownia-select2 fakturownia-moss',
				'default'     => 'standard',
				'description' => __( 'Select the tax classes that the plugin shall use to handling the MOSS.', 'woocommerce-fakturownia' ),
			],
			self::OPTION_MOSS_VALIDATE_IP          => [
				'label'       => __( 'Collect and Validate Evidence', 'woocommerce-fakturownia' ),
				'title'       => __( 'Collect and Validate Evidence', 'woocommerce-fakturownia' ),
				'type'        => 'checkbox',
				'class'       => 'checkbox fakturownia-moss',
				'description' => __( 'Option validates the customer IP address against their billing address, and prompts the customer to self-declare their address if they do not match.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'default'     => 'no',
			],
		] + $lump_sum_fields + [
			'wystawianie_faktur_title_end' => [
				'type' => 'tab_close',
			],
		];
	}

	/**
	 * Get tax classes.
	 *
	 * @return array
	 */
	private function get_tax_classes() {
		$tax_classes                 = \WC_Tax::get_tax_classes();
		$classes_options             = [];
		$classes_options['standard'] = __( 'Standard', 'woocommerce-fakturownia' );
		foreach ( $tax_classes as $class ) {
			$classes_options[ sanitize_title( $class ) ] = esc_html( $class );
		}

		return $classes_options;
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
