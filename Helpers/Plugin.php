<?php

namespace WPDesk\WooCommerceFakturownia\Helpers;

/**
 * Plugin helpers functions.
 *
 * @package WPDesk\Library\FlexibleInvoicesCore\Helpers
 */
class Plugin {

	/**
	 * @param string $plugin
	 *
	 * @return bool
	 */
	public static function is_active( string $plugin ): bool {
		if ( function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( $plugin ) ) {
			return true;
		}

		return in_array( $plugin, (array) get_option( 'active_plugins', [] ), true );
	}

	/**
	 * Is flexible quantity pro plugin is enabled.
	 *
	 * @return bool
	 */
	public static function is_fq_pro_addon_enabled(): bool {
		return self::is_active( 'flexible-quantity/flexible-quantity.php' );
	}

	/**
	 * Is flexible quantity free plugin is enabled.
	 *
	 * @return bool
	 */
	public static function is_fq_free_addon_enabled(): bool {
		return self::is_active( 'flexible-quantity-measurement-price-calculator-for-woocommerce/flexible-quantity-measurement-price-calculator-for-woocommerce.php' );
	}
}
