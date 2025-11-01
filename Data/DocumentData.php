<?php

namespace WPDesk\WooCommerceFakturownia\Data;

use FakturowniaVendor\WPDesk\Invoices\Exception\InvalidInvoiceDataException;
use FakturowniaVendor\WPDesk\Invoices\Field\VatNumber as VatNumberAlias;
use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumValues;
use WC_Order;
use FakturowniaVendor\WPDesk\Invoices\Data\InvoiceData;
use FakturowniaVendor\WPDesk\Invoices\Data\Items\InvoiceItem;
use WPDesk\WooCommerceFakturownia\Api\FakturowniaApi;
use WPDesk\WooCommerceFakturownia\Api\Products\NoProductsFoundException;
use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;
use WPDesk\WooCommerceFakturownia\Helpers\Translations;
use WPDesk\WooCommerceFakturownia\Helpers\WooCommerceSettings;
use WPDesk\WooCommerceFakturownia\Integrations\FQIntegration;
use WPDesk\WooCommerceFakturownia\Product\FakturowniaProductCreator;
use WPDesk\WooCommerceFakturownia\Product\FakturowniaProductId;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;

/**
 * Decorator for InvoiceData class.
 */
class DocumentData extends InvoiceData {

	public const         INVOICE_STATUS           = 'status';
	public const         INVOICE_STATUS_PAID      = 'paid';
	public const         INVOICE_STATUS_ISSUED    = 'issued';
	private const        INVOICE_STATUS_PARTIAL   = 'partial';
	public const         INVOICE_REVERSE_CHARGE   = 'reverse_charge';
	public const         DOCUMENT_KIND            = 'kind';
	public const         INVOICE_NUMBER           = 'number';
	public const         INVOICE_PLACE            = 'place';
	public const         INVOICE_SELL_DATE        = 'sell_date';
	public const         INVOICE_ISSUE_DATE       = 'issue_date';
	public const         INVOICE_PAYMENT_TO       = 'payment_to';
	public const         SELLER_TAX_NO            = 'seller_tax_no';
	public const         CURRENCY                 = 'currency';
	public const         TAX_RATE                 = 'tax';
	public const USE_OSS                          = 'use_oss';
	public const         TAX_RATE_TYPE_ZW         = 'zw';
	public const         TAX_RATE_TYPE_NP         = 'np';
	public const         TAX_RATE_TYPE_DISABLE    = 'disabled'; // This value hide column.
	public const         DOCUMENT_ITEMS           = 'positions';
	public const         ITEM_PRICE_NET           = 'price_net';
	public const         ITEM_PRICE_GROSS         = 'price_gross';
	public const         ITEM_TOTAL_PRICE_GROSS   = 'total_price_gross';
	public const         ITEM_TOTAL_PRICE_NET     = 'total_price_net';
	public const         ITEM_QUANTITY            = 'quantity';
	public const         ITEM_QUANTITY_UNIT       = 'quantity_unit';
	public const         ITEM_QUANTITY_UNIT_VALUE = 'szt';
	public const         ITEM_FULL_NAME           = 'name';
	public const         ITEM_ADDITIONAL_INFO     = 'additional_info';
	public const         BUYER_NAME               = 'buyer_name';
	public const         BUYER_TAX_NO             = 'buyer_tax_no';
	public const         BUYER_STREET             = 'buyer_street';
	public const         BUYER_POST_CODE          = 'buyer_post_code';
	public const         BUYER_CITY               = 'buyer_city';
	public const         BUYER_EMAIL              = 'buyer_email';
	public const         BUYER_PHONE              = 'buyer_phone';
	public const         BUYER_COUNTRY            = 'buyer_country';
	public const         COMMENTS                 = 'description';
	public const         OPTION_EXEMPT_TAX_KIND   = 'exempt_tax_kind';
	public const         PKWIU                    = 'PKWiU';
	public const         ADDITIONAL_DATA_PKWIU    = 'pkwiu';
	public const         ADDITIONAL_INFO_DESC     = 'additional_info_desc';
	public const         ADDITIONAL_INFO          = 'additional_info';

	public const PAYMENT_TYPE = 'payment_type';

	public const PRODUCT_ID = 'product_id';

	public const INVOICE_LANG          = 'lang';
	public const CLIENT_COUNTRY_OPTION = 'client_country';
	public const LUMP_SUM              = 'lump_sum_tax';

	public const SUPPORTED_LANGUAGES = [
		'pl',
		'en',
		'en-GB',
		'ar',
		'cn',
		'cz',
		'de',
		'es',
		'et',
		'fa',
		'fr',
		'hu',
		'hr',
		'it',
		'nl',
		'ru',
		'sk',
		'sl',
		'tr',
	];

	/**
	 * @var \FakturowniaVendor\WPDesk\Invoices\Data\InvoiceData
	 */
	private $invoice_data;

	/**
	 * @var InvoicesIntegration
	 */
	protected $invoice_integration;

	/**
	 * @var FakturowniaApi
	 */
	private $fakturownia_api;

	/**
	 * @var FakturowniaProductCreator
	 */
	private $fakturownia_product_creator;

	/**
	 * @var string
	 */
	private $bank_account_number;

	/**
	 * @var string
	 */
	private $bank_account_name;

	/**
	 * @var string
	 */
	private $sale_date_format;

	/**
	 * @var string
	 */
	private $payment_date;

	/**
	 * @var string
	 */
	private $numbering_series;

	/**
	 * @var string
	 */
	private $template_name;

	/**
	 * @var string
	 */
	private $place_of_issue;

	/**
	 * @var string
	 */
	private $recipient_signature_type;

	/**
	 * @var string
	 */
	private $recipient_signature;

	/**
	 * @var string
	 */
	private $issuer_signature;

	/**
	 * @var string
	 */
	private $legal_basis;

	/**
	 * @var string
	 */
	private $comments;

	/**
	 * @var string
	 */
	private $tax_rate_exempt;

	/**
	 * @var float
	 */
	private $total_price;

	/**
	 * @var WC_Order
	 */
	private $order;

	/**
	 * @var bool
	 */
	private $has_zw = false;

	/**
	 * @param InvoiceData         $invoice_data    Invoice data.
	 * @param InvoicesIntegration $integration     Integration.
	 * @param FakturowniaApi      $fakturownia_api Fakturownia API.
	 */
	public function __construct(
		InvoiceData $invoice_data,
		InvoicesIntegration $integration,
		FakturowniaApi $fakturownia_api
	) {
		$this->invoice_data        = $invoice_data;
		$this->invoice_integration = $integration;
		$this->fakturownia_api     = $fakturownia_api;

		if ( InvoiceForm::TAX_EXEMPT_OPTION_NONE !== $integration->woocommerce_integration->getOptionTaxRateExempt() && ! empty( $integration->woocommerce_integration->getOptionPkwiuAttribute() ) ) {
			$this->setPkwiuFromAttribute( $integration->woocommerce_integration->getOptionPkwiuAttribute() );
		}
	}

	/**
	 * Create from order.
	 *
	 * @param WC_Order             $order            Order.
	 * @param VatNumberAlias|null  $vat_number_field Vat number field.
	 * @param InvoiceOrderDefaults $defaults         Defaults.
	 * @param InvoicesIntegration  $integration      Integration.
	 *
	 * @return InvoiceData
	 * @throws InvalidInvoiceDataException .
	 */
	public static function createFromOrderAndDefaultsAndSettings(
		WC_Order $order,
		$vat_number_field,
		InvoiceOrderDefaults $defaults,
		InvoicesIntegration $integration
	) {

		$invoice_data         = parent::createFromOrder( $order, $vat_number_field, '' );
		$invoice_data         = new static( $invoice_data, $integration, $integration->get_api() );
		$woocommerce_settings = new WooCommerceSettings();
		$invoice_data->setItems( $invoice_data->removeZeroPriceItems( $invoice_data->getItems() ) );

		if ( empty( $invoice_data->getItems() ) ) {
			throw new InvalidInvoiceDataException( __( 'Zamówienie nie zawiera pozycji o niezerowej wartości!', 'woocommerce-fakturownia' ) );
		}

		$invoice_data->setOrder( $order );
		$invoice_data->setTotalPrice( $order );
		$invoice_data->setPaidAmount( wc_format_decimal( $defaults->getDefault( 'paid_amount' ) ) );
		$invoice_data->setIssueDate( $defaults->getDefault( 'issue_date' ) );
		$invoice_data->setSaleDate( $defaults->getDefault( 'sale_date' ) );
		$invoice_data->setPaymentDate( $defaults->getDefault( 'payment_date' ) );
		$invoice_data->setPaymentMethod( $defaults->getDefault( 'payment_method' ) );
		$invoice_data->setComments( $defaults->getDefault( 'comments' ) );

		$invoice_data->setIssuerSignature( $defaults->getDefault( 'issuer_signature' ) );
		$invoice_data->setLegalBasis( $integration->woocommerce_integration->getOptionLegalBasis() );

		$invoice_data->setRecipientSignatureType( 'BPO' );
		$invoice_data->setPlaceOfIssue( $integration->woocommerce_integration->getOptionPlaceOfIssue() );

		$invoice_data->setTaxRateExempt( $integration->woocommerce_integration->getOptionTaxRateExempt() );

		$invoice_data->setProductNameOnItems();

		if ( $integration->woocommerce_integration->is_warehouse_enabled() && 'yes' === $integration->woocommerce_integration->getOptionCreateProducts() ) {
			$invoice_data->setFakturowniaProductCreator( new FakturowniaProductCreator( $integration->get_api(), $woocommerce_settings ) );
		}

		return $invoice_data;
	}

	/**
	 * Set product creator.
	 *
	 * @param FakturowniaProductCreator $fakturowania_product_creator .
	 */
	private function setFakturowniaProductCreator( FakturowniaProductCreator $fakturowania_product_creator ) {
		$this->fakturownia_product_creator = $fakturowania_product_creator;
	}

	/**
	 * Set product codes on items.
	 */
	protected function setProductNameOnItems() {
		$items = $this->getItems();
		foreach ( $items as $item_key => $item ) {
			if ( $item->getOrderItem()->is_type( 'line_item' ) ) {
				$product = $item->getOrderItem()->get_product();
				if ( $product instanceof \WC_Product ) {
					$product_name = $product->get_meta( FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID );
					if ( ! empty( $product_name ) ) {
						$item->setName( $product_name );
					}
				}
			}
		}
	}

	/**
	 * Set order.
	 *
	 * @param WC_Order $order Order.
	 */
	protected function setOrder( WC_Order $order ) {
		$this->order = $order;
	}

	/**
	 * Get order.
	 *
	 * @return WC_Order
	 */
	public function getOrder() {
		return $this->order;
	}

	/**
	 * Set order price total
	 *
	 * @param WC_Order $order Order.
	 */
	protected function setTotalPrice( WC_Order $order ) {
		$this->total_price = floatval( $order->get_total() );
	}

	/**
	 * Get total price.
	 *
	 * @return float
	 */
	public function getTotalPrice() {
		return $this->total_price;
	}

	/**
	 * Add item.
	 *
	 * @param InvoiceItem $item Item.
	 */
	protected function addItem( InvoiceItem $item ) {
		$this->invoice_data->addItem( $item );
	}

	/**
	 * Get items.
	 *
	 * @return InvoiceItem[]
	 */
	public function getItems() {
		return $this->invoice_data->getItems();
	}

	/**
	 * Set items.
	 *
	 * @param InvoiceItem[] $items Items.
	 */
	public function setItems( $items ) {
		$this->invoice_data->setItems( $items );
	}

	/**
	 * Get client data.
	 *
	 * @return \FakturowniaVendor\WPDesk\Invoices\Data\ClientData
	 */
	public function getClientData() {
		return $this->invoice_data->getClientData();
	}

	/**
	 * Set client data.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Data\ClientData $client_data Client data.
	 */
	public function setClientData( $client_data ) {
		$this->invoice_data->setClientData( $client_data );
	}

	/**
	 * Is prices include tax.
	 *
	 * @return bool
	 */
	public function isPricesIncludeTax() {
		return $this->invoice_data->isPricesIncludeTax();
	}

	/**
	 * Set prices include tax.
	 *
	 * @param bool $prices_include_tax Proces include tax.
	 */
	public function setPricesIncludeTax( $prices_include_tax ) {
		$this->invoice_data->setPricesIncludeTax( $prices_include_tax );
	}

	/**
	 * Get paid amount.
	 *
	 * @return float
	 */
	public function getPaidAmount() {
		return floatval( $this->round( $this->invoice_data->getPaidAmount() ) );
	}

	/**
	 * Set paid amount.
	 *
	 * @param float $paid_amount Paid amount.
	 */
	public function setPaidAmount( $paid_amount ) {
		$this->invoice_data->setPaidAmount( $paid_amount );
	}

	/**
	 * Get sale date.
	 *
	 * @return string
	 */
	public function getSaleDate() {
		return $this->invoice_data->getSaleDate();
	}

	/**
	 * Set sale date.
	 *
	 * @param string $sale_date Sale date.
	 */
	public function setSaleDate( $sale_date ) {
		$this->invoice_data->setSaleDate( $sale_date );
	}

	/**
	 * Get issue date.
	 *
	 * @return string
	 */
	public function getIssueDate() {
		return $this->invoice_data->getIssueDate();
	}

	/**
	 * Set issue date.
	 *
	 * @param string $issue_date Issue date.
	 */
	public function setIssueDate( $issue_date ) {
		$this->invoice_data->setIssueDate( $issue_date );
	}

	/**
	 * Get payment amount.
	 *
	 * @return string
	 */
	public function getPaymentMethod() {
		return $this->invoice_data->getPaymentMethod();
	}

	/**
	 * Set payment method.
	 *
	 * @param string $payment_method Payment method.
	 */
	public function setPaymentMethod( $payment_method ) {
		$this->invoice_data->setPaymentMethod( $payment_method );
	}

	/**
	 * Get currency.
	 */
	public function getCurrency() {
		return $this->invoice_data->getCurrency();
	}

	/**
	 * Set currency.
	 *
	 * @param string $currency Currency.
	 */
	public function setCurrency( $currency ) {
		$this->invoice_data->setCurrency( $currency );
	}

	/**
	 * Get bank account name.
	 *
	 * @return string
	 */
	public function getBankAccountName() {
		if ( '' === $this->bank_account_name ) {
			return null;
		}

		return $this->bank_account_name;
	}

	/**
	 * Set bank account name.
	 *
	 * @param string $bank_account_name Bank account.
	 */
	public function setBankAccountName( $bank_account_name ) {
		$this->bank_account_name = $bank_account_name;
	}

	/**
	 * Get bank account number.
	 *
	 * @return string
	 */
	public function getBankAccountNumber() {
		if ( '' === $this->bank_account_number ) {
			return null;
		}

		return $this->bank_account_number;
	}

	/**
	 * Set bank account number.
	 *
	 * @param string $bank_account_number Bank account.
	 */
	public function setBankAccountNumber( $bank_account_number ) {
		$this->bank_account_number = $bank_account_number;
	}

	/**
	 * Get place of issue.
	 *
	 * @return string
	 */
	public function getPlaceOfIssue() {
		return $this->place_of_issue;
	}

	/**
	 * Set place of issue.
	 *
	 * @param string $place_of_issue Place of issue.
	 */
	public function setPlaceOfIssue( $place_of_issue ) {
		$this->place_of_issue = $place_of_issue;
	}

	/**
	 * Get payment date.
	 *
	 * @return string Payment date.
	 */
	public function getPaymentDate() {
		return $this->payment_date;
	}

	/**
	 * Set payment date.
	 *
	 * @param string $payment_date Payment date.
	 */
	public function setPaymentDate( $payment_date ) {
		$this->payment_date = $payment_date;
	}

	/**
	 * Get numbering series.
	 *
	 * @return string
	 */
	public function getNumberingSeries() {
		return $this->numbering_series;
	}

	/**
	 * Set numbering series.
	 *
	 * @param string $numbering_series Numbering series.
	 */
	public function setNumberingSeries( $numbering_series ) {
		$this->numbering_series = $numbering_series;
	}

	/**
	 * Get template name.
	 *
	 * @return string
	 */
	public function getTemplateName() {
		if ( '' === $this->template_name ) {
			return null;
		}

		return $this->template_name;
	}

	/**
	 * Set template name.
	 *
	 * @param string $template_name Template name.
	 */
	public function setTemplateName( $template_name ) {
		$this->template_name = $template_name;
	}

	/**
	 * Get recipient signature type.
	 *
	 * @return string
	 */
	public function getRecipientSignatureType() {
		return $this->recipient_signature_type;
	}

	/**
	 * Set recipient signature type.
	 *
	 * @param string $recipient_signature_type Recipient signature type.
	 */
	public function setRecipientSignatureType( $recipient_signature_type ) {
		$this->recipient_signature_type = $recipient_signature_type;
	}

	/**
	 * Get recipient signature.
	 *
	 * @return string
	 */
	public function getRecipientSignature() {
		return $this->recipient_signature;
	}

	/**
	 * Set recipient signature.
	 *
	 * @param string $recipient_signature Recipient signature.
	 */
	public function setRecipientSignature( $recipient_signature ) {
		$this->recipient_signature = $recipient_signature;
	}

	/**
	 * Get issuer signature.
	 *
	 * @return string
	 */
	public function getIssuerSignature() {
		return $this->issuer_signature;
	}

	/**
	 * Set issuer signature.
	 *
	 * @param string $issuer_signature Issuer signature.
	 */
	public function setIssuerSignature( $issuer_signature ) {
		$this->issuer_signature = $issuer_signature;
	}

	/**
	 * Get comments.
	 *
	 * @return string
	 */
	public function getComments() {
		return $this->comments;
	}

	/**
	 * Set comments.
	 *
	 * @param string $comments Comments.
	 */
	public function setComments( $comments ) {
		$this->comments = $comments;
	}

	/**
	 * Get legal basis.
	 *
	 * @return string
	 */
	public function getLegalBasis() {
		return $this->legal_basis;
	}

	/**
	 * Set legal basis.
	 *
	 * @param string $legal_basis Legal basis.
	 */
	public function setLegalBasis( $legal_basis ) {
		$this->legal_basis = $legal_basis;
	}

	/**
	 * Get tax rate exempt.
	 *
	 * @return string
	 */
	public function getTaxRateExempt() {
		return $this->tax_rate_exempt;
	}

	/**
	 * Set tax rate exempt.
	 *
	 * @param string $tax_rate_exempt Tax rate exempt.
	 */
	public function setTaxRateExempt( $tax_rate_exempt ) {
		$this->tax_rate_exempt = $tax_rate_exempt;
	}

	/**
	 * @param float $price    Order price.
	 * @param float $tax_rate Tax price.
	 *
	 * @return float
	 */
	protected function getTaxTotalPrice( $price, $tax_rate ) {
		return $price + $price * $tax_rate / 100;
	}

	/**
	 * Set PKWIU from attribute.
	 *
	 * @param string $attribute_name Attribute name.
	 */
	private function setPkwiuFromAttribute( $attribute_name ) {
		$items = $this->getItems();
		foreach ( $items as $item_key => $item ) {
			$order_item = $item->getOrderItem();
			if ( $order_item->is_type( 'line_item' ) ) {
				$product = $order_item->get_product();
				if ( $product instanceof \WC_Product ) {
					$pkwiu = $product->get_attribute( $attribute_name );
					if ( ! empty( $pkwiu ) ) {
						$item->addAdditionalData( self::ADDITIONAL_DATA_PKWIU, $pkwiu );
					}
				}
			}
		}
		$this->setItems( $items );
	}

	/**
	 * Find or create Fakturownia product for SKU.
	 *
	 * @param \WC_Product $product .
	 *
	 * @return array
	 * @throws \WPDesk\HttpClient\HttpClientRequestException .
	 * @throws NoProductsFoundException .
	 */
	private function findOrCreateFakturowniaProductForSKU( \WC_Product $product ) {
		$sku = $product->get_sku();
		try {
			$fakturownia_product = $this->fakturownia_api->get_product_by_code( $sku );
		} catch ( NoProductsFoundException $e ) {
			if ( ! empty( $this->fakturownia_product_creator ) ) {
				$fakturownia_product = $this->fakturownia_product_creator->create_from_product( $product );
				$this->fakturownia_product_creator->set_fakturownia_product_id_on_product( $product, $fakturownia_product['id'] );
			} else {
				throw $e;
			}
		}

		return $fakturownia_product;
	}

	/**
	 * Prepare item identity.
	 *
	 * @param array       $item_data .
	 * @param InvoiceItem $item      .
	 *
	 * @return array
	 * @throws \WPDesk\HttpClient\HttpClientRequestException .
	 * @throws NoProductsFoundException .
	 */
	protected function prepareItemIdentity( array $item_data, InvoiceItem $item ) {
		if ( $this->invoice_integration->woocommerce_integration->is_warehouse_enabled() ) {
			$order_item = $item->getOrderItem();
			if ( $order_item->is_type( 'line_item' ) ) {
				/** @var \WC_Product $product */
				$product      = $order_item->get_product();
				$is_variation = $product->is_type( 'variation' );

				$fakturownia_product_id = $product->get_meta( FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID );

				if ( $is_variation && empty( $fakturownia_product_id ) ) {
					$parent_id              = $product->get_parent_id();
					$parent_product         = wc_get_product( $parent_id );
					$fakturownia_product_id = $parent_product->get_meta( FakturowniaProductId::FAKTUROWNIA_PRODUCT_ID );
				}

				if ( ! empty( $fakturownia_product_id ) ) {
					$item_data[ self::PRODUCT_ID ] = $fakturownia_product_id;
					unset( $item_data[ self::ITEM_FULL_NAME ] );
				} elseif ( ! empty( $product->get_sku() ) ) {
						$fakturownia_product           = $this->findOrCreateFakturowniaProductForSKU( $product );
						$item_data[ self::PRODUCT_ID ] = $fakturownia_product['id'];
				} else {
					// Translators: product name.
					throw new NoProductsFoundException( sprintf( __( 'Produkt %1$s nie jest powiązany z żadnym produktem w Fakturowni oraz nie ma zdefiniowanego SKU.', 'woocommerce-fakturownia' ), $product->get_name() ) );
				}
			}
		}
		if ( empty( $item_data[ self::PRODUCT_ID ] ) ) {
			$item_data[ self::ITEM_FULL_NAME ] = $this->get_item_full_name( $item );
		}

		return $item_data;
	}

	protected function get_item_full_name( InvoiceItem $item ): string {
		return $item->getOrderItem()->get_name();
	}

	/**
	 * Remove items with 0 (zero) price.
	 *
	 * @param InvoiceItem[] $items .
	 *
	 * @return InvoiceItem[]
	 */
	private function removeZeroPriceItems( $items ) {
		foreach ( $items as $key => $item ) {
			if ( floatval( 0 ) === floatval( number_format( $item->getPrice(), 2 ) ) ) {
				unset( $items[ $key ] );
			}
		}

		return $items;
	}

	/**
	 * Get MOSS Counties.
	 *
	 * @return array
	 */
	protected function get_eu_countries() {
		return WC()->countries->get_european_union_countries();
	}

	/**
	 * @param \WC_Order_Item $item
	 *
	 * @return string
	 */
	private function get_item_qty( \WC_Order_Item $item ): string {
		$default_qty = $item->get_quantity();
		$item_meta   = $item->get_meta( '_fq_measurement_data' );
		if ( ! empty( $item_meta ) ) {
			$fq_qty = $item_meta['_measurement_needed'] ?? '';
			if ( $fq_qty ) {
				return (float) $fq_qty * $default_qty;
			}
		}

		return (float) $default_qty;
	}


	protected function get_line_items( array $items ): array {
		$line_items = [];

		foreach ( $items as $item ) {
			$order_item = $item->getOrderItem();
			if ( $order_item->is_type( 'line_item' ) ) {
				$line_items[] = $order_item;
			}
		}

		return $line_items;
	}

	private function get_lump_sum_for_shipping( array $items ) {
		$lump_sums = [];
		foreach ( $items as $item ) {
			$item_lump_sum = $this->get_product_lump_sum( $item );
			$lump_sums[]   = ( $item_lump_sum === LumpSumValues::LUMP_SUM_DEFAULT_VALUE || $item_lump_sum === LumpSumValues::LUMP_SUM_EMPTY_VALUE ) ? '' : (float) str_replace( ',', '.', $item_lump_sum );
		}

		return apply_filters( 'fakturownia/lump_sums/shipping_lump_sums', max( $lump_sums ), $items );
	}

	/**
	 * Prepare items as array.
	 *
	 * @return array
	 * @throws \WPDesk\HttpClient\HttpClientRequestException .
	 */
	protected function prepareItemsAsArray() {
		$items_data = [];

		$client_data = $this->getClientData();

		$items = $this->getItems();
		if ( $this->invoice_integration->woocommerce_integration->is_lump_sum_enabled() ) {
			$line_items                 = $this->get_line_items( $items );
			$this->lumpsum_for_shipping = $this->get_lump_sum_for_shipping( $line_items );
		}

		foreach ( $items as $item ) {
			$items_data[] = $this->get_item_data_for_invoice( $item, $client_data );
		}

		return $items_data;
	}

	protected function get_item_data_for_invoice( $item, $client_data ): array {
		$order_item = $item->getOrderItem();
		$fq         = new FQIntegration( $order_item );

		$item_data                             = [];
		$item_data                             = $this->prepareItemIdentity( $item_data, $item );
		$item_data[ self::ITEM_QUANTITY ]      = $item->getQuantity();
		$item_data[ self::ITEM_QUANTITY_UNIT ] = $fq->get_item_unit( self::ITEM_QUANTITY_UNIT_VALUE );
		$item_data[ self::TAX_RATE ]           = $item->getTaxRate();

		if ( $this->isPricesIncludeTax() ) {
			$item_data[ self::ITEM_PRICE_GROSS ]       = $item->getPrice();
			$item_data[ self::ITEM_TOTAL_PRICE_GROSS ] = $this->round( $item->getPrice() * $item->getQuantity() );
		} else {
			$item_data[ self::ITEM_PRICE_NET ]         = $item->getPrice();
			$item_data[ self::ITEM_PRICE_GROSS ]       = $this->getTaxTotalPrice( $item->getPrice(), $item->getTaxRate() );
			$item_data[ self::ITEM_TOTAL_PRICE_NET ]   = $item->getPrice() * $item->getQuantity();
			$item_data[ self::ITEM_TOTAL_PRICE_GROSS ] = $this->getTaxTotalPrice( $item->getPrice(), $item->getTaxRate() ) * $item->getQuantity();
		}
		if ( $this->invoice_integration->woocommerce_integration->is_lump_sum_enabled() ) {
			if ( $order_item->is_type( 'line_item' ) ) {
				$item_lump_sum = $this->get_product_lump_sum( $order_item );
				if ( $item_lump_sum && $item_lump_sum !== LumpSumValues::LUMP_SUM_DEFAULT_VALUE && $item_lump_sum !== LumpSumValues::LUMP_SUM_EMPTY_VALUE ) {
					$item_data[ self::LUMP_SUM ] = $item_lump_sum;
				}
			} elseif ( $order_item->is_type( 'shipping', 'fee' ) ) {
				$item_data[ self::LUMP_SUM ] = apply_filters( 'fakturownia/lump_sums/single_shipping_lump_sum', str_replace( '.', ',', $this->lumpsum_for_shipping ), $order_item );
			}
		}

		if ( 0.0 === $item->getTaxRate() && 'none' !== $this->getTaxRateExempt() ) {
			if ( $this->getTaxRateExempt() === $item->getTaxClass() ) {
				$item_data[ self::TAX_RATE ] = self::TAX_RATE_TYPE_ZW;
				$this->has_zw                = true;

				$pkwiu                                   = $item->getAdditionalDataByName( self::ADDITIONAL_DATA_PKWIU );
				$item_data[ self::ITEM_ADDITIONAL_INFO ] = $pkwiu ?? '';
			}
		}
		$eu_vat_countries = $this->get_eu_countries();
		if ( ! in_array( $client_data->getCountry(), $eu_vat_countries, true ) ) {
			if ( (int) $item->getTaxRate() > 0 ) {
				$item_data[ self::TAX_RATE ] = $item->getTaxRate();
			} else {
				$item_data[ self::TAX_RATE ] = self::TAX_RATE_TYPE_NP;
			}
		}

		return $item_data;
	}


	private function get_product_lump_sum( $item ): string {

		$product_id   = $item->get_product_id();
		$variation_id = $item->get_variation_id();

		$lump_sum = ( new LumpSumValues() )->get_product_lump_sum( $product_id, $variation_id, $this->invoice_integration->woocommerce_integration->get_default_lump_sum() );

		/* Replace dot with comma so Fakturownia API can read it correctly. */

		return str_replace( '.', ',', $lump_sum );
	}

	/**
	 * Prepare recipient as array.
	 *
	 * @return array
	 */
	private function prapareRecipientAsArray() {
		$recipient   = [];
		$client_data = $this->getClientData();
		if ( $client_data->isComapny() ) {
			$recipient[ self::BUYER_NAME ] = wp_specialchars_decode( $client_data->getCompanyName() );
		} else {
			$recipient[ self::BUYER_NAME ] = wp_specialchars_decode( $client_data->getFirstName() . ' ' . $client_data->getLastName() );
		}

		$recipient[ self::BUYER_TAX_NO ] = $client_data->getVatNumber();
		$recipient[ self::BUYER_STREET ] = wp_specialchars_decode( $client_data->getAddress() );
		$address_2                       = wp_specialchars_decode( $client_data->getAddress2() );
		if ( ! empty( $address_2 ) && '' !== trim( $address_2 ) ) {
			$recipient[ self::BUYER_STREET ] .= ' ' . $address_2;
		}
		$recipient[ self::BUYER_POST_CODE ] = $client_data->getPostCode();

		$recipient[ self::BUYER_CITY ]    = wp_specialchars_decode( $client_data->getCity() );
		$recipient[ self::BUYER_EMAIL ]   = $client_data->getEmail();
		$recipient[ self::BUYER_PHONE ]   = $client_data->getPhone();
		$recipient[ self::BUYER_COUNTRY ] = $client_data->getCountry();

		return $recipient;
	}

	/**
	 * Is partial paid status
	 *
	 * @return bool
	 */
	private function is_partial_paid_status() {
		return ( $this->getTotalPrice() - $this->getPaidAmount() > 0 );
	}

	/**
	 * Prepare paid data as array
	 */
	private function preparePaidStatuses() {
		if ( InvoiceOrderDefaults::PAYMENT_METHOD_COD === $this->getPaymentMethod() && (float) $this->getPaidAmount() === 0.0 ) {
			$data[ self::INVOICE_STATUS ]      = self::INVOICE_STATUS_ISSUED;
			$data[ self::INVOICE_STATUS_PAID ] = 0;
		} elseif ( $this->getPaidAmount() === 0.0 ) {
			$data[ self::INVOICE_STATUS ]      = self::INVOICE_STATUS_ISSUED;
			$data[ self::INVOICE_STATUS_PAID ] = $this->getPaidAmount();
		} elseif ( $this->is_partial_paid_status() ) {
			$data[ self::INVOICE_STATUS ]      = self::INVOICE_STATUS_PARTIAL;
			$data[ self::INVOICE_STATUS_PAID ] = $this->getPaidAmount();
		} else {
			$data[ self::INVOICE_STATUS ] = self::INVOICE_STATUS_PAID;
		}

		return $data;
	}

	/**
	 * Prepare data as array.
	 *
	 * @return array
	 */
	public function prepareDataAsArray() {
		$data                                 = [];
		$data[ self::DOCUMENT_KIND ]          = 'vat';
		$data[ self::INVOICE_NUMBER ]         = null;
		$data[ self::INVOICE_PLACE ]          = wp_specialchars_decode( $this->getPlaceOfIssue() );
		$data[ self::INVOICE_ISSUE_DATE ]     = $this->getIssueDate();
		$data[ self::INVOICE_SELL_DATE ]      = $this->getSaleDate();
		$data[ self::INVOICE_PAYMENT_TO ]     = $this->getPaymentDate();
		$data[ self::INVOICE_REVERSE_CHARGE ] = false;
		$data[ self::CURRENCY ]               = $this->getCurrency();
		$data[ self::SELLER_TAX_NO ]          = null;
		$data[ self::COMMENTS ]               = wp_specialchars_decode( $this->getComments() );
		$data[ self::ADDITIONAL_INFO ]        = 0;
		$data[ self::DOCUMENT_ITEMS ]         = $this->prepareItemsAsArray();
		$data[ self::PAYMENT_TYPE ]           = $this->getRealPaymentMethodName( $this->getPaymentMethod() );
		$data[ self::INVOICE_LANG ]           = $this->get_client_lang();
		$data                                += $this->prapareRecipientAsArray();
		$data                                += $this->preparePaidStatuses();
		if ( $this->has_zw ) {
			$data[ self::ADDITIONAL_INFO_DESC ]   = self::PKWIU;
			$data[ self::OPTION_EXEMPT_TAX_KIND ] = $this->getLegalBasis();
		}

		$warehouse_id = $this->invoice_integration->woocommerce_integration->getWarehouseID();
		if ( $this->invoice_integration->woocommerce_integration->is_warehouse_enabled() ) {
			$data['warehouse_id'] = (int) $warehouse_id;
		}

		$this->maybe_add_oss_data( $data );

		$this->invoice_integration->get_logger()->debug( 'Fakturownia: ', $data );

		return $data;
	}

	protected function maybe_add_oss_data( &$data ) {
		if ( $this->is_b2b_moss() || $this->is_customer_moss() ) {
			$data[ self::USE_OSS ] = 1;
			unset( $data[ self::OPTION_EXEMPT_TAX_KIND ] );
		}
		if ( $this->is_b2b_moss() ) {
			$items = [];
			foreach ( $data[ self::DOCUMENT_ITEMS ] as $item ) {
				$item['tax'] = Translations::eu_vat_translation( $this->get_client_lang() );
				$items[]     = $item;
			}
			$data[ self::DOCUMENT_ITEMS ]         = $items;
			$data[ self::INVOICE_REVERSE_CHARGE ] = true;
			$data[ self::COMMENTS ]               = $data[ self::COMMENTS ] . PHP_EOL . __( 'Reverse charge', 'woocommerce-fakturownia' );
		}
	}

	protected function is_b2b_moss(): bool {
		return filter_var( $this->getOrder()->get_meta( 'is_vat_exempt' ), FILTER_VALIDATE_BOOLEAN );
	}

	private function is_customer_moss(): bool {
		return filter_var( $this->getOrder()->get_meta( '_customer_self_declared_country' ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get client lang based on country.
	 *
	 * @return string
	 */
	protected function get_client_lang() {
		$client_data  = $this->getClientData();
		$billing_lang = strtolower( $client_data->getCountry() );
		if ( $this->invoice_integration->woocommerce_integration->getOptionInvoiceLang() === self::CLIENT_COUNTRY_OPTION ) {
			if ( in_array( $billing_lang, self::SUPPORTED_LANGUAGES, true ) ) {
				return $billing_lang;
			}

			return 'en';
		}

		return $this->invoice_integration->woocommerce_integration->getOptionInvoiceLang();
	}


	/**
	 * Convert to Json string.
	 */
	public function toJsonString() {
		return wp_json_encode( $this->prepareDataAsArray(), JSON_PRETTY_PRINT );
	}

	/**
	 * Roud number.
	 *
	 * @param float $float Float number.
	 *
	 * @return float
	 */
	protected function round( $float ) {
		return round( $float, 2 );
	}

	/**
	 * @param string $payment_method
	 *
	 * @return mixed|string
	 */
	public function getRealPaymentMethodName( $payment_method ) {
		if ( WC()->payment_gateways() ) {
			$payment_gateways = WC()->payment_gateways->payment_gateways();
		} else {
			$payment_gateways = [];
		}

		if ( isset( $payment_gateways[ $payment_method ] ) ) {
			return $payment_gateways[ $payment_method ]->get_title();
		}

		return $payment_method;
	}
}
