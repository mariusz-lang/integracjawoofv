<?php

namespace WPDesk\WooCommerceFakturownia\Api;

use Exception;
use Psr\Log\LoggerInterface;
use FakturowniaVendor\WPDesk\ApiClient\Client\Client;
use WC_Order;
use WPDesk\WooCommerceFakturownia\Api\Invoice\GetRequest;
use WPDesk\WooCommerceFakturownia\Api\Invoice\PostResponseJson;
use WPDesk\WooCommerceFakturownia\Api\Products\GetResponseJson;
use WPDesk\WooCommerceFakturownia\Api\Products\GetSingleRequest;
use WPDesk\WooCommerceFakturownia\Api\Products\GetSingleResponseJson;
use WPDesk\WooCommerceFakturownia\Api\Products\NoProductsFoundException;
use WPDesk\WooCommerceFakturownia\Api\Products\PutRequest;
use WPDesk\WooCommerceFakturownia\Api\Products\PutResponseJson;
use WPDesk\WooCommerceFakturownia\Api\Products\QueryRequest;
use WPDesk\WooCommerceFakturownia\Api\Products\TooManyProductsFoundException;
use WPDesk\WooCommerceFakturownia\Data\DocumentData;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;
use WPDesk\WooCommerceFakturownia\Api\Invoice\PostRequest;
use WPDesk\WooCommerceFakturownia\Api\Products\PostRequest as ProductsPostRequest;
use WPDesk\WooCommerceFakturownia\Api\Products\PostResponseJson as ProductsPostResponseJson;
use WPDesk\WooCommerceFakturownia\Logger\LoggerIntegration;
use WPDesk\WooCommerceFakturownia\Logger\LoggerException;

/**
 * Class FakturowniaApi
 *
 * @package WPDesk\WooCommerceFakturownia\Api
 */
class FakturowniaApi {

	/**
	 * User name.
	 *
	 * @var string
	 */
	private $token;

	/**
	 * ApiClient.
	 *
	 * @var InvoicesIntegration
	 */
	private $api_client;

	/**
	 * @var int
	 */
	private $department_id;

	/**
	 * @var int
	 */
	private $warehouse_id;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var bool
	 */
	private $logger_exit;


	/**
	 * @param Client $api_client    API Client.
	 * @param string $token         Token.
	 * @param int    $department_id Department ID.
	 */
	public function __construct( Client $api_client, string $token, int $department_id, $warehouse_id, LoggerIntegration $logger_integration ) {
		$this->api_client    = $api_client;
		$this->token         = $token;
		$this->logger        = $logger_integration->get_logger();
		$this->logger_exit   = $logger_integration->should_exit();
		$this->department_id = $department_id;
		if ( $warehouse_id ) {
			$this->warehouse_id = $warehouse_id;
		} else {
			$this->warehouse_id = $this->get_general_warehouse();
		}
	}

	/**
	 * @return int
	 */
	private function get_general_warehouse(): int {
		$warehouse_id = (int) get_transient( 'fakturownia_warehouse_main' );
		if ( ! $warehouse_id ) {
			try {
				$warehouses_api = $this->get_warehouses()->get_warehouses();
			} catch ( Exception $e ) {
				$warehouses_api = [];
			}
			if ( ! empty( $warehouses_api ) ) {
				foreach ( $warehouses_api as $warehouse ) {
					if ( $warehouse['kind'] === 'main' ) {
						set_transient( 'fakturownia_warehouse_main', $warehouse['id'], WEEK_IN_SECONDS );

						return (int) $warehouse['id'];
					}
				}
			}
		}

		return $warehouse_id;
	}

	/**
	 * @param array $invoice_data Invoice data.
	 *
	 * Return same data with department ID if exist.
	 *
	 * @return array
	 */
	private function pass_department_id_to_data( array $invoice_data ): array {
		if ( $this->department_id ) {
			return array_merge(
				$invoice_data,
				[ 'department_id' => $this->department_id ]
			);
		}

		return $invoice_data;
	}


	/**
	 * Maybe throw exception from response.
	 *
	 * @param ResponseJson $response Response.
	 *
	 * @throws InvoicesException API Exception.
	 */
	private function maybe_throw_exception_from_response( ResponseJson $response ) {
		if ( $response->isError() ) {
			$response_body = $response->getResponseBody();

			$message = '';
			if ( isset( $response_body['message'] ) ) {
				if ( is_array( $response_body['message'] ) ) {
					foreach ( $response_body['message'] as $key => $response_message ) {
						$message .= $key . ' ' . print_r( $response_message, true ) . ' ' . PHP_EOL; //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
					}
				} else {
					$message = $response_body['message'];
				}
			}
			$error_info = $response->get_error_info(
			// Translators: response code.
				sprintf( __( 'Błąd API Fakturownia! Kod błędu: %1$s %2$s.', 'woocommerce-fakturownia' ), $response->getResponseCode(), $message )
			);
			throw new InvoicesException( $error_info );
		}
	}

	/**
	 * Assert issue, sell and payment date
	 *
	 * @param array $data Document data.
	 *
	 * @return void
	 * @throws InvoicesException Exception.
	 */
	private function validate_dates( array $data ): void {
		if ( strtotime( $data['issue_date'] ) > strtotime( $data['payment_to'] ) ) {
			$error_info = __( 'Termin płatności faktury nie może być wcześniejszy niż data wystawienia!', 'woocommerce-fakturownia' );
			throw new InvoicesException( $error_info );
		}
		if ( isset( $data['sell_date'] ) && strtotime( $data['sell_date'] ) > strtotime( $data['payment_to'] ) ) {
			$error_info = __( 'Termin płatności faktury nie może być wcześniejszy niż data sprzedaży!', 'woocommerce-fakturownia' );
			throw new InvoicesException( $error_info );
		}
	}

	/**
	 * Create document (invoice, bill, receipt).
	 *
	 * @param DocumentData $invoice_data Invoice data.
	 *
	 * @return PostResponseJson
	 * @throws Exception API Exception.
	 */
	public function create_document( DocumentData $invoice_data ): PostResponseJson {

		$data              = [];
		$data['api_token'] = $this->token;
		$data['invoice']   = $this->pass_department_id_to_data( $invoice_data->prepareDataAsArray() );
		$order             = $invoice_data->getOrder();
		$this->validate_dates( $data['invoice'] );

		/**
		 * Document creation request filter.
		 *
		 * @var array     $data  Request data.
		 * @var WC_order $order WC Order.
		 */
		$data = apply_filters( 'fakturownia/core/api/request', $data, $order );

		$this->logger->debug( print_r( $data, true ) ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		if ( $this->logger_exit ) {
			throw new LoggerException( 'Logger Exit' );
		}

		$request = new PostRequest(
			$this->api_client->getApiUrl(),
			$this->token,
			$data
		);

		$create_invoice_raw_response = $this->api_client->sendRequest( $request );

		$create_invoice_response = new PostResponseJson(
			$create_invoice_raw_response
		);

		$this->maybe_throw_exception_from_response( $create_invoice_response );

		return $create_invoice_response;
	}

	/**
	 * Get invoice.
	 *
	 * @param int $invoice_id Invoice ID.
	 *
	 * @return DocumentGetResponseJson
	 * @throws Exception API Exception.
	 */
	public function get_invoice( int $invoice_id ): DocumentGetResponseJson {
		$request = new GetRequest(
			$this->api_client->getApiUrl(),
			$this->token,
			$invoice_id
		);

		$invoice_raw_response = $this->api_client->sendRequest( $request );

		$invoice_response = new DocumentGetResponseJson(
			$invoice_raw_response
		);

		$this->maybe_throw_exception_from_response( $invoice_response );

		return $invoice_response;
	}

	/**
	 * Get invoice Pdf.
	 *
	 * @param int $invoice_id Invoice ID.
	 *
	 * @return Response
	 * @throws Exception API Exception.
	 */
	public function get_invoice_pdf( int $invoice_id ): Response {
		$request = new GetRequest(
			$this->api_client->getApiUrl(),
			$this->token,
			$invoice_id,
			'pdf'
		);

		$invoice_raw_response = $this->api_client->sendRequest( $request );

		return new Response( $invoice_raw_response );
	}

	/**
	 * Get bill
	 *
	 * @param int $invoice_id Invoice ID.
	 *
	 * @return DocumentGetResponseJson
	 * @throws Exception API Exception.
	 */
	public function get_bill( int $invoice_id ): DocumentGetResponseJson {
		$request = new GetRequest(
			$this->api_client->getApiUrl(),
			$this->token,
			$invoice_id
		);

		$invoice_raw_response = $this->api_client->sendRequest( $request );

		$invoice_response = new DocumentGetResponseJson(
			$invoice_raw_response
		);

		$this->maybe_throw_exception_from_response( $invoice_response );

		return $invoice_response;
	}


	/**
	 * @return Warehouses\GetResponseJson
	 */
	public function get_warehouses(): Warehouses\GetResponseJson {
		$request = new Warehouses\GetRequest(
			$this->api_client->getApiUrl(),
			$this->token
		);

		$raw_response = $this->api_client->sendRequest( $request );

		$response = new Warehouses\GetResponseJson(
			$raw_response
		);

		$this->maybe_throw_exception_from_response( $response );

		return $response;
	}

	/**
	 * Get products.
	 *
	 * @param int $page Page.
	 *
	 * @return Products\GetResponseJson
	 * @throws Exception API Exception.
	 */
	public function get_products( int $page = 1 ): Products\GetResponseJson {
		$request = new Products\GetRequest(
			$this->api_client->getApiUrl(),
			$this->token,
			$page
		);

		$raw_response = $this->api_client->sendRequest( $request );

		$response = new Products\GetResponseJson(
			$raw_response
		);

		$this->maybe_throw_exception_from_response( $response );

		return $response;
	}

	/**
	 * Query products.
	 *
	 * @param string $query .
	 * @param int    $page  Page.
	 *
	 * @return GetResponseJson
	 * @throws Exception API Exception.
	 */
	public function query_products( string $query, int $page = 1 ): Products\GetResponseJson {
		$request = new QueryRequest(
			$this->api_client->getApiUrl(),
			$this->token,
			$this->warehouse_id,
			$query,
			$page
		);

		$raw_response = $this->api_client->sendRequest( $request );

		$response = new GetResponseJson(
			$raw_response
		);

		$this->maybe_throw_exception_from_response( $response );

		return $response;
	}

	/**
	 * Get product by product code.
	 *
	 * @param string $code .
	 *
	 * @return array
	 * @throws TooManyProductsFoundException .
	 * @throws NoProductsFoundException|Exception .
	 */
	public function get_product_by_code( string $code ): array {
		$query_products_response = $this->query_products( $code );
		$products                = $query_products_response->get_products();
		$fakturownia_product     = false;
		foreach ( $products as $product ) {
			if ( isset( $product['code'] ) && $product['code'] === $code ) {
				if ( false === $fakturownia_product ) {
					$fakturownia_product = $product;
				} else {
					// Translators: product code.
					throw new TooManyProductsFoundException( sprintf( __( 'Istnieje wiele produków o kodzie: %1$s.', 'woocommerce-fakturownia' ), $code ) );
				}
			}
		}
		if ( false === $fakturownia_product ) {
			// Translators: product code.
			throw new NoProductsFoundException( sprintf( __( 'Nie znaleziono żadnego produku o kodzie: %1$s.', 'woocommerce-fakturownia' ), $code ) );
		}

		return $fakturownia_product;
	}

	/**
	 * Create product.
	 *
	 * @param string $name        .
	 * @param string $code        .
	 * @param string $description .
	 * @param string $image_url   .
	 *
	 * @return ProductsPostResponseJson
	 * @throws Exception API Exception.
	 */
	public function create_product( string $name, string $code, array $prices, string $tax, string $description = '', string $image_url = '' ): ProductsPostResponseJson {

		$data['api_token'] = $this->token;
		$data['product']   = [
			'name'        => $name,
			'code'        => $code,
			'description' => $description,
			'image_url'   => $image_url,
			'tax'         => $tax,
		];

		$data['product'] = array_merge( $data['product'], $prices );

		$request = new ProductsPostRequest(
			$this->api_client->getApiUrl(),
			$this->token,
			$data
		);

		$create_product_raw_response = $this->api_client->sendRequest( $request );

		$create_product_response = new ProductsPostResponseJson(
			$create_product_raw_response
		);

		$this->maybe_throw_exception_from_response( $create_product_response );

		return $create_product_response;
	}

	/**
	 * @param string $product_id product id in fakturownia warehouse
	 * @param array  $updated_product_data array with updated single procut params
	 *
	 * @return PutResponseJson
	 */
	public function update_product( string $product_id, array $updated_product_data ): PutResponseJson {

		$data              = [];
		$data['api_token'] = $this->token;
		$data['product']   = $updated_product_data;

		$data    = apply_filters( 'fakturownia/sync/product_update_data', $data, $product_id );
		$request = new PutRequest( $this->api_client->getApiUrl(), $this->token, $product_id, $data );

		$update_product_raw_response = $this->api_client->sendRequest( $request );

		$response = new PutResponseJson( $update_product_raw_response );

		$this->maybe_throw_exception_from_response( $response );

		return $response;
	}

	/**
	 * Get product.
	 *
	 * @param string $id .
	 *
	 * @return GetSingleResponseJson
	 * @throws Exception API Exception.
	 */
	public function get_product( string $id ): GetSingleResponseJson {
		$request = new GetSingleRequest(
			$this->api_client->getApiUrl(),
			$this->token,
			$this->warehouse_id,
			$id
		);

		$raw_response = $this->api_client->sendRequest( $request );

		$response = new GetSingleResponseJson(
			$raw_response
		);

		$this->maybe_throw_exception_from_response( $response );

		return $response;
	}
}
