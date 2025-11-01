<?php

namespace WPDesk\WooCommerceFakturownia\Api;

use FakturowniaVendor\WPDesk\ApiClient\Client\ClientImplementation;
use FakturowniaVendor\WPDesk\Cache\CacheInfoResolverCreator;

/**
 * Class Client
 *
 * @package WPDesk\WooCommerceFakturownia\Api
 */
class Client extends ClientImplementation implements CacheInfoResolverCreator {

	/**
	 * Create resolvers.
	 *
	 * @return CacheInfoResolver[]
	 */
	public function createResolvers() {
		return [ new CacheInfoResolver() ];
	}
}
