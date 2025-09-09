<?php
/**
 * Plugin Name: IT Smart Invoicing
 * Description: Adds Invoice/Packing options inside WooCommerce order page (HPOS-compatible).
 * Version: 1.0.1
 * Author: Your Name
 * Text Domain: it-smart-invoicing
 */
use Dompdf\Dompdf;

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

add_action('admin_head', function() {
    if (isset($_GET['page']) && $_GET['page'] === 'itsi-view-invoice') {
        ?>
        <style>
            #wpadminbar, 
            #adminmenumain, 
            #wpfooter, 
            #screen-meta-links,
            #screen-meta,
            #contextual-help-link-wrap { display: none !important; }
            
            #wpcontent, #wpbody-content { margin: 0; padding: 0; }
            #wpcontent { background: #fff; }
        </style>
        <?php
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

add_action('admin_post_itsi_generate_invoice', 'itsi_generate_invoice_handler');

function itsi_generate_invoice_handler() {
    if ( ! current_user_can('manage_woocommerce') ) {
        wp_die('Unauthorized');
    }

    global $wpdb;
    $table_invoices = $wpdb->prefix . 'itsi_invoices';
    $table_settings = $wpdb->prefix . 'itsi_invoice_settings';

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    if ( ! $order_id ) {
        wp_die('No order ID provided');
    }

    // Check if invoice already exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT invoice_number FROM $table_invoices WHERE order_id = %d",
        $order_id
    ));

    if ( $existing ) {
        // Already generated → redirect back
        wp_redirect( admin_url("post.php?post=$order_id&action=edit&itsi_msg=exists") );
        exit;
    }

    // Get settings
    $settings = $wpdb->get_results("SELECT option_name, option_value FROM $table_settings", OBJECT_K);
    $prefix   = $settings['invoice_prefix']->option_value ?? 'INV-';
    $start    = intval($settings['invoice_start']->option_value ?? 1000);

    // Find last invoice number
    $last_number = $wpdb->get_var("SELECT MAX(CAST(SUBSTRING(invoice_number, LENGTH('$prefix')+1) AS UNSIGNED)) FROM $table_invoices");
    $next_number = $last_number ? $last_number + 1 : $start;

    $invoice_number = $prefix . $next_number;

    // Save
    $wpdb->insert($table_invoices, [
        'order_id'       => $order_id,
        'invoice_number' => $invoice_number,
    ]);

    // Redirect back to order page
    // wp_redirect( admin_url("post.php?post=$order_id&action=edit&itsi_msg=created&invoice=$invoice_number") );
    exit;
}
function render_invoice_page() {
    $order_id       = intval( $_GET['order_id'] );
    $invoice_number = sanitize_text_field( $_GET['invoice'] );
    $mode           = sanitize_text_field( $_GET['mode'] ?? '' );

    $order = wc_get_order( $order_id );

    include plugin_dir_path( __FILE__ ) . '../templates/invoice.php';

    if ( $mode === 'print' ) {
        echo "<script>window.print();</script>";
    }
}
add_action('admin_notices', function() {
    if ( isset($_GET['itsi_msg']) ) {
        if ( $_GET['itsi_msg'] === 'created' && isset($_GET['invoice']) ) {
            echo '<div class="notice notice-success"><p>Invoice created: <strong>' . esc_html($_GET['invoice']) . '</strong></p></div>';
        } elseif ( $_GET['itsi_msg'] === 'exists' ) {
            echo '<div class="notice notice-info"><p>Invoice already exists for this order.</p></div>';
        }
    }
});


function itsi_download_invoice_handler() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( 'Not allowed' );
    }

    $order_id = intval( $_GET['order_id'] );
    $order    = wc_get_order( $order_id );

    ob_start();
    include plugin_dir_path( __FILE__ ) . 'templates/invoice.php';
    $html = ob_get_clean();

    $dompdf = new Dompdf();
    $dompdf->loadHtml( $html );
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream( "invoice-$order_id.pdf", [ "Attachment" => true ] );
    exit;
}
add_action( 'admin_post_itsi_download_invoice', 'itsi_download_invoice_handler' );