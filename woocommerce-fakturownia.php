<?php
/**
 * Plugin Name: WooCommerce Fakturownia
 * Plugin URI: https://www.wpdesk.pl/sk/woocommerce-fakturownia-plugin/
 * Description: Wtyczka integrująca WooCommerce z programem do faktur online Fakturownia.
 * Version: 1.10.6
 * Author: WP Desk
 * Author URI: https://www.wpdesk.pl/sk/woocommerce-fakturownia-author/
 * Text Domain: woocommerce-fakturownia
 * Domain Path: /lang/
 * Requires at least: 6.2
 * Tested up to: 6.8
 * WC requires at least: 8.1
 * WC tested up to: 10.2
 * Requires PHP: 7.4
 *
 * Copyright 2017 WP Desk Ltd.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package WooCommerce Fakturownia
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/* Plugin release time */
$plugin_release_timestamp = '2021-11-29 10:35';
/* Plugin version */
$plugin_version = '1.10.6';

$plugin_name        = 'WooCommerce Fakturownia';
$plugin_class_name  = '\WPDesk\WooCommerceFakturownia\Plugin';
$plugin_text_domain = 'woocommerce-fakturownia';
$product_id         = 'WooCommerce Fakturownia';
$plugin_file        = __FILE__;
$plugin_dir         = dirname( __FILE__ );

define( 'WOOCOMMERCE_FAKTUROWNIA_VERSION', $plugin_version );
define( 'WOOCOMMERCE_FAKTUROWNIA_DIR', $plugin_dir );
define( $plugin_class_name, $plugin_version );

$requirements = [
	'php'     => '7.3',
	'wp'      => '6.0',
	'plugins' => [
		[
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
		],
	],
];

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
	}
} );

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow-common/src/plugin-init-php52.php';
