<?php
/**
 * Plugin Name: Tamironline Order Statuses
 * Plugin URI:  https://tamironline.ir
 * Description: وضعیت‌های سفارشی سفارش برای همگام‌سازی پنل انبار تامیرآنلاین با ووکامرس
 * Version:     1.0.0
 * Author:      Tamironline
 * Author URI:  https://tamironline.ir
 * Text Domain: tamironline-order-statuses
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 9.5
 *
 * @package Tamironline_Order_Statuses
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare HPOS compatibility.
 */
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/* ───────────────────────────────────────────────
 * 1. Define custom statuses
 * ─────────────────────────────────────────────── */

/**
 * Return the array of custom statuses used throughout the plugin.
 *
 * @return array slug => label
 */
function tamironline_custom_order_statuses(): array {
	return array(
		'wc-supply-wait' => __( 'در انتظار تامین', 'tamironline-order-statuses' ),
		'wc-packed'      => __( 'در انتظار اسکن خروج', 'tamironline-order-statuses' ),
		'wc-shipped'     => __( 'ارسال شده', 'tamironline-order-statuses' ),
		'wc-returned'    => __( 'مرجوعی', 'tamironline-order-statuses' ),
	);
}

/* ───────────────────────────────────────────────
 * 2. Register post statuses
 * ─────────────────────────────────────────────── */

add_action( 'init', function () {
	foreach ( tamironline_custom_order_statuses() as $slug => $label ) {
		register_post_status( $slug, array(
			'label'                     => $label,
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of orders */
			'label_count'               => _n_noop(
				$label . ' <span class="count">(%s)</span>',
				$label . ' <span class="count">(%s)</span>',
				'tamironline-order-statuses'
			),
		) );
	}
} );

/* ───────────────────────────────────────────────
 * 3. Add to WooCommerce order status list
 * ─────────────────────────────────────────────── */

add_filter( 'wc_order_statuses', function ( array $statuses ): array {
	$custom = tamironline_custom_order_statuses();

	// Insert custom statuses after "processing"
	$new = array();
	foreach ( $statuses as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'wc-processing' === $key ) {
			foreach ( $custom as $cs_key => $cs_label ) {
				$new[ $cs_key ] = $cs_label;
			}
		}
	}

	// Fallback: if "processing" was not found, append at end
	if ( count( $new ) === count( $statuses ) ) {
		$new = array_merge( $new, $custom );
	}

	return $new;
} );

/* ───────────────────────────────────────────────
 * 4. Bulk actions
 * ─────────────────────────────────────────────── */

add_filter( 'bulk_actions-edit-shop_order', 'tamironline_add_bulk_actions' );
add_filter( 'bulk_actions-woocommerce_page_wc-orders', 'tamironline_add_bulk_actions' );

function tamironline_add_bulk_actions( array $actions ): array {
	foreach ( tamironline_custom_order_statuses() as $slug => $label ) {
		$status_key = str_replace( 'wc-', '', $slug );
		$actions[ 'mark_' . $status_key ] = sprintf(
			/* translators: %s: order status label */
			__( 'تغییر وضعیت به %s', 'tamironline-order-statuses' ),
			$label
		);
	}
	return $actions;
}

/* ───────────────────────────────────────────────
 * 5. Admin CSS — colour badges
 * ─────────────────────────────────────────────── */

add_action( 'admin_head', function () {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	$allowed = array( 'edit-shop_order', 'shop_order', 'woocommerce_page_wc-orders' );
	if ( ! in_array( $screen->id, $allowed, true ) ) {
		return;
	}
	?>
	<style>
		/* ── Supply Wait (amber/orange) ── */
		.order-status.status-supply-wait,
		.wc-orders-list-table .order-status.status-supply-wait,
		mark.order-status.status-supply-wait {
			background: #FFF3E0;
			color: #E65100;
			border-color: #FFB74D;
		}
		mark.order-status.status-supply-wait::after {
			content: "\25CF";
			color: #FB8C00;
			margin-inline-end: 6px;
		}

		/* ── Packed (cyan/teal) ── */
		.order-status.status-packed,
		.wc-orders-list-table .order-status.status-packed,
		mark.order-status.status-packed {
			background: #E0F7FA;
			color: #006064;
			border-color: #4DD0E1;
		}
		mark.order-status.status-packed::after {
			content: "\25CF";
			color: #00BCD4;
			margin-inline-end: 6px;
		}

		/* ── Shipped (indigo/blue) ── */
		.order-status.status-shipped,
		.wc-orders-list-table .order-status.status-shipped,
		mark.order-status.status-shipped {
			background: #E8EAF6;
			color: #1A237E;
			border-color: #7986CB;
		}
		mark.order-status.status-shipped::after {
			content: "\25CF";
			color: #3F51B5;
			margin-inline-end: 6px;
		}

		/* ── Returned (red) ── */
		.order-status.status-returned,
		.wc-orders-list-table .order-status.status-returned,
		mark.order-status.status-returned {
			background: #FFEBEE;
			color: #B71C1C;
			border-color: #EF9A9A;
		}
		mark.order-status.status-returned::after {
			content: "\25CF";
			color: #F44336;
			margin-inline-end: 6px;
		}
	</style>
	<?php
} );

/* ───────────────────────────────────────────────
 * 6. Reports — include custom statuses in reports
 * ─────────────────────────────────────────────── */

add_filter( 'woocommerce_reports_order_statuses', function ( array $statuses ): array {
	return array_merge( $statuses, array( 'supply-wait', 'packed', 'shipped', 'returned' ) );
} );

/* ───────────────────────────────────────────────
 * 7. REST API — ensure statuses work via WC REST API
 *    WooCommerce REST API automatically supports any
 *    status registered via wc_order_statuses filter.
 *    This section adds validation so "supply-wait" etc.
 *    are explicitly allowed.
 * ─────────────────────────────────────────────── */

add_filter( 'woocommerce_rest_shop_order_object_query', function ( array $args, \WP_REST_Request $request ): array {
	$status = $request->get_param( 'status' );
	if ( $status ) {
		$custom_slugs = array( 'supply-wait', 'packed', 'shipped', 'returned' );
		$requested    = is_array( $status ) ? $status : array( $status );
		foreach ( $requested as $s ) {
			if ( in_array( $s, $custom_slugs, true ) ) {
				$args['post_status'] = array_map( function ( $st ) {
					return strpos( $st, 'wc-' ) === 0 ? $st : 'wc-' . $st;
				}, $requested );
				break;
			}
		}
	}
	return $args;
}, 10, 2 );

/* ───────────────────────────────────────────────
 * 8. Email notification for "shipped" status
 * ─────────────────────────────────────────────── */

/**
 * Register the shipped email class.
 */
add_filter( 'woocommerce_email_classes', function ( array $emails ): array {
	$emails['WC_Email_Shipped_Order'] = new Tamironline_Email_Shipped();
	return $emails;
} );

/**
 * Map status transitions to email triggers.
 */
add_action( 'woocommerce_order_status_shipped', function ( int $order_id, \WC_Order $order ) {
	// Trigger the shipped email
	$mailer = WC()->mailer();
	$emails = $mailer->get_emails();
	if ( isset( $emails['WC_Email_Shipped_Order'] ) ) {
		$emails['WC_Email_Shipped_Order']->trigger( $order_id, $order );
	}
}, 10, 2 );

/**
 * Shipped email class.
 */
class Tamironline_Email_Shipped extends WC_Email {

	public function __construct() {
		$this->id             = 'shipped_order';
		$this->customer_email = true;
		$this->title          = __( 'سفارش ارسال شد', 'tamironline-order-statuses' );
		$this->description    = __( 'این ایمیل هنگام ارسال سفارش به مشتری فرستاده می‌شود.', 'tamironline-order-statuses' );
		$this->template_base  = plugin_dir_path( __FILE__ ) . 'templates/';
		$this->template_html  = 'emails/shipped-order.php';
		$this->template_plain = 'emails/plain/shipped-order.php';
		$this->placeholders   = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);

		// Default heading / subject
		$this->heading = __( 'سفارش شما ارسال شد', 'tamironline-order-statuses' );
		$this->subject = __( 'سفارش #{order_number} شما ارسال شد', 'tamironline-order-statuses' );

		parent::__construct();

		$this->recipient = ''; // customer only
	}

	/**
	 * Trigger the email.
	 */
	public function trigger( int $order_id, $order = null ): void {
		$this->setup_locale();

		if ( $order_id && ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order ) {
			return;
		}

		$this->object    = $order;
		$this->recipient = $order->get_billing_email();

		$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
		$this->placeholders['{order_number}'] = $order->get_order_number();

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}

		$this->restore_locale();
	}

	/**
	 * HTML email content.
	 */
	public function get_content_html(): string {
		$template = $this->template_base . $this->template_html;
		if ( ! file_exists( $template ) ) {
			return $this->get_content_fallback();
		}
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Plain text email content.
	 */
	public function get_content_plain(): string {
		$template = $this->template_base . $this->template_plain;
		if ( ! file_exists( $template ) ) {
			return $this->get_content_fallback_plain();
		}
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Fallback HTML content when template file is missing.
	 */
	private function get_content_fallback(): string {
		$order   = $this->object;
		$heading = $this->get_heading();

		ob_start();
		do_action( 'woocommerce_email_header', $heading, $this );
		?>
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
		do_action( 'woocommerce_email_order_details', $order, false, false, $this );
		do_action( 'woocommerce_email_order_meta', $order, false, false, $this );
		do_action( 'woocommerce_email_customer_details', $order, false, false, $this );
		do_action( 'woocommerce_email_footer', $this );

		return ob_get_clean();
	}

	/**
	 * Fallback plain text content.
	 */
	private function get_content_fallback_plain(): string {
		$order = $this->object;
		$text  = $this->get_heading() . "\n\n";
		$text .= sprintf(
			__( '%s عزیز، سفارش شما به شماره %s ارسال شد.', 'tamironline-order-statuses' ),
			$order->get_billing_first_name(),
			$order->get_order_number()
		);
		return $text;
	}

	/**
	 * Default additional content.
	 */
	public function get_default_additional_content(): string {
		return __( 'با تشکر از خرید شما.', 'tamironline-order-statuses' );
	}
}

/* ───────────────────────────────────────────────
 * 9. Admin order list — add status filter dropdown
 * ─────────────────────────────────────────────── */

add_action( 'restrict_manage_posts', function ( string $post_type ) {
	if ( 'shop_order' !== $post_type ) {
		return;
	}
	$custom   = tamironline_custom_order_statuses();
	$current  = isset( $_GET['post_status'] ) ? sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) : '';
	foreach ( $custom as $slug => $label ) {
		$count = wp_count_posts( 'shop_order' )->{$slug} ?? 0;
		printf(
			'<option value="%s" %s>%s (%d)</option>',
			esc_attr( $slug ),
			selected( $current, $slug, false ),
			esc_html( $label ),
			(int) $count
		);
	}
}, 20 );

/* ───────────────────────────────────────────────
 * 10. Allow status transitions from/to custom statuses
 * ─────────────────────────────────────────────── */

add_filter( 'woocommerce_valid_order_statuses_for_payment', function ( array $statuses ): array {
	$statuses[] = 'supply-wait';
	return $statuses;
} );

add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', function ( array $statuses ): array {
	$statuses[] = 'supply-wait';
	$statuses[] = 'packed';
	$statuses[] = 'shipped';
	return $statuses;
} );

/* ───────────────────────────────────────────────
 * 11. My Account — show custom statuses on frontend
 * ─────────────────────────────────────────────── */

add_filter( 'woocommerce_my_account_my_orders_query', function ( array $args ): array {
	if ( isset( $args['status'] ) && is_array( $args['status'] ) ) {
		$args['status'] = array_merge( $args['status'], array(
			'supply-wait',
			'packed',
			'shipped',
			'returned',
		) );
	}
	return $args;
} );
