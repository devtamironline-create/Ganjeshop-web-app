<?php
/**
 * Shipped order email (HTML).
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

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php
	printf(
		/* translators: %s: customer first name */
		esc_html__( '%s عزیز،', 'tamironline-order-statuses' ),
		esc_html( $order->get_billing_first_name() )
	);
?></p>

<p><?php
	printf(
		/* translators: %s: order number */
		esc_html__( 'سفارش شما به شماره %s ارسال شد و به‌زودی به دست شما خواهد رسید.', 'tamironline-order-statuses' ),
		esc_html( $order->get_order_number() )
	);
?></p>

<?php

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
