<?php
if ( ! defined( 'ABSPATH' ) ) exit;
use Dompdf\Dompdf;

class IT_Smart_Invoicing_Admin {

    public function __construct() {
        // // Classic orders
        // add_action( 'add_meta_boxes_shop_order', [ $this, 'add_box' ] );
        // // HPOS orders
        // add_action( 'add_meta_boxes', [ $this, 'maybe_add_box' ], 20, 2 );
        // add_action( 'admin_menu', [ $this, 'register_print_button' ] );
        // // add_action( 'admin_post_itsi_invoice', [ $this, 'render_invoice_page' ] );
        // add_action( 'admin_post_itsi_invoice_pdf', [ $this, 'render_invoice_pdf' ] );
        // add_action('admin_menu', [$this, 'register_admin_menu']);
        // add_action( 'admin_menu', [ $this, 'register_menu' ] );
            add_action( 'add_meta_boxes_shop_order', [ $this, 'add_box' ] );
            // HPOS orders
            add_action( 'add_meta_boxes', [ $this, 'maybe_add_box' ], 20, 2 );
            add_action( 'admin_menu', [ $this, 'register_print_button' ] );

            // Invoice view + download
            add_action( 'admin_post_itsi_invoice', [ $this, 'render_invoice_page' ] );
            // add_action( 'admin_post_itsi_download_invoice', [ $this, 'render_invoice_pdf' ] );
            add_action( 'admin_post_itsi_download_invoice', [ $this, 'render_invoice_pdf' ] );

            // (optional) allow guests to download via email link
            // add_action( 'admin_post_nopriv_itsi_download_invoice', [ $this, 'render_invoice_pdf' ] );

            add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
            add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu() {
        add_submenu_page(
            null, // null = hidden from sidebar
            __( 'View Invoice', 'it-smart-invoicing' ),
            __( 'View Invoice', 'it-smart-invoicing' ),
            'manage_woocommerce',
            'itsi-view-invoice',
            [ $this, 'render_invoice_page' ]
        );
    }

    public function register_admin_menu() {
        add_menu_page(
            'IT Smart Invoicing',                 // Page title
            'Invoicing',                          // Menu title
            'manage_woocommerce',                 // Capability
            'itsi-invoicing',                     // Slug
            [$this, 'render_settings_page'],      // Callback
            'dashicons-media-document',           // Icon
            56                                    // Position
        );
    }

    public function render_settings_page() {
        include plugin_dir_path(__FILE__) . '../admin/settings-page.php';
    }

    function render_invoice_page2() {
            if ( ! current_user_can('manage_woocommerce') ) {
                wp_die('No permission');
            }

            $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
            $action   = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

            if ( ! $order_id ) {
                echo "<p>No order selected.</p>";
                return;
            }

            $order = wc_get_order($order_id);

            if ( ! $order ) {
                echo "<p>Invalid order.</p>";
                return;
            }

            ob_start();
            include plugin_dir_path(__FILE__) . 'templates/invoice-template.php';
            $html = ob_get_clean();

            if ( $action === 'download' ) {
                // Generate PDF
                require_once __DIR__ . '/vendor/autoload.php';
                $dompdf = new Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $dompdf->stream("invoice-{$order_id}.pdf", ["Attachment" => true]);
                exit;
            } elseif ( $action === 'print' ) {
                // Show in browser + auto print
                echo $html;
                echo "<script>window.onload = function(){ window.print(); }</script>";
                exit;
            } else {
                echo "<p>Please select Print or Download.</p>";
            }
        }

    public function render_invoice_pdf() {
        $order_id       = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $invoice_number = isset($_GET['invoice']) ? sanitize_text_field($_GET['invoice']) : '';

        if ( ! $order_id || ! $invoice_number ) {
            wp_die( 'Invalid invoice request.' );
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            wp_die( 'Invalid order ID.' );
        }

        // Capture template output
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Invoice <?php echo esc_html($invoice_number); ?></title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 14px; }
                h1 { font-size: 20px; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; }
                th { background: #f5f5f5; text-align: left; }
            </style>
        </head>
        <body>
            <?php include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/shipping_label.php'; ?>
        </body>
        </html>

        <?php
        $html = ob_get_clean();

        if ( empty( $html ) ) {
            wp_die( 'Invoice template returned no content.' );
        }

        // Load Dompdf
        if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
        }

        try {
           
            // $dompdf = new \Dompdf\Dompdf();
            // $dompdf->loadHtml( $html );
            // $dompdf->setPaper( 'A4', 'portrait' );
            // $dompdf->render();

            // // Stream to browser
            // $dompdf->stream( 'invoice-' . $order_id . '.pdf', [
            //     'Attachment' => true
            // ]);
            exit;
        } catch ( Exception $e ) {
            wp_die( 'PDF generation failed: ' . $e->getMessage() );
        }
    }

    // public function render_invoice_pdf() {
    //     if ( ! isset( $_GET['order_id'] ) ) {
    //         wp_die( 'No order ID provided.' );
    //     }

    //     $order_id       = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    //     $invoice_number = isset($_GET['invoice']) ? sanitize_text_field($_GET['invoice']) : '';
    //     $order    = wc_get_order( $order_id );

    //     if ( ! $order ) {
    //         wp_die( 'Invalid order ID.' );
    //     }

    //     // Capture template output
    //     ob_start();
    //     include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/shipping_label.php';
    //     $html = ob_get_clean();

    //     if ( empty( $html ) ) {
    //         wp_die( 'Invoice template returned no content.' );
    //     }

    //     // Load Dompdf
    //     if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
    //         require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
    //     }

    //     try {
	// 		if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
	// 			wp_die('Dompdf class not found — check vendor/autoload.php');
	// 		}
	// 		// echo $html;
	// 		// exit;
    //         $dompdf = new \Dompdf\Dompdf();
    //         $dompdf->loadHtml( $html );
    //         $dompdf->setPaper( 'A4', 'portrait' );
    //         $dompdf->render();

    //         // Send to browser
    //         $dompdf->stream( 'invoice-' . $order_id . '.pdf', [
    //             'Attachment' => true
    //         ]);
    //         exit;
    //     } catch ( Exception $e ) {
    //         wp_die( 'PDF generation failed: ' . $e->getMessage() );
    //     }
    // }


    public function register_print_button() {
        // no submenu, just the button in order screen (we already did this)
    }

    // public function register_print_page() {
    //     add_submenu_page(
    //         null, // hidden page
    //         'Invoice Print',
    //         'Invoice Print',
    //         'manage_woocommerce',
    //         'itsi-invoice',
    //         [ $this, 'render_invoice_page' ]
    //     );
    // }

    public function render_invoice_page() {
        $order_id       = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $invoice_number = isset($_GET['invoice']) ? sanitize_text_field($_GET['invoice']) : '';
        $mode           = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : '';

        if ( ! $order_id || ! $invoice_number ) {
            wp_die( 'Invalid invoice request.' );
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            wp_die( 'Order not found.' );
        }

        // Get template
        $template = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/shipping_label.php';

        // Start standalone HTML
        echo '<!DOCTYPE html><html><head>';
        echo '<meta charset="UTF-8">';
        echo '<title>Invoice ' . esc_html( $invoice_number ) . '</title>';
        echo '<style>
            @media print {
                @page { margin: 0; }
                body { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
            body { font-family: Arial, sans-serif; margin: 20px; background:#fff; }
        </style>';
        echo '</head><body>';

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div style="color:red;">Invoice template missing.</div>';
        }

        // Auto trigger print if mode=print
        if ( $mode === 'print' ) {
            echo "<script>window.onload = function(){ window.print(); }</script>";
        }

        echo '</body></html>';
        exit;
    }


    public function maybe_add_box( $post_type, $post ) {
        global $current_screen;
            echo "<br>";
            echo "current_screen id is";
            echo "<br>";
            echo $current_screen->id;
        if ( isset( $current_screen->id ) && $current_screen->id === 'woocommerce_page_wc-orders' ) {
            add_meta_box(
                'it-smart-invoicing-box',
                __( 'Invoice/Packing', 'it-smart-invoicing' ),
                [ $this, 'render_box' ],
                $current_screen->id,
                'side',
                'default'
            );
        }
    }


    // public function maybe_add_box( $post_type ) {
    //     echo $post_type;
    //     echo "<br>";
    //     echo "THIS IS TYPE";
    //     if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
    //         echo 'FeaturesUtil not found';
    //         return; // Not HPOS
    //     }
    //     // if ( $post_type === 'shop_order' || $post_type === 'wc_order' ) {
    //         $this->add_box();
    //     // }
    // }

    public function add_box() {
        echo "<br>";
        echo "add_box Called";
        add_meta_box(
            'it-smart-invoicing-box',
            __( 'Invoicing', 'it-smart-invoicing' ),
            [ $this, 'render_box' ],
            'shop_order',
            'side',
            'default'
        );
    }
    public function render_box($post) {
		global $wpdb;
		 // Handle WC_Order or WP_Post
		if ( is_a( $post, 'WC_Order' ) ) {
			$order_id = $post->get_id();
		} else {
			$order_id = $post->ID;
		}
        
        $invoice_table = $wpdb->prefix . 'itsi_invoices';
        $invoice = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $invoice_table WHERE order_id = %d",
            $order_id
        ) );

        if ( $invoice ) {
            $invoice_number = $invoice->invoice_number;

            echo '<p><strong>Invoice Number:</strong> ' . esc_html( $invoice_number ) . '</p>';

            $print_url   = admin_url( 'admin.php?page=itsi-view-invoice&order_id=' . $order_id . '&invoice=' . $invoice_number . '&mode=print' );
            $download_url = admin_url( 'admin-post.php?action=itsi_download_invoice&order_id=' . $order_id. '&invoice=' . $invoice_number );

            echo '<a href="' . esc_url( $print_url ) . '" target="_blank" class="button">Print Invoice</a> ';
            echo '<a href="' . esc_url( $download_url ) . '" class="button button-primary">Download PDF</a>';

        } else {
            $generate_url = admin_url( 'admin-post.php?action=itsi_generate_invoice&order_id=' . $order_id );
            echo '<a href="' . esc_url( $generate_url ) . '" class="button button-primary">Generate Invoice</a>';
        }
    }

    /*public function render_box($post) {
        $order_id = $post->ID;
        $generate_url = wp_nonce_url( admin_url("admin-post.php?action=itsi_generate_invoice&order_id=$order_id"), 'itsi_generate_invoice' );
        echo '<a href="' . esc_url($generate_url) . '" class="button button-primary">Generate Invoice</a>';
    }*/
    

    /* public function render_box( $post ) {
        $order_id = $post->id;
        ?>

        <div class="wrap">
            <h1>Order Actions</h1>
            <?php if ($order_id): ?>
                <a href="<?php echo admin_url( 'admin-post.php?action=itsi_invoice&order_id=' . $order_id ); ?>"><?php esc_html_e( 'Print Invoice', 'it-smart-invoicing' ); ?></a>
                <a href="<?php echo admin_url("admin.php?page=print_invoice&order_id=$order_id&action=download"); ?>" target="_blank" class="button">Download PDF</a>
            <?php else: ?>
                <p>Please select an order.</p>
            <?php endif; ?>
        </div>
        <?php
    } */
}
