<?php
/** @var WC_Order $order_obj */
/** @var array|null $custom_meta */
/** @var array|null $invoice_record */
?>
<!doctype html>
<html>
    <head>
        <meta charset="<?php echo esc_attr( get_bloginfo('charset') ); ?>">
        <title>Invoice #<?php echo esc_html( $invoice_record['invoice_number'] ?? $order_obj->get_order_number() ); ?></title>
        <style>
            body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;padding:24px}
            h1,h2,h3{margin:0 0 12px}
            table{width:100%;border-collapse:collapse;margin-top:12px}
            th,td{border:1px solid #ddd;padding:8px;text-align:left}
            .totals td{font-weight:600}
            .muted{color:#666}
            .meta{margin:12px 0}
            .print-note{margin-top:16px;font-size:12px;color:#666}
        </style>
    </head>
    <body onload="window.print()">
        <h1>Invoice</h1>
        <div class="meta">
            <div><strong>Invoice #:</strong> <?php echo esc_html( $invoice_record['invoice_number'] ?? 'N/A' ); ?></div>
            <div><strong>Order #:</strong> <?php echo esc_html( $order_obj->get_order_number() ); ?></div>
            <div><strong>Date:</strong> <?php echo esc_html( wc_format_datetime( $order_obj->get_date_created() ) ); ?></div>
        </div>

        <h3>Billing</h3>
        <div class="muted">
            <?php echo wp_kses_post( $order_obj->get_formatted_billing_full_name() ); ?><br>
            <?php echo wp_kses_post( $order_obj->get_formatted_billing_address() ); ?><br>
            <?php echo esc_html( $order_obj->get_billing_email() ); ?>
        </div>

        <h3>Items</h3>
        <table>
            <thead><tr><th>Product</th><th>Qty</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ( $order_obj->get_items() as $item ) : ?>
                <tr>
                <td><?php echo esc_html( $item->get_name() ); ?></td>
                <td><?php echo esc_html( $item->get_quantity() ); ?></td>
                <td><?php echo wp_kses_post( $order_obj->get_formatted_line_subtotal( $item ) ); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot class="totals">
            <tr><td colspan="2">Subtotal</td><td><?php echo wp_kses_post( wc_price( $order_obj->get_subtotal(), ['currency' => $order_obj->get_currency()] ) ); ?></td></tr>
            <tr><td colspan="2">Shipping</td><td><?php echo wp_kses_post( wc_price( $order_obj->get_shipping_total(), ['currency' => $order_obj->get_currency()] ) ); ?></td></tr>
            <tr><td colspan="2">Tax</td><td><?php echo wp_kses_post( wc_price( $order_obj->get_total_tax(), ['currency' => $order_obj->get_currency()] ) ); ?></td></tr>
            <tr><td colspan="2">Total</td><td><?php echo wp_kses_post( wc_price( $order_obj->get_total(), ['currency' => $order_obj->get_currency()] ) ); ?></td></tr>
            </tfoot>
        </table>

        <?php if ( ! empty( $custom_meta ) ) : ?>
            <h3>Extra (from dci_wc_orders_meta)</h3>
            <pre class="muted" style="white-space:pre-wrap;"><?php echo esc_html( print_r( $custom_meta, true ) ); ?></pre>
        <?php endif; ?>

        <p class="print-note">This is a printable view. Close this tab after printing.</p>
    </body>
</html>
