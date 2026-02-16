<?php
/**
 * Shipped order email (plain text).
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var string   $additional_content
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 * @var WC_Email $email
 *
 * @package Tamironline_Order_Statuses
 */

defined( 'ABSPATH' ) || exit;

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

printf(
	/* translators: %s: customer first name */
	esc_html__( '%s عزیز،', 'tamironline-order-statuses' ),
	esc_html( $order->get_billing_first_name() )
);

echo "\n\n";

printf(
	/* translators: %s: order number */
	esc_html__( 'سفارش شما به شماره %s ارسال شد و به‌زودی به دست شما خواهد رسید.', 'tamironline-order-statuses' ),
	esc_html( $order->get_order_number() )
);

echo "\n\n";

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

echo "\n";

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n";

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n";
}
