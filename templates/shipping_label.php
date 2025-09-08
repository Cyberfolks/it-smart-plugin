<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #<?php echo $order->get_id(); ?></title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 0;
            max-width: 800px;
        }
        .invoice-box {
            width: 800px;
            padding: 20px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #0d47a1;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header .logo {
            font-size: 26px;
            color: #0d47a1;
            font-weight: bold;
        }
        .company-info {
            text-align: left;
            font-size: 12px;
        }
        .invoice-meta {
            margin: 15px 0;
            font-size: 14px;
            float: inline-end;
        }
        .billing {
            margin-bottom: 20px;
        }
        .billing strong {
            display: block;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }
        table th {
            background: #f5f5f5;
        }
        .totals {
            width: 500px;
        }
        .totals td {
            text-align: right;
        }
        .totals strong {
            font-size: 13px;
        }
        .no-print { margin-top: 20px; }
        .logo_class {
            width:170px;
            height:auto;
        }
    </style>
</head>
    <body>
    <div class="invoice-box">

        <!-- Header -->
        <div class="header">
            <div class="logo">
                <div class="row">
                    <p class="heading">INVOICE</p>
                    <img src="https://directflooringonline.co.uk/wp-content/uploads/2024/02/Direct-Flooring-Online-Logo.png" class="logo_class">

                </div>
            </div>
            <div class="company-info">
                <strong>From</strong><br>
                Direct Flooring Online<br>
                Unit 6 Maple Park<br>
                Essex Road, Hoddesdon<br>
                EN11 0EX<br>
                01992 563394<br>
                GB333385406
            </div>
        </div>

        <!-- Invoice Meta -->
        <div class="invoice-meta">
            <strong>Invoice no:</strong> <?php echo $order->get_id(); ?><br>
            <strong>Order date:</strong> <?php echo wc_format_datetime( $order->get_date_created() ); ?>
        </div>
        <!-- Billing -->
        <div class="billing">
            <strong>Bill to</strong><br>
            <?php echo esc_html( $billing['first_name'] . ' ' . $billing['last_name'] ); ?><br>
            <?php echo esc_html( $billing['address_1'] ); ?><br>
            <?php echo esc_html( $billing['city'] ); ?>, <?php echo esc_html( $billing['state'] ); ?><br>
            <?php echo esc_html( $billing['postcode'] ); ?><br>
            <?php echo esc_html( $billing['email'] ); ?><br>
            <?php echo esc_html( $billing['phone'] ); ?>
        </div>
        <!-- <div class="Shipping">
            <strong>Bill to</strong><br>
            <?php echo esc_html( $billing['first_name'] . ' ' . $billing['last_name'] ); ?><br>
            <?php echo esc_html( $billing['address_1'] ); ?><br>
            <?php echo esc_html( $billing['city'] ); ?>, <?php echo esc_html( $billing['state'] ); ?><br>
            <?php echo esc_html( $billing['postcode'] ); ?><br>
            <?php echo esc_html( $billing['email'] ); ?><br>
            <?php echo esc_html( $billing['phone'] ); ?>
        </div> -->

        <!-- Items -->
        <table>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Product</th>
                    <th>Details</th>
                    <th>Quantity</th>
                    <th>Unit price</th>
                    <!--  <th>Total price</th> -->
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ( $items as $item_id => $item ) : 
                    $product = $item->get_product();
                ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo esc_html( $item->get_name() ); ?></td>
                        <td><?php
                        $custom_keys = [
                            '_Square Metres',
                            '_Number of packs required',
                            '_Square Metres Covered',
                            // '_Price'
                        ];
                        foreach ( $custom_keys as $key ) {
                            $val = $item->get_meta( $key );
                            if ( $val ) {
                                echo '<div>'.esc_html(str_replace("_", "", $key)).': <b>'.esc_html($val).'</b></div>';
                            }
                        }
                        ?></td>
                        <td><?php echo esc_html( $item->get_quantity() ); ?></td>
                        <td><?php echo wc_price( $order->get_item_subtotal( $item, false, true ) ); ?></td>
                    <!--  <td><?php echo wc_price( $order->get_line_total( $item, true, true ) ); ?></td> -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals">
            <tbody>
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td><?php echo wc_price( $order->get_subtotal() ); ?></td>
                </tr>
                <tr>
                    <td><strong>Shipping</strong></td>
                    <td><?php echo wp_kses_post( $order->get_shipping_to_display() ); ?></td>
                </tr>
                <?php if ( $order->get_discount_total() > 0 ) : ?>
                <tr>
                    <td><strong>Discount</strong></td>
                    <td>-<?php echo wc_price( $order->get_discount_total() ); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Tax</strong></td>
                    <td><?php echo wc_price( $order->get_total_tax() ); ?></td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong><?php echo wc_price( $order->get_total() ); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
        <div class="no-print">
            <button onclick="window.print()">🖨 Print Invoice</button>
            <a href="<?php echo admin_url( 'admin-post.php?action=itsi_invoice_pdf&order_id=' . $order->get_id() ); ?>" class="button">
                📄 Download PDF
            </a>
        </div>
    </body>
</html>
