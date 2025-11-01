<?php

namespace WPDesk\WooCommerceFakturownia;

use Exception;
use FakturowniaVendor\WPDesk\Invoices\WooCommerce\DocumentCreatorForOrderStatus;
use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumFields;
use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumProductFields;
use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumValues;
use FakturowniaVendor\WPDesk\WooCommerce\EUVAT\Integration\ValidateOSS;
use FakturowniaVendor\Psr\Log\LoggerInterface;
use WC_Order;
use FakturowniaVendor\WPDesk\ApiClient\Client\ClientFactory;
use FakturowniaVendor\WPDesk\Invoices\Documents\Type;
use FakturowniaVendor\WPDesk\Invoices\Field\InvoiceAsk;
use FakturowniaVendor\WPDesk\Invoices\Field\VatNumber;
use FakturowniaVendor\WPDesk\Invoices\OrdersTable\OrderColumn;
use FakturowniaVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\WooCommerceFakturownia\Block\VatNumber\DataStore;
use WPDesk\WooCommerceFakturownia\Field\RequiredVatNumber;
use WPDesk\WooCommerceFakturownia\Helpers\WooCommerceSettings;
use WPDesk\WooCommerceFakturownia\Logger\LoggerIntegration;
use WPDesk\WooCommerceFakturownia\Api\ClientOptions;
use WPDesk\WooCommerceFakturownia\Api\FakturowniaApi;
use WPDesk\WooCommerceFakturownia\Data\InvoiceOrderDefaults;
use WPDesk\WooCommerceFakturownia\Documents\DocumentType;
use FakturowniaVendor\WPDesk\Invoices\Exception\UnknownDocumentTypeException;
use FakturowniaVendor\WPDesk\Invoices\Integration;
use FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent;
use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;
use WPDesk\WooCommerceFakturownia\Product\FakturowniaProductId;
use WPDesk\WooCommerceFakturownia\Product\FakturowniaProductIdAjax;
use WPDesk\WooCommerceFakturownia\Product\FakturowniaProductLumpSum;
use WPDesk\WooCommerceFakturownia\Product\FakturowniaProductUpdater;
use WPDesk\WooCommerceFakturownia\Webhoks\ProductUpdateListener;

/**
 * Class InvoicesIntegration
 *
 * @package WPDesk\WooCommerceFakturownia
 */
class InvoicesIntegration extends Integration {

	const VAT_NUMBER_FIELD_ID = 'nip';

	/**
	 * @var WoocommerceIntegration
	 */
	public $woocommerce_integration;

	/**
	 * Nip field.
	 *
	 * @var RequiredVatNumber
	 */
	private $vat_number_field;

	/**
	 * Invoice ask field.
	 *
	 * @var InvoiceAsk
	 */
	private $document_ask_field;

	/**
	 * Fakturownia API.
	 *
	 * @var FakturowniaApi
	 */
	private $api;

	/**
	 * Document types.
	 *
	 * @var DocumentType[];
	 */
	private $supported_document_types_by_metadata_name_and_type;

	/**
	 * API URL.
	 *
	 * @var string
	 */
	private $api_url = 'https://%s.fakturownia.pl/';

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	public static $plugin_info;

	/**
	 * \InvoicesIntegration constructor.
	 *
	 * @param WoocommerceIntegration $woocommerce_integration WooCommerce integration.
	 */
	public function __construct( WoocommerceIntegration $woocommerce_integration ) {
		$this->woocommerce_integration = $woocommerce_integration;
		parent::__construct(
			$woocommerce_integration->id,
			__( 'Fakturownia', 'woocommerce-fakturownia' ),
			__( 'Wystawiono', 'woocommerce-fakturownia' ),
			__( 'Brak', 'woocommerce-fakturownia' ),
			__( 'Wyślij email', 'woocommerce-fakturownia' ),
			// Translators: document number.
			__( 'Dla tego zamówienia istnieje dokument %1$s! Utworzyć nowy? Istniejący dokument należy obsłużyć w Fakturowni.', 'woocommerce-fakturownia' )
		);

		$api_client                  = $this->getApiClient();
		$token                       = $this->woocommerce_integration->getUserToken();
		$department_id               = (int) $this->woocommerce_integration->getDepartmentId();
		$warehouse_id                = (int) $this->woocommerce_integration->getWarehouseID();
		$woocommerce_settings_helper = new WooCommerceSettings();
		$logger_integration          = new LoggerIntegration( 'fakturownia', 'fakturownia_settings' );
		$this->logger                = $logger_integration->get_logger();
		$this->api                   = new FakturowniaApi( $api_client, $token, $department_id, $warehouse_id, $logger_integration );
		$this->init_product_update_listener( $woocommerce_settings_helper );
		$this->initFakturowniaProductId();
		$this->init_lump_sum_support();
		if ( $this->woocommerce_integration->getOptionSyncWooPrices() ) {
			$this->init_woocommerce_to_fakturownia_product_synchronization( $woocommerce_settings_helper );
		}
		$this->add_hookable( new DownloadDocument( $this ) );
		$this->add_hookable( $logger_integration );
		$this->add_hookable( new User() );
		$this->initEUVat();
	}

	/**
	 * @return LoggerInterface
	 */
	public function get_logger() {
		return $this->logger;
	}

	/**
	 * @return Renderer
	 */
	public function get_renderer() {
		return $this->woocommerce_integration->get_renderer();
	}

	/**
	 * Init stock listener.
	 */
	private function init_product_update_listener( WooCommerceSettings $woocommerce_settings_helper ) {
		$enable_listener = $this->woocommerce_integration->is_price_sync_enabled() || $this->woocommerce_integration->is_warehouse_enabled();
		if ( $enable_listener ) {
			$this->add_hookable(
				new ProductUpdateListener(
					$this->woocommerce_integration,
					$woocommerce_settings_helper
				)
			);
			do_action( 'fakturownia/warehouse/init', $this->woocommerce_integration->getOptionWarehouseWebhookToken(), $this );
		}
	}


	private function init_woocommerce_to_fakturownia_product_synchronization( WooCommerceSettings $woocommerce_settings_helper ) {
		$this->add_hookable( new FakturowniaProductUpdater( $this->api, $woocommerce_settings_helper ) );
	}

	/**
	 * Init stock listener.
	 */
	private function initFakturowniaProductId() {
		$this->add_hookable( new FakturowniaProductId( $this->api ) );
		$this->add_hookable( new FakturowniaProductIdAjax( $this->api ) );
	}

	/**
	 * Init stock listener.
	 */
	private function init_lump_sum_support() {
		$lump_sum_values = (array) new LumpSumValues();

		/* Remove 'np' value as it is not supported by Fakturownia */
		unset( $lump_sum_values['np'] );

		$lump_sum_fields = new LumpSumProductFields( $lump_sum_values );
		if ( $this->woocommerce_integration->is_lump_sum_enabled() ) {
			$this->add_hookable( new FakturowniaProductLumpSum( $lump_sum_fields ) );
		}
	}

	/**
	 * Add supported document type.
	 *
	 * @param DocumentType $document_type Document type.
	 */
	public function addSupportedDocumentType( $document_type ) {
		parent::addSupportedDocumentType( $document_type );
		$this->supported_document_types_by_metadata_name_and_type[ $document_type->getMetaDataName() . $document_type->getMetaDataType() ] = $document_type;
	}


	/**
	 * Get document type by metadata name and type.
	 *
	 * @param string $metadata_name_and_type_name Metadata name and type name.
	 *
	 * @return DocumentType|bool
	 */
	public function get_document_type_by_metadata_name_and_type( $metadata_name_and_type_name ) {
		if ( isset( $this->supported_document_types_by_metadata_name_and_type[ $metadata_name_and_type_name ] ) ) {
			return $this->supported_document_types_by_metadata_name_and_type[ $metadata_name_and_type_name ];
		}

		return false;
	}

	public function initSupportedDocumentTypes() {
		$ajax_get_pdf_handler = $this->getAjaxGetPdfHandler();
		$documents            = new SupportedDocuments( $this, $ajax_get_pdf_handler );
		$documents->add_support_for_receipt();
		$documents->add_support_for_correction();
		$documents->add_support_for_invoice();
		$documents->add_support_for_invoice_without_vat();
		$documents->add_support_for_invoice_proforma();
		$documents->add_support_for_invoice_proforma_without_vat();
		$documents->add_support_for_invoice_foreign();
		$documents->add_support_for_bill();
	}

	/**
	 * Get API.
	 *
	 * @return FakturowniaApi
	 */
	public function get_api() {
		return $this->api;
	}

	/**
	 * Init API Client
	 */
	public function initApiClient() {
		$client_options = new ClientOptions();
		$client_options->setCachedClient( true );
		$api_url = sprintf( $this->api_url, $this->woocommerce_integration->getAccountName() );
		$client_options->setApiUrl( $api_url );
		$client_factory = new ClientFactory();
		$api_client     = $client_factory->createClient( $client_options );
		$this->setApiClient( $api_client );
	}

	/**
	 *
	 * @return bool
	 */
	public function has_account_name() {
		return ! empty( $this->woocommerce_integration->getAccountName() );
	}

	/**
	 * Inits document creators.
	 */
	public function initDocumentCreators() {
		$this->add_hookable( new DocumentCreatorForOrderStatus( $this ) );
	}

	/**
	 * Should add field to option.
	 *
	 * @param string $option_value Option value.
	 *
	 * @return bool
	 */
	private function should_add_field_for_option( $option_value ) {
		if ( in_array(
			$option_value,
			[
				InvoiceForm::ASK_AND_AUTO_GENERATE,
				InvoiceForm::ASK_AND_NOT_GENERATE,
			],
			true
		) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add fields for invoices.
	 */
	private function add_fields_for_invoices() {
		if ( $this->should_add_field_for_option( $this->woocommerce_integration->getOptionGenerateInvoice() ) ) {
			$this->document_ask_field = new InvoiceAsk(
				'faktura',
				__( 'Chcę otrzymać fakturę VAT', 'woocommerce-fakturownia' )
			);
			$this->add_hookable( $this->document_ask_field );
		}
	}

	/**
	 * Add fields for invoices without VAT.
	 */
	private function add_fields_for_invoices_without_vat() {
		if ( $this->should_add_field_for_option( $this->woocommerce_integration->getOptionGenerateInvoice() ) ) {
			$this->document_ask_field = new InvoiceAsk(
				'faktura',
				__( 'Chcę otrzymać fakturę bez VAT', 'woocommerce-fakturownia' )
			);
			$this->add_hookable( $this->document_ask_field );
		}
	}


	/**
	 * Add fields for bills.
	 */
	private function add_fields_for_bills() {
		if ( $this->should_add_field_for_option( $this->woocommerce_integration->getOptionGenerateBill() ) ) {
			$this->document_ask_field = new InvoiceAsk(
				'rachunek',
				__( 'Chcę otrzymać rachunek', 'woocommerce-fakturownia' )
			);
			$this->add_hookable( $this->document_ask_field );
		}
	}

	/**
	 * Inits checkout fields.
	 */
	public function initFields() {

		$this->vat_number_field = new RequiredVatNumber(
			self::VAT_NUMBER_FIELD_ID,
			__( 'VAT', 'woocommerce-fakturownia' ),
			__( 'Numer NIP', 'woocommerce-fakturownia' )
		);

		$this->add_hookable( $this->vat_number_field );

		if ( $this->woocommerce_integration->isOptionValidateCheckoutNip() || $this->woocommerce_integration->eu_vat_settings()->eu_vat_vies_validate ) {
			( new Field\Validator\ValidateVatNumber() )->validate_sanitize( self::VAT_NUMBER_FIELD_ID );
		}

		if ( wc_tax_enabled() ) {
			$this->add_fields_for_invoices();
		} elseif ( 'invoice_without_tax' === $this->woocommerce_integration->getOptionDocumentType() ) {
				$this->add_fields_for_invoices_without_vat();
		} else {
			$this->add_fields_for_bills();
		}

		if ( $this->should_add_field_for_option( $this->woocommerce_integration->getOptionGenerateInvoice() ) ) {
			add_action( 'woocommerce_after_checkout_validation', [ $this, 'should_validate_nip' ], 10, 2 );
		}
	}

	/**
	 * @param array     $data   Post data.
	 * @param \WP_Error $errors Checkout errors.
	 *
	 * @return mixed
	 */
	public function should_validate_nip( $data, $errors ) {
		if ( isset( $data['billing_faktura'] ) ) {
			$invoice_ask = (string) $data['billing_faktura'];
			if ( $invoice_ask !== '1' ) {
				if ( $errors instanceof \WP_Error && $errors->has_errors() ) {
					$errors->remove( 'billing_nip_required' );
				}
			}
		}

		return $data;
	}

	/**
	 * Get vat number field.
	 *
	 * @return VatNumber
	 */
	public function get_vat_number_field() {
		return $this->vat_number_field;
	}


	/**
	 * Get EU countries.
	 *
	 * @return string[]
	 */
	private function get_eu_countries() {
		return WC()->countries->get_european_union_countries();
	}

	/**
	 * Init integrations.
	 */
	public function initIntegrations() {
		$this->add_hookable( new GTU() );
		$this->add_hookable( new ProcedureDesignations() );
	}

	/**
	 * Initialize EU VAT number.
	 */
	private function initEUVat() {
		$eu_vat_settings = $this->woocommerce_integration->eu_vat_settings();
		$eu_vat          = new EUVatIntegration( $eu_vat_settings, $this->get_logger() );
		if ( wc_tax_enabled() && $eu_vat_settings->eu_vat_vies_validate ) {
			$moss_link = 'https://www.wpdesk.pl/sk/woocommerce-fakturownia-inv/';
			$eu_vat->set_vat_field_name( 'billing_nip' );
			$eu_vat->set_plugin_data( __( 'Fakturownia WooCommerce', 'woocommerce-fakturownia' ), '1.4.0', $moss_link );
			$this->add_hookable( $eu_vat );
			$oss = new ValidateOSS( $eu_vat->get_shop_settings(), $eu_vat_settings, $eu_vat->get_validator() );
			$this->add_hookable( new DataStore( $oss ) );
		}

		$this->add_hookable( new Block\VatNumber\RegisterCheckoutBlocks( self::$plugin_info, $eu_vat_settings, $eu_vat->get_shop_settings() ) );
	}


	/**
	 * Inits order column.
	 */
	public function initOrderColumn() {
		$this->add_hookable(
			new OrderColumn( $this, __( 'Fakturownia', 'woocommerce-fakturownia' ), $this->document_ask_field, $this->getAjaxGetPdfHandler() )
		);
	}

	/**
	 * Prepare order defaults.
	 *
	 * @param WC_Order $order         Order.
	 * @param Type     $document_type Document type.
	 *
	 * @return InvoiceOrderDefaults
	 */
	public function prepareOrderDefaults( $order, Type $document_type ) {
		return new InvoiceOrderDefaults( $order, $this, $document_type );
	}


	/**
	 * Create document from data.
	 *
	 * @param WC_Order $order             Order.
	 * @param array    $data              Posted data.
	 * @param bool     $overwriteExisting Overwrite already created.
	 *
	 * @return bool
	 * @throws Exception .
	 */
	public function createDocumentForOrder( $order, array $data, $overwriteExisting = true ) {
		$type_name = '';
		if ( isset( $data['type'] ) ) {
			$type_name = $data['type'];
		}
		$type = $this->getSupportedDocumentType( $type_name );
		if ( $type ) {
			$type->getCreator()->createDocumentForOrder( $order, $data );
		} else {
			throw new UnknownDocumentTypeException( $type_name );
		}

		return true;
	}

	/**
	 * Get Document PDF.
	 *
	 * @param MetadataContent $metadata_content Meta data content.
	 *
	 * @return string
	 * @throws UnknownDocumentTypeException Exception.
	 */
	public function getDocumentPdf( MetadataContent $metadata_content ) {
		$metadata = $metadata_content->get();

		$metadata      = $metadata[ array_key_last( $metadata ) ];
		$document_type = $this->get_document_type_by_metadata_name_and_type(
			$metadata_content->getMetaDataName() . $metadata['typ']
		);

		if ( $document_type ) {
			return $document_type->getDocumentPdf( $metadata['id'] );
		}

		throw new UnknownDocumentTypeException(
			$metadata_content->getMetaDataName() . $metadata['typ']
		);
	}
}
