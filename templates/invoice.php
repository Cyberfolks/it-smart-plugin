<?php
/**
 * Invoice Template
 * 
 * @var WC_Order $order
 */

if ( ! isset( $order ) || ! $order instanceof WC_Order ) {
    echo '<div class="notice notice-error"><p>WC_Order, order.</p></div>';
    return;
}

$billing = $order->get_address( 'billing' );
$items   = $order->get_items();
?>
