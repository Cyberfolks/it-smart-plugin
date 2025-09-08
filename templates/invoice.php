<?php
/**
 * Invoice Template
 * 
 * @var WC_Order $order
 */

if ( ! isset( $order ) || ! $order instanceof WC_Order ) {
    return;
}

$billing = $order->get_address( 'billing' );
$items   = $order->get_items();
?>
