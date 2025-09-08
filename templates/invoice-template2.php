<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #<?php echo $order->get_order_number(); ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { margin-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .meta { margin-top: 20px; }
        .order-header { margin-bottom: 20px; }
        .no-print { margin-top: 20px; }
    </style>
</head>
<body>

    <div class="order-header">
        <h1>Invoice #<?php echo $order->get_order_number(); ?></h1>
        <p><strong>Date:</strong> <?php echo wc_format_datetime( $order->get_date_created() ); ?></p>
        <p><strong>Billing:</strong> <?php echo $order->get_formatted_billing_full_name(); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Custom Data</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $order->get_items() as $item_id => $item ) : ?>
            <tr>
                <td><?php echo esc_html( $item->get_name() ); ?></td>
                <td><?php echo esc_html( $item->get_quantity() ); ?></td>
                <td><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
                <td>
                    <?php
                    $custom_keys = [
                        '_Square Metres',
                        '_Number of packs required',
                        '_Square Metres Covered',
                        '_Price'
                    ];
                    foreach ( $custom_keys as $key ) {
                        $val = $item->get_meta( $key );
                        if ( $val ) {
                            echo '<div><strong>' . esc_html( $key ) . ':</strong> ' . esc_html( $val ) . '</div>';
                        }
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="no-print">
        <button onclick="window.print()">🖨 Print Invoice</button>
        <a href="<?php echo admin_url( 'admin-post.php?action=itsi_invoice_pdf&order_id=' . $order->get_id() ); ?>" class="button">
            📄 Download PDF
        </a>
    </div>

</body>
</html>
