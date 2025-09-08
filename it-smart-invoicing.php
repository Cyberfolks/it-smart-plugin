<?php
/**
 * Plugin Name: IT Smart Invoicing
 * Description: Adds Invoice/Packing options inside WooCommerce order page (HPOS-compatible).
 * Version: 1.0.1
 * Author: Your Name
 * Text Domain: it-smart-invoicing
 */


if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ITSI_FILE', __FILE__ );
define( 'ITSI_DIR', plugin_dir_path( __FILE__ ) );
define( 'ITSI_URL', plugin_dir_url( __FILE__ ) );
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Declare HPOS compatibility
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            ITSI_FILE,
            true
        );
    }
});

if ( is_admin() ) {
    require_once ITSI_DIR . 'includes/class-it-smart-invoicing-admin.php';
    add_action( 'plugins_loaded', function() {
        if ( class_exists( 'WooCommerce' ) ) {
            new IT_Smart_Invoicing_Admin();
        }
    });
}

register_activation_hook(__FILE__, 'itsi_install_tables');

function itsi_install_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_invoices = $wpdb->prefix . 'itsi_invoices';
    $table_settings = $wpdb->prefix . 'itsi_invoice_settings';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Invoices table
    $sql1 = "CREATE TABLE $table_invoices (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id BIGINT(20) UNSIGNED NOT NULL UNIQUE,
        invoice_number VARCHAR(50) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql1);

    // Settings table
    $sql2 = "CREATE TABLE $table_settings (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        option_name VARCHAR(100) NOT NULL UNIQUE,
        option_value LONGTEXT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql2);

    // Insert default settings if not exists
    $wpdb->insert($table_settings, [
        'option_name'  => 'invoice_prefix',
        'option_value' => 'INV-'
    ]);
    $wpdb->insert($table_settings, [
        'option_name'  => 'invoice_start',
        'option_value' => '1000'
    ]);
    $wpdb->insert($table_settings, [
        'option_name'  => 'company_name',
        'option_value' => 'My Company Ltd.'
    ]);
    $wpdb->insert($table_settings, [
        'option_name'  => 'company_address',
        'option_value' => '123 Street, City'
    ]);
    $wpdb->insert($table_settings, [
        'option_name'  => 'company_contact',
        'option_value' => '+1 555-555-5555'
    ]);
}
