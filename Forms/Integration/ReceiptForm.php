<?php

namespace WPDesk\WooCommerceFakturownia\Forms\Integration;

use FakturowniaVendor\WPDesk\Forms\AbstractForm;
use WPDesk\WooCommerceFakturownia\Helpers\WooCommerce;
use WPDesk\WooCommerceFakturownia\WoocommerceIntegration;

/**
 * Class ReceiptForm
 *
 * @package WPDesk\WooCommerceFakturownia\Forms
 */
class ReceiptForm extends AbstractForm {

	const SHORTCODE_ORDER_NUMBER = '[order_number]';

	const MANUAL_GENERATE                = 'r';
	const AUTO_GENERATE                  = 'a';
	const AUTO_GENERATE_WITHOUT_INVOICE  = 'i';
	const OPTION_AUTOSEND_RECEIPT        = 'autosend_receipt';
	const OPTION_GENERATE_RECEIPT_STATUS = 'generate_receipt_status';
	const OPTION_GENERATE_RECEIPT        = 'generate_receipt';
	const OPTION_PAYMENT_DATE            = 'payment_day_paragon';
	const OPTION_COMMENT_CONTENT         = 'comment_content_receipt';
	const OPTION_DOCUMENT_TYPE           = 'receipt';
	const OPTION_RECEIPT_LANG            = 'receipt_lang';
	const CLIENT_COUNTRY                 = 'client_country';

	/**
	 * Form ID.
	 *
	 * @var string
	 */
	protected $form_id = 'receipt';

	/**
	 * Is active.
	 *
	 * @var bool
	 */
	private $is_active = true;

	/**
	 * ReceiptForm constructor.
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
		return [
			'wystawianie_paragonow_title'        => [
				'title' => __( 'Wystawianie paragonów', 'woocommerce-fakturownia' ),
				'type'  => 'tab_open',
			],
			self::OPTION_GENERATE_RECEIPT        => [
				'title'   => __( 'Wystawianie paragonów', 'woocommerce-fakturownia' ),
				'type'    => 'select',
				'options' => [
					self::MANUAL_GENERATE               => __( 'Ręcznie', 'woocommerce-fakturownia' ),
					self::AUTO_GENERATE                 => __( 'Automatycznie', 'woocommerce-fakturownia' ),
					self::AUTO_GENERATE_WITHOUT_INVOICE => __( 'Automatycznie, jeśli kupujący nie chce faktury / rachunku', 'woocommerce-fakturownia' ),
				],
				'default' => '1',
				'class'   => 'select option-generate',
			],
			self::OPTION_GENERATE_RECEIPT_STATUS => [
				'title'       => __( 'Status zamówienia', 'woocommerce-fakturownia' ),
				'type'        => 'multiselect',
				'description' => __( 'Wybierz status zamówienia przy którym rachunek zostanie automatycznie wystawiony.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'options'     => WooCommerce::get_woocommerce_statuses(),
				'class'       => 'fakturownia-select2 option-status',
				'default'     => '1',
			],
			self::OPTION_AUTOSEND_RECEIPT        => [
				'title'    => __( 'Automatycznie wysyłaj paragon po wystawieniu', 'woocommerce-fakturownia' ),
				'type'     => 'checkbox',
				'desc_tip' => false,
				'default'  => 'yes',
				'class'    => 'option-autosend',
			],
			'dane_paragonu_title'                => [
				'title' => __( 'Parametry paragonu', 'woocommerce-fakturownia' ),
				'type'  => 'title',
			],
			self::OPTION_PAYMENT_DATE => [
				'title'       => __( 'Termin płatności dla paragonów', 'woocommerce-fakturownia' ),
				'type'        => 'select',
				'description' => __( 'Termin płatności dla paragonów w dniach.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'options'     => [
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
				],
				'default'     => '7',
			],
			self::OPTION_RECEIPT_LANG => [
				'title'       => __( 'Język paragonów', 'woocommerce-fakturownia' ),
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
			self::OPTION_COMMENT_CONTENT         => [
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
			'wystawianie_paragonow_title_end'    => [
				'type' => 'tab_close',
			],
		];
	}
}
