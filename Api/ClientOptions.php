<?php

namespace WPDesk\WooCommerceFakturownia\Api;

use FakturowniaVendor\WPDesk\ApiClient\Client\ApiClientOptions;
/**
 * Class ClientOptions
 *
 * @package FakturowniaVendor\WPDesk\WooCommerceFakturownia\Api
 */
class ClientOptions implements ApiClientOptions {

	const DEFAULT_CACHE_TTL = 600;

	/**
	 * Http client class.
	 *
	 * @var string
	 */
	private $http_client_class = \FakturowniaVendor\WPDesk\HttpClient\Curl\CurlClient::class;

	/**
	 * Persistence class.
	 *
	 * @var string
	 */
	protected $persistence_class = \FakturowniaVendor\WPDesk\Persistence\MemoryContainer::class;

	/**
	 * Logger.
	 *
	 * @var \FakturowniaVendor\Psr\Log\LoggerInterface
	 */
	private $logger;

	/**
	 * Serializer class.
	 *
	 * @var string
	 */
	private $serializer_class = Serializer::class;

	/**
	 * API URL.
	 *
	 * @var string
	 */
	private $api_url = 'https://%.fakturownia.pl/';

	/**
	 * Cached client.
	 *
	 * @var bool
	 */
	protected $cached_client = false;

	/**
	 * Cache TTL.
	 *
	 * @var int
	 */
	protected $cache_ttl = self::DEFAULT_CACHE_TTL;

	/**
	 * API Mime Type.
	 *
	 * @var string
	 */
	private $api_mime_type = 'application/json';

	/**
	 * Default request headers - added to each request.
	 *
	 * @var array
	 */
	private $default_request_headers = [];

	/**
	 * PlatformFactoryOptions constructor.
	 */
	public function __construct() {
		$this->logger = new \FakturowniaVendor\Psr\Log\NullLogger();
	}

	/**
	 * Get Api client class
	 *
	 * @return string
	 */
	public function getApiClientClass() {
		return Client::class;
	}

	/**
	 * Get HTTP client class.
	 *
	 * @return string
	 */
	public function getHttpClientClass() {
		return $this->http_client_class;
	}

	/**
	 * Set HTTP client class
	 *
	 * @param string $http_client_class HTTP client class.
	 */
	public function setHttpClientClass( $http_client_class ) {
		$this->http_client_class = $http_client_class;
	}

	/**
	 * Get persistence class
	 *
	 * @return string
	 */
	public function getPersistenceClass() {
		return $this->persistence_class;
	}

	/**
	 * Set persistence class
	 *
	 * @param string $persistence_class Class.
	 */
	public function setPersistenceClass( $persistence_class ) {
		$this->persistence_class = $persistence_class;
	}

	/**
	 * Get serializer class.
	 *
	 * @return string
	 */
	public function getSerializerClass() {
		return $this->serializer_class;
	}

	/**
	 * Set serializer class
	 *
	 * @param string $serializer_class Class.
	 */
	public function setSerializerClass( $serializer_class ) {
		$this->serializer_class = $serializer_class;
	}

	/**
	 * Get Api URL
	 *
	 * @return string
	 */
	public function getApiUrl() {
		return $this->api_url;
	}

	/**
	 * Set Api URL
	 *
	 * @param string $api_url Api URL.
	 */
	public function setApiUrl( $api_url ) {
		$this->api_url = $api_url;
	}

	/**
	 * Get mime type
	 *
	 * @return string
	 */
	public function getApiMimeType() {
		return $this->api_mime_type;
	}

	/**
	 * Set mime type
	 *
	 * @param string $api_mime_type Mime_type.
	 */
	public function setApiMimeType( $api_mime_type ) {
		$this->api_mime_type = $api_mime_type;
	}

	/**
	 * Get logger
	 *
	 * @return \FakturowniaVendor\Psr\Log\LoggerInterface
	 */
	public function getLogger() {
		return $this->logger;
	}

	/**
	 * Set logger
	 *
	 * @param \FakturowniaVendor\Psr\Log\LoggerInterface $logger
	 */
	public function setLogger( $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Is cached client
	 *
	 * @return bool
	 */
	public function isCachedClient() {
		return $this->cached_client;
	}

	/**
	 * Set cached client
	 *
	 * @param bool $cached_client Cached client.
	 */
	public function setCachedClient( $cached_client ) {
		$this->cached_client = $cached_client;
	}

	/**
	 * Set cache TTL
	 *
	 * @param int $cache_ttl Cache TTL.
	 */
	public function setCacheTtl( $cache_ttl ) {
		$this->cache_ttl = $cache_ttl;
	}

	/**
	 * Get cache TTL
	 *
	 * @return int
	 */
	public function getCacheTtl() {
		return $this->cache_ttl;
	}

	/**
	 * Get default request headers
	 *
	 * @return array
	 */
	public function getDefaultRequestHeaders() {
		return $this->default_request_headers;
	}

	/**
	 * Set default request headers
	 *
	 * @param array $default_request_headers Headers.
	 */
	public function setDefaultRequestHeaders( $default_request_headers ) {
		$this->default_request_headers = $default_request_headers;
	}
}
