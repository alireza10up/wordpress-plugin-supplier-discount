<?php
/**
 * Plugin Name: Supplier Discount for WooCommerce
 * Plugin URI: https://github.com/alireza10up/wordpress-plugin-supplier-discount
 * Description: Adds supplier discount percentage field to WooCommerce products and applies discount for supplier role users.
 * Version: 1.0.0
 * Author: Alireza Vahdani
 * Author URI: https://alireza10up.ir
 * Text Domain: xyz-supplier-discount
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('XYZ_SUPPLIER_DISCOUNT_VERSION', '1.0.0');
define('XYZ_SUPPLIER_DISCOUNT_PLUGIN_FILE', __FILE__);
define('XYZ_SUPPLIER_DISCOUNT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('XYZ_SUPPLIER_DISCOUNT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('XYZ_SUPPLIER_DISCOUNT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Supplier Discount Plugin requires WooCommerce to be installed and active.', 'xyz-supplier-discount');
        echo '</p></div>';
    });
    return;
}

// Load Composer autoloader
if (file_exists(XYZ_SUPPLIER_DISCOUNT_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once XYZ_SUPPLIER_DISCOUNT_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Composer dependencies not found. Please run "composer install" in the plugin directory.', 'xyz-supplier-discount');
        echo '</p></div>';
    });
    return;
}

use XyzSupplierPlugin\Plugin;

/**
 * Main plugin instance
 *
 * @return Plugin
 */
function xyz_supplier_discount() {
    return Plugin::get_instance();
}

// Initialize the plugin
xyz_supplier_discount();
