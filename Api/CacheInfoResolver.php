<?php

namespace WPDesk\WooCommerceFakturownia\Api;

use FakturowniaVendor\WPDesk\ApiClient\Client\RequestCacheInfoResolver;
use FakturowniaVendor\WPDesk\Cache\HowToCache;
use WPDesk\WooCommerceFakturownia\Api\Products\GetRequest as ProductsGetRequest;
use WPDesk\WooCommerceFakturownia\Api\Warehouses\GetRequest as WarehousesGetRequest;

/**
 * Cache info resolver. Resolves which requests should be cached.
 */
class CacheInfoResolver extends RequestCacheInfoResolver {

	const ONE_DAY = 86400;

	/**
	 * Is supported?
	 *
	 * @param \FakturowniaVendor\WPDesk\ApiClient\Request\Request $request .
	 *
	 * @return bool
	 */
	public function isSupported( $request ) {
		if ( $request instanceof ProductsGetRequest ) {
			return true;
		}
		if ( $request instanceof WarehousesGetRequest ) {
			return true;
		}

		return false;
	}

	/**
	 * Should cache?
	 *
	 * @param \FakturowniaVendor\WPDesk\ApiClient\Request\Request $request .
	 *
	 * @return bool
	 */
	public function shouldCache( $request ) {
		return $this->isSupported( $request );
	}

	/**
	 * Prepare how to cache.
	 *
	 * @param \FakturowniaVendor\WPDesk\ApiClient\Request\Request $request .
	 *
	 * @return HowToCache
	 */
	public function prepareHowToCache( $request ) {
		return new HowToCache( $this->prepareCacheKey( $request ), self::ONE_DAY );
	}

	/**
	 * Prepare cache key.
	 *
	 * @param \FakturowniaVendor\WPDesk\ApiClient\Request\Request $request .
	 *
	 * @return bool
	 */
	private function prepareCacheKey( $request ) {
		return md5( $request->getEndpoint() );
	}
}
